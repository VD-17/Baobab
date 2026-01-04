CREATE TABLE users (
    userId INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    street_address VARCHAR(255),
    suburb VARCHAR(100),
    postal_code VARCHAR(10),
    province VARCHAR(100),
    city VARCHAR(100),
    profile_picture VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    phoneNumber VARCHAR(20) NOT NULL,
    is_seller TINYINT DEFAULT 0,
    is_admin TINYINT DEFAULT 0,
    bio VARCHAR(500) DEFAULT NULL
);

CREATE TABLE profileimg (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid INT NOT NULL,
    status TINYINT NOT NULL,
    FOREIGN KEY (userid) REFERENCES users(userId) ON DELETE CASCADE
);

CREATE TABLE seller_bank_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    account_holder_name VARCHAR(100) NOT NULL,
    bank_name VARCHAR(100) NOT NULL,
    branch_code VARCHAR(10) NOT NULL,
    account_number VARCHAR(20) NOT NULL,
    account_type ENUM('savings', 'current', 'cheque') NOT NULL,
    is_verified BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productName VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    productCategory VARCHAR(100) NOT NULL,
    subCategory VARCHAR(100),
    quality ENUM('Brand New', 'Like New', 'Very Good', 'Good', 'Old', 'Need Repair') NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    productPicture VARCHAR(255) NOT NULL,
    productVideo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    delivery_method VARCHAR(50) NOT NULL DEFAULT 'Meet-Up',
    status enum('active', 'pending') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (userId) REFERENCES users(userId)
);

CREATE TABLE reviews (
    reviewId INT AUTO_INCREMENT PRIMARY KEY,
    productId INT NOT NULL,
    userId INT NOT NULL,
    reviewer VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE
);

CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productId INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (userId, productId)
);

CREATE TABLE favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userId INT NOT NULL,
    productId INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (userId) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (productId) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (userId, productId)
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payout_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    payfast_payment_id VARCHAR(100) NULL,
    sandbox_mode BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (buyer_id) REFERENCES users(userId),
    FOREIGN KEY (seller_id) REFERENCES users(userId)
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    item_price DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE seller_earnings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    seller_id INT NOT NULL,
    order_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL, 
    status ENUM('pending', 'paid', 'disputed') DEFAULT 'pending',
    payout_batch_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    payout_status enum('pending','processing','completed','failed') DEFAULT 'pending',
    payout_id VARCHAR(100) DEFAULT NULL,
    payout_date datetime DEFAULT NULL,
    updated_at timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
    FOREIGN KEY (seller_id) REFERENCES users(userId),
    FOREIGN KEY (order_id) REFERENCES orders(id)
);

CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sender_id INT,
    receiver_id INT,
    message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_read BOOLEAN DEFAULT FALSE,
    message_type ENUM('text', 'image') DEFAULT 'text',
    FOREIGN KEY (sender_id) REFERENCES users(userId),
    FOREIGN KEY (receiver_id) REFERENCES users(userId)
);

CREATE TABLE conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user1_id INT DEFAULT NULL,
    user2_id INT DEFAULT NULL,
    last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user1_id) REFERENCES users(userId),
    FOREIGN KEY (user2_id) REFERENCES users(userId)
);

CREATE TABLE payout_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    earning_id INT NOT NULL,
    seller_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payout_id VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    processed_by INT NOT NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (earning_id) REFERENCES seller_earnings(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(userId) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(userId) ON DELETE SET NULL
);


