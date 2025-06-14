<!-- Export Modal (Dialog) -->
<div id="export-modal" class="hidden fixed inset-0 z-50 bg-background/80 backdrop-blur-sm">
	<div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-card p-6 shadow-lg duration-200 rounded-lg">
		<h2 class="text-lg font-semibold leading-none tracking-tight">Export Successful</h2>
		<p class="text-sm text-muted-foreground">Your files have been generated. Click to open in a new tab:</p>
		<ul id="export-file-list" class="space-y-2 pt-2">
			<!-- JS will populate this -->
		</ul>
		<div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4">
			<button id="close-modal-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">
				Close
			</button>
		</div>
	</div>
</div>

<!-- Data initialization script -->
<script>
	// Pass PHP data to JavaScript in a structured object
	const initialData = {
		projects: <?php echo json_encode($projects_summary); ?>,
		defaultModels: <?php echo json_encode($default_models ?? []); ?>
	};
</script>

<!-- Main application script -->
<script src="js/dashboard.js"></script>

</body>
</html>
