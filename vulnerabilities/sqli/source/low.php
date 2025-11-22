<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Validate input - ensure it's numeric
	if( is_numeric( $id ) ) {
		$id = intval( $id );

		switch ($_DVWA['SQLI_DB']) {
			case MYSQL:
				// Use PDO prepared statement to prevent SQL injection
				global $db;
				
				$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
				$data->bindParam( ':id', $id, PDO::PARAM_INT );
				$data->execute();
				$row = $data->fetch();

				// Make sure only 1 result is returned
				if( $data->rowCount() == 1 ) {
					// Get values
					$first = $row["first_name"];
					$last  = $row["last_name"];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
				break;
				
			case SQLITE:
				global $sqlite_db_connection;

				// Use SQLite3 prepared statement to prevent SQL injection
				$stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;');
				$stmt->bindValue(':id', $id, SQLITE3_INTEGER);
				$result = $stmt->execute();

				if ($result) {
					// Validate result structure
					$num_columns = $result->numColumns();
					if ($num_columns == 2) {
						$row = $result->fetchArray();
						
						// Get values
						$first = $row["first_name"];
						$last  = $row["last_name"];


						// Feedback for end user
						$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
					}
				} else {
					echo "Error in fetch ".$sqlite_db_connection->lastErrorMsg();
				}
				break;
		}
	} else {
		// Invalid input - not numeric
		$html .= "<pre>Invalid ID. Please enter a numeric value.</pre>";
	}
}

?>
