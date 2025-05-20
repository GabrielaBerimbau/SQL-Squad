const apiUrl = "https://wheatley.cs.up.ac.za/COS221/api.php";
let products = []; // Global variable to store all products

/**
 * Fetches all products from the API
 */
function fetchProducts() {
    const productContainer = document.getElementById("product-container");
    productContainer.innerHTML = `<img id="loading-animation" src="img/loading.gif" alt="Loading...">`;

    // Build the request data - adjust fields to match your database structure
    const requestData = {
        type: "GetAllProducts",
        return: [
            "product_id",
            "name",
            "brand",
            "description",
            "specifications",
            "images",
            "avg_rating",
            "category_id"
        ],
        limit: 50
    };

    // Create and send the request
    const xhr = new XMLHttpRequest();
    xhr.open("POST", apiUrl, true);
    xhr.setRequestHeader("Content-Type", "application/json");

    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            const productContainer = document.getElementById("product-container");
            productContainer.innerHTML = "";

            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                console.log("Product data as JSON object: ", response);

                if (response.status === "success") {
                    products = response.data;
                    console.log("Product data successful: ", products);
                    populateFilters();
                    updateProductDisplay();
                } else {
                    productContainer.innerHTML = `<p>Error: ${response.message}</p>`;
                    console.error("Return error:", response.status);
                }
            } else {
                productContainer.innerHTML = `<p>Failed to fetch products. Try again later.</p>`;
                console.error("Failed to fetch products:", xhr.statusText);
            }
        }
    };

    xhr.send(JSON.stringify(requestData));
}



/**
 * Populates filter dropdowns with available options from products
 */


function populateFilters(){
    const categories = new Set();
    const brands = new Set();

    products.forEach(product => {
        if (product.category_id) categories.add(product.category_id);
        if (product.brand) brands.add(product.brand);
    });
    // Populate category dropdown
    populateDropdown("category-select", categories);
    
    // Populate brand dropdown
    populateDropdown("brand-select", brands);
    
    // Set up price range
    const priceRange = document.getElementById("price-range");
    if (priceRange) {
        priceRange.addEventListener("input", function() {
            document.getElementById("price-range-value").textContent = `Max Price: R${this.value}`;
            updateProductDisplay();
        });
    }
}


/**
 * Populates a dropdown with options
 * @param {string} id - The ID of the select element
 * @param {Set} options - Set of options to add
 */
function populateDropdown(id, options) {
    const select = document.getElementById(id);
    if (!select) return;
    
    // Keep the default option
    const defaultOption = select.querySelector("option");
    select.innerHTML = "";
    if (defaultOption) select.appendChild(defaultOption);
    
    // Add options
    options.forEach(option => {
        const opt = document.createElement("option");
        opt.value = option;
        opt.textContent = option;
        select.appendChild(opt);
    });
}


/**
 * Populates a dropdown with options
 * @param {string} id - The ID of the select element
 * @param {Set} options - Set of options to add
 */
function populateDropdown(id, options) {
    const select = document.getElementById(id);
    if (!select) return;
    
    // Keep the default option
    const defaultOption = select.querySelector("option");
    select.innerHTML = "";
    if (defaultOption) select.appendChild(defaultOption);
    
    // Add options
    options.forEach(option => {
        const opt = document.createElement("option");
        opt.value = option;
        opt.textContent = option;
        select.appendChild(opt);
    });
}



/**
 * Updates product display based on selected filters
 */
function updateProductDisplay() {
    const productContainer = document.getElementById("product-container");
    if (!productContainer) return;
    
    // Get filter values
    const searchInput = document.querySelector(".search-bar")?.value.toLowerCase() || "";
    const selectedCategory = document.getElementById("category-select")?.value || "default";
    const selectedBrand = document.getElementById("brand-select")?.value || "default";
    const priceLimit = parseFloat(document.getElementById("price-range")?.value || 1000);
    const sortOption = document.getElementById("sort-select")?.value || "default";
    
    // Filter products
    let filteredProducts = products.filter(product => {
        // Match search text
        const matchesSearch = product.name.toLowerCase().includes(searchInput);
        
        // Match category
        const matchesCategory = selectedCategory === "default" || 
                               product.category_id === selectedCategory;
        
        // Match brand
        const matchesBrand = selectedBrand === "default" || 
                            product.brand === selectedBrand;
        
        // Return combined filter result
        return matchesSearch && matchesCategory && matchesBrand;
    });
    
    // Sort products
    if (sortOption === "price-low") {
        filteredProducts.sort((a, b) => a.price - b.price);
    } else if (sortOption === "price-high") {
        filteredProducts.sort((a, b) => b.price - a.price);
    }
    
    // Display filtered products
    if (filteredProducts.length === 0) {
        productContainer.innerHTML = "<p>No products found.</p>";
        return;
    }
    
    productContainer.innerHTML = "";
    
    // Create product cards
    filteredProducts.forEach(product => {
        // Get the first image or use placeholder
        const imageUrl = product.images ? product.images.split(',')[0] : "img/placeholder.jpg";
        
        // Create product card
        const productCard = document.createElement("div");
        productCard.className = "product-card";
        
        // Add content to card
        productCard.innerHTML = `
            <img src="${imageUrl}" alt="${product.name}" onerror="this.src='img/placeholder.jpg'">
            <h2>${product.name}</h2>
            <p>Brand: ${product.brand || "N/A"}</p>
            <p>${product.avg_rating ? `Rating: ${product.avg_rating}/5` : ""}</p>
            <button class="btn view-details" data-id="${product.product_id}">View Details</button>
        `;
        
        // Add card to container
        productContainer.appendChild(productCard);
    });
    
    // Add event listeners to view details buttons
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const productId = this.getAttribute('data-id');
            window.location.href = `product-details.php?id=${productId}`;
        });
    });
}



/**
 * Add event listeners for filtering and sorting
 */
function addEventListeners() {
    // Search bar
    const searchBar = document.querySelector(".search-bar");
    if (searchBar) {
        searchBar.addEventListener("input", updateProductDisplay);
    }
    
    // Category filter
    const categorySelect = document.getElementById("category-select");
    if (categorySelect) {
        categorySelect.addEventListener("change", updateProductDisplay);
    }
    
    // Brand filter
    const brandSelect = document.getElementById("brand-select");
    if (brandSelect) {
        brandSelect.addEventListener("change", updateProductDisplay);
    }
    
    // Sort select
    const sortSelect = document.getElementById("sort-select");
    if (sortSelect) {
        sortSelect.addEventListener("change", updateProductDisplay);
    }
}

// Initialize on page load
document.addEventListener("DOMContentLoaded", function() {
    fetchProducts();
    addEventListeners();
    
    // Handle success alert from URL parameters (e.g., after registration)
    const successAlert = document.getElementById('success-alert');
    if (successAlert) {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('registered') === 'true') {
            successAlert.textContent = "Registration Successful! Welcome to CompareIt.";
            successAlert.style.display = 'block';
            
            // Hide after 5 seconds
            setTimeout(() => {
                successAlert.style.display = 'none';
            }, 5000);
            
            // Clean URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    }
});



