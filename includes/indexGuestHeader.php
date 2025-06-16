<section id="header">
    <a href="../index.php">
        <img src="assets/images/Logo/logo (1).png" alt="Baobab's Logo" id="logoImg">
    </a>
    <div id="searchbar"> 
        <form action="pages/search_handler.php" method="GET" style="display: flex; align-items: center; position: relative; width: 100%;">
            <i class="fa-solid fa-magnifying-glass" id="search"></i>
            <input class="search" type="search" id="searchInput" name="query" 
                placeholder="Search products or users" 
                value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                autocomplete="off">
            
            <div class="search-icons">
                <button type="submit">
                    <i class="bi bi-arrow-return-left"></i>
                </button>
            </div>
            
            <div id="autocomplete-results" class="autocomplete-dropdown"></div>
        </form>
    </div>
    <div id="navbar">
        <li><a class="signup" id="user" href="pages/signUp.php"><span>Sign Up</span></a></li>
        <li><a class="signup" id="user" href="pages/signIn.php"><span>Sign In</span></a></li>
    </div>
</section>