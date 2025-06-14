<?php

// This is a CLI script, not a web script.
// // It is called by `shell_exec` from control_project.php
// // Usage: php worker.php {user_id} {project_id} {api_key}
	if (php_sapi_name() !== 'cli') {
		die("This script can only be run from the command line.");
	}
// Suppress browser output if run accidentally error_reporting(E_ALL);

	ini_set('display_errors', '1');
	ini_set('log_errors', '1');
	ini_set('error_log', __DIR__ . '/worker_errors.log'); // Log errors to a file
	require_once __DIR__ . '/../includes/functions.php';
	if ($argc < 4) {
		die("Usage: php worker.php {user_id} {project_id} {api_key}\n");
	}
	$user_id = $argv[1];
	$project_id = $argv[2];
	$api_key = $argv[3];

// --- Main Worker Logic ---
	function run_translation_job($user_id, $project_id, $api_key)
	{
		$project = load_project($user_id, $project_id);
		if (!$project) {
			error_log("Worker: Could not load project {$project_id} for user {$user_id}. Aborting.");
			return;
		}
		$project['status'] = 'translating';
		$project['control_signal'] = null;
		save_project($user_id, $project);
		$llm_service = $project['llm_service'];
		$model_name = $project['model_name'];
		try {
			$examples_content = file_get_contents(EXAMPLES_DIR . $project['examples_file']);
			$example_pairs = json_decode($examples_content, true);
		} catch (Exception $e) {
			$project['status'] = 'error';
			$project['error_message'] = "Failed to load examples file: " . $e->getMessage();
			save_project($user_id, $project);
			return;
		}
		$num_sections = count($project['sections']);
		for ($i = 0; $i < $num_sections; $i++) {
// Reload project file in each iteration to check for control signals

			$project = load_project($user_id, $project_id);
			if (!$project) {
				error_log("Worker: Project file for {$project_id} was deleted. Stopping thread.");
				return;
			}

// Check for stop signal
//
			if (($project['control_signal'] ?? null) === 'stop') {
				error_log("Worker: Stop signal received for project {$project_id}. Pausing.");
				$project['status'] = 'paused';
				$project['control_signal'] = null;
				save_project($user_id, $project);
				return;
			}
			$section = &$project['sections'][$i];
			if ($section['status'] === 'done') {
				continue;
			}
			$messages = [];
			$start_index = max(0, $i - $project['context_length']);
			for ($j = $start_index; $j < $i; $j++) {
				$prev_section = $project['sections'][$j];
				if ($prev_section['status'] === 'done' && !empty($prev_section['translation'])) {
					$messages[] = ['role' => 'user', 'content' => $prev_section['original']];
					$messages[] = ['role' => 'assistant', 'content' => $prev_section['translation']];
				}
			}
			$messages[] = ['role' => 'user', 'content' => $section['original']];
			$temp_system_prompt = $project['system_prompt_template'];
			if (strpos($temp_system_prompt, '**EXAMPLES**') !== false && count($example_pairs) >= 2) {
				$keys = array_rand($example_pairs, 2);
				$examples_str = "";
				foreach ($keys as $key) {
					$ex = $example_pairs[$key];
					$examples_str .= "{$project['source_language']}\n{$ex[0]}\n{$project['target_language']}\n{$ex[1]}\n\n";
				}
				$temp_system_prompt = str_replace('**EXAMPLES**', trim($examples_str), $temp_system_prompt);
			}
			error_log("Worker: Translating section {$section['section_number']}/{$num_sections} for project {$project_id}");
			$result = llm_translate($temp_system_prompt, $messages, $llm_service, $model_name, $api_key);
			$section['translation'] = $result['content'];
			$section['prompt_tokens'] = $result['prompt_tokens'];
			$section['completion_tokens'] = $result['completion_tokens'];
			$section['status'] = (strpos($result['content'], 'Error:') === 0) ? 'error' : 'done';
			save_project($user_id, $project);
			sleep(1);

// Small delay to prevent overwhelming the API
		} // Finalize project
		$final_project = load_project($user_id, $project_id);
		if ($final_project) {
			$final_project['status'] = 'complete';
			save_project($user_id, $final_project);
			error_log("Worker: Translation finished for project {$project_id}");
		}
	}

// Execute the main function
	run_translation_job($user_id, $project_id, $api_key);
