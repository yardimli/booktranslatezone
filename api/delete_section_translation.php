<?php // api/delete_section_translation.php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';

	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$project_id = $_POST['project_id'] ?? null;
	$section_index = isset($_POST['section_index']) ? (int)$_POST['section_index'] : -1;
	$user_id = $_SESSION['user_id'];

	if (!$project_id || $section_index < 0) {
		echo json_encode(['success' => false, 'message' => 'Missing project ID or section index.']);
		exit;
	}

	$project = load_project($user_id, $project_id);
	if (!$project) {
		echo json_encode(['success' => false, 'message' => 'Project not found.']);
		exit;
	}

	if (!isset($project['sections'][$section_index])) {
		echo json_encode(['success' => false, 'message' => 'Section index out of bounds.']);
		exit;
	}

// Clear the translation and reset status
	$project['sections'][$section_index]['translation'] = '';
	$project['sections'][$section_index]['status'] = 'pending';
	$project['sections'][$section_index]['prompt_tokens'] = 0;
	$project['sections'][$section_index]['completion_tokens'] = 0;

// If the project was complete, it's now paused as it needs more work
	if ($project['status'] === 'complete') {
		$project['status'] = 'paused';
	}

// Recalculate progress
	$done_count = 0;
	foreach ($project['sections'] as $section) {
		if ($section['status'] === 'done') {
			$done_count++;
		}
	}

	save_project($user_id, $project);

	echo json_encode([
		'success' => true,
		'message' => 'Translation deleted successfully.',
		'new_progress_done' => $done_count
	]);
