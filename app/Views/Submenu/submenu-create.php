<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add Submenu' ?></h2>
        </div>

        <form id="createSubmenuForm" method="post" action="<?= base_url('create-submenu/store') ?>" class="needs-validation" novalidate>
            <?= csrf_field() ?>

            <!-- Searchable Dropdown Menu -->
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
                    <input type="hidden" name="parent" id="selected_menu_id" value="<?= old('parent') ?>" required>
                    <div class="search-dropdown-list" id="menu_dropdown" style="display: none;"></div>
                </div>
                <div class="invalid-feedback">Please select a menu.</div>
                <?php if (isset($validation) && isset($validation['parent'])) : ?>
                    <div class="server-error-feedback">
                        <?php echo $validation['parent']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Submenu -->
            <div class="form-group <?php echo isset($validation) && isset($validation['submenu']) ? 'has-error' : ''; ?>">
                <label for="editUnitName" class="form-label">Sub Menu</label>
                <input type="text" name="submenu" id="editUnitName" class="form-input <?php echo isset($validation) && isset($validation['submenu']) ? 'is-invalid' : ''; ?>"
                       placeholder="Enter Submenu here... "
                       value="<?php echo old('submenu'); ?>"
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

            <!-- Status -->
            <div class="form-group" id="status-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="1" 
                           <?= (old('status') == '1' || !old('status')) ? 'checked' : '' ?> required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="2"
                           <?= old('status') == '2' ? 'checked' : '' ?>>
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

<script>
// Menu data from PHP
const menuData = [
    <?php foreach ($menus as $menu): ?>
    {
        id: <?= $menu['id'] ?>,
        name: "<?= esc($menu['name']) ?>"
    },
    <?php endforeach; ?>
];

// Enhanced Searchable Dropdown Class
class SearchableMenuDropdown {
    constructor() {
        this.searchInput = document.getElementById('menu_search');
        this.dropdown = document.getElementById('menu_dropdown');
        this.hiddenInput = document.getElementById('selected_menu_id');
        this.selectedIndex = -1;
        this.filteredMenus = [];
        
        this.init();
    }

    init() {
        // Set initial value if there's an old value
        const oldValue = "<?= old('parent') ?>";
        if (oldValue) {
            const selectedMenu = menuData.find(menu => menu.id == oldValue);
            if (selectedMenu) {
                this.searchInput.value = selectedMenu.name;
                this.searchInput.classList.add('has-selection');
                this.hideValidationError();
            }
        }

        // Event listeners
        this.searchInput.addEventListener('input', (e) => this.handleInput(e));
        this.searchInput.addEventListener('focus', () => this.handleFocus());
        this.searchInput.addEventListener('click', () => this.handleClick());
        this.searchInput.addEventListener('keydown', (e) => this.handleKeydown(e));
        this.searchInput.addEventListener('blur', () => this.handleBlur());
        
        this.dropdown.addEventListener('click', (e) => this.handleDropdownClick(e));
        
        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => this.handleOutsideClick(e));
    }

    handleInput(e) {
        const value = e.target.value.trim();
        
        if (value === '') {
            this.dropdown.style.display = 'none';
            this.hiddenInput.value = '';
            this.searchInput.classList.remove('has-selection');
            this.showValidationError();
            return;
        }
        
        // Check if current value matches exactly with a menu
        const exactMatch = menuData.find(menu => menu.name === value);
        if (!exactMatch) {
            this.hiddenInput.value = '';
            this.searchInput.classList.remove('has-selection');
            this.showValidationError();
        } else {
            this.hideValidationError();
        }
        
        this.filterMenus(value);
    }

    handleFocus() {
        if (this.searchInput.value.trim() !== '') {
            this.filterMenus(this.searchInput.value);
        } else {
            this.filterMenus('');
        }
    }

    handleClick() {
        if (this.searchInput.value.trim() !== '') {
            this.filterMenus(this.searchInput.value);
        } else {
            this.filterMenus('');
        }
    }

    handleKeydown(e) {
        const items = this.dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.selectedIndex = Math.min(this.selectedIndex + 1, items.length - 1);
                this.updateSelection(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.selectedIndex = Math.max(this.selectedIndex - 1, -1);
                this.updateSelection(items);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (this.selectedIndex >= 0 && items[this.selectedIndex]) {
                    this.selectMenu(items[this.selectedIndex]);
                }
                break;
                
            case 'Escape':
                this.dropdown.style.display = 'none';
                this.selectedIndex = -1;
                break;
        }
    }

    handleBlur() {
        // Delay hiding to allow clicking on dropdown items
        setTimeout(() => {
            if (!this.dropdown.contains(document.activeElement)) {
                this.dropdown.style.display = 'none';
                // Show validation error if no valid selection
                if (!this.hiddenInput.value && this.searchInput.value) {
                    this.showValidationError();
                }
            }
        }, 150);
    }

    handleDropdownClick(e) {
        if (e.target.classList.contains('search-dropdown-item') && !e.target.classList.contains('no-results')) {
            this.selectMenu(e.target);
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

// Initialize dropdown and form validation
document.addEventListener('DOMContentLoaded', function() {
    // Initialize searchable dropdown
    const menuDropdown = new SearchableMenuDropdown();
    
    // Form validation
    const form = document.getElementById('createSubmenuForm');
    
    form.addEventListener('submit', function(e) {
        let isValid = form.checkValidity();
        
        // Custom validation for menu dropdown
        const menuId = document.getElementById('selected_menu_id').value;
        if (!menuId) {
            isValid = false;
            menuDropdown.showValidationError();
        } else {
            menuDropdown.hideValidationError();
        }
        
        // Custom validation for submenu (at least two words)
        const submenuInput = document.getElementById('editUnitName');
        const submenuValue = submenuInput.value.trim();
        const words = submenuValue.split(/\s+/).filter(word => word.length > 0);
        
        if (submenuValue && words.length < 2) {
            isValid = false;
            submenuInput.setCustomValidity('Submenu must contain at least two words');
        } else {
            submenuInput.setCustomValidity('');
        }
        
        // Custom validation for status
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
    
    // Real-time validation for submenu
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