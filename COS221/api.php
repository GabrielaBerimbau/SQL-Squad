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
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "category_id = ?";
            $params[] = $data['category_id'];
            $types .= "i";  // Integer for category_id
        }
        
        // Check for brand filter
        if (isset($data['brand']) && !empty($data['brand']) && $data['brand'] !== 'default') {
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "brand = ?";
            $params[] = $data['brand'];
            $types .= "s";
        }
        
        // Check for search term
        if (isset($data['search']) && !empty($data['search'])) {
            $searchTerm = "%" . $data['search'] . "%";
            $whereClause .= ($whereClause ? " AND " : " WHERE ") . "(name LIKE ? OR description LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $types .= "ss";
        }
        
        // Build the query
        $query = "SELECT * FROM PRODUCT" . $whereClause;
        
        // Add sorting
        if (isset($data['sort']) && $data['sort'] !== 'default') {
            switch ($data['sort']) {
                case 'rating-high':
                    $query .= " ORDER BY avg_rating DESC NULLS LAST";
                    break;
                case 'rating-low':
                    $query .= " ORDER BY avg_rating ASC NULLS LAST";
                    break;
                default:
                    $query .= " ORDER BY product_id ASC";
                    break;
            }
        } else {
            $query .= " ORDER BY product_id ASC";
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
        
        // Return the data
        $this->returnSuccess([
            'products' => $products,
            'categories' => $categories,
            'brands' => $brands
        ]);
        
        $stmt->close();
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