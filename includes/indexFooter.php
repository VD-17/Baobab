<!DOCTYPE html>
<html lang="en">
<?php 
    include('head.php');
?>
<head>
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/google.css">
</head>
<body>
    <footer class="section-p1">
        <div class="col">
            <img src="assets/images/Logo/logo (1).png" alt="Baobab's Logo" id="logoImg">
            <h6>A community marketplace where you can buy and sell items.</h6>
            <h6>directly from each other.</h6>
            <br>
            <h4>FIND US ON</h4>
            <p><strong>Address:</strong> 562 River Road, Mowbray, Cape Town</p>
            <p><strong>Phone:</strong> +016 222 2365 / (+27) 12 345 6789</p>
            <p><strong>Hours:</strong> 10:00 - 18:00, Mon - Sat</p>
            <div class="follow">
                <h4>Follow us</h4>
                <div class="icon">
                    <i class="fab fa-facebook-f"></i>
                    <i class="fab fa-twitter"></i>
                    <i class="fab fa-instagram"></i>
                    <i class="fa-solid fa-envelope"></i>
                </div>
            </div>
        </div>

        <div class="col">
            <h4>BAOBAB</h4>
            <a href="pages/about.php">About Us</a>
            <a href="pages/contact.php">Contact Us</a>
        </div>

        <div class="col">
            <h4>My Account</h4>
            <a href="pages/signIn.php">Sign In</a>
            <a href="pages/cart.php">View Cart</a>
            <a href="pages/favourite.php">My Favourites</a>
            <a href="pages/settings.php">Settings</a>
        </div>

        <div class="col">
            <h4>LEGAL</h4>
            <a href="pages/terms.php">Terms of Service</a>
            <a href="pages/privacy_policy.php">Privacy Policy</a>
            <a href="pages/community.php">Community Guidelines</a>
            <a href="pages/safety.php">Safety Guidelines</a>
        </div>

        <div class="col">
            <h4>HELP & SUPPORT</h4>
            <a href="pages/faq.php">FAQ</a>
            <a href="pages/baobab_works.php">How Baobab Works</a>
        </div>

        <div class="copyright">
            <p>Copyright <i class="fa-regular fa-copyright"></i> 2025 | Baobab. All rights reserved</p>
        </div>

        <div class="footer-translate">
            <span class="translate-label">🌐 Language:</span>
            <div id="google_translate_element"></div>
        </div>

        <script type="text/javascript">
        function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'af,zu,xh,st,tn,ss,ve,ts,nr,nso,en',
            layout: google.translate.TranslateElement.InlineLayout.SIMPLE,
            autoDisplay: false
        }, 'google_translate_element');
        }
        </script>

        <script type="text/javascript" 
                src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
        </script>
    </footer>
</body>
</html>