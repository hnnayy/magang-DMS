<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

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
                                        <?php if ($canDelete): ?>
                                            <form action="<?= site_url('create-submenu/delete') ?>" method="post" onsubmit="return confirmDelete(event, this);">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="id" value="<?= $submenu['id'] ?>">
                                                <button type="submit" class="btn btn-link p-0 text-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($canUpdate): ?>
                                            <button class="btn btn-link p-0 text-primary" data-bs-toggle="modal" data-bs-target="#editModal"
                                                onclick="openEditModal(<?= $submenu['id'] ?>, <?= esc($submenu['parent']) ?>, '<?= esc($submenu['name']) ?>', <?= $submenu['status'] ?>)">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
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

<!-- Modal Add Submenu -->
<?php if ($canCreate): ?>
<div class="modal fade" id="addModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Add New Submenu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="addSubmenuForm" action="<?= site_url('create-submenu/store') ?>">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu <span class="text-danger">*</span></label>
                        <select name="parent" id="addParentName" class="form-select" required>
                            <option value="">-- Choose Menu --</option>
                            <?php foreach ($menus as $menu) : ?>
                                <option value="<?= $menu['id'] ?>"><?= esc($menu['name']) ?></option>
                            <?php endforeach ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Submenu <span class="text-danger">*</span></label>
                        <input type="text" name="submenu" id="addSubmenuName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block">Status <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="addStatusActive" value="1" required checked>
                            <label class="form-check-label" for="addStatusActive">Active</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="addStatusInactive" value="2" required>
                            <label class="form-check-label" for="addStatusInactive">Inactive</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Submenu</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal Edit Submenu -->
<?php if ($canUpdate): ?>
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header">
                <h5 class="modal-title">Edit Submenu</h5>
                <!-- Close button removed -->
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
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
            
            // Close dropdown when clicking outside
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
            
            // Clear hidden input if text doesn't match any option
            const exactMatch = this.data.find(item => 
                item[this.textKey].toLowerCase() === query.toLowerCase()
            );
            if (!exactMatch) {
                this.hiddenInput.value = '';
                this.searchInput.classList.remove('has-selection');
            }
        }

        handleBlur(e) {
            // Delay hiding to allow clicking on dropdown items
            setTimeout(() => {
                if (!this.dropdown.contains(document.activeElement)) {
                    this.hideDropdown();
                }
            }, 150);
        }

        handleKeydown(e) {
            if (!this.dropdown.style.display || this.dropdown.style.display === 'none') {
                return;
            }

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
                    if (this.selectedIndex >= 0) {
                        this.selectItem(this.filteredData[this.selectedIndex]);
                    }
                    break;
                case 'Escape':
                    this.hideDropdown();
                    break;
            }
        }

        updateSelection() {
            const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
            items.forEach((item, index) => {
                item.classList.toggle('selected', index === this.selectedIndex);
            });
        }

        selectItem(item) {
            this.searchInput.value = item[this.textKey];
            this.hiddenInput.value = item[this.valueKey];
            this.searchInput.classList.add('has-selection');
            this.hideDropdown();
            
            // Trigger change event for other dependencies
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
            if (value) {
                this.searchInput.classList.add('has-selection');
            }
        }
    }

    // Initialize searchable dropdown for edit modal
    let editMenuDropdown;
    const menuData = <?= json_encode($menus) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Menu dropdown for edit modal
        editMenuDropdown = new SearchableDropdown(
            'editParentName-search', 
            'editParentName', 
            'editParentName-dropdown', 
            menuData
        );
    });
</script>

<script>
    $(document).ready(function () {
        // Auto hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);

        // Initialize DataTable
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
                    "paginate": {
                        "first": "First",
                        "last": "Last",
                        "next": "Next",
                        "previous": "Previous"
                    }
                }
            });
        } else {
            console.warn('⚠️ DataTables failed to initialize because the number of columns does not match.');
        }
    });

    <?php if ($canDelete): ?>
    function confirmDelete(event, form) {
        event.preventDefault();
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
                form.submit();
            }
        });
        return false;
    }
    <?php endif; ?>

    <?php if ($canUpdate): ?>
    function openEditModal(id, parentId, submenuName, status) {
        const form = document.getElementById('editUnitForm');
        if (!form) return;
        
        // Set nilai ke form fields
        document.getElementById('editUnitId').value = id;
        document.getElementById('editUnitName').value = submenuName;

        // Find menu text by parentId
        const selectedMenu = menuData.find(menu => menu.id == parentId);
        if (selectedMenu && editMenuDropdown) {
            editMenuDropdown.setValue(parentId, selectedMenu.name);
        }

        // Set radio button status
        if (status == 1) {
            document.getElementById('editStatusActive').checked = true;
        } else {
            document.getElementById('editStatusInactive').checked = true;
        }
    }
    <?php endif; ?>

    <?php if ($canCreate): ?>
    // Client-side validation for duplicate submenu names on add
    const addForm = document.getElementById('addSubmenuForm');
    if (addForm) {
        addForm.addEventListener('submit', function (e) {
            const inputName = document.getElementById('addSubmenuName').value.trim().toLowerCase();
            const parentId = document.getElementById('addParentName').value;

            let isDuplicate = false;
            <?php if (!empty($submenus)): ?>
                <?php foreach ($submenus as $submenu): ?>
                    if ('<?= strtolower(trim($submenu['name'])) ?>' === inputName && '<?= $submenu['parent'] ?>' === parentId) {
                        isDuplicate = true;
                    }
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
    // Client-side validation for duplicate submenu names on edit
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
                        '<?= $submenu['id'] ?>' !== currentId) {
                        isDuplicate = true;
                    }
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

<?= $this->endSection() ?>