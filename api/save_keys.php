<?php
	session_start();
	require_once '../includes/auth.php';
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$_SESSION['api_keys'] = [
		'openai' => trim($_POST['openai_key'] ?? ''),
		'openrouter' => trim($_POST['openrouter_key'] ?? '')
	];

	echo json_encode(['success' => true, 'message' => 'API keys saved for this session.']);
