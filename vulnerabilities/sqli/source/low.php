<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Validate input - ensure it's numeric
	if(is_numeric( $id )) {
		$id = intval($id);
		
		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Declare global PDO connection
				global $db;
				
				// Use prepared statement to prevent SQL injection
				$stmt = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id);' );
				$stmt->bindParam( ':id', $id, PDO::PARAM_INT );
				$stmt->execute();

				// Get results
				while( $row = $stmt->fetch( PDO::FETCH_ASSOC ) ) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
				break;
				
			case SQLITE:
				global $sqlite_db_connection;

				// Use prepared statement to prevent SQL injection
				$stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id;' );
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $stmt->execute();

				if ($result !== false) {
					while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];

						// Feedback for end user
						$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
					}
				} else {
					echo "Error in fetch.";
				}
				break;
		}
	} else {
		// Invalid input - not numeric
		$html .= "<pre>Invalid ID provided.</pre>";
	}
}

?>
