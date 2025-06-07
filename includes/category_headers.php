<?php
function getCategoryHeader($categoryName) {

// Define headers for each category
    $headers = [
        'electronics' => [
            'title' => '#Electronics',
            'subtitle' => 'Discover the Latest Tech & Gadgets!',
            'image' => '../assets/images/Headers/electronics.jpg',
            'overlay_color' => 'rgba(255, 159, 28, 0.3)',
            'class' => 'electronics-header'
        ],
        'vehicle' => [
            'title' => '#Vehicles',
            'subtitle' => 'Find Your Perfect Ride!',
            'image' => '../assets/images/Headers/vehicle.jpg',
            'overlay_color' => 'rgba(8, 3, 87, 0.3)',
            'class' => 'vehicles-header'
        ],
        'home' => [
            'title' => '#Home',
            'subtitle' => 'Everything for Your Perfect Home!',
            'image' => '../assets/images/Headers/home.png',
            'overlay_color' => 'rgba(60, 66, 111, 0.3)',
            'class' => 'home-header'
        ],
        'fashion' => [
            'title' => '#Fashion',
            'subtitle' => 'Style That Speaks to You!',
            'image' => '../assets/images/Headers/fashion.jpg',
            'overlay_color' => 'rgba(214, 255, 183, 0.3)',
            'class' => 'fashion-header'
        ],
        'furniture' => [
            'title' => '#Furniture',
            'subtitle' => 'Comfort Meets Style!',
            'image' => '../assets/images/Headers/furniture2.jpg',
            'overlay_color' => 'rgba(255, 159, 28, 0.2)',
            'class' => 'furniture-header'
        ],
        'toys-games' => [
            'title' => '#Toys & Games',
            'subtitle' => 'Fun for All Ages!',
            'image' => '../assets/images/Headers/toys2.jpeg',
            'overlay_color' => 'rgba(60, 66, 111, 0.2)',
            'class' => 'toys-header'
        ],
        'outdoor-sports' => [
            'title' => '#Outdoor & Sports',
            'subtitle' => 'Gear Up for Adventure!',
            'image' => '../assets/images/Headers/sports.jpg',
            'overlay_color' => 'rgba(8, 3, 87, 0.2)',
            'class' => 'sports-header'
        ],
        'antiques-collectibles' => [
            'title' => '#Antiques & Collectibles',
            'subtitle' => 'Treasures from the Past!',
            'image' => '../assets/images/Headers/antiques.jpg',
            'overlay_color' => 'rgba(255, 159, 28, 0.4)',
            'class' => 'antiques-header'
        ],
        'books' => [
            'title' => '#Books',
            'subtitle' => 'Knowledge is Power!',
            'image' => '../assets/images/Headers/books2.jpg',
            'overlay_color' => 'rgba(214, 255, 183, 0.4)',
            'class' => 'books-header'
        ]
    ];

    return $headers[$categoryName] ?? [
        'title' => '#Products',
        'subtitle' => 'Discover Amazing Deals!',
        'image' => '../assets/images/headers/default_banner.jpg',
        'overlay_color' => 'rgba(214, 255, 183, 0.3)',
        'class' => 'default-header'
    ];

}
?>