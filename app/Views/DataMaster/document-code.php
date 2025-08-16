<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<?php
// Ambil privilege dari session untuk submenu ini
$privileges = session()->get('privileges');
$currentSubmenu = 'document-code'; // atau sesuai dengan slug submenu Anda

// Set default privileges jika tidak ada
$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;

// Hitung jumlah kolom untuk colspan
$totalColumns = 4; // No, Type, Code, Document Name
if ($canUpdate || $canDelete) {
    $totalColumns = 5; // tambah kolom Action
}
?>

<div class="px-4 py-3 w-100">
    <h4>Predefined Document Code</h4>
    <hr>

    <!-- Filter Section -->
    <div class="bg-light border rounded p-3 mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label for="filterCategory" class="form-label fw-semibold">Filter Category</label>
                <div class="dropdown">
                    <input type="text" 
                           class="form-control dropdown-toggle" 
                           id="filterCategory" 
                           placeholder="Search categories..." 
                           data-bs-toggle="dropdown" 
                           aria-expanded="false"
                           autocomplete="off">
                    <ul class="dropdown-menu w-100" id="categoryDropdown" style="max-height: 200px; overflow-y: auto;">
                        <li><a class="dropdown-item" href="#" data-value="">All Categories</a></li>
                        <?php 
                        // Tampilkan semua kategori yang memiliki use_predefined_codes = 1
                        foreach ($kategori_dokumen as $kategori) {
                            if (isset($kategori['use_predefined_codes']) && $kategori['use_predefined_codes'] == 1) {
                                echo '<li><a class="dropdown-item" href="#" data-value="' . esc($kategori['id']) . '">' . esc($kategori['nama']) . '</a></li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            </div>
            <div class="col-xl-4 col-lg-4 col-md-6">
                <label for="searchDocument" class="form-label fw-semibold">Search Document</label>
                <input type="text" class="form-control" id="searchDocument" placeholder="Search documents...">
            </div>
            <div class="col-xl-4 col-lg-4 col-md-12">
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-secondary flex-fill" id="resetFilters">
                        <i class="bi bi-arrow-counterclockwise"></i> Reset
                    </button>
                    <?php if ($canCreate): ?>
                        <button type="button" class="btn btn-primary flex-fill" data-bs-toggle="modal" data-bs-target="#addModal">
                            <i class="fas fa-plus me-1"></i> Add Document Code
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="documentTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center">No</th>
                    <th>Category</th>
                    <th>Code</th>
                    <th>Document Name</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="documentTableBody">
            <?php if (!empty($kode_dokumen)) : ?>
                <?php foreach ($kode_dokumen as $kode): ?>
                <tr data-document-id="<?= $kode['id'] ?>" data-document-type-id="<?= $kode['document_type_id'] ?>">
                    <td class="text-center"></td> <!-- Kolom No akan diisi oleh DataTable -->
                    <td><?= esc($kode['kategori_nama']) ?></td>
                    <td><?= esc($kode['kode']) ?></td>
                    <td><?= esc($kode['nama']) ?></td>
                    <?php if ($canUpdate || $canDelete): ?>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if ($canUpdate): ?>
                                    <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                            onclick="editDocument(<?= $kode['id'] ?>, '<?= esc($kode['kategori_nama'], 'js') ?>', '<?= esc($kode['kode'], 'js') ?>', '<?= esc($kode['nama'], 'js') ?>', <?= $kode['document_type_id'] ?>)"
                                            title="Edit">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($canDelete): ?>
                                    <button class="btn btn-link p-0" onclick="confirmDocumentDelete(<?= $kode['id'] ?>)" title="Delete">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Modal -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Add Document Code</h5>
            </div>
            <form method="post" action="<?= base_url('document-code/add') ?>" id="addDocumentCodeForm" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="addDocumentType" class="form-label">Document Type <span class="text-danger">*</span></label>
                        <div class="dropdown">
                            <input type="text" 
                                   class="form-control dropdown-toggle doccode-text-uppercase-auto" 
                                   id="addDocumentType" 
                                   placeholder="Search document types..." 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false"
                                   autocomplete="off"
                                   required>
                            <input type="hidden" id="addDocumentTypeId" name="document_type_id" required>
                            <ul class="dropdown-menu w-100" id="addCategoryDropdown" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <?php if (isset($kategori['use_predefined_codes']) && $kategori['use_predefined_codes'] == 1): ?>
                                        <li><a class="dropdown-item" href="#" data-value="<?= $kategori['id'] ?>" data-name="<?= esc(strtoupper($kategori['nama'])) ?>"><?= esc(strtoupper($kategori['nama'])) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="doccode-invalid-feedback">
                            Document Type is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="addCode" name="kode" required>
                        <div class="doccode-invalid-feedback">
                            Code is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="addDocumentName" class="form-label">Document Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="addDocumentName" name="nama" required>
                        <div class="doccode-invalid-feedback">
                            Document Name is required.
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Edit Modal -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Document Code</h5>
            </div>
            <form method="post" action="<?= base_url('document-code/edit') ?>" id="editDocumentCodeForm" novalidate>
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" id="editId" name="id">
                    <input type="hidden" id="editDocumentTypeId" name="document_type_id">
                    <div class="mb-3">
                        <label for="editDocumentType" class="form-label">Document Type <span class="text-danger">*</span></label>
                        <div class="dropdown">
                            <input type="text" 
                                   class="form-control dropdown-toggle doccode-text-uppercase-auto" 
                                   id="editDocumentType" 
                                   placeholder="Select Document Type..." 
                                   data-bs-toggle="dropdown" 
                                   aria-expanded="false"
                                   autocomplete="off"
                                   required>
                            <ul class="dropdown-menu w-100" id="editCategoryDropdown" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach ($kategori_dokumen as $kategori): ?>
                                    <?php if (isset($kategori['use_predefined_codes']) && $kategori['use_predefined_codes'] == 1): ?>
                                        <li><a class="dropdown-item" href="#" data-value="<?= $kategori['id'] ?>" data-name="<?= esc(strtoupper($kategori['nama'])) ?>"><?= esc(strtoupper($kategori['nama'])) ?></a></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <div class="doccode-invalid-feedback">
                            Document Type is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editCode" class="form-label">Code <span class="text-danger">*</span></label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="editCode" name="kode" required>
                        <div class="doccode-invalid-feedback">
                            Code is required.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="editDocumentName" class="form-label">Document Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control doccode-text-uppercase-auto" id="editDocumentName" name="nama" required>
                        <div class="doccode-invalid-feedback">
                            Document Name is required.
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('script') ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let documentTable;
    let selectedCategory = '';
    let searchText = '';

    // Custom form validation function
    function validateDocumentCodeForm(form) {
        let isValid = true;
        
        // Check regular required fields
        const requiredFields = form.querySelectorAll('input[required]:not([type="hidden"]), select[required]');
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Special validation for hidden document type fields
        const hiddenTypeField = form.querySelector('#addDocumentTypeId, #editDocumentTypeId');
        const visibleTypeField = form.querySelector('#addDocumentType, #editDocumentType');
        
        if (!hiddenTypeField.value.trim()) {
            visibleTypeField.classList.add('is-invalid');
            isValid = false;
        } else {
            visibleTypeField.classList.remove('is-invalid');
        }
        
        return isValid;
    }

    <?php if ($canUpdate): ?>
    function editDocument(id, type, code, name, typeId) {
        $('#editId').val(id);
        $('#editDocumentType').val(type.toUpperCase());
        $('#editDocumentTypeId').val(typeId);
        $('#editCode').val(code.toUpperCase());
        $('#editDocumentName').val(name.toUpperCase());
        
        // Remove any existing validation classes
        const editModal = document.getElementById('editModal');
        const inputs = editModal.querySelectorAll('.form-control, .form-select');
        inputs.forEach(function(input) {
            input.classList.remove('is-invalid');
        });
    }
    <?php endif; ?>

    <?php if ($canDelete): ?>
    function confirmDocumentDelete(documentId) {
        Swal.fire({
            title: 'Are you sure?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                deleteDocument(documentId);
            }
        });
    }

    async function deleteDocument(documentId) {
        try {
            // Tampilkan loading
            Swal.fire({
                title: 'Please wait...',
                text: 'Deleting document code...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData();
            formData.append('id', documentId);
            formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

            const response = await fetch('<?= base_url('document-code/delete') ?>', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            // Pastikan response OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();

            if (result.status === 'success') {
                // Remove row from DataTable
                const row = documentTable.row($(`tr[data-document-id="${documentId}"]`));
                if (row.length) {
                    row.remove().draw();
                } else {
                    // Fallback: reload page if row removal fails
                    location.reload();
                }
                
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: result.message,
                    confirmButtonColor: '#28a745'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: result.message || 'Failed to delete document code.',
                    confirmButtonColor: '#d33'
                });
            }
        } catch (error) {
            console.error('Error deleting document:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred while deleting the document code. Please check your network connection and try again.',
                confirmButtonColor: '#d33'
            });
        }
    }
    <?php endif; ?>

    $(document).ready(function() {
        // Initialize DataTable with proper column configuration
        documentTable = $('#documentTable').DataTable({
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "infoEmpty": "Showing 0 to 0 of 0 entries",
                "infoFiltered": "(filtered from _MAX_ total entries)",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                },
                "emptyTable": "No data found",
                "zeroRecords": "No matching records found"
            },
            "searching": true, // Enable DataTables search
            "columnDefs": [
                { 
                    "targets": 0, // No column
                    "orderable": false, // Disable sorting
                    "render": function (data, type, row, meta) {
                        // Return sequential number based on visible row index
                        return meta.row + 1; // Start from 1
                    }
                },
                { 
                    "targets": <?= ($canUpdate || $canDelete) ? '[-1]' : '[]' ?>, // Action column
                    "orderable": false // Disable sorting on action column
                }
            ],
            "order": [] // Data sudah diurutkan alfabetis dari database
        });

        // Custom filter for DataTables
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const row = documentTable.row(dataIndex).node();
                const documentTypeId = $(row).data('document-type-id');
                const code = data[2].toLowerCase(); // Code (index 2)
                const name = data[3].toLowerCase(); // Document Name (index 3)
                const categoryFilter = selectedCategory;
                const search = searchText.toLowerCase();

                // Apply search filter (searches in code and name)
                if (search && !code.includes(search) && !name.includes(search)) {
                    return false;
                }

                // Apply category filter (match document_type_id)
                if (categoryFilter && documentTypeId != categoryFilter) {
                    return false;
                }

                return true;
            }
        );

        // Search input handler
        $('#searchDocument').on('keyup', function() {
            searchText = $(this).val();
            documentTable.draw(); // Use custom filter with draw
        });

        // Category filter dropdown
        $('#filterCategory').on('click', function() {
            // Show all items first
            $('#categoryDropdown .dropdown-item').parent().show();
            // Show dropdown
            $(this).dropdown('show');
        });

        // Searchable Dropdown Logic for Category
        $('#filterCategory').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#categoryDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            // Show dropdown if not already shown
            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle dropdown item selection
        $('#categoryDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).text();
            const selectedValue = $(this).data('value');
            
            $('#filterCategory').val(selectedText);
            selectedCategory = selectedValue;
            
            // Close dropdown
            $('#filterCategory').dropdown('hide');
            
            // Apply filter
            documentTable.draw();
        });

        // Show all items when dropdown is opened
        $('#filterCategory').on('show.bs.dropdown', function() {
            $('#categoryDropdown .dropdown-item').parent().show();
        });

        // Reset filters
        $('#resetFilters').on('click', function() {
            $('#searchDocument').val('');
            $('#filterCategory').val('');
            selectedCategory = '';
            searchText = '';
            documentTable.draw(); // Redraw table with no filters
        });

        // Auto uppercase function for inputs with class 'doccode-text-uppercase-auto'
        $(document).on('input', '.doccode-text-uppercase-auto', function() {
            const cursorPosition = this.selectionStart;
            const oldLength = this.value.length;
            this.value = this.value.toUpperCase();
            const newLength = this.value.length;
            
            // Restore cursor position
            this.setSelectionRange(cursorPosition + (newLength - oldLength), cursorPosition + (newLength - oldLength));
        });
        
        // Also handle paste events
        $(document).on('paste', '.doccode-text-uppercase-auto', function(e) {
            const element = this;
            setTimeout(function() {
                element.value = element.value.toUpperCase();
            }, 1);
        });

        <?php if ($canCreate): ?>
        // Add Modal Searchable Dropdown Logic
        let addSelectedTypeId = '';

        // Show dropdown when clicked
        $('#addDocumentType').on('click', function() {
            // Show all items first
            $('#addCategoryDropdown .dropdown-item').parent().show();
            // Show dropdown
            $(this).dropdown('show');
        });

        // Searchable Dropdown Logic for Add Modal
        $('#addDocumentType').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#addCategoryDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            // Show dropdown if not already shown
            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle dropdown item selection for Add Modal
        $('#addCategoryDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).data('name');
            const selectedValue = $(this).data('value');
            
            $('#addDocumentType').val(selectedText);
            $('#addDocumentTypeId').val(selectedValue);
            addSelectedTypeId = selectedValue;
            
            // Remove validation error if exists
            $('#addDocumentType').removeClass('is-invalid');
            
            // Close dropdown
            $('#addDocumentType').dropdown('hide');
        });

        // Show all items when dropdown is opened
        $('#addDocumentType').on('show.bs.dropdown', function() {
            $('#addCategoryDropdown .dropdown-item').parent().show();
        });

        // Add Document Code Form Validation
        document.getElementById('addDocumentCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateDocumentCodeForm(this)) {
                this.submit();
            }
        });

        // Reset add modal when closed
        document.getElementById('addModal').addEventListener('hidden.bs.modal', function () {
            const form = this.querySelector('form');
            form.reset();
            
            // Reset hidden field as well
            $('#addDocumentTypeId').val('');
            addSelectedTypeId = '';
            
            // Remove validation classes
            const inputs = form.querySelectorAll('.form-control, .form-select');
            inputs.forEach(function(input) {
                input.classList.remove('is-invalid');
            });
        });

        // Autofocus on add modal open
        document.getElementById('addModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('addDocumentType').focus();
        });

        // Real-time validation for add form
        document.getElementById('addDocumentType').addEventListener('input', function() {
            // Check both visible and hidden fields
            if (this.value.trim() && $('#addDocumentTypeId').val().trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('addCode').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('addDocumentName').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        <?php endif; ?>

        <?php if ($canUpdate): ?>
        // Edit Modal Searchable Dropdown Logic
        let editSelectedTypeId = '';

        // Show dropdown when clicked ONLY
        $('#editDocumentType').on('click', function() {
            // Show all items first
            $('#editCategoryDropdown .dropdown-item').parent().show();
            // Show dropdown
            $(this).dropdown('show');
        });

        // Searchable Dropdown Logic for Edit Modal
        $('#editDocumentType').on('input', function() {
            const searchText = this.value.toLowerCase();
            const dropdownItems = $('#editCategoryDropdown .dropdown-item');
            
            dropdownItems.each(function() {
                const itemText = $(this).text().toLowerCase();
                if (itemText.includes(searchText) || searchText === '') {
                    $(this).parent().show();
                } else {
                    $(this).parent().hide();
                }
            });

            // Show dropdown if not already shown
            if (!$(this).next('.dropdown-menu').hasClass('show')) {
                $(this).dropdown('show');
            }
        });

        // Handle dropdown item selection for Edit Modal
        $('#editCategoryDropdown').on('click', '.dropdown-item', function(e) {
            e.preventDefault();
            const selectedText = $(this).data('name');
            const selectedValue = $(this).data('value');
            
            $('#editDocumentType').val(selectedText);
            $('#editDocumentTypeId').val(selectedValue);
            editSelectedTypeId = selectedValue;
            
            // Remove validation error if exists
            $('#editDocumentType').removeClass('is-invalid');
            
            // Close dropdown
            $('#editDocumentType').dropdown('hide');
        });

        // Show all items when dropdown is opened
        $('#editDocumentType').on('show.bs.dropdown', function() {
            $('#editCategoryDropdown .dropdown-item').parent().show();
        });

        // Edit Document Code Form Validation
        document.getElementById('editDocumentCodeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (validateDocumentCodeForm(this)) {
                this.submit();
            }
        });

        // Autofocus on edit modal open - focus to Code field instead
        document.getElementById('editModal').addEventListener('shown.bs.modal', function () {
            document.getElementById('editCode').focus();
        });

        // Real-time validation for edit form
        document.getElementById('editDocumentType').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('editCode').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });

        document.getElementById('editDocumentName').addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('is-invalid');
            }
        });
        <?php endif; ?>

        // Display flash messages
        <?php if (session()->has('added_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('added_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('updated_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('updated_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('deleted_message')): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: '<?= esc(session()->getFlashdata('deleted_message')) ?>',
                showConfirmButton: true,
                timer: 3000
            });
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= esc(session()->getFlashdata('error')) ?>',
                showConfirmButton: true,
                timer: 5000
            });
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>