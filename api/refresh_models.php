<?php
	session_start();
	require_once '../includes/auth.php';
	require_once '../includes/functions.php';
	header('Content-Type: application/json');

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
		exit;
	}

	try {
		$response_json = file_get_contents('https://openrouter.ai/api/v1/models');
		if ($response_json === false) {
			throw new Exception("Failed to fetch models from OpenRouter API.");
		}
		$data = json_decode($response_json, true);
		if (!isset($data['data'])) {
			throw new Exception("Invalid response format from OpenRouter API.");
		}

		$simplified_models = [];
		foreach ($data['data'] as $model) {
			$simplified_models[] = ["id" => $model["id"], "name" => $model["name"]];
		}
		usort($simplified_models, fn($a, $b) => strcasecmp($a['name'], $b['name']));

		file_put_contents(OPENROUTER_MODELS_FILE, json_encode($simplified_models, JSON_PRETTY_PRINT));
		echo json_encode(["success" => true, "message" => "Successfully saved " . count($simplified_models) . " models."]);

	} catch (Exception $e) {
		echo json_encode(["success" => false, "message" => "An error occurred: " . $e->getMessage()]);
	}
