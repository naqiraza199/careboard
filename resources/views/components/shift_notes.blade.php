    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.noteTypes = @json($noteTypes);

        function openAttachmentPopup(el) {
            const attachments = JSON.parse(el.dataset.attachments || '[]');
            if (!attachments.length) return;

            const imageExts = ['jpg', 'jpeg', 'png', 'gif', 'tif', 'tiff', 'bmp', 'webp'];
            const fileIcons = { pdf: '📄', doc: '📝', docx: '📝', xls: '📊', xlsx: '📊', csv: '📊', txt: '📋', zip: '🗜️', eml: '📧' };

            let html = '<div style="display:flex;flex-direction:column;gap:12px;max-height:70vh;overflow-y:auto;padding:4px;">';
            attachments.forEach(att => {
                const ext = att.name.split('.').pop().toLowerCase();
                const isImage = imageExts.includes(ext);
                const icon = fileIcons[ext] || '📎';

                if (isImage) {
                    html += `<div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;">
                        <img src="${att.url}" style="max-width:100%;border-radius:6px;display:block;margin-bottom:8px;" alt="${att.name}">
                        <a href="${att.url}" download="${att.name}" style="display:inline-block;background:#3b82f6;color:#fff;padding:6px 14px;border-radius:4px;font-size:13px;text-decoration:none;">⬇ Download</a>
                    </div>`;
                } else {
                    html += `<div style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;display:flex;align-items:center;gap:12px;">
                        <span style="font-size:28px;">${icon}</span>
                        <div style="flex:1;font-size:14px;font-weight:500;word-break:break-all;">${att.name}</div>
                        <a href="${att.url}" download="${att.name}" style="flex-shrink:0;background:#3b82f6;color:#fff;padding:6px 14px;border-radius:4px;font-size:13px;text-decoration:none;">⬇ Download</a>
                    </div>`;
                }
            });
            html += '</div>';

            Swal.fire({
                title: 'Attachments',
                html: html,
                width: '600px',
                showConfirmButton: false,
                showCloseButton: true,
            });
        }
    document.addEventListener('open-add-notes-modal', () => {
        Swal.fire({
            title: '📝 Add Shift Note',
            html: `
                <style>
                    .swal2-show { width: 60%; }
                    .swal-form-group { margin-bottom: 1rem; text-align: left; }
                    .swal-label { font-weight: 600; margin-bottom: 0.3rem; color: #374151; }
                    .swal-input, .swal-textarea, .swal-select, .swal-file { width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;margin-top: 10px; }
                    .swal-input:focus, .swal-textarea:focus, .swal-select:focus { border-color: #3b82f6; box-shadow: 0 0 0 1px #3b82f6; }
                    .swal-checkbox { display: flex; align-items: center; gap: 8px; }
                    .file-list { margin-top: 0.5rem; border: 1px dashed #d1d5db; padding: 6px; max-height: 120px; overflow-y: auto; }
                    .file-item { display: flex; justify-content: space-between; padding: 4px; margin-bottom: 4px; }
                    .file-remove { color: #ef4444; cursor: pointer; }
                    .mileage-input { display: none; margin-top: 0.5rem; }
                    .mileage-input.active { display: block; }
                </style>
                <div class="swal-form-group">
                    <label for="noteType" class="swal-label">Note Type</label>
                    <select id="noteType" class="swal-select">
                        @foreach($noteTypes as $note)
                            <option value="{{ $note->type }}">{{ $note->type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="swal-form-group">
                    <label for="noteBody" class="swal-label">Note Body</label>
                    <textarea id="noteBody" class="swal-textarea" rows="3" placeholder="Enter your note..."></textarea>
                </div>
                <div class="swal-form-group mileage-input" id="mileageInput">
                    <label for="mileage" class="swal-label">Mileage (km)</label>
                    <input type="number" id="mileage" class="swal-input" placeholder="Enter mileage" min="0" step="0.1">
                </div>
                <div class="swal-form-group">
                    <label class="swal-checkbox">
                        <input type="checkbox" id="keepPrivate"> Keep note private
                    </label>
                </div>
                <div class="swal-form-group">
                    <label for="attachments" class="swal-label">Attach Documents</label>
                    <input type="file" id="attachments" class="swal-file" multiple accept=".jpg,.jpeg,.gif,.png,.tif,.doc,.docx,.xls,.xlsx,.csv,.pdf,.txt,.zip,.eml">
                    <div id="attachedFiles" class="file-list"></div>
                    <small style="color:#6b7280">Allowed: jpg, jpeg, gif, png, tif, doc, docx, xls, xlsx, csv, pdf, txt, zip, eml</small>
                </div>
                <div class="swal-form-group" id="incidentActions" style="display:none; margin-top:10px; text-align:right;">
                        <button type="button" id="applyIncident" style="background:#3b82f6;color:#fff;border:none;padding:6px 12px;border-radius:4px;margin-right:5px;cursor:pointer;">Apply Header</button>
                        <button type="button" id="dismissIncident" style="background:#ef4444;color:#fff;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">Dismiss</button>
                    </div>

            `,
            showCancelButton: true,
            confirmButtonText: 'Add Note',
            showLoaderOnConfirm: true,
            allowOutsideClick: () => !Swal.isLoading(),
            preConfirm: () => {
            const noteType = document.getElementById('noteType').value;
            const noteBody = document.getElementById('noteBody').value.trim();
            const keepPrivate = document.getElementById('keepPrivate').checked;
            const mileage = noteType === 'Mileage' ? parseFloat(document.getElementById('mileage').value) || null : null;
            const attachments = document.getElementById('attachments').files;

            if (!noteBody) {
                Swal.showValidationMessage('Please enter a note body');
                return false;
            }

            return new Promise((resolve) => {
                const noteAddedHandler = () => {
                    window.removeEventListener('note-added', noteAddedHandler);
                    resolve(true);
                };
                window.addEventListener('note-added', noteAddedHandler);

                const dispatchSave = () => {
                    Livewire.dispatch('saveNotes', { noteType, noteBody, keepPrivate, mileage });
                };

                if (attachments.length > 0) {
                    @this.uploadMultiple('attachments', attachments,
                        dispatchSave,
                        () => {
                            window.removeEventListener('note-added', noteAddedHandler);
                            Swal.showValidationMessage('File upload failed. Please try again.');
                        }
                    );
                } else {
                    dispatchSave();
                }
            });
        },
        didOpen: () => {
            const noteTypeSelect = document.getElementById('noteType');
            const noteBodyTextarea = document.getElementById('noteBody');
            const mileageInput = document.getElementById('mileageInput');
            const attachmentsInput = document.getElementById('attachments');
            const attachedFilesDiv = document.getElementById('attachedFiles');
            const applyBtn = document.getElementById('applyIncident');
            const dismissBtn = document.getElementById('dismissIncident');
            const incidentActions = document.getElementById('incidentActions');

            let originalIncidentBody = "";

            noteTypeSelect.addEventListener('change', () => {
                const selectedType = noteTypeSelect.value;
                const note = window.noteTypes.find(n => n.type === selectedType);

                noteBodyTextarea.value = note ? note.body : '';
                originalIncidentBody = note ? note.body : '';

                incidentActions.style.display = (selectedType === 'Incident') ? 'none' : 'none';
                mileageInput.classList.toggle('active', selectedType === 'Mileage');
            });

            // Watch for edits in Incident textarea
            noteBodyTextarea.addEventListener('input', () => {
                if (noteTypeSelect.value === 'Incident') {
                    // Show buttons only if textarea differs from original
                    incidentActions.style.display = (noteBodyTextarea.value !== originalIncidentBody) ? 'block' : 'none';
                }
            });

            // Apply button → update DB but keep modal open
            applyBtn.addEventListener('click', () => {
                Livewire.dispatch('updateIncidentHeader', { body: noteBodyTextarea.value });
                // ✅ Do NOT close modal
            });

            // Dismiss button → revert changes
            dismissBtn.addEventListener('click', () => {
                noteBodyTextarea.value = originalIncidentBody;
                incidentActions.style.display = 'none';
            });

            attachmentsInput.addEventListener('change', () => {
                attachedFilesDiv.innerHTML = '';
                Array.from(attachmentsInput.files).forEach((file, i) => {
                    const fileDiv = document.createElement('div');
                    fileDiv.className = 'file-item';
                    fileDiv.innerHTML = `<span>${file.name}</span><span class="file-remove" data-index="${i}">✖</span>`;
                    attachedFilesDiv.appendChild(fileDiv);
                });

                attachedFilesDiv.querySelectorAll('.file-remove').forEach(icon => {
                    icon.addEventListener('click', () => {
                        const index = parseInt(icon.dataset.index);
                        const dt = new DataTransfer();
                        Array.from(attachmentsInput.files).filter((_, i) => i !== index).forEach(file => dt.items.add(file));
                        attachmentsInput.files = dt.files;
                        attachmentsInput.dispatchEvent(new Event('change'));
                    });
                });
            });
        }
        }).then(() => {
            // Note saved and redirect handled by Livewire
        });
        });

            window.addEventListener('incident-header-updated', e => {
                const note = window.noteTypes.find(n => n.type === 'Incident');
                if (note) note.body = e.detail.body;
            });




    </script>
    @endpush