import './../../../../vendor/filament/filament/resources/js/app.js';

// Global Search Enhancement
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl+K or Cmd+K to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('.fi-global-search-field input');
            if (searchInput) {
                searchInput.focus();
                searchInput.select();
            }
        }
        
        // Escape to close search results
        if (e.key === 'Escape') {
            const searchInput = document.querySelector('.fi-global-search-field input');
            if (searchInput && document.activeElement === searchInput) {
                searchInput.blur();
            }
        }
    });

    // Enhanced search input behavior
    const searchInput = document.querySelector('.fi-global-search-field input');
    if (searchInput) {
        // Add loading state
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();
            if (query.length > 0) {
                this.classList.add('searching');
            } else {
                this.classList.remove('searching');
            }
        });

        // Add placeholder animation
        searchInput.addEventListener('focus', function() {
            this.classList.add('focused');
        });

        searchInput.addEventListener('blur', function() {
            this.classList.remove('focused');
        });
    }

    // Enhanced search results behavior
    document.addEventListener('click', function(e) {
        const searchResults = document.querySelector('.fi-global-search-results-ctn');
        const searchInput = document.querySelector('.fi-global-search-field input');
        
        // Close results when clicking outside
        if (searchResults && !searchResults.contains(e.target) && !searchInput.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });

    // Add search suggestions
    const searchSuggestions = [
        'Jadwal',
        'Teknisi',
        'Laporan',
        'Divisi',
        'User',
        'Supervisor',
        'Request'
    ];

    // Create suggestions dropdown
    function createSuggestionsDropdown() {
        const searchField = document.querySelector('.fi-global-search-field');
        if (!searchField) return;

        const suggestionsDiv = document.createElement('div');
        suggestionsDiv.className = 'search-suggestions absolute top-full left-0 right-0 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg z-50 hidden';
        suggestionsDiv.innerHTML = `
            <div class="p-2">
                <div class="text-xs text-gray-500 dark:text-gray-400 mb-2 px-2">Saran pencarian:</div>
                ${searchSuggestions.map(suggestion => `
                    <div class="suggestion-item px-2 py-1 hover:bg-gray-100 dark:hover:bg-gray-700 rounded cursor-pointer text-sm">
                        ${suggestion}
                    </div>
                `).join('')}
            </div>
        `;

        searchField.appendChild(suggestionsDiv);

        // Show suggestions on focus
        searchInput.addEventListener('focus', function() {
            if (this.value.length === 0) {
                suggestionsDiv.classList.remove('hidden');
            }
        });

        // Hide suggestions on blur
        searchInput.addEventListener('blur', function() {
            setTimeout(() => {
                suggestionsDiv.classList.add('hidden');
            }, 200);
        });

        // Handle suggestion clicks
        suggestionsDiv.addEventListener('click', function(e) {
            if (e.target.classList.contains('suggestion-item')) {
                searchInput.value = e.target.textContent.trim();
                searchInput.focus();
                suggestionsDiv.classList.add('hidden');
                // Trigger search
                searchInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    // Initialize suggestions
    createSuggestionsDropdown();
});

// Add custom CSS for enhanced styling
const style = document.createElement('style');
style.textContent = `
    .searching {
        background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>');
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 16px;
        padding-right: 40px;
    }
    
    .search-suggestions {
        max-height: 200px;
        overflow-y: auto;
    }
    
    .suggestion-item {
        transition: background-color 0.15s ease;
    }
    
    .fi-global-search-field.focused input {
        border-color: rgb(245 158 11);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
`;
document.head.appendChild(style);
