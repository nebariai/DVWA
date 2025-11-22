<?php

// Include the secure command executor
require_once DVWA_WEB_PAGE_TO_ROOT . 'dvwa/includes/SecureCommandExecutor.php';

if( isset( $_POST[ 'Submit' ]  ) ) {
	// Get input
	$target = $_REQUEST[ 'ip' ];

	// Create secure executor instance
	$executor = new SecureCommandExecutor();
	
	// Execute ping securely
	$result = $executor->executePing($target);
	
	// Display result
	if ($result['success']) {
		$html .= "<pre>{$result['output']}</pre>";
	} else {
		$html .= "<pre>ERROR: {$result['error']}</pre>";
	}
}

?>
