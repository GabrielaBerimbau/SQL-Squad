// products.js
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const productContainer = document.getElementById('product-container');
    const searchBar = document.querySelector('.search-bar');
    const categorySelect = document.getElementById('category-select');
    const brandSelect = document.getElementById('brand-select');
    const priceRange = document.getElementById('price-range');
    const priceRangeValue = document.getElementById('price-range-value');
    const sortSelect = document.getElementById('sort-select');
    const loadingAnimation = document.getElementById('loading-animation');
    const successAlert = document.getElementById('success-alert');
    
    // Current filter values
    let filters = {
        category_id: 'default',
        brand: 'default',
        sort: 'default',
        search: ''
    };
    
    // Initialize product display
    loadProducts();
    
    // Setup event listeners
    searchBar.addEventListener('input', debounce(function() {
        filters.search = this.value.trim();
        loadProducts();
    }, 500));
    
    categorySelect.addEventListener('change', function() {
        filters.category_id = this.value;
        loadProducts();
    });
    
    brandSelect.addEventListener('change', function() {
        filters.brand = this.value;
        loadProducts();
    });
    
    sortSelect.addEventListener('change', function() {
        filters.sort = this.value;
        loadProducts();
    });
    
    // Load products from API
    function loadProducts() {
        // Show loading animation
        loadingAnimation.style.display = 'block';
        productContainer.innerHTML = '';
        
        // Prepare data for API request
        const requestData = {
            type: 'GetAllProducts',
            ...filters
        };
        
        // Fetch products from API
        fetch('api.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestData)
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading animation
            loadingAnimation.style.display = 'none';
            
            if (data.status === 'success') {
                displayProducts(data.data.products);
                populateFilterDropdowns(data.data.categories, data.data.brands);
            } else {
                productContainer.innerHTML = `<p class="error-message">Error loading products: ${data.data}</p>`;
            }
        })
        .catch(error => {
            loadingAnimation.style.display = 'none';
            console.error('Error:', error);
            productContainer.innerHTML = '<p class="error-message">Failed to load products. Please try again later.</p>';
        });
    }
    
    // Display products in the container
    function displayProducts(products) {
        if (products.length === 0) {
            productContainer.innerHTML = '<p class="no-products">No products found matching your criteria.</p>';
            return;
        }
        
        // Clear previous products
        productContainer.innerHTML = '';
        
        // Create product cards
        products.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            
            // Format the rating or show "Not Rated"
            const ratingDisplay = product.avg_rating !== null ? 
                `${parseFloat(product.avg_rating).toFixed(1)} / 5.0` : 
                'Not Rated Yet';
            
            productCard.innerHTML = `
                <img src="${product.primary_image}" alt="${product.name}" class="product-image">
                <h3 class="product-name">${product.name}</h3>
                <p class="product-brand">${product.brand || 'Brand not specified'}</p>
                <p class="product-rating">★ ${ratingDisplay}</p>
                <p class="product-description">${product.description || 'No description available'}</p>
                <button class="add-to-cart-btn" data-id="${product.product_id}">Add to Cart</button>
                <button class="view-details-btn" onclick="window.location.href='product-details.php?id=${product.product_id}'">View Details</button>
            `;
            
            productContainer.appendChild(productCard);
        });
    }
    
    // Populate filter dropdowns with data from API
    function populateFilterDropdowns(categories, brands) {
        // Only populate if this is the first load (to avoid resetting user selections)
        if (categorySelect.options.length <= 1) {
            // Populate categories
            categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.name;
                categorySelect.appendChild(option);
            });
        }
        
        if (brandSelect.options.length <= 1) {
            // Populate brands
            brands.forEach(brand => {
                const option = document.createElement('option');
                option.value = brand;
                option.textContent = brand;
                brandSelect.appendChild(option);
            });
        }
    }
    
    // Show alert message
    function showAlert(message, type) {
        successAlert.textContent = message;
        successAlert.className = 'success-alert ' + type;
        successAlert.style.display = 'block';
        
        setTimeout(() => {
            successAlert.style.display = 'none';
        }, 3000);
    }
    
    // Debounce function for search input
    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this;
            const args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                func.apply(context, args);
            }, wait);
        };
    }
});