<?php // --- CONSTANTS ---
	define('PROJECTS_DIR', __DIR__ . '/../projects/');
	define('PROMPTS_DIR', __DIR__ . '/../prompts/');
	define('EXAMPLES_DIR', __DIR__ . '/../examples/');
	define('UPLOADS_DIR', __DIR__ . '/../uploads/');
	define('OUTPUT_DIR', __DIR__ . '/../output/');
	define('OPENROUTER_MODELS_FILE', __DIR__ . '/../openrouter_models.json');

// --- HELPER FUNCTIONS ---
	function get_user_project_dir($user_id) {
		$path = PROJECTS_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	function get_user_prompt_dir($user_id) {
		$path = PROMPTS_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	function get_user_example_dir($user_id) {
		$path = EXAMPLES_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	function resolve_asset_path($type, $filename, $user_id) {
		if (empty($filename) || empty($user_id)) return null;

		$user_path = null;
		$global_path = null;

		if ($type === 'prompt') {
			$user_path = get_user_prompt_dir($user_id) . $filename;
			$global_path = PROMPTS_DIR . $filename;
		} elseif ($type === 'example') {
			$user_path = get_user_example_dir($user_id) . $filename;
			$global_path = EXAMPLES_DIR . $filename;
		}

		// Check user-specific directory first
		if ($user_path && file_exists($user_path)) {
			return $user_path;
		}
		// Fallback to global directory
		if ($global_path && file_exists($global_path) && is_file($global_path)) {
			return $global_path;
		}
		return null; // File not found in either location
	}


	function get_project_path($user_id, $project_id) {
		return get_user_project_dir($user_id) . "project_{$project_id}.json";
	}

	function load_project($user_id, $project_id) {
		$path = get_project_path($user_id, $project_id);
		if (file_exists($path)) {
			$content = file_get_contents($path);
			return json_decode($content, true);
		}
		return null;
	}

	function save_project($user_id, $project_data) {
		$path = get_project_path($user_id, $project_data['id']);
		file_put_contents($path, json_encode($project_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}

	function slugify($text) {
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
		return empty($text) ? 'n-a' : $text;
	}

	function llm_translate($system_prompt, $messages, $llm_service, $model_name, $api_key) {
		$url = '';
		$headers = [
			'Content-Type: application/json'
		];
		if ($llm_service === 'openai') {
			$url = getenv('OPENAI_API_BASE') ?: 'https://api.openai.com/v1/chat/completions';
			$headers[] = 'Authorization: Bearer ' . $api_key;
		} elseif ($llm_service === 'openrouter') {
			$url = getenv('OPENROUTER_API_BASE') ?: 'https://openrouter.ai/api/v1/chat/completions';
			$headers[] = 'Authorization: Bearer ' . $api_key;
			$headers[] = 'HTTP-Referer: ' . ($_SERVER['HTTP_HOST'] ?? 'localhost');
			$headers[] = 'X-Title: Book Translator Zone';
		} else {
			return ["content" => "Error: LLM service '{$llm_service}' not configured.", "prompt_tokens" => 0, "completion_tokens" => 0];
		}

		$all_messages = array_merge([['role' => 'system', 'content' => $system_prompt]], $messages);

		$post_fields = json_encode([
			'model' => $model_name,
			'messages' => $all_messages,
			'max_tokens' => 4096,
			'stream' => false
		]);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response_body = curl_exec($ch);
		$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curl_error = curl_error($ch);
		curl_close($ch);

		if ($curl_error) {
			return ["content" => "Error: cURL Error: " . $curl_error, "prompt_tokens" => 0, "completion_tokens" => 0];
		}

		$response_data = json_decode($response_body, true);

		if ($http_code >= 400 || !isset($response_data['choices'][0]['message']['content'])) {
			$error_message = $response_data['error']['message'] ?? $response_body;
			return ["content" => "Error: API call failed with status {$http_code}. " . $error_message, "prompt_tokens" => 0, "completion_tokens" => 0];
		}

		return [
			"content" => $response_data['choices'][0]['message']['content'],
			"prompt_tokens" => $response_data['usage']['prompt_tokens'] ?? 0,
			"completion_tokens" => $response_data['usage']['completion_tokens'] ?? 0
		];
	}
