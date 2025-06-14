<?php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header('Location: ../dashboard.php');
		exit;
	}

	$project_id = $_POST['project_id'] ?? null;
	$user_id = $_SESSION['user_id'];

	if ($project_id) {
		$project = load_project($user_id, $project_id);
		if ($project) {
			// Delete project JSON file
			$project_path = get_project_path($user_id, $project_id);
			if (file_exists($project_path)) {
				unlink($project_path);
			}

			// Delete original source upload
			$upload_path = UPLOADS_DIR . $project['source_file'];
			if (file_exists($upload_path)) {
				unlink($upload_path);
			}
		}
	}

	header("Location: ../dashboard.php");
	exit();
