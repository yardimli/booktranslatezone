<?php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		header('Location: ../dashboard.php');
		exit;
	}

// Basic validation
	if (empty($_POST['book_name']) || empty($_FILES['source_file']) || $_FILES['source_file']['error'] !== UPLOAD_ERR_OK) {
		// In a real app, add user-friendly error feedback
		die("Error: Missing required fields or file upload error.");
	}

	$user_id = $_SESSION['user_id'];

// Handle file upload
	$file_tmp_path = $_FILES['source_file']['tmp_name'];
	$file_name = $_FILES['source_file']['name'];
	$file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
	if (strtolower($file_ext) !== 'txt') {
		die("Error: Only .txt files are allowed.");
	}
	$new_filename = uniqid('', true) . "_" . basename($file_name);
	$dest_path = UPLOADS_DIR . $new_filename;

	if (!move_uploaded_file($file_tmp_path, $dest_path)) {
		die("Error: Failed to move uploaded file.");
	}

// Create Project Logic (ported from Python)
	$project_id = bin2hex(random_bytes(16));
	$book_name = $_POST['book_name'];
	$source_lang = $_POST['source_language'];
	$target_lang = $_POST['target_language'];
	$examples_file = $_POST['examples_file'];
	$prompt_file = $_POST['prompt_file'];
	$word_limit = (int)($_POST['section_word_limit'] ?? 500);
	$context_len = (int)($_POST['context_length'] ?? 4);
	$llm_service = $_POST['llm_service'];
	$model_name = $_POST['model_name'];

	$text = file_get_contents($dest_path);
	$system_prompt_path = resolve_asset_path('prompt', $prompt_file, $user_id);
	if (!$system_prompt_path) {
		die("Error: System prompt file '{$prompt_file}' not found.");
	}
	$system_prompt_template = file_get_contents($system_prompt_path);

	$paragraphs = explode("\n", $text);
	$sections_text = [];
	$current_section = '';
	$current_word_count = 0;

	foreach ($paragraphs as $paragraph) {
		$paragraph_word_count = str_word_count($paragraph);
		if ($paragraph_word_count > $word_limit) {
			if ($current_section) $sections_text[] = trim($current_section);
			$sections_text[] = trim($paragraph);
			$current_section = '';
			$current_word_count = 0;
			continue;
		}
		if ($current_word_count + $paragraph_word_count > $word_limit && $current_section) {
			$sections_text[] = trim($current_section);
			$current_section = $paragraph;
			$current_word_count = $paragraph_word_count;
		} else {
			if ($current_section) $current_section .= "\n";
			$current_section .= $paragraph;
			$current_word_count += $paragraph_word_count;
		}
	}
	if ($current_section) $sections_text[] = trim($current_section);

	$project_data = [
		"id" => $project_id, "user_id" => $user_id, "book_name" => $book_name, "source_file" => $new_filename,
		"source_language" => $source_lang, "target_language" => $target_lang, "examples_file" => $examples_file,
		"system_prompt_file" => $prompt_file, "system_prompt_template" => $system_prompt_template,
		"section_word_limit" => $word_limit, "context_length" => $context_len, "llm_service" => $llm_service,
		"model_name" => $model_name, "status" => "new", "control_signal" => null, "sections" => []
	];

	foreach ($sections_text as $i => $section_text) {
		if (empty(trim($section_text))) continue;
		$project_data["sections"][] = [
			"section_number" => $i + 1, "original" => $section_text, "translation" => "",
			"status" => "pending", "prompt_tokens" => 0, "completion_tokens" => 0
		];
	}

	save_project($user_id, $project_data);

	header("Location: ../dashboard.php");
	exit();
