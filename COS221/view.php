<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View - Splendore</title>
    <link rel="stylesheet" href="css/view.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
</head>
<body>

    <nav class="navbar">
        <ul>
            <li><a href="products.html" class="nav-link active">Products</a></li>
            <li><a href="deals.html" class="nav-link">Best Deals</a></li>
            <li><a href="wishlist.html" class="nav-link">Wishlist</a></li>
        </ul>
    </nav>

    <div class="page-layout">
        <div class="view-container">
            <div class="carousel" id="carousel">
                <!-- Images will be inserted dynamically -->
            </div>

            <div class="product-info">
                <h2 class="product-title" id="product-title"></h2>
                <p class="product-price" id="product-price"></p>
                <p class="product-category" id="product-category"></p>
                <p class="product-brand" id="product-brand"></p>
                <p class="product-description" id="product-description"></p>
                <button class="button wishlist" id="add-to-wishlist">Wishlist</button>
            </div>

            <div class="reviews-section" id="reviews-section">
                <h3 class="reviews-title">Customer Reviews</h3>
                <!-- Reviews will be inserted dynamically -->
            </div>

            <div class="review-button-container">
                <button class="review-button">Review This Product</button>
            </div>
        </div>

        <div class="sidebar" id="retailer-sidebar">
            <!-- Retailer offers will be inserted dynamically -->
        </div>
    </div>

    <script src="view.js"></script>
</body>
</html>