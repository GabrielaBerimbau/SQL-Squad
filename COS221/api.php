<?php
require_once 'config.php';

class API 
{
    private $conn;
    
    public function __construct() 
    {
        $this->conn = Config::getConnection();
    }
    
    public function processRequest() 
    {
        session_start();

        if (empty($_POST) && $_SERVER['CONTENT_TYPE'] === 'application/json') 
        {
            $json = file_get_contents('php://input');
            $data = json_decode($json, true);
        } 
        else 
        {
            $data = $_POST;
        }
        
        if (empty($data)) 
        {
            $this->returnError("Missing parameters", 400);
            return;
        }
        
        if (!isset($data['type'])) 
        {
            $this->returnError("Missing type", 400);
            return;
        }
        
        // In api.php, update the switch statement in processRequest method to include Wishlist
    switch ($data['type']) {
        case 'Register':
            $this->register($data);
            break;
        case 'Login':
            $this->login($data);
            break;
        case 'GetAllProducts':
            $this->getAllProducts($data);
            break;
        case 'GetWishlistItems':
            $this->getWishlistItems();
            break;
        case 'Wishlist':  // Add this case
            $this->handleWishlist($data);
            break;
        default:
            $this->returnError("Invalid type", 400);
            break;
    }
    }

    private function register($data) 
    {
        $reqFields = ['username', 'email', 'password', 'role'];
        
        foreach ($reqFields as $field) 
        {
            if (!isset($data[$field]) || trim($data[$field]) === '') 
            {
                $this->returnError("$field required", 400);
                return;
            }
        }
        
        // Check if email already exists
        $stmt = $this->conn->prepare("SELECT user_id FROM USERS WHERE email = ?");
        $stmt->bind_param("s", $data['email']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) 
        {
            $this->returnError("Email exists", 409);
            return;
        }
        
        // Check if username already exists
        $stmt = $this->conn->prepare("SELECT user_id FROM USERS WHERE username = ?");
        $stmt->bind_param("s", $data['username']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) 
        {
            $this->returnError("Username exists", 409);
            return;
        }
        
        // Hash the password 
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Set default value for is_active
        $isActive = 1;
        
        // Insert the new user
        $stmt = $this->conn->prepare("INSERT INTO USERS (username, email, password, role, is_active) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $data['username'], $data['email'], $hashedPassword, $data['role'], $isActive);
        
        if ($stmt->execute()) 
        {
            $userId = $this->conn->insert_id;
            $this->returnSuccess(['user_id' => $userId]);
        } 
        else 
        {
            $this->returnError("Registration failed", 500);
        }
    }

    private function getWishlistItems() 
{
    // Check if user is logged in via session
    if (!isset($_SESSION['user_id'])) {
        $this->returnError("You must be logged in to view your wishlist", 401);
        return;
    }
    
    $userId = $_SESSION['user_id'];
    
    // Query to get all wishlist items with product details and listing info
    $query = "
        SELECT 
            p.*,
            l.price,
            l.in_stock,
            l.listing_id,
            l.user_id AS seller_id,
            COALESCE(AVG(r.rating), 0) AS avg_rating,
            COUNT(r.rating) AS review_count
        FROM 
            WISHLIST w
        JOIN 
            PRODUCT p ON w.product_id = p.product_id
        LEFT JOIN 
            LISTING l ON p.product_id = l.product_id
        LEFT JOIN 
            REVIEW r ON p.product_id = r.product_id
        WHERE 
            w.user_id = ?
        GROUP BY 
            p.product_id, l.listing_id
        ORDER BY 
            p.name ASC
    ";
    
    $stmt = $this->conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all products in wishlist
    $products = [];
    while ($row = $result->fetch_assoc()) {
        // Process images field (assuming it's stored as JSON)
        if (!empty($row['images'])) {
            // Try to decode as JSON first
            $imageArray = json_decode($row['images'], true);
            if ($imageArray) {
                $row['primary_image'] = $imageArray[0] ?? 'img/default-product.jpg';
            } else {
                // If not JSON, try as comma-separated
                $imageArray = explode(',', $row['images']);
                $row['primary_image'] = trim($imageArray[0]) ?: 'img/default-product.jpg';
            }
        } else {
            $row['primary_image'] = 'img/default-product.jpg';
        }
        
        // Format the price and rating
        $row['price_formatted'] = 'R' . number_format($row['price'], 2);
        $row['avg_rating'] = round($row['avg_rating'], 1);
        
        $products[] = $row;
    }
    
    // Return the data
    $this->returnSuccess([
        'products' => $products,
        'count' => count($products)
    ]);
    
    $stmt->close();
}

    private function login($data) 
    {
        // Check required fields
        if (!isset($data['username']) || !isset($data['password'])) 
        {
            $this->returnError("Username and password required", 400);
            return;
        }

        $username = trim($data['username']);
        $password = $data['password'];

        // Validate username not empty
        if (empty($username)) {
            $this->returnError("Username cannot be empty", 400);
            return;
        }

        // Query to check if username exists and get user details
        $stmt = $this->conn->prepare("SELECT user_id, username, password, role, is_active FROM USERS WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) 
        {
            // Username not found
            $this->returnError("Invalid username or password", 401);
            return;
        }

        $user = $result->fetch_assoc();
        
        // Check if account is active
        if ($user['is_active'] != 1) 
        {
            $this->returnError("Account is inactive", 403);
            return;
        }

        // Verify password
        if (password_verify($password, $user['password'])) 
        {
            // Password is correct, create session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Return success with user info
            $userData = [
                'user_id' => $user['user_id'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
            
            $this->returnSuccess($userData);
        } 
        else 
        {
            // Password is incorrect
            $this->returnError("Invalid username or password", 401);
        }
        
        $stmt->close();
    }

    private function getAllProducts($data) 
    {
        // Initialize the WHERE clause and parameters array
        $whereClause = "";
        $params = [];
        $types = "";
        
        // Check for category_id filter
        if (isset($data['category_id']) && !empty($data['category_id']) && $data['category_id'] !== 'default') {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "p.category_id = ?";
            $params[] = $data['category_id'];
            $types .= "i";  // Integer for category_id
        }
        
        // Check for brand filter
        if (isset($data['brand']) && !empty($data['brand']) && $data['brand'] !== 'default') {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "p.brand = ?";
            $params[] = $data['brand'];
            $types .= "s";
        }
        
        // Check for price range filter if provided
        if (isset($data['maxPrice']) && is_numeric($data['maxPrice'])) {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "l.price <= ?";
            $params[] = $data['maxPrice'];
            $types .= "d";  // Decimal for price
        }
        
        // Check for search term
        if (isset($data['search']) && !empty($data['search'])) {
            $searchTerm = "%" . $data['search'] . "%";
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "(p.name LIKE ? OR p.description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        // Build the base query - join PRODUCT with LISTING and include average rating from REVIEW
        $query = "
            SELECT 
                p.*,
                l.price,
                l.in_stock,
                l.listing_id,
                l.user_id AS seller_id,
                COALESCE(AVG(r.rating), 0) AS avg_rating,
                COUNT(r.rating) AS review_count
            FROM 
                PRODUCT p
            LEFT JOIN 
                LISTING l ON p.product_id = l.product_id
            LEFT JOIN 
                REVIEW r ON p.product_id = r.product_id
            $whereClause
            GROUP BY 
                p.product_id, l.listing_id
        ";
        
        // Add sorting
        if (isset($data['sort']) && $data['sort'] !== 'default') {
            switch ($data['sort']) {
                case 'price-low':
                    $query .= " ORDER BY l.price ASC";
                    break;
                case 'price-high':
                    $query .= " ORDER BY l.price DESC";
                    break;
                case 'rating-high':
                    $query .= " ORDER BY avg_rating DESC";
                    break;
                case 'rating-low':
                    $query .= " ORDER BY avg_rating ASC";
                    break;
                case 'Wishlist':
                    $this->handleWishlist($data);
                    break;
                    $query .= " ORDER BY p.product_id ASC";
                    break;
            }
        } else {
            $query .= " ORDER BY p.product_id ASC";
        }
        
        // Prepare and execute the statement
        $stmt = $this->conn->prepare($query);
        
        // Bind parameters if any
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Fetch all products
        $products = [];
        while ($row = $result->fetch_assoc()) {
            // Process images field (assuming it's stored as JSON)
            if (!empty($row['images'])) {
                // Try to decode as JSON first
                $imageArray = json_decode($row['images'], true);
                if ($imageArray) {
                    $row['primary_image'] = $imageArray[0] ?? 'img/default-product.jpg';
                } else {
                    // If not JSON, try as comma-separated
                    $imageArray = explode(',', $row['images']);
                    $row['primary_image'] = trim($imageArray[0]) ?: 'img/default-product.jpg';
                }
            } else {
                $row['primary_image'] = 'img/default-product.jpg';
            }
            
            // Format the price and rating
            $row['price_formatted'] = 'R' . number_format($row['price'], 2);
            $row['avg_rating'] = round($row['avg_rating'], 1);
            
            $products[] = $row;
        }
        
        // Get categories for filter dropdown
        $categoryQuery = "SELECT category_id, name FROM CATEGORY ORDER BY name";
        $categoryResult = $this->conn->query($categoryQuery);
        $categories = [];
        if ($categoryResult) {
            while ($row = $categoryResult->fetch_assoc()) {
                $categories[] = $row;
            }
        }
        
        // Get unique brands for filter dropdown
        $brandQuery = "SELECT DISTINCT brand FROM PRODUCT WHERE brand IS NOT NULL AND brand != '' ORDER BY brand";
        $brandResult = $this->conn->query($brandQuery);
        $brands = [];
        if ($brandResult) {
            while ($row = $brandResult->fetch_assoc()) {
                $brands[] = $row['brand'];
            }
        }
        
        // Get price range for slider
        $priceQuery = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM LISTING WHERE price > 0";
        $priceResult = $this->conn->query($priceQuery);
        $priceRange = $priceResult->fetch_assoc();
        
        // Return the data
        $this->returnSuccess([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'price_range' => $priceRange
        ]);
        
        $stmt->close();
    }

    private function handleWishlist($data) 
    {
        // Check if user is logged in via session
        if (!isset($_SESSION['user_id'])) {
            $this->returnError("You must be logged in to manage your wishlist", 401);
            return;
        }
        
        // Check required parameters
        if (!isset($data['action']) || !isset($data['product_id'])) {
            $this->returnError("Missing required parameters", 400);
            return;
        }
        
        $userId = $_SESSION['user_id'];
        $productId = $data['product_id'];
        $action = $data['action'];
        
        // Verify the product exists
        $productCheck = $this->conn->prepare("SELECT product_id FROM PRODUCT WHERE product_id = ?");
        $productCheck->bind_param("i", $productId);
        $productCheck->execute();
        $productResult = $productCheck->get_result();
        
        if ($productResult->num_rows === 0) {
            $this->returnError("Product not found", 404);
            return;
        }
        
        switch ($action) {
            case 'add':
                // Check if item is already in wishlist
                $checkStmt = $this->conn->prepare("SELECT * FROM WISHLIST WHERE user_id = ? AND product_id = ?");
                $checkStmt->bind_param("ii", $userId, $productId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                if ($checkResult->num_rows > 0) {
                    $this->returnError("Product already in wishlist", 409);
                    return;
                }
                
                // Add to wishlist
                $addStmt = $this->conn->prepare("INSERT INTO WISHLIST (user_id, product_id) VALUES (?, ?)");
                $addStmt->bind_param("ii", $userId, $productId);
                
                if ($addStmt->execute()) {
                    $this->returnSuccess(["message" => "Product added to wishlist"]);
                } else {
                    $this->returnError("Failed to add product to wishlist", 500);
                }
                break;
                
            case 'remove':
                // Remove from wishlist
                $removeStmt = $this->conn->prepare("DELETE FROM WISHLIST WHERE user_id = ? AND product_id = ?");
                $removeStmt->bind_param("ii", $userId, $productId);
                
                if ($removeStmt->execute()) {
                    if ($this->conn->affected_rows > 0) {
                        $this->returnSuccess(["message" => "Product removed from wishlist"]);
                    } else {
                        $this->returnError("Product was not in wishlist", 404);
                    }
                } else {
                    $this->returnError("Failed to remove product from wishlist", 500);
                }
                break;
                
            case 'check':
                // Check if product is in wishlist
                $checkStmt = $this->conn->prepare("SELECT * FROM WISHLIST WHERE user_id = ? AND product_id = ?");
                $checkStmt->bind_param("ii", $userId, $productId);
                $checkStmt->execute();
                $checkResult = $checkStmt->get_result();
                
                $this->returnSuccess([
                    "in_wishlist" => $checkResult->num_rows > 0
                ]);
                break;
                
            default:
                $this->returnError("Invalid action", 400);
                break;
        }
    }

    private function returnError($msg, $statusCode = 400) 
    {
        http_response_code($statusCode);
        
        $resp = [
            'status' => 'error',
            'timestamp' => round(microtime(true) * 1000),
            'data' => $msg
        ];
        
        $this->sendResponse($resp);
    }
    
    private function returnSuccess($data) 
    {
        http_response_code(200);
        
        $resp = [
            'status' => 'success',
            'timestamp' => round(microtime(true) * 1000),
            'data' => $data
        ];
        
        $this->sendResponse($resp);
    }
    
    private function sendResponse($resp) 
    {
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    }
}

$api = new API();
$api->processRequest();
?>