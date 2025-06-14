<!-- Clone & Edit Modal -->
<div id="clone-modal" class="hidden fixed inset-0 z-50 bg-background/80 backdrop-blur-sm">
	<div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-2xl translate-x-[-50%] translate-y-[-50%] gap-4 border bg-card p-6 shadow-lg duration-200 rounded-lg">
		<h2 id="clone-modal-title" class="text-lg font-semibold leading-none tracking-tight">Clone & Edit</h2>
		<p class="text-sm text-muted-foreground">Give your new file a name and modify the content below. It will be saved privately to your account.</p>
		<form id="clone-form" class="space-y-4 pt-2">
			<input type="hidden" id="clone-type" name="type">
			<div>
				<label for="clone-new-name" class="text-sm font-medium leading-none">New File Name (will be slugified)</label>
				<input type="text" id="clone-new-name" name="new_name" placeholder="My Custom Prompt" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm mt-1">
			</div>
			<div>
				<label for="clone-content" class="text-sm font-medium leading-none">Content</label>
				<textarea id="clone-content" name="content" rows="12" class="flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-mono mt-1"></textarea>
			</div>
		</form>
		<div id="clone-status" class="text-sm mt-2"></div>
		<div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4">
			<button id="cancel-clone-btn" type="button" class="mt-2 sm:mt-0 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80">Cancel</button>
			<button id="save-clone-btn" type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90">Save Custom File</button>
		</div>
	</div>
</div>
