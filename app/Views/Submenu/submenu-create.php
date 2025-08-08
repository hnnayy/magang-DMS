<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add Submenu' ?></h2>
        </div>
        <form id="createSubmenuForm" method="post" action="<?= base_url('create-submenu/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label" for="menu_search">Menu</label>
                <div class="search-dropdown-container">
                    <input 
                        type="text" 
                        id="menu_search" 
                        class="form-input search-input <?php echo isset($validation) && isset($validation['parent']) ? 'is-invalid' : ''; ?>" 
                        placeholder="Search menu..."
                        autocomplete="off"
                        required
                    >
                    <input type="hidden" name="parent" id="selected_menu_id" required>
                    <div class="search-dropdown-list" id="menu_dropdown" style="display: none;"></div>
                </div>
                <div class="invalid-feedback">Please select a menu.</div>
                <?php if (isset($validation) && isset($validation['parent'])) : ?>
                    <div class="server-error-feedback">
                        <?php echo $validation['parent']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group <?php echo isset($validation) && isset($validation['submenu']) ? 'has-error' : ''; ?>">
                <label for="editUnitName" class="form-label">Submenu</label>
                <input type="text" name="submenu" id="editUnitName" class="form-input <?php echo isset($validation) && isset($validation['submenu']) ? 'is-invalid' : ''; ?>"
                       placeholder="Enter Submenu here..."
                       pattern="^\S+\s+\S+"
                       title="Submenu must contain at least two words"
                       required>
                <div class="invalid-feedback">Submenu must consist of at least two words.</div>
                <?php if (isset($validation) && isset($validation['submenu'])) : ?>
                    <div class="server-error-feedback">
                        <?php echo $validation['submenu']; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="1" required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="2" required>
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
                <div class="invalid-feedback">Please select a status.</div>
            </div>
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </div>
    <div class="illustration-section">
        <img src="<?= base_url('assets/images/profil/Logo_Telkom_University.png') ?>" alt="User Illustration" class="illustration-img">
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const menuData = [
    <?php foreach ($menus as $menu): ?>
    {
        id: <?= $menu['id'] ?>,
        name: "<?= esc($menu['name']) ?>"
    },
    <?php endforeach; ?>
];

class SearchableMenuDropdown {
    constructor() {
        this.searchInput = document.getElementById('menu_search');
        this.dropdown = document.getElementById('menu_dropdown');
        this.hiddenInput = document.getElementById('selected_menu_id');
        this.selectedIndex = -1;
        this.filteredMenus = [];
        this.debounceTimeout = null;
        
        this.init();
    }

    init() {
        this.searchInput.addEventListener('input', (e) => this.handleInput(e));
        this.searchInput.addEventListener('focus', () => this.handleFocus());
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.searchInput.addEventListener('blur', () => this.handleBlur());
        this.dropdown.addEventListener('click', (e) => this.handleDropdownClick(e));
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
    }

    debounce(func, wait) {
        return (...args) => {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => func.apply(this, args), wait);
        };
    }

    handleInput(e) {
        const value = e.target.value.trim();
        
        this.debounce(() => {
            if (value === '') {
                this.dropdown.style.display = 'none';
                this.hiddenInput.value = '';
                this.searchInput.classList.remove('has-selection');
                this.showValidationError();
                return;
            }

            const exactMatch = menuData.find(menu => menu.name.toLowerCase() === value.toLowerCase());
            if (exactMatch) {
                this.hiddenInput.value = exactMatch.id;
                this.searchInput.classList.add('has-selection');
                this.hideValidationError();
                this.dropdown.style.display = 'none';
            } else {
                this.hiddenInput.value = '';
                this.searchInput.classList.remove('has-selection');
                this.showValidationError();
            }
            
            this.filterMenus(value);
        }, 300)();
    }

    handleFocus() {
        this.searchInput.select();
        this.filterMenus(this.searchInput.value.trim());
    }

    handleKeydown(e) {
        const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                if (this.selectedIndex >= 0) {
                    items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
                }
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection(items);
                if (this.selectedIndex >= 0) {
                    items[this.selectedIndex].scrollIntoView({ block: 'nearest' });
                }
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    this.selectMenu(items[this.selectedIndex]);
                } else {
                    const exactMatch = menuData.find(menu => menu.name.toLowerCase() === this.searchInput.value.trim().toLowerCase());
                    if (exactMatch) {
                        this.searchInput.value = exactMatch.name;
                        this.hiddenInput.value = exactMatch.id;
                        this.searchInput.classList.add('has-selection');
                        this.dropdown.style.display = 'none';
                        this.hideValidationError();
                    }
                }
                break;
                
            case 'Escape':
                e.preventDefault();
                this.dropdown.style.display = 'none';
                this.selectedIndex = -1;
                break;
        }
    }

    handleBlur() {
        setTimeout(() => {
            if (!this.dropdown.contains(document.activeElement) && !this.searchInput.contains(document.activeElement)) {
                this.dropdown.style.display = 'none';
                if (!this.hiddenInput.value && this.searchInput.value) {
                    this.showValidationError();
                }
            }
        }, 150);
    }

    handleDropdownClick(e) {
        const item = e.target.closest('.search-dropdown-item');
        if (item && !item.classList.contains('no-results')) {
            this.selectMenu(item);
        }
    }

    handleOutsideClick(e) {
        if (!this.searchInput.contains(e.target) && !this.dropdown.contains(e.target)) {
            this.dropdown.style.display = 'none';
            this.selectedIndex = -1;
        }
    }

    filterMenus(searchTerm) {
        this.filteredMenus = menuData.filter(menu => 
            menu.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        this.dropdown.innerHTML = '';
        
        if (this.filteredMenus.length === 0) {
            this.dropdown.innerHTML = '<div class="search-dropdown-item no-results">No menus found</div>';
        } else {
            this.filteredMenus.forEach((menu, index) => {
                const item = document.createElement('div');
                item.className = 'search-dropdown-item';
                item.textContent = menu.name;
                item.dataset.id = menu.id;
                item.dataset.index = index;
                item.tabIndex = 0;
                this.dropdown.appendChild(item);
            });
        }
        
        this.selectedIndex = -1;
        this.dropdown.style.display = 'block';
    }

    updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === this.selectedIndex);
        });
    }

    selectMenu(item) {
        const menuId = item.dataset.id;
        const menuName = item.textContent;
        
        this.searchInput.value = menuName;
        this.hiddenInput.value = menuId;
        this.searchInput.classList.add('has-selection');
        this.dropdown.style.display = 'none';
        this.selectedIndex = -1;
        this.hideValidationError();
        this.searchInput.focus();
    }

    showValidationError() {
        this.searchInput.classList.add('is-invalid');
        const container = this.searchInput.closest('.form-group');
        const feedback = container?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'block';
        }
    }

    hideValidationError() {
        this.searchInput.classList.remove('is-invalid');
        const container = this.searchInput.closest('.form-group');
        const feedback = container?.querySelector('.invalid-feedback');
        if (feedback) {
            feedback.style.display = 'none';
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const menuDropdown = new SearchableMenuDropdown();
    const form = document.getElementById('createSubmenuForm');

    // SweetAlert untuk error duplikasi atau success
    <?php if (session('swal')): ?>
        const swalData = <?= json_encode(session('swal')) ?>;
        Swal.fire({
            icon: swalData.icon,
            title: swalData.title,
            text: swalData.text,
            confirmButtonText: 'OK',
            confirmButtonColor: '#6c5ce7',
            customClass: {
                popup: 'custom-swal',
            }
        });
    <?php endif; ?>

    form.addEventListener('submit', function(e) {
        let isValid = form.checkValidity();
        const menuId = document.getElementById('selected_menu_id').value;
        
        if (!menuId) {
            isValid = false;
            menuDropdown.showValidationError();
        } else {
            menuDropdown.hideValidationError();
        }

        const submenuInput = document.getElementById('editUnitName');
        const submenuValue = submenuInput.value.trim();
        const words = submenuValue.split(/\s+/).filter(word => word.length > 0);
        
        if (submenuValue && words.length < 2) {
            isValid = false;
            submenuInput.setCustomValidity('Submenu must contain at least two words');
        } else {
            submenuInput.setCustomValidity('');
        }

        const statusInputs = form.querySelectorAll('input[name="status"]');
        const statusGroup = document.getElementById('status-group');
        const isStatusChecked = Array.from(statusInputs).some(input => input.checked);
        
        if (!isStatusChecked) {
            isValid = false;
            let feedback = statusGroup.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'block';
            }
        } else {
            let feedback = statusGroup.querySelector('.invalid-feedback');
            if (feedback) {
                feedback.style.display = 'none';
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        form.classList.add('was-validated');
    });

    document.getElementById('editUnitName').addEventListener('input', function() {
        const value = this.value.trim();
        const words = value.split(/\s+/).filter(word => word.length > 0);
        
        if (value && words.length < 2) {
            this.setCustomValidity('Submenu must contain at least two words');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
<?= $this->endSection() ?>