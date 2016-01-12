<?php

	$json_msg = file_get_contents('php://input');
	$msg = json_decode($json_msg);

	$mysqli = new mysqli("localhost", "root", "pass_word", "dev_schema");
//	$mysqli = new mysqli("mysql.main-hosting.com", "u277145571_admin", "pass_word", "u277145571_db");
	$mysqli->query(sprintf( 'INSERT INTO messages
								(msg_text, to_user, from_user, create_date) 
							values
								("%1$s", %2$s, %3$s, now())',
							$msg->{'msg'}, $msg->{'toUser'}, $msg->{'fromUser'}
					)
				);
	$mysqli->close();
?>