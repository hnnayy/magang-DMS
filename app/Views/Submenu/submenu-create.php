<?= $this->extend('layout/main_layout') ?>
<?= $this->section('content') ?>

<div class="container">
    <div class="form-section">
        <div class="form-section-divider">
            <h2><?= $title ?? 'Add Submenu' ?></h2>
        </div>

        <form id="createSubmenuForm" method="post" action="<?= base_url('create-submenu/store') ?>">
            <?= csrf_field() ?>

            <!-- Searchable Dropdown Menu -->
            <div class="form-group">
                <label class="form-label" for="menu_search">Menu</label>
                <div class="search-dropdown-container">
                    <input 
                        type="text" 
                        id="menu_search" 
                        class="form-input search-input <?php echo isset($validation) && isset($validation['parent']) ? 'is-invalid' : ''; ?>" 
                        placeholder="Search and select menu..."
                        autocomplete="off"
                        required
                    >
                    <input type="hidden" name="parent" id="selected_menu_id" value="<?= old('parent') ?>">
                    <div class="search-dropdown-list" id="menu_dropdown" style="display: none;"></div>
                </div>
                <?php if (isset($validation) && isset($validation['parent'])) : ?>
                    <div class="invalid-feedback">
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
                       required>
                <?php if (isset($validation) && isset($validation['submenu'])) : ?>
                    <div class="invalid-feedback">
                        <?php echo $validation['submenu']; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Status -->
            <div class="form-group">
                <label class="form-label d-block">Status</label>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="active" value="1" checked required>
                    <label class="form-check-label" for="active">Active</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="status" id="inactive" value="2">
                    <label class="form-check-label" for="inactive">Inactive</label>
                </div>
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

// Searchable dropdown functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('menu_search');
    const dropdown = document.getElementById('menu_dropdown');
    const hiddenInput = document.getElementById('selected_menu_id');
    let selectedIndex = -1;
    let filteredMenus = [];

    // Set initial value if there's an old value
    const oldValue = "<?= old('parent') ?>";
    if (oldValue) {
        const selectedMenu = menuData.find(menu => menu.id == oldValue);
        if (selectedMenu) {
            searchInput.value = selectedMenu.name;
            searchInput.classList.add('has-selection');
        }
    }

    // Filter and display menu options
    function filterMenus(searchTerm) {
        filteredMenus = menuData.filter(menu => 
            menu.name.toLowerCase().includes(searchTerm.toLowerCase())
        );
        
        dropdown.innerHTML = '';
        
        if (filteredMenus.length === 0) {
            dropdown.innerHTML = '<div class="search-dropdown-item no-results">No menus found</div>';
        } else {
            filteredMenus.forEach((menu, index) => {
                const item = document.createElement('div');
                item.className = 'search-dropdown-item';
                item.textContent = menu.name;
                item.dataset.id = menu.id;
                item.dataset.index = index;
                dropdown.appendChild(item);
            });
        }
        
        selectedIndex = -1;
        dropdown.style.display = 'block';
    }

    // Handle input events
    searchInput.addEventListener('input', function() {
        const value = this.value.trim();
        
        if (value === '') {
            dropdown.style.display = 'none';
            hiddenInput.value = '';
            this.classList.remove('has-selection');
            return;
        }
        
        // Check if current value matches exactly with a menu
        const exactMatch = menuData.find(menu => menu.name === value);
        if (!exactMatch) {
            hiddenInput.value = '';
            this.classList.remove('has-selection');
        }
        
        filterMenus(value);
    });

    // Handle focus events - show all options when clicked
    searchInput.addEventListener('focus', function() {
        if (this.value.trim() !== '') {
            filterMenus(this.value);
        } else {
            // Show all menus when input is focused and empty
            filterMenus('');
        }
    });

    // Handle click events - also show dropdown
    searchInput.addEventListener('click', function() {
        if (this.value.trim() !== '') {
            filterMenus(this.value);
        } else {
            // Show all menus when input is clicked and empty
            filterMenus('');
        }
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.search-dropdown-item:not(.no-results)');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
                updateSelection(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                selectedIndex = Math.max(selectedIndex - 1, -1);
                updateSelection(items);
                break;
                
            case 'Enter':
                e.preventDefault();
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    selectMenu(items[selectedIndex]);
                }
                break;
                
            case 'Escape':
                dropdown.style.display = 'none';
                selectedIndex = -1;
                break;
        }
    });

    // Update visual selection
    function updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === selectedIndex);
        });
    }

    // Select a menu
    function selectMenu(item) {
        const menuId = item.dataset.id;
        const menuName = item.textContent;
        
        searchInput.value = menuName;
        hiddenInput.value = menuId;
        searchInput.classList.add('has-selection');
        dropdown.style.display = 'none';
        selectedIndex = -1;
    }

    // Handle click events on dropdown items
    dropdown.addEventListener('click', function(e) {
        if (e.target.classList.contains('search-dropdown-item') && !e.target.classList.contains('no-results')) {
            selectMenu(e.target);
        }
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
            selectedIndex = -1;
        }
    });
});

// Original submenu validation
document.getElementById('createSubmenuForm').addEventListener('submit', function (e) {
    const submenu = document.getElementById('editUnitName');
    const menuId = document.getElementById('selected_menu_id').value;
    
    // Validate menu selection
    if (!menuId) {
        e.preventDefault();
        const searchInput = document.getElementById('menu_search');
        searchInput.classList.add('is-invalid');
        
        // Show error message for menu
        let menuFeedback = document.querySelector('.search-dropdown-container + .invalid-feedback');
        if (!menuFeedback) {
            menuFeedback = document.createElement('div');
            menuFeedback.className = 'invalid-feedback';
            document.querySelector('.search-dropdown-container').parentNode.appendChild(menuFeedback);
        }
        menuFeedback.textContent = 'Please select a valid menu.';
        menuFeedback.style.display = 'block';
    } else {
        const searchInput = document.getElementById('menu_search');
        searchInput.classList.remove('is-invalid');
        const menuFeedback = document.querySelector('.search-dropdown-container + .invalid-feedback');
        if (menuFeedback) menuFeedback.style.display = 'none';
    }
    
    // Validate submenu
    const valid = submenu.value.trim().match(/^\S+\s+\S+/);
    if (!valid) {
        e.preventDefault();
        submenu.classList.add('is-invalid');
        let feedback = document.querySelector('#editUnitName + .invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            submenu.parentNode.appendChild(feedback);
        }
        feedback.textContent = 'Submenu harus terdiri dari minimal dua kata.';
    } else {
        submenu.classList.remove('is-invalid');
        let feedback = document.querySelector('#editUnitName + .invalid-feedback');
        if (feedback) feedback.remove();
    }
});
</script>

<?= $this->endSection() ?>