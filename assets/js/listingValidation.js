document.addEventListener('DOMContentLoaded', function() {
    // Form and field references
    const form = document.getElementById('productForm');

    const productName = document.getElementById('productName');
    const description = document.getElementById('description');
    const productCategory = document.getElementById('productCategory');
    const subCategory = document.getElementById('subCategory');
    const subcategoriesDatalist = document.getElementById('sub')
    const quality = document.getElementById('quality');
    const price = document.getElementById('price');
    const productPicture = document.getElementById('productPicture');
    const imagePreview = document.getElementById('imagePreview');
    const productVideo = document.getElementById('productVideo');
    const videoPreview = document.getElementById('videoPreview');

    const maxImageFileSize = 5 * 1024 * 1024;
    const maxVideoFileSize = 50 * 1024 * 1024;
    const allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    const allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];

    // Province to subCategory mapping for dynamic dropdown
    const categories = {
        'Electronics': ['Mobile phones', 'Laptops', 'Computers', 'Tablets', 'Cameras', 'Wearables(Smartwtaches)', 'Accessories'],
        'Vehicle': ['Cars', 'Motorcucles', 'Bicycles', 'Trucks', 'Parts & Accessories'],
        'Home': ['Home Decor', 'Kitchen', 'Appliances', 'Living Room', 'Bathroom', 'Garden'],
        'Fashion': ['Men\'s Clothings', 'Women\'s Clothings', 'Kids\'s Clothings', 'Footwear', 'Jewelry', 'Bags', 'Watches', 'Sunglasses'],
        'Furniture': ['Sofas', 'Chairs', 'Tables', 'storage(Cupboards, shelves, cabinets)', 'beds & mattresses'],
        'Toys & Games': ['Board games', 'Video games', 'Action Figures', 'Puzzles', 'Dolls & Plush toys'],
        'Outdoor & Sports': ['Camping equipment', 'outdoor gear', 'Sports equipment', 'Gym/Fitness equipments'],
        'Antiques & Collectibles': ['Arts', 'Coins & Currency', 'Stamps', 'Vintage Items'],
        'Books': ['Educational & Academics', 'Fiction & Non-Fiction', 'Comics', 'Magazines', 'Other']
    };

    // Error display function
    function showError(input, message) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
        }
        input.classList.add('error-border');
    }

    // Success function
    function showSuccess(input) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = '';
            errorContainer.style.display = 'none';
        }
        input.classList.remove('error-border');
    }

    // Validate product name
    function checkProductName(input) {
        if (input.value.trim() === '') {
            showError(input, 'Product name is required');
            return false;
        }
        if (input.value.length > 255) {
            showError(input, 'Product name must be less than 255 characters.');
            return false;
        }
        showSuccess(input);
        return true;
    }

    function checkDescription(input) {
        if (input.value.trim() === '') {
            showError(input, 'Description is required');
            return false;
        } 
        if (input.value.length > 1000) {
            showError(input, 'Description must be less than 1000 characters');
            return false;
        } if (input.value.length < 10) {
            showError(input, 'Description must be at least 10 characters long.');
            return false;
        }
        showSuccess(input);
        return true;
    }

    function checkProductCategory(input) {
        if (!input.value) {
            showError(input, 'Please select a product category');
            return false;
        }
        showSuccess(input);
        return true;
    }

    function checkQuality(input) {
        if (!input.value) {
            showError(input, 'Please select product quality');
            return false;
        }
        showSuccess(input);
        return true;
    }   

    function checkPrice(input) {
        if (input.value.trim() === '') {
            showError(input, 'Price is required');
            return false;
        }
        if (isNaN(input.value) || parseFloat(input.value) < 0) {
            showError(input, 'Price must be a positive number');
            return false;
        }
        if (!/^\d+(\.\d{1,2})?$/.test(input.value)) {
            showError(input, 'Price must have at most 2 decimal places.');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Check profile picture
    function checkProductPicture(input) {
        if (input.files.length === 0) {
            showError(input, 'At least one product image is required');
            return false;
        }
        
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            if (!allowedImageTypes.includes(file.type)) {
                showError(input, 'Product pictures must be JPEG, PNG, JPG');
                return false;
            }
            if (file.size > maxImageFileSize) {
                showError(input, 'Each product picture must be less than 5MB');
                return false;
            }
        }
        showSuccess(input);
        return true;
    }

    function checkVideo(input) {
        if (input.files.length === 0) {
            showSuccess(input);
            return true;
        }
        const file = input.files[0];
        if (!allowedVideoTypes.includes(file.type)) {
            showError(input, 'Product video must be MP4, WEBM, OGG');
            return false;
        }
        if (file.size > maxVideoFileSize) { // FIXED: Now 50MB
            showError(input, 'Product video must be less than 50MB');
            return false;
        }
        showSuccess(input);
        return true;
    }

    if (productCategory && subCategory && subcategoriesDatalist) {
        productCategory.addEventListener('input', function() {
            const selectedCategory = this.value;
            subcategoriesDatalist.innerHTML = '';
            subCategory.value = '';
            
            if (categories[selectedCategory]) {
                categories[selectedCategory].forEach(subCategoryName => {
                    const option = document.createElement('option');
                    option.value = subCategoryName;
                    subcategoriesDatalist.appendChild(option);
                });
            }
        });
    }

    if (productPicture && imagePreview) {
        productPicture.addEventListener('change', function() {
            imagePreview.innerHTML = ''; // Clear previous previews
            const files = this.files;

            if (files.length === 0) {
                showError(productPicture, 'At least one image is required.');
                return;
            }

            for (let i = 0; i < files.length; i++) {
                const file = files[i];

                // Validate file type
                if (!allowedImageTypes.includes(file.type)) {
                    showError(productPicture, 'Only JPG, JPEG, and PNG files are allowed.');
                    this.value = ''; // Clear the input
                    imagePreview.innerHTML = '';
                    return;
                }

                // Validate file size
                if (file.size > maxImageFileSize) {
                    showError('productPicture', 'Each image must be less than 5MB.');
                    this.value = '';
                    imagePreview.innerHTML = '';
                    return;
                }

                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imgContainer = document.createElement('div');
                    imgContainer.style.position = 'relative';
                    imgContainer.style.width = '100px';
                    imgContainer.style.height = '100px';
                    imgContainer.style.margin = '5px';

                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.width = '100%';
                    img.style.height = '100%';
                    img.style.objectFit = 'cover';

                    // Add remove button
                    const removeBtn = document.createElement('button');
                    removeBtn.textContent = 'X';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '0';
                    removeBtn.style.right = '0';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.border = 'none';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.padding = '2px 6px';

                    removeBtn.addEventListener('click', function() {
                        imgContainer.remove();
                        // Update file input (remove the file from the input)
                        const dt = new DataTransfer();
                        for (let j = 0; j < files.length; j++) {
                            if (j !== i) {
                                dt.items.add(files[j]);
                            }
                        }
                        productPicture.files = dt.files;
                        if (productPicture.files.length === 0) {
                            displayError('productPicture-error', 'At least one image is required.');
                        }
                    });

                    imgContainer.appendChild(img);
                    imgContainer.appendChild(removeBtn);
                    imagePreview.appendChild(imgContainer);
                };
                reader.readAsDataURL(file);
            }
            showSuccess('productPicture');
        });
    } else {
        showSuccess(productPicture);
        return true;
    }

    // Video Preview
    if (productVideo && videoPreview) {
        productVideo.addEventListener('change', function() {
            videoPreview.innerHTML = ''; // Clear previous preview
            const file = this.files[0];

            if (!file) {
                showError(productVideo);
                return;
            }

            // Validate file type
            if (!allowedVideoTypes.includes(file.type)) {
                showError(productVideo, 'Only MP4, WebM, and OGG files are allowed.');
                this.value = '';
                return;
            }

            // Validate file size
            if (file.size > maxVideoFileSize) {
                showError(productVideo, 'Video must be less than 5MB.');
                this.value = '';
                return;
            }

            // Create preview
            const reader = new FileReader();
            reader.onload = function(e) {
                const video = document.createElement('video');
                video.src = e.target.result;
                video.controls = true;
                video.style.maxWidth = '200px';
                video.style.maxHeight = '200px';
                videoPreview.appendChild(video);
            };
            reader.readAsDataURL(file);
            showSuccess(productVideo);
        });
    } else {
        showSuccess(productVideo)
    }

    // Real-time validation event listeners
    if (productName) {
        productName.addEventListener('blur', () => {
            checkProductName(productName);
        });
    }
    if (description) {
        description.addEventListener('blur', () => {
            checkDescription(description);
        });
    }
    if (productCategory) {
        productCategory.addEventListener('blur', () => checkProductCategory(productCategory));
    }
    if (quality) {
        quality.addEventListener('blur', () => checkQuality(quality));
    }
    if (price) {
        price.addEventListener('blur', () => checkPrice(price));
    }

    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const isProductNameValid = checkProductName(productName);
            const isDescriptionValid = checkDescription(description);
            const isProductCategoryValid = checkProductCategory(productCategory);
            const isQualityValid = checkQuality(quality);
            const isPriceValid = checkPrice(price);
            const isProductPictureValid = checkProductPicture(productPicture);
            const isProductVideoValid = checkVideo(productVideo);

            if (isProductNameValid && isDescriptionValid && isProductCategoryValid && isQualityValid && isPriceValid && isProductPictureValid && isProductVideoValid) {
                console.log('Form is valid and ready to submit');
                this.submit();
            } else {
                console.log('Form validation failed');
            }
        });
    }
});