<?php
	session_start();
	require_once 'includes/auth.php';
	require_once 'includes/header.php'; // This now contains all the data loading and HTML head
?>
	<div class="container py-8">
		<div class="flex justify-between items-start mb-4">
			<div>
				<div class="flex items-center">
					<img src="https://booktranslationzone.com/images/btz2.png" style="width: 80px; height: 80px; margin-right: 20px;" alt="Book Translate Zone Logo">
					<div>
						<h1 class="text-3xl font-bold tracking-tight">Book Translate Zone</h1>
						<p class="text-muted-foreground">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
					</div>
				</div>
			</div>
			<div class="flex items-center gap-4">
				<button id="api-key-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80">API Keys</button>
				<a href="logout.php" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors h-10 px-4 py-2 bg-destructive text-destructive-foreground hover:bg-destructive/90">Logout</a>
			</div>
		</div>

		<h2 class="text-2xl font-semibold tracking-tight mt-8">Create New Translation Job</h2>
		<!-- New Project Form styled as a Card -->
		<div class="bg-card text-card-foreground rounded-xl border shadow mb-12 mt-2">
			<form action="api/create_project.php" method="post" enctype="multipart/form-data" class="p-6 space-y-6">
				<input type="hidden" name="model_name" id="model_name_hidden">
				<div class="space-y-2">
					<label for="book_name" class="text-sm font-medium leading-none">Book Name</label>
					<input type="text" id="book_name" name="book_name" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
				</div>
				<div class="space-y-2">
					<label for="source_file" class="text-sm font-medium leading-none">Source Text File (.txt)</label>
					<input type="file" id="source_file" name="source_file" accept=".txt" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm text-muted-foreground ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
				</div>
				<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
					<div class="space-y-2">
						<label for="source_language" class="text-sm font-medium leading-none">Source Language</label>
						<select id="source_language" name="source_language" required class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
							<option value="English" selected>English</option>
							<option value="Turkish">Turkish</option>
							<option value="German">German</option>
							<option value="French">French</option>
							<option value="Spanish">Spanish</option>
							<option value="Italian">Italian</option>
							<option value="Portuguese">Portuguese</option>
							<option value="Dutch">Dutch</option>
							<option value="Swedish">Swedish</option>
							<option value="Norwegian">Norwegian</option>
							<option value="Danish">Danish</option>
							<option value="Finnish">Finnish</option>
							<option value="Polish">Polish</option>
							<option value="Russian">Russian</option>
							<option value="Czech">Czech</option>
							<option value="Greek">Greek</option>
							<option value="Hungarian">Hungarian</option>
							<option value="Romanian">Romanian</option>
							<option value="Traditional Chinese">Traditional Chinese</option>
							<!-- Add other languages as needed -->
						</select>
					</div>
					<div class="space-y-2">
						<label for="target_language" class="text-sm font-medium leading-none">Target Language</label>
						<select id="target_language" name="target_language" required class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
							<option value="Turkish" selected>Turkish</option>
							<option value="English">English</option>
							<option value="German">German</option>
							<option value="French">French</option>
							<option value="Spanish">Spanish</option>
							<option value="Italian">Italian</option>
							<option value="Portuguese">Portuguese</option>
							<option value="Dutch">Dutch</option>
							<option value="Swedish">Swedish</option>
							<option value="Norwegian">Norwegian</option>
							<option value="Danish">Danish</option>
							<option value="Finnish">Finnish</option>
							<option value="Polish">Polish</option>
							<option value="Russian">Russian</option>
							<option value="Czech">Czech</option>
							<option value="Greek">Greek</option>
							<option value="Hungarian">Hungarian</option>
							<option value="Romanian">Romanian</option>
							<option value="Traditional Chinese">Traditional Chinese</option>
							<!-- Add other languages as needed -->
						</select>
					</div>
					<div class="space-y-2">
						<label for="examples_file" class="text-sm font-medium leading-none">Translation Examples File</label>
						<div class="flex items-center gap-2">
							<select id="examples_file" name="examples_file" required class="flex-grow h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
								<?php if (!empty($user_example_files)): ?>
									<optgroup label="Custom Examples">
										<?php foreach ($user_example_files as $file): ?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option><?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<optgroup label="Default Examples">
									<?php foreach ($default_example_files as $file): ?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option><?php endforeach; ?>
								</optgroup>
							</select>
							<button type="button" id="clone-example-btn" class="h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium">Clone & Edit</button>
						</div>
					</div>
					<div class="space-y-2">
						<label for="prompt_file" class="text-sm font-medium leading-none">System Prompt File</label>
						<div class="flex items-center gap-2">
							<select id="prompt_file" name="prompt_file" required class="flex-grow h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
								<?php if (!empty($user_prompt_files)): ?>
									<optgroup label="Custom Prompts">
										<?php foreach ($user_prompt_files as $file): ?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option><?php endforeach; ?>
									</optgroup>
								<?php endif; ?>
								<optgroup label="Default Prompts">
									<?php foreach ($default_prompt_files as $file): ?><option value="<?php echo htmlspecialchars($file); ?>"><?php echo htmlspecialchars($file); ?></option><?php endforeach; ?>
								</optgroup>
							</select>
							<button type="button" id="clone-prompt-btn" class="h-10 px-4 py-2 bg-secondary text-secondary-foreground hover:bg-secondary/80 inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium">Clone & Edit</button>
						</div>
					</div>
					<div class="space-y-2"><label for="section_word_limit" class="text-sm font-medium leading-none">Section Word Limit</label><input type="number" id="section_word_limit" name="section_word_limit" value="500" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></div>
					<div class="space-y-2"><label for="context_length" class="text-sm font-medium leading-none">Context Length (sections)</label><input type="number" id="context_length" name="context_length" value="4" required class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"></div>
					<div class="flex items-end gap-2">
						<div class="flex-grow space-y-2"><label for="llm_service" class="text-sm font-medium leading-none">LLM Service</label><select id="llm_service" name="llm_service" required class="flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
								<?php foreach ($available_services as $service): ?>
									<option value="<?php echo $service; ?>"><?php echo ucfirst($service); ?></option>
								<?php endforeach; ?>
							</select></div>
						<button type="button" id="refresh-models-btn" class="hidden inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">Refresh List</button>
					</div>
					<div class="space-y-2">
						<label for="model_name_input" class="text-sm font-medium leading-none">Model Name</label>
						<input type="text" id="model_name_input" class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
						<select id="model_name_select" class="hidden flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50">
							<?php if (!empty($openrouter_models)): ?>
								<?php foreach ($openrouter_models as $model): ?>
									<option value="<?php echo htmlspecialchars($model['id']); ?>"><?php echo htmlspecialchars($model['name']); ?></option>
								<?php endforeach; ?>
							<?php else: ?>
								<option value="" disabled>No models loaded. Please refresh.</option>
							<?php endif; ?>
						</select>
					</div>
				</div>
				<div class="flex items-center pt-4">
					<button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-primary text-primary-foreground hover:bg-primary/90 h-10 px-4 py-2">Create Project</button>
				</div>
			</form>
		</div>

		<!-- Existing Projects List -->
		<div class="rounded-xl border bg-card text-card-foreground shadow">
			<div class="p-6">
				<h2 class="text-2xl font-semibold tracking-tight">Existing Projects</h2>
			</div>
			<div class="relative w-full overflow-auto">
				<table class="w-full caption-bottom text-sm">
					<thead class="[&_tr]:border-b">
					<tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
						<th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Book / Model</th>
						<th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Status</th>
						<th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0 w-1/3">Progress</th>
						<th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground [&:has([role=checkbox])]:pr-0">Actions</th>
					</tr>
					</thead>
					<tbody class="[&_tr:last-child]:border-0">
					<?php foreach ($projects_summary as $project): ?>
						<tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
							<td class="p-4 align-middle font-medium">
								<?php echo $project['name']; ?>
								<div class="text-xs text-muted-foreground font-normal"><?php echo ucfirst($project['llm_service']); ?>: <?php echo htmlspecialchars($project['model_name']); ?></div>
							</td>
							<td class="p-4 align-middle">
								<span id="status-<?php echo $project['id']; ?>"></span>
							</td>
							<td class="p-4 align-middle">
								<div class="w-full bg-secondary rounded-full h-2">
									<div id="progress-bar-<?php echo $project['id']; ?>" class="bg-primary h-2 rounded-full transition-all duration-500" style="width: 0%"></div>
								</div>
								<div id="progress-text-<?php echo $project['id']; ?>" class="text-center text-xs text-muted-foreground mt-1"></div>
							</td>
							<td class="p-4 align-middle">
								<div class="flex items-center gap-2">
									<button class="btn-control" data-project-id="<?php echo $project['id']; ?>"></button>
									<button class="btn-export inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-9 px-3" data-project-id="<?php echo $project['id']; ?>">Export</button>
									<form action="api/delete_project.php" method="post" onsubmit="return confirm('Are you sure? This cannot be undone.');">
										<input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
										<button type="submit" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-destructive text-destructive-foreground hover:bg-destructive/90 h-9 px-3">Delete</button>
									</form>
								</div>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
<?php
	require_once 'includes/api_key_modal.php';
// Add the new Clone/Edit modal before the footer
	require_once 'includes/clone_edit_modal.php';
	require_once 'includes/footer.php'; // This now contains the JS and closing tags
?>
