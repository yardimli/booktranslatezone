// booktranslatezone/js/dashboard.js
document.addEventListener('DOMContentLoaded', function () {
	// Access data passed from PHP via the initialData object
	const projects = initialData.projects;
	const defaultModels = initialData.defaultModels;
	
	// --- THEME TOGGLE ---
	const themeToggleButton = document.getElementById('theme-toggle-btn');
	if (themeToggleButton) {
		themeToggleButton.addEventListener('click', () => {
			const isDark = document.documentElement.classList.toggle('dark');
			localStorage.setItem('theme', isDark ? 'dark' : 'light');
		});
	}
	
	// --- IMPORTANT NOTE ---
	// This version uses client-side AJAX to process translations.
	// The browser tab MUST remain open for the translation to continue.
	
	// --- API KEY MODAL ---
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
		fetch('api/save_keys.php', {method: 'POST', body: formData})
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
	const originalFileInput = document.getElementById('clone-original-file');
	const promptSelect = document.getElementById('prompt_file');
	const exampleSelect = document.getElementById('examples_file');
	
	function updateCloneButton(type) {
		const select = type === 'prompt' ? promptSelect : exampleSelect;
		const button = type === 'prompt' ? clonePromptBtn : cloneExampleBtn;
		if (!select || !button) return;
		const selectedOption = select.options[select.selectedIndex];
		if (selectedOption && selectedOption.parentElement.tagName === 'OPTGROUP' && selectedOption.parentElement.label.startsWith('Custom')) {
			button.textContent = 'Edit';
		} else {
			button.textContent = 'Clone & Edit';
		}
	}
	
	function openCloneModal(type) {
		const isPrompt = type === 'prompt';
		const sourceSelect = isPrompt ? promptSelect : exampleSelect;
		const sourceFile = sourceSelect.value;
		if (!sourceFile) {
			alert('Please select a file to clone or edit first.');
			return;
		}
		const selectedOption = sourceSelect.options[sourceSelect.selectedIndex];
		const isEditMode = selectedOption && selectedOption.parentElement.tagName === 'OPTGROUP' && selectedOption.parentElement.label.startsWith('Custom');
		cloneModalTitle.textContent = isEditMode ? (isPrompt ? 'Edit Custom Prompt' : 'Edit Custom Example') : (isPrompt ? 'Clone & Edit Prompt' : 'Clone & Edit Examples');
		cloneTypeInput.value = type;
		cloneContentInput.value = 'Loading...';
		cloneStatus.textContent = '';
		saveCloneBtn.textContent = isEditMode ? 'Save Changes' : 'Save Custom File';
		if (isEditMode) {
			cloneNameInput.value = sourceFile.split('.').slice(0, -1).join('.');
			cloneNameInput.readOnly = true;
			cloneNameInput.classList.add('bg-secondary', 'cursor-not-allowed');
			originalFileInput.value = sourceFile;
		} else {
			cloneNameInput.value = `My Copy of ${sourceFile.split('.').slice(0, -1).join('.')}`;
			cloneNameInput.readOnly = false;
			cloneNameInput.classList.remove('bg-secondary', 'cursor-not-allowed');
			originalFileInput.value = '';
		}
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
	
	function closeCloneModal() {
		cloneModal.classList.add('hidden');
		cloneNameInput.readOnly = false;
		cloneNameInput.classList.remove('bg-secondary', 'cursor-not-allowed');
	}
	
	if (promptSelect) {
		promptSelect.addEventListener('change', () => updateCloneButton('prompt'));
		updateCloneButton('prompt');
	}
	if (exampleSelect) {
		exampleSelect.addEventListener('change', () => updateCloneButton('example'));
		updateCloneButton('example');
	}
	if (clonePromptBtn) clonePromptBtn.addEventListener('click', () => openCloneModal('prompt'));
	if (cloneExampleBtn) cloneExampleBtn.addEventListener('click', () => openCloneModal('example'));
	if (cancelCloneBtn) cancelCloneBtn.addEventListener('click', closeCloneModal);
	if (cloneModal) cloneModal.addEventListener('click', (e) => {
		if (e.target === cloneModal) closeCloneModal();
	});
	if (saveCloneBtn) saveCloneBtn.addEventListener('click', () => {
		const formData = new FormData(cloneForm);
		const originalText = saveCloneBtn.textContent;
		saveCloneBtn.disabled = true;
		saveCloneBtn.textContent = 'Saving...';
		cloneStatus.textContent = '';
		fetch('api/clone_and_save.php', {method: 'POST', body: formData})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					cloneStatus.className = 'text-sm text-green-600';
					cloneStatus.textContent = data.message;
					const isEditMode = originalFileInput.value !== '';
					if (!isEditMode && data.new_file) {
						// Only add to dropdown if it was a clone (new file)
						const type = cloneTypeInput.value;
						const select = type === 'prompt' ? promptSelect : exampleSelect;
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
						updateCloneButton(type); // Update button text to "Edit"
					}
					setTimeout(closeCloneModal, 1500);
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
				saveCloneBtn.textContent = originalText;
			});
	});
	
	// --- DYNAMIC MODEL INPUT ---
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
	if (refreshBtn) refreshBtn.addEventListener('click', function () {
		this.disabled = true;
		this.textContent = 'Refreshing...';
		fetch('api/refresh_models.php', {method: 'POST'})
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
	
	// --- AJAX-BASED TRANSLATION LOGIC ---
	const translationJobs = {}; // Stores the state of each running job
	
	async function translateNextSection(projectId) {
		const job = translationJobs[projectId];
		const project = projects.find(p => p.id === projectId);
		
		if (!job || !job.isRunning || !project) {
			updateUI(projectId, {status: 'paused', done: project.progress_done, total: project.progress_total});
			return; // Job was stopped or completed
		}
		
		// Update UI to show we are working
		updateUI(projectId, {status: 'translating', done: project.progress_done, total: project.progress_total});
		
		const formData = new FormData();
		formData.append('project_id', projectId);
		// We no longer send section_index; the server finds the next pending one.
		
		try {
			const response = await fetch('api/translate_section.php', {method: 'POST', body: formData});
			if (!response.ok) {
				throw new Error(`Server responded with status ${response.status}`);
			}
			const data = await response.json();
			
			if (!data.success) {
				throw new Error(data.message || 'An unknown error occurred on the server.');
			}
			
			// Update client-side project data with accurate count from server
			project.progress_done = data.new_progress_done || project.progress_done;
			
			if (data.completed) {
				// Project is fully translated
				job.isRunning = false;
				project.progress_done = project.progress_total;
				updateUI(projectId, {status: 'complete', done: project.progress_total, total: project.progress_total});
				return; // End the loop
			}
			
			// A section was translated successfully, continue to the next one
			setTimeout(() => translateNextSection(projectId), 100); // Small delay
			
		} catch (error) {
			job.isRunning = false;
			updateUI(projectId, {status: 'error', done: project.progress_done, total: project.progress_total});
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
			fetch('api/control_project.php', {method: 'POST', body: formData})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						translationJobs[projectId] = {isRunning: true};
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
			fetch('api/control_project.php', {method: 'POST', body: formData}); // Inform server
			const project = projects.find(p => p.id === projectId);
			updateUI(projectId, {status: 'paused', done: project.progress_done, total: project.progress_total});
		}
	}
	
	// --- UI AND EXPORT ---
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
	
	function hideModal() {
		exportModal.classList.add('hidden');
	}
	closeExportModalBtn.addEventListener('click', hideModal);
	exportModal.addEventListener('click', (event) => {
		if (event.target === exportModal) hideModal();
	});
	
	function handleExportClick(event) {
		const button = event.target;
		const projectId = button.dataset.projectId;
		button.disabled = true;
		button.textContent = '...';
		const formData = new FormData();
		formData.append('project_id', projectId);
		fetch(`api/export.php`, {method: 'POST', body: formData})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					showExportModal(data.single_file, data.parallel_file);
				} else {
					alert(`Export failed: ${data.message}`);
				}
			})
			.catch(err => {
				alert('A network error occurred during export.');
			})
			.finally(() => {
				button.disabled = false;
				button.textContent = 'Export';
			});
	}
	
	function updateUI(projectId, data) {
		const progressBar = document.getElementById(`progress-bar-${projectId}`);
		const progressText = document.getElementById(`progress-text-${projectId}`);
		const statusBadge = document.getElementById(`status-${projectId}`);
		const controlBtn = document.querySelector(`.btn-control[data-project-id="${projectId}"]`);
		const exportBtn = document.querySelector(`.btn-export[data-project-id="${projectId}"]`);
		const viewBtn = document.querySelector(`.btn-view[data-project-id="${projectId}"]`);
		
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
		
		const hasProgress = data.done > 0;
		exportBtn.disabled = !hasProgress;
		if (viewBtn) {
			if (hasProgress) {
				viewBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
			} else {
				viewBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
			}
		}
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
