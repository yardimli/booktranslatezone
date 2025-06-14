<!-- Export Modal (Dialog) -->
<div id="export-modal" class="hidden fixed inset-0 z-50 bg-background/80 backdrop-blur-sm">
	<div class="fixed left-[50%] top-[50%] z-50 grid w-full max-w-lg translate-x-[-50%] translate-y-[-50%] gap-4 border bg-card p-6 shadow-lg duration-200 rounded-lg">
		<h2 class="text-lg font-semibold leading-none tracking-tight">Export Successful</h2>
		<p class="text-sm text-muted-foreground">Your files have been generated. Click to open in a new tab:</p>
		<ul id="export-file-list" class="space-y-2 pt-2">
			<!-- JS will populate this -->
		</ul>
		<div class="flex flex-col-reverse sm:flex-row sm:justify-end sm:space-x-2 mt-4">
			<button id="close-modal-btn" class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 bg-secondary text-secondary-foreground hover:bg-secondary/80 h-10 px-4 py-2">Close</button>
		</div>
	</div>
</div>

<script>
		document.addEventListener('DOMContentLoaded', function() {
		// --- IMPORTANT NOTE ---
		// This version uses client-side AJAX to process translations.
		// The browser tab MUST remain open for the translation to continue.

		// --- API KEY MODAL (Unchanged) ---
		const apiKeyModal = document.getElementById('api-key-modal');
		const openModalBtn = document.getElementById('api-key-btn');
		const closeModalBtn = document.getElementById('close-api-modal-btn');
		const saveKeysBtn = document.getElementById('save-api-keys-btn');
		const apiKeyStatus = document.getElementById('api-key-status');
		if (openModalBtn) openModalBtn.addEventListener('click', () => apiKeyModal.classList.remove('hidden'));
		if (closeModalBtn) closeModalBtn.addEventListener('click', () => apiKeyModal.classList.add('hidden'));
		if (apiKeyModal) apiKeyModal.addEventListener('click', (e) => {
		if (e.target === apiKeyModal) apiKeyModal.classList.add('hidden');
	});
		if (saveKeysBtn) saveKeysBtn.addEventListener('click', () => {
		const formData = new FormData(document.getElementById('api-key-form'));
		saveKeysBtn.disabled = true;
		saveKeysBtn.textContent = 'Saving...';
		apiKeyStatus.textContent = '';
		fetch('api/save_keys.php', { method: 'POST', body: formData })
		.then(res => res.json())
		.then(data => {
		if (data.success) {
		apiKeyStatus.className = 'text-sm text-green-600';
		apiKeyStatus.textContent = 'Keys saved for this session!';
		setTimeout(() => apiKeyModal.classList.add('hidden'), 1500);
	} else {
		apiKeyStatus.className = 'text-sm text-red-600';
		apiKeyStatus.textContent = 'Error: ' + data.message;
	}
	}).catch(err => {
		apiKeyStatus.className = 'text-sm text-red-600';
		apiKeyStatus.textContent = 'A network error occurred.';
	}).finally(() => {
		saveKeysBtn.disabled = false;
		saveKeysBtn.textContent = 'Save Keys';
	});
	});

		// --- CLONE & EDIT MODAL ---
		const cloneModal = document.getElementById('clone-modal');
		const clonePromptBtn = document.getElementById('clone-prompt-btn');
		const cloneExampleBtn = document.getElementById('clone-example-btn');
		const cancelCloneBtn = document.getElementById('cancel-clone-btn');
		const saveCloneBtn = document.getElementById('save-clone-btn');
		const cloneModalTitle = document.getElementById('clone-modal-title');
		const cloneForm = document.getElementById('clone-form');
		const cloneTypeInput = document.getElementById('clone-type');
		const cloneNameInput = document.getElementById('clone-new-name');
		const cloneContentInput = document.getElementById('clone-content');
		const cloneStatus = document.getElementById('clone-status');

		function openCloneModal(type) {
		const isPrompt = type === 'prompt';
		const sourceSelect = isPrompt ? document.getElementById('prompt_file') : document.getElementById('examples_file');
		const sourceFile = sourceSelect.value;

		if (!sourceFile) {
		alert('Please select a file to clone first.');
		return;
	}

		cloneModalTitle.textContent = isPrompt ? 'Clone & Edit Prompt' : 'Clone & Edit Examples';
		cloneTypeInput.value = type;
		cloneNameInput.value = `My Copy of ${sourceFile.split('.').slice(0, -1).join('.')}`;
		cloneContentInput.value = 'Loading...';
		cloneStatus.textContent = '';
		cloneModal.classList.remove('hidden');

		fetch(`api/get_file_content.php?type=${type}&file=${encodeURIComponent(sourceFile)}`)
		.then(res => res.json())
		.then(data => {
		if (data.success) {
		cloneContentInput.value = data.content;
	} else {
		cloneContentInput.value = `Error: ${data.message}`;
	}
	})
		.catch(err => {
		cloneContentInput.value = 'Error loading file content.';
	});
	}

		if(clonePromptBtn) clonePromptBtn.addEventListener('click', () => openCloneModal('prompt'));
		if(cloneExampleBtn) cloneExampleBtn.addEventListener('click', () => openCloneModal('example'));

		if(cancelCloneBtn) cancelCloneBtn.addEventListener('click', () => {
		cloneModal.classList.add('hidden');
	});

		if(cloneModal) cloneModal.addEventListener('click', (e) => {
		if (e.target === cloneModal) {
		cloneModal.classList.add('hidden');
	}
	});

		if(saveCloneBtn) saveCloneBtn.addEventListener('click', () => {
		const formData = new FormData(cloneForm);
		saveCloneBtn.disabled = true;
		saveCloneBtn.textContent = 'Saving...';
		cloneStatus.textContent = '';

		fetch('api/clone_and_save.php', { method: 'POST', body: formData })
		.then(res => res.json())
		.then(data => {
		if (data.success) {
		cloneStatus.className = 'text-sm text-green-600';
		cloneStatus.textContent = data.message;

		const type = cloneTypeInput.value;
		const select = type === 'prompt' ? document.getElementById('prompt_file') : document.getElementById('examples_file');
		let optgroup = select.querySelector('optgroup[label="Custom Prompts"]') || select.querySelector('optgroup[label="Custom Examples"]');

		if (!optgroup) {
		optgroup = document.createElement('optgroup');
		optgroup.label = type === 'prompt' ? 'Custom Prompts' : 'Custom Examples';
		select.prepend(optgroup);
	}

		const newOption = document.createElement('option');
		newOption.value = data.new_file.value;
		newOption.textContent = data.new_file.text;
		optgroup.prepend(newOption);
		newOption.selected = true;

		setTimeout(() => {
		cloneModal.classList.add('hidden');
	}, 1000);
	} else {
		cloneStatus.className = 'text-sm text-red-600';
		cloneStatus.textContent = `Error: ${data.message}`;
	}
	})
		.catch(err => {
		cloneStatus.className = 'text-sm text-red-600';
		cloneStatus.textContent = 'A network error occurred.';
	})
		.finally(() => {
		saveCloneBtn.disabled = false;
		saveCloneBtn.textContent = 'Save Custom File';
	});
	});

		// --- DYNAMIC MODEL INPUT (Unchanged) ---
		const defaultModels = <?php echo json_encode($default_models ?? []); ?>;
		const serviceSelect = document.getElementById('llm_service');
		const modelInput = document.getElementById('model_name_input');
		const modelSelect = document.getElementById('model_name_select');
		const hiddenModelInput = document.getElementById('model_name_hidden');
		const refreshBtn = document.getElementById('refresh-models-btn');

		function toggleModelInput() {
		if (!serviceSelect) return;
		const selectedService = serviceSelect.value;
		if (selectedService === 'openrouter') {
		modelInput.classList.add('hidden');
		modelSelect.classList.remove('hidden');
		refreshBtn.classList.remove('hidden');
		hiddenModelInput.value = modelSelect.value;
	} else {
		modelInput.classList.remove('hidden');
		modelSelect.classList.add('hidden');
		refreshBtn.classList.add('hidden');
		modelInput.value = defaultModels[selectedService] || '';
		hiddenModelInput.value = modelInput.value;
	}
	}
		if (serviceSelect) serviceSelect.addEventListener('change', toggleModelInput);
		if (modelInput) modelInput.addEventListener('input', () => hiddenModelInput.value = modelInput.value);
		if (modelSelect) modelSelect.addEventListener('change', () => hiddenModelInput.value = modelSelect.value);
		if (refreshBtn) refreshBtn.addEventListener('click', function() {
		this.disabled = true;
		this.textContent = 'Refreshing...';
		fetch('api/refresh_models.php', { method: 'POST' })
		.then(response => response.json())
		.then(data => {
		if (data.success) {
		alert(data.message);
		location.reload();
	} else {
		alert('Error: ' + data.message);
		this.disabled = false;
		this.textContent = 'Refresh List';
	}
	}).catch(error => {
		alert('A network error occurred.');
		this.disabled = false;
		this.textContent = 'Refresh List';
	});
	});
		toggleModelInput();

		// --- NEW: AJAX-BASED TRANSLATION LOGIC ---
		const projects = <?php echo json_encode($projects_summary); ?>;
		const translationJobs = {}; // Stores the state of each running job

		// Find the first section that isn't 'done'
		function findNextSectionIndex(project) {
			if (!project || !project.progress_total) return 0;
			return project.progress_done; // Since 'done' is a count, it's also the index of the next item
		}

		async function translateNextSection(projectId) {
			const job = translationJobs[projectId];
			const project = projects.find(p => p.id === projectId);

			if (!job || !job.isRunning || !project) {
				updateUI(projectId, { status: 'paused', done: job.currentIndex, total: project.progress_total });
				return; // Job was stopped or completed
			}

			const sectionIndex = job.currentIndex;

			if (sectionIndex >= project.progress_total) {
				job.isRunning = false;
				updateUI(projectId, { status: 'complete', done: project.progress_total, total: project.progress_total });
				return; // All sections are done
			}

			// Update UI to show we are working on the current section
			updateUI(projectId, { status: 'translating', done: sectionIndex, total: project.progress_total });

			const formData = new FormData();
			formData.append('project_id', projectId);
			formData.append('section_index', sectionIndex);

			try {
				const response = await fetch('api/translate_section.php', { method: 'POST', body: formData });
				if (!response.ok) {
					throw new Error(`Server responded with status ${response.status}`);
				}
				const data = await response.json();

				if (data.success) {
					job.currentIndex++;
					project.progress_done++; // Manually update our client-side count
					// Call the next iteration
					setTimeout(() => translateNextSection(projectId), 100); // Small delay
				} else {
					throw new Error(data.message || 'An unknown error occurred on the server.');
				}
			} catch (error) {
				job.isRunning = false;
				updateUI(projectId, { status: 'error', done: sectionIndex, total: project.progress_total });
				alert(`Translation failed for project ${project.name}: ${error.message}`);
			}
		}

		function handleControlButtonClick(event) {
			const button = event.target;
			const projectId = button.dataset.projectId;
			const action = button.textContent.toLowerCase();

			const formData = new FormData();
			formData.append('project_id', projectId);

			if (action === 'translate' || action === 'resume' || action === 'retry') {
				formData.append('action', 'start'); // 'start', 'resume', 'retry' all initiate the process
				fetch('api/control_project.php', { method: 'POST', body: formData })
					.then(res => res.json())
					.then(data => {
						if (data.success) {
							const project = projects.find(p => p.id === projectId);
							const startIndex = findNextSectionIndex(project);
							translationJobs[projectId] = { isRunning: true, currentIndex: startIndex };
							translateNextSection(projectId);
						} else {
							alert(`Error: ${data.message}`);
						}
					}).catch(err => alert('Network error.'));

			} else if (action === 'stop') {
				if (translationJobs[projectId]) {
					translationJobs[projectId].isRunning = false; // Signal the loop to stop
				}
				formData.append('action', 'pause');
				fetch('api/control_project.php', { method: 'POST', body: formData }); // Inform server
				const project = projects.find(p => p.id === projectId);
				const currentIndex = translationJobs[projectId]?.currentIndex ?? project.progress_done;
				updateUI(projectId, { status: 'paused', done: currentIndex, total: project.progress_total });
			}
		}

		// --- UI AND EXPORT (Largely Unchanged) ---
		const exportModal = document.getElementById('export-modal');
		const closeExportModalBtn = document.getElementById('close-modal-btn');
		const exportFileList = document.getElementById('export-file-list');

		function showExportModal(singleFile, parallelFile) {
			exportFileList.innerHTML = `
            <li><a href="output/${singleFile}" target="_blank" class="font-medium text-primary underline-offset-4 hover:underline">Single View (Translation Only)</a></li>
            <li><a href="output/${parallelFile}" target="_blank" class="font-medium text-primary underline-offset-4 hover:underline">Parallel View (Side-by-Side)</a></li>
        `;
			exportModal.classList.remove('hidden');
		}
		function hideModal() { exportModal.classList.add('hidden'); }
		closeExportModalBtn.addEventListener('click', hideModal);
		exportModal.addEventListener('click', (event) => { if (event.target === exportModal) hideModal(); });

		function handleExportClick(event) {
			const button = event.target;
			const projectId = button.dataset.projectId;
			button.disabled = true; button.textContent = '...';
			const formData = new FormData();
			formData.append('project_id', projectId);
			fetch(`api/export.php`, { method: 'POST', body: formData })
				.then(response => response.json())
				.then(data => {
					if (data.success) { showExportModal(data.single_file, data.parallel_file); }
					else { alert(`Export failed: ${data.message}`); }
				})
				.catch(err => { alert('A network error occurred during export.'); })
				.finally(() => { button.disabled = false; button.textContent = 'Export'; });
		}

		function updateUI(projectId, data) {
			const progressBar = document.getElementById(`progress-bar-${projectId}`);
			const progressText = document.getElementById(`progress-text-${projectId}`);
			const statusBadge = document.getElementById(`status-${projectId}`);
			const controlBtn = document.querySelector(`.btn-control[data-project-id="${projectId}"]`);
			const exportBtn = document.querySelector(`.btn-export[data-project-id="${projectId}"]`);
			if (!progressBar || !progressText || !statusBadge || !controlBtn || !exportBtn) return;

			const percent = data.total > 0 ? (data.done / data.total) * 100 : 0;
			progressBar.style.width = `${percent}%`;
			progressText.textContent = `${data.done} / ${data.total} sections (${Math.round(percent)}%)`;

			const badgeBaseClasses = 'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2';
			const buttonBaseClasses = 'btn-control inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 h-9 px-3';

			statusBadge.textContent = data.status;
			statusBadge.className = badgeBaseClasses;
			controlBtn.className = buttonBaseClasses;
			controlBtn.disabled = false;

			switch (data.status) {
				case 'translating':
					statusBadge.classList.add('border-transparent', 'bg-yellow-500/20', 'text-yellow-700');
					controlBtn.textContent = 'Stop';
					controlBtn.classList.add('bg-destructive', 'text-destructive-foreground', 'hover:bg-destructive/90');
					break;
				case 'paused':
					statusBadge.classList.add('border-transparent', 'bg-secondary', 'text-secondary-foreground');
					controlBtn.textContent = 'Resume';
					controlBtn.classList.add('bg-secondary', 'text-secondary-foreground', 'hover:bg-secondary/80');
					break;
				case 'complete':
					statusBadge.classList.add('border-transparent', 'bg-green-500/20', 'text-green-700');
					controlBtn.textContent = 'Complete';
					controlBtn.classList.add('bg-secondary', 'text-secondary-foreground', 'opacity-50', 'cursor-not-allowed');
					controlBtn.disabled = true;
					break;
				case 'error':
					statusBadge.classList.add('border-transparent', 'bg-destructive', 'text-destructive-foreground');
					controlBtn.textContent = 'Retry';
					controlBtn.classList.add('bg-primary', 'text-primary-foreground', 'hover:bg-primary/90');
					break;
				default: // 'new'
					statusBadge.classList.add('border-transparent', 'bg-primary/20', 'text-primary');
					controlBtn.textContent = 'Translate';
					controlBtn.classList.add('bg-primary', 'text-primary-foreground', 'hover:bg-primary/90');
					break;
			}
			exportBtn.disabled = (data.status === 'new' || data.done === 0);
		}

		// --- Initial Page Load ---
		projects.forEach(project => {
			updateUI(project.id, {
				done: project.progress_done,
				total: project.progress_total,
				status: project.status
			});
			const controlBtn = document.querySelector(`.btn-control[data-project-id="${project.id}"]`);
			if (controlBtn) controlBtn.addEventListener('click', handleControlButtonClick);
			const exportBtn = document.querySelector(`.btn-export[data-project-id="${project.id}"]`);
			if (exportBtn) exportBtn.addEventListener('click', handleExportClick);
		});
	});
</script>
</body>
</html>
