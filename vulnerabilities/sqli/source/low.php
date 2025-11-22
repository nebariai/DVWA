<?php

if( isset( $_REQUEST[ 'Submit' ] ) ) {
	// Get input
	$id = $_REQUEST[ 'id' ];

	// Was a number entered?
	if(is_numeric( $id )) {
		$id = intval($id);

	switch ($_DVWA['SQLI_DB']) {
		case MYSQL:
			// Check the database
			$data = $db->prepare( 'SELECT first_name, last_name FROM users WHERE user_id = (:id) LIMIT 1;' );
			$data->bindParam( ':id', $id, PDO::PARAM_INT );
			$data->execute();
			$row = $data->fetch();

			// Get results
			// Make sure only 1 result is returned
			if( $data->rowCount() == 1 ) {
				// Get values
				$first = $row[ 'first_name' ];
				$last  = $row[ 'last_name' ];

				// Feedback for end user
				$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
			}
			break;
		case SQLITE:
			global $sqlite_db_connection;

			$stmt = $sqlite_db_connection->prepare('SELECT first_name, last_name FROM users WHERE user_id = :id LIMIT 1;' );
			$stmt->bindValue(':id',$id,SQLITE3_INTEGER);
			$result = $stmt->execute();

			if ($result !== false) {
				$num_columns = $result->numColumns();
				if ($num_columns == 2) {
					$row = $result->fetchArray();

					// Get values
					$first = $row[ 'first_name' ];
					$last  = $row[ 'last_name' ];

					// Feedback for end user
					$html .= "<pre>ID: {$id}<br />First name: {$first}<br />Surname: {$last}</pre>";
				}
				
				// Finalize result after fetching data
				$result->finalize();
			}

			break;
		}
	} else {
		// Provide feedback for invalid input
		$html .= "<pre>Invalid ID format.</pre>";
	}
}

?>
