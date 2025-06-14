<?php
	session_start();
	require_once 'includes/auth.php';
	require_once 'includes/functions.php';

	$project_id = $_GET['id'] ?? null;
	$user_id = $_SESSION['user_id'];

	if (!$project_id) {
		header("Location: dashboard.php");
		exit;
	}

	$project = load_project($user_id, $project_id);

	if (!$project) {
		die("Project not found or access denied.");
	}

	// Calculate initial progress
	$total_sections = count($project['sections']);
	$done_sections = 0;
	foreach ($project['sections'] as $section) {
		if ($section['status'] === 'done') {
			$done_sections++;
		}
	}

	// Use the header from the dashboard for consistency, but we need to define the variables it expects
	$projects_summary = []; // Not needed on this page, but header might expect it
	$default_models = []; // Not needed on this page
	require_once 'includes/header.php'; // We reuse the header for styles and scripts
?>

<div class="container py-8">
	<div class="flex justify-between items-center mb-6">
		<div>
			<a href="dashboard.php" class="text-sm text-primary hover:underline">← Back to Dashboard</a>
			<h1 class="text-3xl font-bold tracking-tight mt-1"><?php echo htmlspecialchars($project['book_name']); ?></h1>
			<p id="project-summary" class="text-muted-foreground">
				Displaying <?php echo $total_sections; ?> sections. Progress: <span id="progress-count"><?php echo $done_sections; ?></span>/<?php echo $total_sections; ?>
			</p>
		</div>
		<div id="view-status-badge" class="text-lg font-semibold">
			Status: <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold bg-primary/20 text-primary"><?php echo ucfirst($project['status']); ?></span>
		</div>
	</div>

	<div id="action-status" class="mb-4"></div>

	<div class="space-y-4">
		<?php foreach ($project['sections'] as $index => $section): ?>
			<div class="bg-card text-card-foreground rounded-xl border shadow-sm" id="section-<?php echo $index; ?>">
				<div class="p-4">
					<div class="flex justify-between items-center mb-2">
						<h3 class="font-semibold">Section <?php echo $section['section_number']; ?></h3>
						<button
							class="btn-delete-translation inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-8 px-3 bg-destructive text-destructive-foreground hover:bg-destructive/90 disabled:opacity-50"
							data-project-id="<?php echo $project_id; ?>"
							data-section-index="<?php echo $index; ?>"
							<?php if (empty($section['translation'])) echo 'disabled'; ?>>
							Delete Translation
						</button>
					</div>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
						<div>
							<h4 class="text-sm font-medium text-muted-foreground mb-1">Original (<?php echo htmlspecialchars($project['source_language']); ?>)</h4>
							<div class="text-sm p-3 bg-secondary rounded-md max-h-60 overflow-y-auto">
								<pre class="whitespace-pre-wrap font-sans"><?php echo htmlspecialchars($section['original']); ?></pre>
							</div>
						</div>
						<div>
							<h4 class="text-sm font-medium text-muted-foreground mb-1">Translation (<?php echo htmlspecialchars($project['target_language']); ?>)</h4>
							<div id="translation-cell-<?php echo $index; ?>" class="text-sm p-3 border border-input rounded-md max-h-60 overflow-y-auto min-h-[6rem]">
								<?php if (!empty($section['translation'])): ?>
									<pre class="whitespace-pre-wrap font-sans"><?php echo htmlspecialchars($section['translation']); ?></pre>
								<?php else: ?>
									<span class="text-muted-foreground italic">No translation yet.</span>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

<script src="js/view_project.js"></script>

</body>
</html>
