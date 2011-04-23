<?php

echo json_encode(array(
				'files' => $_FILES,
				'post' => $_POST,
				'server' => $_SERVER,
			));
			
?>