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

<?php include('header.php'); ?>

<div class="page-layout">
        <div class="view-container">
            
            <div class="carousel">
                <img src="candle1.png" alt="Lavender Bliss - Image 1" class="carousel-image">
                <img src="candle1-2.jpg" alt="Lavender Bliss - Image 2" class="carousel-image">
                <img src="candle1-3.jpg" alt="Lavender Bliss - Image 3" class="carousel-image">
            </div>

            <div class="product-info">
                <h2 class="product-title">Lavender Bliss</h2>
                <p class="product-price"><strong>Original Price:</strong> R200</p>
                <p class="product-category"><strong>Category:</strong> Aromatherapy Candle</p>
                <p class="product-brand"><strong>Brand:</strong> Yankee Candle</p>

                <p class="product-description">
                    Experience the calming scent of Lavender Bliss, designed to bring relaxation and tranquility. 
                    This hand-poured soy wax candle is infused with pure lavender essential oils, 
                    perfect for unwinding after a long day. Ideal for meditation, sleep, and stress relief.
                </p>

                <button class="button wishlist">Wishlist</button>
            </div>

            <div class="reviews-section">
                <h3 class="reviews-title">Customer Reviews</h3>
                
                <div class="review">
                    <div class="review-header">
                        <span class="review-author">Sophie M.</span>
                        <span class="review-date">April 28, 2025</span>
                    </div>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="review-comment">
                        This candle is absolutely divine! The lavender scent is authentic and not overwhelming.
                        It creates such a relaxing atmosphere in my bedroom, and I've been sleeping better since I started using it.
                        Burns evenly and lasts longer than most candles I've tried. Definitely worth the price!
                    </p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <span class="review-author">Daniel T.</span>
                        <span class="review-date">April 15, 2025</span>
                    </div>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star" class="empty-star"></i>
                    </div>
                    <p class="review-comment">
                        I bought this as a gift for my wife who loves lavender. She was impressed by the quality and how well
                        the scent filled our living room without being overpowering. The only reason for 4 stars instead of 5
                        is that I wish it came in a slightly larger size. Great product otherwise!
                    </p>
                </div>
                
                <div class="review">
                    <div class="review-header">
                        <span class="review-author">Lerato N.</span>
                        <span class="review-date">March 30, 2025</span>
                    </div>
                    <div class="stars">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="far fa-star" class="empty-star"></i>
                        <i class="far fa-star" class="empty-star"></i>
                    </div>
                    <p class="review-comment">
                        The scent is lovely but not as strong as I expected. The candle looks beautiful but burns down 
                        quicker than advertised. Would still recommend for the quality of the lavender fragrance, but 
                        be aware that it might not last as long as you hope.
                    </p>
                </div>
            </div>

            <div class="review-button-container">
                <button class="review-button">Review This Product</button>
            </div>
        </div>

        <div class="sidebar">
            <div class="retailer-block">
                <div class="retailer-name">Candle Haven</div>
                <div class="price-container">
                    <span class="new-price">R170</span>
                    <span class="old-price">R200</span>
                </div>
                <button class="view-offer-btn">View Offer</button>
            </div>
            
            <div class="retailer-block">
                <div class="retailer-name">Aroma Essentials</div>
                <div class="price-container">
                    <span class="new-price">R175</span>
                    <span class="old-price">R200</span>
                </div>
                <button class="view-offer-btn">View Offer</button>
            </div>
            
            <div class="retailer-block">
                <div class="retailer-name">Scent & Soul</div>
                <div class="price-container">
                    <span class="new-price">R165</span>
                    <span class="old-price">R200</span>
                </div>
                <button class="view-offer-btn">View Offer</button>
            </div>
        </div>
    </div>

</body>
</html>