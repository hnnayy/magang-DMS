<?php $this->extend('layout/main_layout') ?>
<?php $this->section('content') ?>

<?php
// Ambil privilege dari session untuk submenu ini
$privileges = session()->get('privileges');
$currentSubmenu = 'create-submenu'; // atau sesuai dengan slug submenu management Anda

// Set default privileges jika tidak ada
$canCreate = isset($privileges[$currentSubmenu]['can_create']) ? $privileges[$currentSubmenu]['can_create'] : 0;
$canUpdate = isset($privileges[$currentSubmenu]['can_update']) ? $privileges[$currentSubmenu]['can_update'] : 0;
$canDelete = isset($privileges[$currentSubmenu]['can_delete']) ? $privileges[$currentSubmenu]['can_delete'] : 0;
?>

<div class="px-4 py-3 w-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4>Submenu List</h4>
    </div>
    <hr>

    <!-- Flash Messages -->
    <?php if (session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive shadow-sm rounded bg-white p-3">
        <table class="table table-bordered table-hover align-middle" id="submenuTable">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 5%;">No</th>
                    <th style="width: 25%;">Menu</th>
                    <th style="width: 30%;">Submenu</th>
                    <th class="text-center" style="width: 15%;">Status</th>
                    <?php if ($canUpdate || $canDelete): ?>
                        <th class="text-center" style="width: 20%;">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody id="tableBody">
                <?php if (!empty($submenus)) : ?>
                    <?php $no = 1; foreach ($submenus as $submenu) : ?>
                        <tr>
                            <td class="text-center"><?= $no++ ?></td>
                            <td><?= esc($submenu['parent_name']) ?></td>
                            <td><?= esc($submenu['name']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= $submenu['status'] == 1 ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $submenu['status'] == 1 ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <?php if ($canUpdate || $canDelete): ?>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-2">
                                        <?php if ($canUpdate): ?>
                                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                                onclick="openEditModal(<?= $submenu['id'] ?>, <?= esc($submenu['parent']) ?>, '<?= esc($submenu['name']) ?>', <?= $submenu['status'] ?>)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        <?php endif; ?>
                                        <?php if ($canDelete): ?>
                                            <form id="deleteForm_<?= $submenu['id'] ?>" action="<?= site_url('create-submenu/delete') ?>" method="post">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $submenu['id'] ?>">
                                                <button type="button" class="btn btn-link p-0 text-danger" onclick="confirmDelete(<?= $submenu['id'] ?>)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?= ($canUpdate || $canDelete) ? '5' : '4' ?>" class="text-center text-muted">No submenu data available.</td>
                    </tr>
                <?php endif ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Edit Submenu - FIXED VERSION -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Submenu</h5>
            </div>
            <form method="post" id="editUnitForm" action="<?= site_url('create-submenu/update') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <input type="hidden" name="id" id="editUnitId">
                    <div class="mb-3">
                        <label class="form-label">Menu <span class="text-danger">*</span></label>
                        <div class="search-dropdown-container">
                            <input type="text" 
                                   id="editParentName-search" 
                                   class="form-control search-input" 
                                   placeholder="Search menu..." 
                                   autocomplete="off"
                                   required>
                            <input type="hidden" 
                                   id="editParentName" 
                                   name="parent" 
                                   required>
                            <div id="editParentName-dropdown" class="search-dropdown-list" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Submenu <span class="text-danger">*</span></label>
                        <input type="text" name="submenu" id="editUnitName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="editStatusActive" value="1" required>
                            <label class="form-check-label" for="editStatusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="editStatusInactive" value="2" required>
                            <label class="form-check-label" for="editStatusInactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                    <button type="button" class="btn btn-secondary" id="cancelEditBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- JS: Searchable Dropdown Implementation -->
<script>
    // Searchable Dropdown Class
    class SearchableDropdown {
        constructor(searchInputId, hiddenInputId, dropdownId, data, textKey = 'name', valueKey = 'id') {
            this.searchInput = document.getElementById(searchInputId);
            this.hiddenInput = document.getElementById(hiddenInputId);
            this.dropdown = document.getElementById(dropdownId);
            this.data = data;
            this.textKey = textKey;
            this.valueKey = valueKey;
            this.filteredData = [...data];
            this.selectedIndex = -1;
            
            this.init();
        }

        init() {
            this.searchInput.addEventListener('input', (e) => this.handleInput(e));
            this.searchInput.addEventListener('focus', () => this.showDropdown());
            this.searchInput.addEventListener('blur', (e) => this.handleBlur(e));
            this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
            
            document.addEventListener('click', (e) => {
                if (!e.target.closest('.search-dropdown-container')) {
                    this.hideDropdown();
                }
            });
        }

        handleInput(e) {
            const query = e.target.value.toLowerCase();
            this.filteredData = this.data.filter(item => 
                item[this.textKey].toLowerCase().includes(query)
            );
            this.selectedIndex = -1;
            this.renderDropdown();
            this.showDropdown();
            
            const exactMatch = this.data.find(item => 
                item[this.textKey].toLowerCase() === query.toLowerCase()
            );
            if (!exactMatch) {
                this.hiddenInput.value = '';
                this.searchInput.classList.remove('has-selection');
            }
        }

        handleBlur(e) {
            setTimeout(() => {
                if (!this.dropdown.contains(document.activeElement)) {
                    this.hideDropdown();
                }
            }, 150);
        }

        handleKeydown(e) {
            if (!this.dropdown.style.display || this.dropdown.style.display === 'none') return;

            switch (e.key) {
                case 'ArrowDown':
                    e.preventDefault();
                    this.selectedIndex = Math.min(this.selectedIndex + 1, this.filteredData.length - 1);
                    this.updateSelection();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                    this.updateSelection();
                    break;
                case 'Enter':
                    e.preventDefault();
                    if (this.selectedIndex >= 0) this.selectItem(this.filteredData[this.selectedIndex]);
                    break;
                case 'Escape':
                    this.hideDropdown();
                    break;
            }
        }

        updateSelection() {
            const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
            items.forEach((item, index) => item.classList.toggle('selected', index === this.selectedIndex));
        }

        selectItem(item) {
            this.searchInput.value = item[this.textKey];
            this.hiddenInput.value = item[this.valueKey];
            this.searchInput.classList.add('has-selection');
            this.hideDropdown();
            this.hiddenInput.dispatchEvent(new Event('change'));
        }

        showDropdown() {
            this.renderDropdown();
            this.dropdown.style.display = 'block';
        }

        hideDropdown() {
            this.dropdown.style.display = 'none';
            this.selectedIndex = -1;
        }

        renderDropdown() {
            this.dropdown.innerHTML = '';
            
            if (this.filteredData.length === 0) {
                const noResults = document.createElement('div');
                noResults.className = 'search-dropdown-item no-results';
                noResults.textContent = 'No results found';
                this.dropdown.appendChild(noResults);
                return;
            }

            this.filteredData.forEach((item, index) => {
                const div = document.createElement('div');
                div.className = 'search-dropdown-item';
                div.textContent = item[this.textKey];
                div.addEventListener('click', () => this.selectItem(item));
                this.dropdown.appendChild(div);
            });
        }

        setValue(value, text) {
            this.hiddenInput.value = value;
            this.searchInput.value = text;
            if (value) this.searchInput.classList.add('has-selection');
        }

        // Method to reset form
        reset() {
            this.hiddenInput.value = '';
            this.searchInput.value = '';
            this.searchInput.classList.remove('has-selection');
            this.hideDropdown();
        }
    }

    let editMenuDropdown;
    let editModalInstance;
    const menuData = <?= json_encode($menus) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        editMenuDropdown = new SearchableDropdown(
            'editParentName-search', 
            'editParentName', 
            'editParentName-dropdown', 
            menuData
        );

        // Initialize modal instance
        const editModalElement = document.getElementById('editModal');
        if (editModalElement) {
            editModalInstance = new bootstrap.Modal(editModalElement);
        }

        // Handle cancel button click
        const cancelBtn = document.getElementById('cancelEditBtn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', function() {
                closeEditModal();
            });
        }

        // Handle modal close events
        const editModal = document.getElementById('editModal');
        if (editModal) {
            editModal.addEventListener('hidden.bs.modal', function () {
                resetEditForm();
            });
        }

        <?php if (session('swal')): ?>
            const swalData = <?= json_encode(session('swal')) ?>;
            Swal.fire({
                icon: swalData.icon,
                title: swalData.title,
                text: swalData.text,
                confirmButtonText: 'OK',
                confirmButtonColor: '#6c5ce7',
                customClass: { popup: 'custom-swal' }
            });
        <?php endif; ?>
    });

    // Function to close edit modal properly
    function closeEditModal() {
        if (editModalInstance) {
            editModalInstance.hide();
        }
        
        // Fallback - force remove backdrop if still exists
        setTimeout(() => {
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }, 300);
    }

    // Function to reset edit form
    function resetEditForm() {
        const form = document.getElementById('editUnitForm');
        if (form) {
            form.reset();
        }
        
        if (editMenuDropdown) {
            editMenuDropdown.reset();
        }
        
        // Clear all input values
        document.getElementById('editUnitId').value = '';
        document.getElementById('editUnitName').value = '';
        document.getElementById('editParentName').value = '';
        document.getElementById('editParentName-search').value = '';
    }

    <?php if ($canDelete): ?>
    function confirmDelete(id) {
        Swal.fire({
            title: 'Are you sure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: 'rgba(118, 125, 131, 1)',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: $('#deleteForm_' + id).attr('action'),
                    type: 'POST',
                    data: $('#deleteForm_' + id).serialize(),
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Successfully Deleted',
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#6c5ce7',
                                customClass: { popup: 'custom-swal' }
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    $('#submenuTable').DataTable().row($('#deleteForm_' + id).closest('tr')).remove().draw();
                                }
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to delete submenu.',
                            confirmButtonColor: '#abb3baff'
                        });
                    }
                });
            }
        });
    }
    <?php endif; ?>

    $(document).ready(function () {
        setTimeout(function() { $('.alert').fadeOut('slow'); }, 5000);

        const table = $('#submenuTable');
        const thCount = table.find('thead th').length;
        const tdCount = table.find('tbody tr:visible:first td').length;

        if (thCount === tdCount || table.find('tbody tr').length === 0) {
            table.DataTable({
                "pageLength": 10,
                "ordering": true,
                "searching": true,
                "language": {
                    "search": "Search:",
                    "lengthMenu": "Show _MENU_ entries per page",
                    "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                    "paginate": { "first": "First", "last": "Last", "next": "Next", "previous": "Previous" }
                }
            });
        } else {
            console.warn('⚠️ DataTables failed to initialize because the number of columns does not match.');
        }
    });

    <?php if ($canUpdate): ?>
    function openEditModal(id, parentId, submenuName, status) {
        const form = document.getElementById('editUnitForm');
        if (!form) return;
        
        // Reset form first
        resetEditForm();
        
        // Set form values
        document.getElementById('editUnitId').value = id;
        document.getElementById('editUnitName').value = submenuName;

        const selectedMenu = menuData.find(menu => menu.id == parentId);
        if (selectedMenu && editMenuDropdown) {
            editMenuDropdown.setValue(parentId, selectedMenu.name);
        }

        if (status == 1) {
            document.getElementById('editStatusActive').checked = true;
        } else {
            document.getElementById('editStatusInactive').checked = true;
        }

        // Show modal
        if (editModalInstance) {
            editModalInstance.show();
        }
    }
    <?php endif; ?>

    <?php if ($canCreate): ?>
    const addForm = document.getElementById('addSubmenuForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('addSubmenuName').value.trim().toLowerCase();
            const parentId = document.getElementById('addParentName').value;

            let isDuplicate = false;
            <?php if (!empty($submenus)): ?>
                <?php foreach ($submenus as $submenu): ?>
                    if ('<?= strtolower(trim($submenu['name'])) ?>' === inputName && '<?= $submenu['parent'] ?>' === parentId) isDuplicate = true;
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Submenu name already exists in this menu. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>

    <?php if ($canUpdate): ?>
    const editForm = document.getElementById('editUnitForm');
    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('editUnitName').value.trim().toLowerCase();
            const parentId = document.getElementById('editParentName').value;
            const currentId = document.getElementById('editUnitId').value;

            let isDuplicate = false;
            <?php if (!empty($submenus)): ?>
                <?php foreach ($submenus as $submenu): ?>
                    if ('<?= strtolower(trim($submenu['name'])) ?>' === inputName && 
                        '<?= $submenu['parent'] ?>' === parentId && 
                        '<?= $submenu['id'] ?>' !== currentId) isDuplicate = true;
                <?php endforeach; ?>
            <?php endif; ?>

            if (isDuplicate) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: 'Submenu name already exists in this menu. Please choose a different name.',
                    confirmButtonColor: '#abb3baff'
                });
                return false;
            }
        });
    }
    <?php endif; ?>
</script>
<?php $this->endSection() ?>