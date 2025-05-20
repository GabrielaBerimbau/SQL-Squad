<?php
    session_start();

    require_once 'config.php';

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $username = isset($_POST['username'])? $_POST['username']: '';
        $password = isset($_POST['password'])? $_POST['password']: '';

        if(empty($username) || empty($password)){
            echo json_encode([
                'success'=> false, 'message'=> 'Please enter username and password'
            ]);

            exit;
        }

        try{
            $stmt = $conn->prepare("SELECT user_id, password, role, is_active FROM USERS WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if($result->num_rows ===1){
                $user = $result->fetch_assoc();

                if($user['is_active'] != 1){
                    echo json_encode([
                        'success'=> false, 'message'=> 'Your account has been deactivated.'
                    ]);

                    exit;
                }

                if(password_verify($password, $user['password'])){
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $user['role'];

                    $redirect = '';

                    switch($user['role']){
                        case  'admin':
                            $redirect = 'adminView.php'; // going to php file
                            break;

                        case 'retailer':
                            $redirect = 'retailerView.php';
                            break;

                        case 'customer':
                            $redirect = 'products.php';
                            break;

                        default:
                            $redirect = 'login.php'; //***check
                            break;
                    }

                    echo json_encode([
                        'success'=> true, 'redirect'=> $redirect
                    ]);
                }

                else{
                    echo json_encode([
                        'success'=> false, 'message'=> 'Invalid username or password'
                    ]);
                }
            }

            else{
                echo json_encode([
                    'success'=> false, 'message'=> 'Invalid username or password'
                ]);
            }
        }

        catch(Exception $e){
            echo json_encode([
                'success'=> false, 'message'=> 'A system error occured. Please try again later.'
            ]);

            error_log('Login error: ' . $e->getMessage());
        }
    }

    else{
        echo json_encode([
            'success'=> false, 'message'=> 'Invalid request method'
        ]);
    }
?>