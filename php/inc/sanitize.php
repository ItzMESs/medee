<?php
// Whitelist HTML cleaner for article bodies written in the admin editor.
function clean_html($html)
{
    $html = trim((string) $html);
    if ($html === '') {
        return '';
    }

    $allowed = [
        'p' => ['class'], 'br' => [], 'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'span' => ['class'], 'sub' => [], 'sup' => [], 'h1' => ['class'], 'h2' => ['class'], 'h3' => ['class'],
        'h4' => ['class'], 'blockquote' => ['class'], 'pre' => ['class'], 'code' => [], 'ul' => ['class'],
        'ol' => ['class'], 'li' => ['class', 'data-list'], 'hr' => [], 'figure' => ['class'], 'figcaption' => [],
        'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
        'iframe' => ['src', 'width', 'height', 'allow', 'allowfullscreen', 'frameborder', 'class'],
    ];
    $drop = ['script', 'style', 'object', 'embed', 'form', 'input', 'button', 'textarea', 'select', 'svg', 'math', 'template', 'noscript', 'head', 'title', 'meta', 'link', 'base'];
    $iframeHosts = ['www.youtube.com', 'youtube.com', 'www.youtube-nocookie.com', 'www.facebook.com', 'player.vimeo.com'];

    $doc = new DOMDocument();
    $prev = libxml_use_internal_errors(true);
    $doc->loadHTML('<?xml encoding="utf-8"?><html><body><div id="root">' . $html . '</div></body></html>', LIBXML_NONET);
    libxml_clear_errors();
    libxml_use_internal_errors($prev);
    $root = $doc->getElementById('root');
    if (!$root) {
        return '';
    }

    $walk = function (DOMNode $node) use (&$walk, $allowed, $drop, $iframeHosts) {
        $children = [];
        foreach ($node->childNodes as $c) {
            $children[] = $c;
        }
        foreach ($children as $child) {
            if ($child instanceof DOMText) {
                continue;
            }
            if (!($child instanceof DOMElement)) {
                $node->removeChild($child);
                continue;
            }
            $tag = strtolower($child->nodeName);
            if (in_array($tag, $drop, true)) {
                $node->removeChild($child);
                continue;
            }
            if (!isset($allowed[$tag])) {
                // Unknown tag: keep its contents, drop the tag itself.
                $walk($child);
                while ($child->firstChild) {
                    $node->insertBefore($child->firstChild, $child);
                }
                $node->removeChild($child);
                continue;
            }
            $attrs = [];
            foreach ($child->attributes as $attr) {
                $attrs[] = $attr->name;
            }
            foreach ($attrs as $name) {
                $value = $child->getAttribute($name);
                $keep = in_array(strtolower($name), $allowed[$tag], true);
                if ($keep && ($name === 'href' || $name === 'src')) {
                    $keep = (bool) preg_match('#^(https?://|/|\#|mailto:)#i', trim($value));
                }
                if (!$keep) {
                    $child->removeAttribute($name);
                }
            }
            if ($tag === 'iframe') {
                $host = parse_url($child->getAttribute('src'), PHP_URL_HOST);
                if (strpos($child->getAttribute('src'), 'https://') !== 0 || !in_array($host, $iframeHosts, true)) {
                    $node->removeChild($child);
                    continue;
                }
            }
            if ($tag === 'a' && $child->getAttribute('target') === '_blank') {
                $child->setAttribute('rel', 'noopener');
            }
            $walk($child);
        }
    };
    $walk($root);

    $out = '';
    foreach ($root->childNodes as $c) {
        $out .= $doc->saveHTML($c);
    }
    return $out;
}
