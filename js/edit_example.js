// booktranslatezone/js/edit_example.js
document.addEventListener('DOMContentLoaded', () => {
	const form = document.getElementById('edit-examples-form');
	const container = document.getElementById('examples-container');
	const addRowBtn = document.getElementById('add-row-btn');
	const statusDiv = document.getElementById('status-message');
	const saveBtn = document.getElementById('save-changes-btn');
	
	const createRowElement = () => {
		const row = document.createElement('div');
		row.className = 'example-row grid grid-cols-12 gap-4 items-start p-2 rounded-md border bg-card';
		row.innerHTML = `
            <div class="col-span-5">
                <textarea name="source[]" class="flex min-h-[250px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-sans" placeholder="Source text..."></textarea>
            </div>
            <div class="col-span-5">
                <textarea name="target[]" class="flex min-h-[250px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm font-sans" placeholder="Target text..."></textarea>
            </div>
            <div class="col-span-2 flex items-center justify-end">
                <button type="button" class="delete-row-btn inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium h-9 w-9 bg-destructive text-destructive-foreground hover:bg-destructive/90" title="Delete Row">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                </button>
            </div>
        `;
		return row;
	};
	
	// Event delegation for delete buttons
	container.addEventListener('click', (e) => {
		const deleteButton = e.target.closest('.delete-row-btn');
		if (deleteButton) {
			deleteButton.closest('.example-row').remove();
		}
	});
	
	addRowBtn.addEventListener('click', () => {
		const newRow = createRowElement();
		container.appendChild(newRow);
		newRow.querySelector('textarea').focus();
	});
	
	form.addEventListener('submit', (e) => {
		e.preventDefault();
		
		const sourceInputs = form.querySelectorAll('textarea[name="source[]"]');
		const targetInputs = form.querySelectorAll('textarea[name="target[]"]');
		
		const examples = [];
		for (let i = 0; i < sourceInputs.length; i++) {
			const sourceText = sourceInputs[i].value.trim();
			const targetText = targetInputs[i].value.trim();
			
			// Don't save completely empty rows
			if (sourceText !== '' || targetText !== '') {
				examples.push([sourceText, targetText]);
			}
		}
		
		const content = JSON.stringify(examples, null, 2); // Pretty print JSON
		const filename = document.getElementById('filename').value;
		
		const formData = new FormData();
		formData.append('type', 'example');
		formData.append('original_file', filename); // This tells the API it's an update
		formData.append('new_name', filename); // Required field, not used for update filename logic
		formData.append('content', content);
		
		// UI feedback
		saveBtn.disabled = true;
		saveBtn.textContent = 'Saving...';
		statusDiv.innerHTML = '';
		
		fetch('api/clone_and_save.php', {
			method: 'POST',
			body: formData
		})
			.then(res => res.json())
			.then(data => {
				if (data.success) {
					statusDiv.innerHTML = `<div class="p-3 text-sm text-green-700 bg-green-100 rounded-md">${data.message}</div>`;
				} else {
					statusDiv.innerHTML = `<div class="p-3 text-sm text-red-700 bg-red-100 rounded-md">Error: ${data.message}</div>`;
				}
			})
			.catch(err => {
				statusDiv.innerHTML = `<div class="p-3 text-sm text-red-700 bg-red-100 rounded-md">A network error occurred. Please try again.</div>`;
			})
			.finally(() => {
				saveBtn.disabled = false;
				saveBtn.textContent = 'Save Changes';
			});
	});
});
