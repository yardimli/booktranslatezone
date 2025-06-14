<?php
// api/control_project.php

	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$project_id = $_POST['project_id'] ?? null;
	$action = $_POST['action'] ?? null;
	$user_id = $_SESSION['user_id'];

	if (!$project_id || !$action) {
		echo json_encode(['success' => false, 'message' => 'Missing project ID or action.']);
		exit;
	}

	$project = load_project($user_id, $project_id);
	if (!$project) {
		echo json_encode(['success' => false, 'message' => 'Project not found or access denied.']);
		exit;
	}

	$response = ['success' => true];

	switch ($action) {
		case 'start':
		case 'resume':
		case 'retry':
			// Check for API key before allowing start
			$llm_service = $project['llm_service'];
			if (empty($_SESSION['api_keys'][$llm_service] ?? '')) {
				echo json_encode(['success' => false, 'message' => "API Key for '{$llm_service}' is not set. Please set it via the API Keys button."]);
				exit;
			}
			$project['status'] = 'translating';
			$response['message'] = 'Translation process initiated.';
			break;

		case 'pause':
			if ($project['status'] === 'translating') {
				$project['status'] = 'paused';
				$response['message'] = 'Translation paused.';
			} else {
				$response = ['success' => false, 'message' => 'Project is not running.'];
			}
			break;

		default:
			$response = ['success' => false, 'message' => 'Invalid action.'];
			break;
	}

	if ($response['success']) {
		save_project($user_id, $project);
	}

	echo json_encode($response);
