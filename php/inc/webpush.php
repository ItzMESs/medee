<?php
// Self-hosted Web Push (no third-party service): VAPID (RFC 8292) authentication and
// aes128gcm payload encryption (RFC 8291 / RFC 8188), using only PHP's openssl + hash.

function b64u_encode($bin)
{
    return rtrim(strtr(base64_encode($bin), '+/', '-_'), '=');
}

function b64u_decode($str)
{
    $str = strtr((string) $str, '-_', '+/');
    return base64_decode($str . str_repeat('=', (4 - strlen($str) % 4) % 4));
}

function push_supported()
{
    return function_exists('openssl_pkey_derive') && function_exists('hash_hkdf') && function_exists('curl_init')
        && in_array('aes-128-gcm', openssl_get_cipher_methods(), true);
}

function push_enabled()
{
    return setting('push_enabled') === '1' && push_supported();
}

// Uncompressed P-256 point (0x04 || X || Y) from an openssl key.
function ec_public_raw($key)
{
    $d = openssl_pkey_get_details($key);
    return "\x04" . str_pad($d['ec']['x'], 32, "\0", STR_PAD_LEFT) . str_pad($d['ec']['y'], 32, "\0", STR_PAD_LEFT);
}

// PEM public key from a raw uncompressed P-256 point (the browser's p256dh key).
function ec_public_pem($raw)
{
    $der = hex2bin('3059301306072a8648ce3d020106082a8648ce3d030107034200') . $raw;
    return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
}

function new_ec_key()
{
    return openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
}

// Creates the site's VAPID key pair on first use; returns [publicKeyB64u, privateKeyPem].
function vapid_keys()
{
    $pub = setting('vapid_public');
    $priv = setting('vapid_private');
    if ($pub === '' || $priv === '') {
        $key = new_ec_key();
        openssl_pkey_export($key, $priv);
        $pub = b64u_encode(ec_public_raw($key));
        save_setting('vapid_private', $priv);
        save_setting('vapid_public', $pub);
        settings(true);
    }
    return [$pub, $priv];
}

// openssl_sign gives a DER ECDSA signature; JWT ES256 wants raw r || s (32 bytes each).
function der_to_raw_signature($der)
{
    $pos = 2;
    $out = '';
    for ($i = 0; $i < 2; $i++) {
        $len = ord($der[$pos + 1]);
        $int = substr($der, $pos + 2, $len);
        $out .= str_pad(ltrim($int, "\0"), 32, "\0", STR_PAD_LEFT);
        $pos += 2 + $len;
    }
    return $out;
}

function vapid_authorization($endpoint)
{
    list($pub, $priv) = vapid_keys();
    $parts = parse_url($endpoint);
    $aud = $parts['scheme'] . '://' . $parts['host'] . (isset($parts['port']) ? ':' . $parts['port'] : '');
    $email = setting('contact_email');
    $sub = $email !== '' ? 'mailto:' . $email : rtrim(setting('site_url'), '/');
    $input = b64u_encode(json_encode(['typ' => 'JWT', 'alg' => 'ES256'])) . '.'
        . b64u_encode(json_encode(['aud' => $aud, 'exp' => time() + 12 * 3600, 'sub' => $sub]));
    openssl_sign($input, $der, $priv, OPENSSL_ALGO_SHA256);
    return 'vapid t=' . $input . '.' . b64u_encode(der_to_raw_signature($der)) . ', k=' . $pub;
}

// RFC 8291 message encryption; returns the aes128gcm body for one subscription.
function push_encrypt($payload, $p256dhB64u, $authB64u)
{
    $uaPublic = b64u_decode($p256dhB64u);
    $authSecret = b64u_decode($authB64u);
    if (strlen($uaPublic) !== 65 || strlen($authSecret) !== 16) {
        throw new RuntimeException('Буруу subscription түлхүүр.');
    }
    $local = new_ec_key();
    $asPublic = ec_public_raw($local);
    $ecdh = openssl_pkey_derive(openssl_pkey_get_public(ec_public_pem($uaPublic)), $local, 32);
    if ($ecdh === false) {
        throw new RuntimeException('ECDH амжилтгүй.');
    }
    $ikm = hash_hkdf('sha256', $ecdh, 32, "WebPush: info\0" . $uaPublic . $asPublic, $authSecret);
    $salt = random_bytes(16);
    $cek = hash_hkdf('sha256', $ikm, 16, "Content-Encoding: aes128gcm\0", $salt);
    $nonce = hash_hkdf('sha256', $ikm, 12, "Content-Encoding: nonce\0", $salt);
    $tag = '';
    $cipher = openssl_encrypt($payload . "\x02", 'aes-128-gcm', $cek, OPENSSL_RAW_DATA, $nonce, $tag);
    return $salt . pack('N', 4096) . chr(65) . $asPublic . $cipher . $tag;
}

// Sends $data (title, body, url) to every subscriber in parallel. Returns [sent, failed].
function push_broadcast(array $data)
{
    if (!push_enabled()) {
        return [0, 0];
    }
    $payload = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    $sent = 0;
    $failed = 0;
    $subs = rows('SELECT * FROM push_subscriptions');
    foreach (array_chunk($subs, 50) as $chunk) {
        $mh = curl_multi_init();
        $handles = [];
        foreach ($chunk as $sub) {
            try {
                $body = push_encrypt($payload, $sub['p256dh'], $sub['auth']);
            } catch (Exception $ex) {
                $failed++;
                continue;
            }
            $ch = curl_init($sub['endpoint']);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_HTTPHEADER => [
                    'Authorization: ' . vapid_authorization($sub['endpoint']),
                    'Content-Encoding: aes128gcm',
                    'Content-Type: application/octet-stream',
                    'TTL: 86400',
                    'Urgency: high',
                ],
            ]);
            curl_multi_add_handle($mh, $ch);
            $handles[(int) $sub['id']] = $ch;
        }
        do {
            $status = curl_multi_exec($mh, $running);
            if ($running) {
                curl_multi_select($mh, 1.0);
            }
        } while ($running && $status === CURLM_OK);
        foreach ($handles as $id => $ch) {
            $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code >= 200 && $code < 300) {
                $sent++;
                q('UPDATE push_subscriptions SET last_sent_at = ? WHERE id = ?', [now(), $id]);
            } else {
                $failed++;
                // The browser unsubscribed or the subscription expired.
                if ($code === 404 || $code === 410) {
                    q('DELETE FROM push_subscriptions WHERE id = ?', [$id]);
                }
            }
            curl_multi_remove_handle($mh, $ch);
            curl_close($ch);
        }
        curl_multi_close($mh);
    }
    return [$sent, $failed];
}

function push_article(array $article)
{
    $result = push_broadcast([
        'title' => $article['title'],
        'body' => $article['excerpt'] !== '' ? mb_substr($article['excerpt'], 0, 160) : setting('site_name'),
        'url' => absolute_url(article_path($article)),
        'image' => $article['image'] ? absolute_url($article['image']) : null,
        'tag' => 'article-' . $article['id'],
    ]);
    q('INSERT INTO push_log (article_id, sent_at, sent, failed) VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE sent_at = VALUES(sent_at), sent = VALUES(sent), failed = VALUES(failed)',
        [$article['id'], now(), $result[0], $result[1]]);
    return $result;
}

// ---------- Public endpoints ----------

function push_json_input()
{
    $data = json_decode((string) file_get_contents('php://input'), true);
    return is_array($data) ? $data : [];
}

function push_subscribe()
{
    header('Content-Type: application/json; charset=utf-8');
    $d = push_json_input();
    $endpoint = isset($d['endpoint']) ? (string) $d['endpoint'] : '';
    $p256dh = isset($d['keys']['p256dh']) ? (string) $d['keys']['p256dh'] : '';
    $auth = isset($d['keys']['auth']) ? (string) $d['keys']['auth'] : '';
    if (!push_enabled() || strpos($endpoint, 'https://') !== 0 || strlen($endpoint) > 2000
        || strlen(b64u_decode($p256dh)) !== 65 || strlen(b64u_decode($auth)) !== 16) {
        http_response_code(400);
        echo json_encode(['ok' => false]);
        return;
    }
    q('INSERT INTO push_subscriptions (endpoint_hash, endpoint, p256dh, auth, created_at) VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE p256dh = VALUES(p256dh), auth = VALUES(auth)',
        [hash('sha256', $endpoint), $endpoint, $p256dh, $auth, now()]);
    echo json_encode(['ok' => true]);
}

function push_unsubscribe()
{
    header('Content-Type: application/json; charset=utf-8');
    $d = push_json_input();
    if (!empty($d['endpoint'])) {
        q('DELETE FROM push_subscriptions WHERE endpoint_hash = ?', [hash('sha256', (string) $d['endpoint'])]);
    }
    echo json_encode(['ok' => true]);
}
