<?php
/**
 * Products page for Splendore
 */
include('config.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - COS221</title>
    <link rel="stylesheet" href="css/products.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
</head>
<body>

    <header>
        <h1>All Products</h1>
        
    </header>

    <?php include('header.php'); ?>
    <div class="success-alert" id="success-alert"></div>


    <!-- Search Bar -->
    <input type="text" class="search-bar" placeholder="Search for products...">


    <!-- Filters & Sorting -->
    <div class="filters">
        <div class="filter-box">
            <h3>Category</h3>
            <select id="category-select">
                <option value="default">All Categories</option>
            </select>
        </div>


        <div class="filter-box">
            <h3>Brand</h3>
            <select id="brand-select">
                <option value="default">All Brands</option>
            </select>
        </div>

        <div class="filter-box">
            <h3>Price Range</h3>
            <input type="range" id="price-range" min="0" max="1000" step="50">
            <p id="price-range-value">Max Price: R1000</p>
        </div>

        <div class="filter-box">
            <h3>Sort By</h3>
            <select id="sort-select">
                <option value="default">Default</option>
                <option value="price-low">Price: Low to High</option>
                <option value="price-high">Price: High to Low</option>
            </select>
        </div>
    </div>

    <!-- Product List -->
    <div id="product-container" class="product-container">
        <img id="loading-animation" src="img/loading.gif" alt="Loading..." /> 
    </div>

   

    <script src="js/products.js"></script>

</body>
<?php include 'footer.php'; ?>