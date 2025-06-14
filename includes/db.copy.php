<?php
	// --- DATABASE CONFIGURATION ---
	define('DB_HOST', 'localhost');
	define('DB_USER', 'root');
	define('DB_PASS', '');
	define('DB_NAME', '');

	// Create connection
	$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check connection
	if ($mysqli->connect_error) {
		die("ERROR: Could not connect. " . $mysqli->connect_error);
	}

// Set charset to utf8mb4
	$mysqli->set_charset("utf8mb4");
