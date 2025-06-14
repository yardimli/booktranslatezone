<?php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	$project_id = $_POST['project_id'] ?? null;
	$user_id = $_SESSION['user_id'];

	$project = load_project($user_id, $project_id);
	if (!$project) {
		echo json_encode(['success' => false, 'message' => 'Project not found.']);
		exit;
	}

	try {
		$timestamp = date("Ymd-His");
		$book_slug = slugify($project['book_name']);

		$filename_single = "{$book_slug}-{$timestamp}-single.html";
		$filename_parallel = "{$book_slug}-{$timestamp}-parallel.html";

		$single_text = '';
		$parallel_text = '<table border="1" style="width:100%; border-collapse: collapse;">' . "\n";
		$parallel_text .= '<thead><tr><th style="width: 50%; padding: 10px;">Translated</th><th style="width: 50%; padding: 10px;">Original</th></tr></thead>' . "\n<tbody>";

		// Sort sections by number before exporting
		$sections = $project['sections'];
		usort($sections, fn($a, $b) => $a['section_number'] <=> $b['section_number']);

		foreach ($sections as $section) {
			if (!empty($section['translation'])) {
				$original = nl2br(htmlspecialchars($section['original'] ?? ''));
				$translation = nl2br(htmlspecialchars($section['translation'] ?? ''));

				$tags_to_remove = ["<turkish_translation>", "</turkish_translation>", "<translation>", "</translation>"];
				$original = str_replace($tags_to_remove, "", $original);
				$translation = str_replace($tags_to_remove, "", $translation);

				$single_text .= $translation . "<br/><br/>";
				$parallel_text .= '<tr><td style="width: 50%; padding: 10px; vertical-align: top;">' . $translation . '</td><td style="width: 50%; padding: 10px; vertical-align: top;">' . $original . '</td></tr>' . "\n";
			}
		}
		$parallel_text .= "</tbody></table>\n";

		$html_template = '<!DOCTYPE html><html><head><title>{title}</title><meta charset="UTF-8"></head><body>{body}</body></html>';

		file_put_contents(OUTPUT_DIR . $filename_single, str_replace(['{title}', '{body}'], [$project['book_name'], $single_text], $html_template));
		file_put_contents(OUTPUT_DIR . $filename_parallel, str_replace(['{title}', '{body}'], [$project['book_name'] . ' (Parallel)', $parallel_text], $html_template));

		echo json_encode([
			"success" => true,
			"message" => "Export successful.",
			"single_file" => $filename_single,
			"parallel_file" => $filename_parallel
		]);

	} catch (Exception $e) {
		echo json_encode(['success' => false, 'message' => 'An unexpected error occurred during export: ' . $e->getMessage()]);
	}
