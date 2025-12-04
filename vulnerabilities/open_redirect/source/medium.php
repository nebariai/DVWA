<?php

if (array_key_exists ("redirect", $_GET) && $_GET['redirect'] != "") {
	$redirect = $_GET['redirect'];
	
	// Validate that the redirect is a safe relative path
	// Block any URL with a protocol (http://, https://, javascript:, data:, etc.)
	// Block protocol-relative URLs (//)
	if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*:/i', $redirect) || 
	    strpos($redirect, '//') === 0 || 
	    strpos($redirect, '\\\\') === 0) {
		http_response_code (500);
		?>
		<p>Absolute URLs and protocol-relative URLs not allowed.</p>
		<?php
		exit;
	}
	
	// Additional validation: only allow paths starting with info.php
	// This creates an allowlist of acceptable redirect targets
	if (strpos($redirect, 'info.php') === 0) {
		header ("location: " . $redirect);
		exit;
	} else {
		http_response_code (500);
		?>
		<p>You can only redirect to the info page.</p>
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
