<?php
// api/translate_section.php

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

// Check for API Key
	$llm_service = $project['llm_service'];
	$api_key = $_SESSION['api_keys'][$llm_service] ?? '';
	if (empty($api_key)) {
		echo json_encode(['success' => false, 'message' => "API Key for '{$llm_service}' is not set. Please set it via the API Keys button."]);
		exit;
	}

	if (!isset($project['sections'][$section_index])) {
		echo json_encode(['success' => false, 'message' => 'Section index out of bounds.']);
		exit;
	}

// --- Main Translation Logic for a single section ---
	$section = &$project['sections'][$section_index];
	$model_name = $project['model_name'];

// Build context from previous sections
	$messages = [];
	$start_context_index = max(0, $section_index - $project['context_length']);
	for ($j = $start_context_index; $j < $section_index; $j++) {
		$prev_section = $project['sections'][$j];
		if ($prev_section['status'] === 'done' && !empty($prev_section['translation'])) {
			$messages[] = ['role' => 'user', 'content' => $prev_section['original']];
			$messages[] = ['role' => 'assistant', 'content' => $prev_section['translation']];
		}
	}
	$messages[] = ['role' => 'user', 'content' => $section['original']];

// Prepare system prompt with examples
	$temp_system_prompt = $project['system_prompt_template'];
	try {
		$examples_path = resolve_asset_path('example', $project['examples_file'], $user_id);
		$examples_content = $examples_path ? file_get_contents($examples_path) : '[]';
		$example_pairs = json_decode($examples_content, true);
		if (strpos($temp_system_prompt, '**EXAMPLES**') !== false && count($example_pairs) >= 2) {
			$keys = array_rand($example_pairs, 2);
			$examples_str = "";
			foreach ($keys as $key) {
				$ex = $example_pairs[$key];
				$examples_str .= "{$project['source_language']}\n{$ex[0]}\n{$project['target_language']}\n{$ex[1]}\n\n";
			}
			$temp_system_prompt = str_replace('**EXAMPLES**', trim($examples_str), $temp_system_prompt);
		}
	} catch (Exception $e) {
		// Non-fatal error, just proceed without examples
	}


// Call the LLM
	$result = llm_translate($temp_system_prompt, $messages, $llm_service, $model_name, $api_key);

// Update the project data
	$section['translation'] = $result['content'];
	$section['prompt_tokens'] = $result['prompt_tokens'];
	$section['completion_tokens'] = $result['completion_tokens'];

	if (strpos($result['content'], 'Error:') === 0) {
		$section['status'] = 'error';
		$project['status'] = 'error';
		$project['error_message'] = "Error in section " . ($section_index + 1) . ": " . $result['content'];
		save_project($user_id, $project);
		echo json_encode(['success' => false, 'message' => $project['error_message']]);
		exit;
	}

	$section['status'] = 'done';

// Check if all sections are done
	$all_done = true;
	foreach ($project['sections'] as $s) {
		if ($s['status'] !== 'done') {
			$all_done = false;
			break;
		}
	}
	if ($all_done) {
		$project['status'] = 'complete';
	}

	save_project($user_id, $project);

	echo json_encode([
		'success' => true,
		'message' => 'Section translated successfully.',
		'translation' => $result['content']
	]);
