<?php

/**
 * DVWA - Comment Preview (High Difficulty XSS)
 *
 * This preview feature has protections that can be bypassed:
 * - Strips <script> tags with regex (but misses event handlers)
 * - Blocks 'javascript:' in href (but allows data: URIs)
 * - HTML entity encodes < and > (but only outside attributes)
 *
 * Bypass techniques:
 * - Use event handlers: <img src=x onerror=alert(1)>
 * - Use SVG: <svg onload=alert(1)>
 * - Use data: URI: <a href="data:text/html,<script>alert(1)</script>">
 * - Use template injection in attributes
 */

header("X-XSS-Protection: 0");

if (array_key_exists("comment", $_GET) && $_GET['comment'] != NULL) {
    $comment = $_GET['comment'];

    // "Security" - Strip script tags (case insensitive, but misses other vectors)
    $comment = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '[removed]', $comment);

    // "Security" - Block javascript: protocol (but not data: or other protocols)
    $comment = preg_replace('/javascript:/i', '[blocked]', $comment);

    // "Security" - Block onclick, onmouseover (but misses onerror, onload, onfocus, etc.)
    $comment = preg_replace('/on(click|mouseover|mouseout)/i', 'on_$1', $comment);

    // "Security" - Weak tag stripping (only strips some dangerous tags)
    $blocked_tags = array('iframe', 'object', 'embed', 'applet');
    foreach ($blocked_tags as $tag) {
        $comment = preg_replace('/<' . $tag . '\b[^>]*>/i', '[tag removed]', $comment);
    }

    // Render the "sanitized" preview - still vulnerable!
    $html .= '<div class="comment-preview">';
    $html .= '<h3>Comment Preview:</h3>';
    $html .= '<div class="preview-content">' . $comment . '</div>';
    $html .= '</div>';

    // Also vulnerable: reflected in a hidden input
    $html .= '<input type="hidden" name="raw_comment" value="' . $comment . '">';
}

// Display form
$html .= '
<form method="GET">
    <label for="comment">Write your comment:</label><br>
    <textarea name="comment" id="comment" rows="4" cols="50">' . (isset($_GET['comment']) ? $_GET['comment'] : '') . '</textarea><br>
    <input type="submit" value="Preview Comment">
</form>';

?>
