document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('productForm');

    // Define missing constants
    const allowedImageTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    const allowedVideoTypes = ['video/mp4', 'video/webm', 'video/ogg'];
    const maxImageFileSize = 5 * 1024 * 1024; // 5MB
    const maxVideoFileSize = 50 * 1024 * 1024; // 50MB

    const productName = document.getElementById('productName');
    const description = document.getElementById('description');
    const productCategory = document.getElementById('productCategory');
    const subCategory = document.getElementById('subCategory');
    const subcategoriesDatalist = document.getElementById('sub');
    const quality = document.getElementById('quality');
    const price = document.getElementById('price');
    const productPicture = document.getElementById('productPicture');
    const productVideo = document.getElementById('productVideo');

    function showError(input, message) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = message;
            errorContainer.style.display = 'block';
        }
        input.classList.add('error-border');
    }

    function showSuccess(input) {
        const errorContainer = document.getElementById(`${input.id}-error`);
        if (errorContainer) {
            errorContainer.textContent = '';
            errorContainer.style.display = 'none';
        }
        input.classList.remove('error-border');
    }

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
        } 
        if (input.value.length < 10) {
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

    function checkProductPicture(input) {
        // If no files selected, skip validation (existing images will be kept)
        if (!input || !input.files) {
            return true;
        }

        if (input.files.length === 0) {
            showSuccess(input);
            return true;
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
        if (!input || !input.files) {
            return true;
        }
        // If no file selected, skip validation (existing video will be kept)
        if (input.files.length === 0) {
            showSuccess(input);
            return true;
        }

        const file = input.files[0];
        if (!allowedVideoTypes.includes(file.type)) {
            showError(input, 'Product video must be MP4, WEBM, OGG');
            return false;
        }
        if (file.size > maxVideoFileSize) {
            showError(input, 'Product video must be less than 50MB');
            return false;
        }
        showSuccess(input);
        return true;
    }

    // Image preview functionality
    const imagePreview = document.getElementById('imagePreview');
    if (productPicture && imagePreview) {
        productPicture.addEventListener('change', () => {
            imagePreview.innerHTML = '';
            const files = Array.from(productPicture.files);
            files.forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        const previewItem = document.createElement('div');
                        previewItem.className = 'image-container';
                        previewItem.style.position = 'relative';
                        previewItem.style.display = 'inline-block';
                        previewItem.style.margin = '5px';

                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.style.maxWidth = '100px';
                        img.style.maxHeight = '100px';
                        img.style.objectFit = 'cover';
                        img.style.borderRadius = '5px';

                        const removeButton = document.createElement('button');
                        removeButton.type = 'button';
                        removeButton.textContent = '✖';
                        removeButton.style.position = 'absolute';
                        removeButton.style.top = '2px';
                        removeButton.style.right = '2px';
                        removeButton.style.background = 'rgba(255, 0, 0, 0.7)';
                        removeButton.style.color = 'white';
                        removeButton.style.border = 'none';
                        removeButton.style.borderRadius = '50%';
                        removeButton.style.width = '20px';
                        removeButton.style.height = '20px';
                        removeButton.style.cursor = 'pointer';
                        removeButton.style.display = 'flex';
                        removeButton.style.alignItems = 'center';
                        removeButton.style.justifyContent = 'center';
                        removeButton.style.fontSize = '12px';

                        removeButton.addEventListener('click', () => {
                            previewItem.remove();
                            const dataTransfer = new DataTransfer();
                            files.forEach((f, i) => {
                                if (i !== index) {
                                    dataTransfer.items.add(f);
                                }
                            });
                            productPicture.files = dataTransfer.files;
                        });

                        previewItem.appendChild(img);
                        previewItem.appendChild(removeButton);
                        imagePreview.appendChild(previewItem);
                    };
                    reader.readAsDataURL(file);
                }
            });
        });
    }

    // Video preview functionality
    // const videoPreview = document.getElementById('videoPreview');
    // if (productVideo && videoPreview) {
    //     productVideo.addEventListener('change', () => {
    //         videoPreview.innerHTML = '';
    //         const file = productVideo.files[0];
    //         if (file && file.type.startsWith('video/')) {
    //             const reader = new FileReader();
    //             reader.onload = (e) => {
    //                 const previewItem = document.createElement('div');
    //                 previewItem.className = 'video-container';
    //                 previewItem.style.position = 'relative';
    //                 previewItem.style.display = 'inline-block';
    //                 previewItem.style.margin = '5px';

    //                 const video = document.createElement('video');
    //                 video.src = e.target.result;
    //                 video.controls = true;
    //                 video.style.maxWidth = '200px';
    //                 video.style.maxHeight = '200px';
    //                 video.style.borderRadius = '5px';

    //                 const removeButton = document.createElement('button');
    //                 removeButton.type = 'button';
    //                 removeButton.textContent = '✖';
    //                 removeButton.style.position = 'absolute';
    //                 removeButton.style.top = '2px';
    //                 removeButton.style.right = '2px';
    //                 removeButton.style.background = 'rgba(255, 0, 0, 0.7)';
    //                 removeButton.style.color = 'white';
    //                 removeButton.style.border = 'none';
    //                 removeButton.style.borderRadius = '50%';
    //                 removeButton.style.width = '20px';
    //                 removeButton.style.height = '20px';
    //                 removeButton.style.cursor = 'pointer';
    //                 removeButton.style.display = 'flex';
    //                 removeButton.style.alignItems = 'center';
    //                 removeButton.style.justifyContent = 'center';
    //                 removeButton.style.fontSize = '12px';

    //                 removeButton.addEventListener('click', () => {
    //                     previewItem.remove();
    //                     productVideo.value = '';
    //                 });

    //                 previewItem.appendChild(video);
    //                 previewItem.appendChild(removeButton);
    //                 videoPreview.appendChild(previewItem);
    //             };
    //             reader.readAsDataURL(file);
    //         }
    //     });
    // }

    // Event listeners for validation
    if (productName) {
        productName.addEventListener('blur', () => checkProductName(productName));
    }
    if (description) {
        description.addEventListener('blur', () => checkDescription(description));
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
    if (productPicture) {
        productPicture.addEventListener('change', () => checkProductPicture(productPicture));
    }
    // if (productVideo) {
    //     productVideo.addEventListener('change', () => checkVideo(productVideo));
    // }

    // Form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const isProductNameValid = checkProductName(productName);
            const isDescriptionValid = checkDescription(description);
            const isProductCategoryValid = checkProductCategory(productCategory);
            const isQualityValid = checkQuality(quality);
            const isPriceValid = checkPrice(price);
            const isProductPictureValid = productPicture ? checkProductPicture(productPicture) : true;
            //  const isProductVideoValid = productVideo ? checkVideo(productVideo) : true;

            if (isProductNameValid && isDescriptionValid && isProductCategoryValid && 
                isQualityValid && isPriceValid && isProductPictureValid) {
                console.log('Form is valid and ready to submit');
                this.submit();
            } else {
                console.log('Form validation failed');
            }
        });
    }
});