$(document).ready(function() {
    var selectedRating = 0;

    // Mouse enter - highlight stars
    $(document).on('mouseenter', '.submit_star', function() {
        var rating_value = $(this).data('rating');
        resetStar();
        for (var i = 1; i <= rating_value; i++) {
            $('.submit_star').eq(i - 1).addClass('text-warning');
        }
    });

    // Mouse leave - show selected rating or reset
    $(document).on('mouseleave', '.submit_star', function() {
        resetStar();
        if (selectedRating > 0) {
            for (var i = 1; i <= selectedRating; i++) {
                $('.submit_star').eq(i - 1).addClass('text-warning');
            }
        }
    });

    // Click to select rating
    $(document).on('click', '.submit_star', function() {
        var rating_value = $(this).data('rating');
        selectedRating = rating_value;
        $('#selected_rating').val(rating_value);
        resetStar();
        for (var i = 1; i <= rating_value; i++) {
            $('.submit_star').eq(i - 1).addClass('text-warning');
        }
    });

    function resetStar() {
        $('.submit_star').removeClass('text-warning');
    }

    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        var userName = $('#reviewer').val();
        var userMessage = $('#message').val();
        var rating_value = $('#selected_rating').val();
        var productId = $('input[name="product_id"]').val();

        if (userName === '' || userMessage === '' || rating_value === '0') {
            alert('Please fill all fields and select a rating.');
            return false;
        }

        $.ajax({
            url: '../pages/review.php',
            method: 'POST',
            data: { 
                rating_value: rating_value, 
                userName: userName, 
                userMessage: userMessage, 
                product_id: productId 
            },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    alert('Error: ' + data.error);
                } else {
                    loadData();
                    $('#reviewer').val('');
                    $('#message').val('');
                    $('#selected_rating').val('0');
                    selectedRating = 0;
                    resetStar();
                    alert('Review submitted successfully.');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', xhr.responseText);
                alert('Error submitting review: ' + error);
            }
        });
    });

    function loadData() {
        var productId = $('input[name="product_id"]').val();
        $.ajax({
            url: '../pages/review.php',
            method: 'POST',
            data: { action: 'load_data', product_id: productId },
            dataType: 'json',
            success: function(data) {
                if (data.error) {
                    console.error('Error loading data:', data.error);
                    return;
                }

                $('#totalRatings').text(data.averageRatings);
                $('#total_review_rating').text(data.totalReviews + ' reviews');
                $('.total_5_star_review').text(data.totalRatings5);
                $('.total_4_star_review').text(data.totalRatings4);
                $('.total_3_star_review').text(data.totalRatings3);
                $('.total_2_star_review').text(data.totalRatings2);
                $('.total_1_star_review').text(data.totalRatings1);

                $('#5_star_progress').css('width', data.totalReviews > 0 ? (data.totalRatings5 / data.totalReviews * 100) + '%' : '0%');
                $('#4_star_progress').css('width', data.totalReviews > 0 ? (data.totalRatings4 / data.totalReviews * 100) + '%' : '0%');
                $('#3_star_progress').css('width', data.totalReviews > 0 ? (data.totalRatings3 / data.totalReviews * 100) + '%' : '0%');
                $('#2_star_progress').css('width', data.totalReviews > 0 ? (data.totalRatings2 / data.totalReviews * 100) + '%' : '0%');
                $('#1_star_progress').css('width', data.totalReviews > 0 ? (data.totalRatings1 / data.totalReviews * 100) + '%' : '0%');

                var countStar = 0;
                $('.main_star').each(function() {
                    countStar++;
                    if (Math.ceil(parseFloat(data.averageRatings)) >= countStar) {
                        $(this).addClass('text-warning').removeClass('star-light');
                    } else {
                        $(this).removeClass('text-warning').addClass('star-light');
                    }
                });

                $('#displayReviews').empty();
                if (data.ratingsList.length > 0) {
                    data.ratingsList.forEach(function(review) {
                        $('#displayReviews').append(`
                            <div class="review-item">
                                <p class="reviewer-name">${review.name}</p>
                                <p class="review-rating">${review.rating} <i class="fa-solid fa-star"></i></p>
                                <span class="review-message">${review.message}</span>
                                <div class="review-like">
                                    <button><i class="fa-solid fa-thumbs-up"></i></button>
                                    <input type="number" class="like-count" name="numLike" value="0">
                                </div>
                            </div>
                        `);
                    });
                } else {
                    $('#displayReviews').append('<p>No reviews yet.</p>');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading data:', xhr.responseText);
            }
        });
    }

    // Load data on page load
    loadData();
});