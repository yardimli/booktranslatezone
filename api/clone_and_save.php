<?php // api/clone_and_save.php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';

	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$user_id = $_SESSION['user_id'];
	$type = $_POST['type'] ?? null; // 'prompt' or 'example'
	$new_name = trim($_POST['new_name'] ?? '');
	$content = $_POST['content'] ?? '';

	if (!$type || empty($new_name) || !isset($content)) {
		echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
		exit;
	}

	$slug = slugify($new_name);
	$extension = ($type === 'prompt') ? '.txt' : '.json';
	$filename = $slug . $extension;

	$dir = null;
	if ($type === 'prompt') {
		$dir = get_user_prompt_dir($user_id);
	} elseif ($type === 'example') {
		$dir = get_user_example_dir($user_id);
	}

	if (!$dir) {
		echo json_encode(['success' => false, 'message' => 'Invalid type specified.']);
		exit;
	}

	$path = $dir . $filename;

	if (file_exists($path)) {
		echo json_encode(['success' => false, 'message' => 'A custom file with this name already exists. Please choose another name.']);
		exit;
	}

	if (file_put_contents($path, $content) !== false) {
		echo json_encode([
			'success' => true,
			'message' => 'File saved successfully.',
			'new_file' => [
				'value' => $filename,
				'text' => $filename // Use the filename for consistency in the dropdown
			]
		]);
	} else {
		echo json_encode(['success' => false, 'message' => 'Failed to save the file. Check directory permissions.']);
	}
