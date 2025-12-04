<?php

if (array_key_exists ("redirect", $_GET) && $_GET["redirect"] != "") {
	$redirect = $_GET["redirect"];
	
	// Parse the URL to check for protocol and host
	$parsed = parse_url($redirect);
	
	// Reject if URL contains a scheme (protocol) or host
	if (isset($parsed["scheme"]) || isset($parsed["host"])) {
		http_response_code (500);
		?>
		<p>Absolute URLs not allowed.</p>
		<?php
		exit;
	}
	
	// Reject protocol-relative URLs (starting with //)
	if (strpos($redirect, '//') === 0) {
		http_response_code (500);
		?>
		<p>Protocol-relative URLs not allowed.</p>
		<?php
		exit;
	}
	
	// Additional validation: ensure it's a relative path
	if (preg_match('/^[a-zA-Z0-9_\-\.\/\?=&]+$/', $redirect)) {
		header ("location: " . $redirect);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>Invalid redirect format.</p>
		<?php
		exit;
	}
}

http_response_code (500);
?>
<p>Missing redirect target.</p>
<?php
exit;
?>
