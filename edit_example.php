<?php
	// booktranslatezone/edit_example.php
	session_start();
	require_once 'includes/auth.php';
	require_once 'includes/functions.php';

	$filename = $_GET['file'] ?? null;
	$user_id = $_SESSION['user_id'];

	if (!$filename) {
		header("Location: dashboard.php");
		exit;
	}

	// Security check: Ensure the file being edited is a custom user file, not a default one.
	$user_example_dir = get_user_example_dir($user_id);
	$path = $user_example_dir . $filename;

	// realpath() resolves symlinks, '..', etc.
	// This check ensures the requested file is actually inside the user's designated folder.
	if (!file_exists($path) || strpos(realpath($path), realpath($user_example_dir)) !== 0) {
		die("Access denied: You can only edit your own custom example files.");
	}

	$content = file_get_contents($path);
	$examples = json_decode($content, true);

	if (json_last_error() !== JSON_ERROR_NONE) {
		// Handle case where file is corrupted
		$examples = []; // Start with an empty set
		$error_message = "Warning: The file was corrupted (invalid JSON). Please review and save to fix.";
	}

	// Dummy variables for header.php which expects them
	$projects_summary = [];
	$default_models = [];
	require_once 'includes/header.php';
?>

<div class="container py-8">
	<div class="flex justify-between items-center mb-6">
		<div>
			<a href="dashboard.php" class="text-sm text-primary hover:underline">← Back to Dashboard</a>
			<h1 class="text-3xl font-bold tracking-tight mt-1">Edit Examples: <?php echo htmlspecialchars($filename); ?></h1>
			<p class="text-muted-foreground">Add, remove, or edit translation pairs. Blank rows will be ignored on save.</p>
		</div>
	</div>

	<?php if (isset($error_message)): ?>
		<div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
			<?php echo $error_message; ?>
		</div>
	<?php endif; ?>

	<div id="status-message" class="mb-4"></div>

	<form id="edit-examples-form" class="space-y-4">
		<input type="hidden" id="filename" value="<?php echo htmlspecialchars($filename); ?>">

		<!-- Table Header -->
		<div class="grid grid-cols-12 gap-4 items-center font-medium text-muted-foreground px-2">
			<div class="col-span-5">Source Text</div>
			<div class="col-span-5">Target Text</div>
			<div class="col-span-2 text-right">Action</div>
		</div>

		<div id="examples-container" class="space-y-2">
			<?php if (!empty($examples)): ?>
				<?php foreach ($examples as $index => $pair): ?>
					<div class="example-row grid grid-cols-12 gap-4 items-start p-2 rounded-md border bg-card">
						<div class="col-span-5">
							<textarea name="source[]" class="flex min-h-[250px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-sans" placeholder="Source text..."><?php echo htmlspecialchars($pair[0] ?? ''); ?></textarea>
						</div>
						<div class="col-span-5">
							<textarea name="target[]" class="flex min-h-[250px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-sans" placeholder="Target text..."><?php echo htmlspecialchars($pair[1] ?? ''); ?></textarea>
						</div>
						<div class="col-span-2 flex items-center justify-end">
							<button type="button" class="delete-row-btn inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 w-9 bg-destructive text-destructive-foreground hover:bg-destructive/90" title="Delete Row">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
							</button>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>

		<div class="flex items-center gap-4 pt-4">
			<button type="button" id="add-row-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80">Add Row</button>
			<button type="submit" id="save-changes-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90">Save Changes</button>
		</div>
	</form>
</div>

<script src="js/edit_example.js"></script>
</body>
</html>
