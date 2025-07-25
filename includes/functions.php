<?php // --- CONSTANTS ---
	define('PROJECTS_DIR', __DIR__ . '/../projects/');
	define('PROMPTS_DIR', __DIR__ . '/../prompts/');
	define('EXAMPLES_DIR', __DIR__ . '/../examples/');
	define('UPLOADS_DIR', __DIR__ . '/../uploads/');
	define('OUTPUT_DIR', __DIR__ . '/../output/');
	define('LOGS_DIR', __DIR__ . '/../logs/'); // New: Directory for logs
	define('OPENROUTER_MODELS_FILE', __DIR__ . '/../openrouter_models.json');

	//make sure directories exist
	if (!is_dir(PROJECTS_DIR)) {
		mkdir(PROJECTS_DIR, 0775, true);
	}
	if (!is_dir(PROMPTS_DIR)) {
		mkdir(PROMPTS_DIR, 0775, true);
	}
	if (!is_dir(EXAMPLES_DIR)) {
		mkdir(EXAMPLES_DIR, 0775, true);
	}
	if (!is_dir(UPLOADS_DIR)) {
		mkdir(UPLOADS_DIR, 0775, true);
	}
	if (!is_dir(OUTPUT_DIR)) {
		mkdir(OUTPUT_DIR, 0775, true);
	}
	// New: Create logs directory if it doesn't exist
	if (!is_dir(LOGS_DIR)) {
		mkdir(LOGS_DIR, 0775, true);
	}


// --- HELPER FUNCTIONS ---
	function get_user_project_dir($user_id)
	{
		$path = PROJECTS_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	function get_user_prompt_dir($user_id)
	{
		$path = PROMPTS_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	function get_user_example_dir($user_id)
	{
		$path = EXAMPLES_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	// New: Helper function for user-specific logs
	function get_user_log_dir($user_id)
	{
		$path = LOGS_DIR . $user_id . '/';
		if (!is_dir($path)) {
			mkdir($path, 0775, true);
		}
		return $path;
	}

	/**
	 * Finds the user ID associated with a given project ID by scanning project directories.
	 * This is used for shareable log links and assumes project IDs are unique.
	 * Note: This can be slow if there are many users.
	 *
	 * @param string $project_id The ID of the project to find.
	 * @return string|null The user ID if found, otherwise null.
	 */
	function find_user_id_for_project($project_id) // MODIFIED: New function to find project owner
	{
		if (empty($project_id)) {
			return null;
		}

		// Sanitize project_id to prevent directory traversal attacks (e.g., '../')
		$clean_project_id = basename($project_id);
		if ($clean_project_id !== $project_id) {
			// An attempt to traverse directories was made.
			return null;
		}

		// Search through all user directories inside the main projects directory
		$user_dirs = glob(PROJECTS_DIR . '*', GLOB_ONLYDIR);
		foreach ($user_dirs as $user_dir) {
			$project_file = $user_dir . "/project_{$clean_project_id}.json";
			if (file_exists($project_file)) {
				return basename($user_dir); // The directory name is the user_id
			}
		}

		return null; // Project not found in any user directory
	}

	function resolve_asset_path($type, $filename, $user_id)
	{
		if (empty($filename) || empty($user_id)) {
			return null;
		}

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


	function get_project_path($user_id, $project_id)
	{
		return get_user_project_dir($user_id) . "project_{$project_id}.json";
	}

	// New: Helper function to get the path for a project's log file
	function get_log_path($user_id, $project_id)
	{
		return get_user_log_dir($user_id) . "project_{$project_id}.log";
	}

	function load_project($user_id, $project_id)
	{
		$path = get_project_path($user_id, $project_id);
		if (file_exists($path)) {
			$content = file_get_contents($path);
			return json_decode($content, true);
		}
		return null;
	}

	function save_project($user_id, $project_data)
	{
		$path = get_project_path($user_id, $project_data['id']);
		file_put_contents($path, json_encode($project_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	}

	// New: Function to log the details of an LLM interaction
	function log_llm_interaction($user_id, $project_id, $system_prompt, $messages, $result)
	{
		$log_path = get_log_path($user_id, $project_id);
		$timestamp = date('Y-m-d H:i:s T');
		$total_tokens = ($result['prompt_tokens'] ?? 0) + ($result['completion_tokens'] ?? 0);

		$log_content = "======================================================================\n";
		$log_content .= "Timestamp: {$timestamp}\n";
		$log_content .= "======================================================================\n\n";

		$log_content .= "--- SYSTEM PROMPT ---\n";
		$log_content .= $system_prompt . "\n\n";

		$log_content .= "--- MESSAGES (PROMPT) ---\n";
		$log_content .= json_encode($messages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

		$log_content .= "--- LLM RESPONSE ---\n";
		$log_content .= "Content:\n" . $result['content'] . "\n\n";

		$log_content .= "--- USAGE ---\n";
		$log_content .= "Prompt Tokens:     " . ($result['prompt_tokens'] ?? 'N/A') . "\n";
		$log_content .= "Completion Tokens: " . ($result['completion_tokens'] ?? 'N/A') . "\n";
		$log_content .= "Total Tokens:      " . $total_tokens . "\n\n\n";

		file_put_contents($log_path, $log_content, FILE_APPEND);
	}

	function slugify($text)
	{
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
		$text = preg_replace('~[^-\w]+~', '', $text);
		$text = trim($text, '-');
		$text = preg_replace('~-+~', '-', $text);
		$text = strtolower($text);
		return empty($text) ? 'n-a' : $text;
	}

	function llm_translate($system_prompt, $messages, $llm_service, $model_name, $api_key)
	{
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
			$headers[] = 'X-Title: Book Translation Zone';
		} else {
			return ["content" => "Error: LLM service '{$llm_service}' not configured.", "prompt_tokens" => 0, "completion_tokens" => 0];
		}

		$all_messages = array_merge([['role' => 'system', 'content' => $system_prompt]], $messages);

		$post_fields = json_encode([
			'model' => $model_name,
			'messages' => $all_messages,
			'max_tokens' => 20000,
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
