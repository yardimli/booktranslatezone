<!-- API Key Modal (Dialog) -->
<div id="api-key-modal" class="hidden fixed inset-0 z-50 bg-background/80 backdrop-blur-sm">
	<div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-card p-6 shadow-lg duration-200 rounded-lg">
		<h2 class="text-lg font-semibold leading-none tracking-tight">Set API Keys for this Session</h2>
		<p class="text-sm text-muted-foreground">These keys are not stored in the database and will be forgotten when you log out. You must set the key for the service you intend to use.</p>
		<form id="api-key-form" class="space-y-4 pt-2">
			<div>
				<label for="openai_key" class="text-sm font-medium leading-none">OpenAI API Key</label>
				<input type="password" id="openai_key" name="openai_key" placeholder="sk-..." class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm mt-1">
			</div>
			<div>
				<label for="openrouter_key" class="text-sm font-medium leading-none">OpenRouter API Key</label>
				<input type="password" id="openrouter_key" name="openrouter_key" placeholder="sk-or-..." class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm mt-1">
			</div>
		</form>
		<div id="api-key-status" class="text-sm"></div>
		<div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4">
			<button id="close-api-modal-btn" type="button" class="mt-2 sm:mt-0 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80">Close</button>
			<button id="save-api-keys-btn" type="button" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-primary text-primary-foreground hover:bg-primary/90">Save Keys</button>
		</div>
	</div>
</div>
