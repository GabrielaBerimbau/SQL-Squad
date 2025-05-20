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
    }
?>