<?php

// Include secure command executor
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/SecureCommandExecutor.php';

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = $_REQUEST[ 'ip' ];

	// Execute ping securely using SecureCommandExecutor
	$result = SecureCommandExecutor::executePing($target, 4);
	
	if ($result['success']) {
		// HTML-encode output to prevent XSS
		$html .= "<pre>" . SecureCommandExecutor::encodeOutput($result['output']) . "</pre>";
	} else {
		// Display error message (already safe)
		$html .= "<pre>ERROR: " . htmlspecialchars($result['error'], ENT_QUOTES, 'UTF-8') . "</pre>";
	}
}

?>
