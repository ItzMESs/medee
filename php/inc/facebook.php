<?php
function fb_configured()
{
    return setting('fb_page_id') !== '' && setting('fb_page_token') !== '';
}

// Publishes the article link to the configured Facebook Page feed.
// Facebook builds the preview card from the article's Open Graph tags,
// so site_url must be a public address Facebook can reach.
function fb_post_article(array $article)
{
    if (!fb_configured()) {
        throw new RuntimeException('Facebook пэйжийн ID болон access token тохируулаагүй байна.');
    }
    $link = absolute_url(article_path($article));
    $message = $article['excerpt'] !== '' ? $article['title'] . "\n\n" . $article['excerpt'] : $article['title'];

    $ch = curl_init('https://graph.facebook.com/v21.0/' . rawurlencode(setting('fb_page_id')) . '/feed');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query(['message' => $message, 'link' => $link, 'access_token' => setting('fb_page_token')]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
    ]);
    $body = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($body === false) {
        $msg = 'Facebook-тэй холбогдож чадсангүй: ' . $curlError;
    } else {
        $data = json_decode($body, true);
        if ($status >= 200 && $status < 300 && !empty($data['id'])) {
            q('UPDATE articles SET fb_post_id = ?, fb_error = NULL WHERE id = ?', [$data['id'], $article['id']]);
            return $data['id'];
        }
        $msg = !empty($data['error']['message']) ? $data['error']['message'] : 'Facebook API алдаа (HTTP ' . $status . ')';
    }
    q('UPDATE articles SET fb_error = ? WHERE id = ?', [$msg, $article['id']]);
    throw new RuntimeException($msg);
}
