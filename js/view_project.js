// js/view_project.js
document.addEventListener('DOMContentLoaded', function () {
	const totalSections = document.querySelectorAll('.btn-delete-translation').length;
	
	document.querySelectorAll('.btn-delete-translation').forEach(button => {
		button.addEventListener('click', function (event) {
			const projectId = this.dataset.projectId;
			const sectionIndex = this.dataset.sectionIndex;
			
			if (!confirm('Are you sure you want to delete the translation for this section? This cannot be undone.')) {
				return;
			}
			
			const formData = new FormData();
			formData.append('project_id', projectId);
			formData.append('section_index', sectionIndex);
			
			const statusDiv = document.getElementById('action-status');
			statusDiv.innerHTML = `<div class="p-2 text-sm text-yellow-700 bg-yellow-100 rounded-md">Deleting...</div>`;
			this.disabled = true;
			
			fetch('api/delete_section_translation.php', {
				method: 'POST',
				body: formData
			})
				.then(res => res.json())
				.then(data => {
					if (data.success) {
						statusDiv.innerHTML = `<div class="p-2 text-sm text-green-700 bg-green-100 rounded-md">Translation for section ${parseInt(sectionIndex) + 1} deleted successfully. The project status has been updated.</div>`;
						
						// Update UI for the specific section
						const translationCell = document.getElementById(`translation-cell-${sectionIndex}`);
						translationCell.innerHTML = `<span class="text-muted-foreground italic">No translation yet.</span>`;
						this.disabled = true;
						
						// Update overall progress counter
						const progressCountSpan = document.getElementById('progress-count');
						progressCountSpan.textContent = data.new_progress_done;
						
					} else {
						statusDiv.innerHTML = `<div class="p-2 text-sm text-red-700 bg-red-100 rounded-md">Error: ${data.message}</div>`;
						this.disabled = false;
					}
				})
				.catch(err => {
					statusDiv.innerHTML = `<div class="p-2 text-sm text-red-700 bg-red-100 rounded-md">A network error occurred. Please try again.</div>`;
					this.disabled = false;
				});
		});
	});
});
