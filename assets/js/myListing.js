document.addEventListener('DOMContentLoaded', function() {
    const actionSelects = document.querySelectorAll('#action');

    actionSelects.forEach(select => {
        select.addEventListener('change', function() {
            const action = this.value;
            const productId = this.dataset.productId; // Assumes product ID is set in HTML
            if (action && productId) {
                switch (action) {
                    case 'View':
                        window.location.href = `../pages/viewProduct.php?id=${productId}`;
                        break;
                    case 'Edit':
                        window.location.href = `../pages/editProduct.php?id=${productId}`;
                        break;
                    case 'Delete':
                        if (confirm('Are you sure you want to delete this product?')) {
                            window.location.href = `../api/Listing/deleteProduct.php?id=${productId}`;
                        }
                        break;
                }
                this.value = ''; // Reset dropdown
            }
        });
    });
});


