function applyFilters() {
    document.getElementById('filterForm').submit();
}

function applySort(sortValue) {
    const url = new URL(window.location);
    url.searchParams.set('sort', sortValue);
    url.searchParams.delete('page'); // Reset to first page when sorting
    window.location.href = url.toString();
}

function clearFilters() {
    const url = new URL(window.location);
    const query = url.searchParams.get('query');
    
    // Keep only the search query, remove all filters
    url.search = '';
    if (query) {
        url.searchParams.set('query', query);
    }
    
    window.location.href = url.toString();
}

function updatePriceDisplay(value) {
    document.getElementById('priceValue').textContent = value;
    document.querySelector('input[name="max_price"]').value = value;
}

// Auto-apply filters when price range changes
document.addEventListener('DOMContentLoaded', function() {
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.addEventListener('change', function() {
            applyFilters();
        });
    }
});