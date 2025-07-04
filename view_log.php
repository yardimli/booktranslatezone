<?php
	// MODIFIED: Removed session and authentication to make this page public for shareable links.
	require_once 'includes/functions.php';

	$project_id = $_GET['id'] ?? null;

	if (!$project_id) {
		http_response_code(400);
		die('Error: Project ID is missing.');
	}

	// MODIFIED: Find the user ID associated with this project ID to locate the files.
	// This is necessary because the URL is now shareable and doesn't rely on a session.
	$user_id = find_user_id_for_project($project_id);

	if (!$user_id) {
		// If no user is found for this project, the project does not exist or the ID is wrong.
		http_response_code(404);
		die('Error: Project not found.');
	}

	// MODIFIED: Now that we have the user_id, we can load the project to get its name.
	$project = load_project($user_id, $project_id);
	if (!$project) {
		// This is an unlikely race condition if the project file was just deleted,
		// but it's a good practice to handle it.
		http_response_code(404);
		die('Error: Project data could not be loaded.');
	}

	$log_path = get_log_path($user_id, $project_id);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Log for <?php echo htmlspecialchars($project['name']); ?></title>
	<style>
      body {
          font-family: monospace, "Courier New", Courier;
          background-color: #1e1e1e;
          color: #d4d4d4;
          line-height: 1.6;
          padding: 20px;
          margin: 0;
      }
      h1 {
          color: #4ec9b0;
          border-bottom: 1px solid #444;
          padding-bottom: 10px;
      }
      pre {
          white-space: pre-wrap;
          word-wrap: break-word;
          background-color: #252526;
          padding: 15px;
          border-radius: 5px;
          border: 1px solid #333;
      }
      p {
          font-size: 1.1em;
      }
	</style>
</head>
<body>
<h1>Translation Log for: <?php echo htmlspecialchars($project['name']); ?></h1>
<?php if (file_exists($log_path)) : ?>
	<pre><?php echo htmlspecialchars(file_get_contents($log_path)); ?></pre>
<?php else : ?>
	<p>No log file found for this project yet. Logs are created when a translation is attempted.</p>
<?php endif; ?>
</body>
</html>
