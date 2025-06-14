<?php // api/get_file_content.php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';

	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$type = $_GET['type'] ?? null; // 'prompt' or 'example'
	$file = $_GET['file'] ?? null;
	$user_id = $_SESSION['user_id'];

	if (!$type || !$file) {
		echo json_encode(['success' => false, 'message' => 'Missing type or file parameter.']);
		exit;
	}

// Use the new resolver function to find the correct file path
	$path = resolve_asset_path($type, $file, $user_id);

	if ($path && file_exists($path)) {
		$content = file_get_contents($path);
		echo json_encode(['success' => true, 'content' => $content]);
	} else {
		echo json_encode(['success' => false, 'message' => 'File not found.']);
	}
