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
