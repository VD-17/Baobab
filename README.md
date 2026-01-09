# Baobab – C2C E-Commerce Platform
Baobab is a consumer-to-consumer (C2C) e-commerce platform built for the South African market. The platform empowers individuals to buy and sell products across multiple categories including electronics, vehicles, clothing, and furniture. It features in-app messaging for direct buyer-seller communication, secure transactions through a trusted payment gateway, and a user-friendly product listing process.

This project was developed as part of an E-commerce course, applying real-world e-commerce principles, system design practices, user experience, and full-stack development concepts.

## Technologies Used
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Design:** Figma
- **Development Tools:** VS Code, XAMPP (Apache + MySQL)
- **Version Control:** GitHub
- **Integrations:** PayFast (sandbox mode), Google Translate API

## Features
### User Features
- **Authentication:** Sign up, sign in, logout
- **Product Management:** Add, edit, and delete product listings
- **Shopping Experience:**
  - Browse product categories
  - View product details
  - Message sellers via in-app chat
  - Add products to favorites
  - Add to cart and simulate checkout
  - Leave product reviews
- **User Dashboard:**
  - Edit profile information
  - Manage product listings
  - View and manage orders
  - Manage settings and change password
  - Add and manage payment details

### Admin Features
- View platform activity
- Manage users and products
- Add and manage admin accounts
- Handle seller payouts
- View analytics and performance metrics
- Manage platform settings
- Oversee overall system performance

## Development Process
The project began with writing a project proposal, designing wireframes, system architecture diagrams, and database schema design.

I followed an iterative development approach:

1. **Authentication System** – Built role-based authentication routing users and admins to their respective dashboards
2. **Product Listings** – Developed functionality for sellers to create and manage product listings
3. **Shop Interface** – Created the product browsing experience and detailed product pages
4. **Favorites System** – Implemented product favoriting functionality
5. **Messaging Feature** – Built a custom messaging system from scratch for buyer-seller communication
6. **Notifications** – Added alerts for new messages
7. **Shopping Cart & Payments** – Developed cart functionality and integrated PayFast sandbox for payment simulation
8. **Review System** – Implemented product reviews
9. **User Dashboard** – Built comprehensive dashboard for managing products, orders, and settings
10. **Homepage & Navigation** – Designed intuitive homepage with search and category browsing
11. **Localization** – Integrated Google Translate API to support all South African languages

Throughout this process, I documented my learning to ensure deep understanding of each component and its integration within the larger system.

## Key Learnings
This project was my first full-stack development experience and provided valuable insights:

- **Full-Stack Integration:** Understanding how frontend communicates with backend via PHP and MySQL
- **Advanced CSS:** Responsive design principles and keyframe animations for improved UX
- **Payment Processing:** Experience integrating third-party payment gateways
- **API Integration:** Successfully implemented Google Translate API
- **Development Tools:** Proficiency with XAMPP for local server management
- **Custom Messaging:** Built messaging architecture from scratch, structuring the database for real-time communication using user IDs
- **Deployment & Version Control:** Learned deployment processes and Git workflows
- **Overall Growth:** Enhanced problem-solving abilities, planning skills, and confidence in building end-to-end solutions

## Future Improvements
- **Concurrency:** Implement asynchronous processing to support simultaneous user access
- **Database Locking:** Add transaction locks to prevent inventory conflicts during purchases
- **Shipping Integration:** Introduce shipping features to streamline product deliveries (currently users arrange meetups via messaging)
- **Enhanced Security:** Implement additional security measures for secure transactions
- **Advanced Analytics:** Expand analytics capabilities for sellers and admins

## Installation & Setup
### Prerequisites
- XAMPP Control Panel
- Git
- Code editor (VS Code recommended)

### Steps to Run Locally

1. **Clone the repository**
```bash
   git clone https://github.com/VD-17/Baobab.git
```

2. **Open the project in your IDE**

3. **Install and configure XAMPP**
   - Download and install XAMPP if not already installed
   - Open XAMPP Control Panel
   - Start Apache and MySQL modules

4. **Import the database**
   - Access phpMyAdmin (usually at `localhost/phpmyadmin`)
   - Create a new database
   - Import the provided SQL file (located in root folder)

5. **Run the application**
   - Navigate to `localhost/baobab` in your browser


**Note:** This project uses PayFast in sandbox mode for payment simulation. For production use, proper payment gateway configuration and additional security measures are required.
