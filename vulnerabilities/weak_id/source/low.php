<?php

$html = "";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	// Generate cryptographically secure random session ID (256 bits)
	// Uses CSPRNG (Cryptographically Secure Pseudo-Random Number Generator)
	// to prevent session ID prediction and enumeration attacks
	try {
		// Primary: Use PHP 7+ random_bytes() which uses OS-level CSPRNG
		$cookie_value = bin2hex(random_bytes(32));
	} catch (Exception $e) {
		// Fallback: Use OpenSSL with mandatory cryptographic strength verification
		$cookie_value = bin2hex(openssl_random_pseudo_bytes(32, $crypto_strong));
		if (!$crypto_strong) {
			// Log the failure for administrator investigation
			error_log("DVWA Weak ID: Failed to generate cryptographically secure random bytes");
			// Display user-friendly error message and halt execution
			die("Unable to generate secure session ID. Please contact administrator.");
		}
	}

	// Detect HTTPS connection to set secure flag appropriately
	// Secure flag prevents cookie transmission over unencrypted HTTP
	$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

	// Extract hostname without port for valid cookie domain attribute
	$domain = parse_url('http://' . $_SERVER['HTTP_HOST'], PHP_URL_HOST);

	// Set cookie with security flags:
	// - HttpOnly: Prevents JavaScript access (XSS mitigation)
	// - Secure: Conditional on HTTPS (prevents transmission over HTTP when using HTTPS)
	setcookie("dvwaSession", $cookie_value, time()+3600, "/vulnerabilities/weak_id/", $domain, $secure, true);
}
?>
