document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const autocompleteResults = document.getElementById('autocomplete-results');
    let currentSelection = -1;
    let searchTimeout;

    if (!searchInput || !autocompleteResults) return;

    // Handle input changes
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        
        clearTimeout(searchTimeout);
        
        if (query.length < 2) {
            hideResults();
            return;
        }

        // Debounce the search to avoid too many requests
        searchTimeout = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });

    // Handle keyboard navigation
    searchInput.addEventListener('keydown', function(e) {
        const items = autocompleteResults.querySelectorAll('.autocomplete-item');
        
        switch(e.key) {
            case 'ArrowDown':
                e.preventDefault();
                currentSelection = Math.min(currentSelection + 1, items.length - 1);
                updateSelection(items);
                break;
            case 'ArrowUp':
                e.preventDefault();
                currentSelection = Math.max(currentSelection - 1, -1);
                updateSelection(items);
                break;
            case 'Enter':
                if (currentSelection >= 0 && items[currentSelection]) {
                    e.preventDefault();
                    selectItem(items[currentSelection]);
                }
                break;
            case 'Escape':
                hideResults();
                break;
        }
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !autocompleteResults.contains(e.target)) {
            hideResults();
        }
    });

    function fetchSuggestions(query) {
        fetch(`api/autocomplete.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Autocomplete error:', error);
                hideResults();
            });
    }

    function displayResults(suggestions) {
        if (suggestions.length === 0) {
            hideResults();
            return;
        }

        let html = '';
        suggestions.forEach((item, index) => {
            const icon = getTypeIcon(item.type);
            html += `
                <div class="autocomplete-item" data-value="${item.suggestion}" data-type="${item.type}" data-id="${item.id || ''}">
                    <i class="${icon} autocomplete-icon"></i>
                    <span>${escapeHtml(item.suggestion)}</span>
                    <span class="autocomplete-type">${item.type}</span>
                </div>
            `;
        });

        autocompleteResults.innerHTML = html;
        autocompleteResults.style.display = 'block';
        currentSelection = -1;

        // Add click handlers
        autocompleteResults.querySelectorAll('.autocomplete-item').forEach(item => {
            item.addEventListener('click', () => selectItem(item));
        });
    }

    function selectItem(item) {
        const value = item.dataset.value;
        const type = item.dataset.type;
        const id = item.dataset.id;

        searchInput.value = value;
        hideResults();

        // Submit the form or redirect based on type
        if (type === 'user' && id) {
            window.location.href = `pages/profile.php?userId=${id}`;
        } else {
            // Submit the search form
            searchInput.closest('form').submit();
        }
    }

    function updateSelection(items) {
        items.forEach((item, index) => {
            item.classList.toggle('selected', index === currentSelection);
        });
    }

    function hideResults() {
        autocompleteResults.style.display = 'none';
        currentSelection = -1;
    }

    function getTypeIcon(type) {
        switch(type) {
            case 'product': return 'fa-solid fa-box';
            case 'category': return 'fa-solid fa-tag';
            case 'user': return 'fa-solid fa-user';
            default: return 'fa-solid fa-search';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});