<?php
class Config 
{
    private static $dbHost = "wheatley.cs.up.ac.za";
    private static $dbUser = "u24601358"; 
    private static $dbPass = "GSJRNMQRDA7GZFYATARBH7TMQNTQKJ7A"; 
    private static $dbName = "u24601358_SQL_Squad"; 
    
    private static $conn = null;
    
    public static function getConnection() 
    {
        if (self::$conn == null) 
        {
            try 
            {
                self::$conn = new mysqli(self::$dbHost, self::$dbUser, self::$dbPass, self::$dbName);
                
                if (self::$conn->connect_error) 
                {
                    throw new Exception("Connection failed: " . self::$conn->connect_error);
                }
            } 
            catch (Exception $e) 
            {
                die("Database connection error: " . $e->getMessage());
            }
        }
        return self::$conn;
    }
    
    public static function closeConnection() 
    {
        if (self::$conn != null) 
        {
            self::$conn->close();
            self::$conn = null;
        }
    }
    
    public static function generateToken($length = 32) 
    {
        return bin2hex(random_bytes($length / 2));
    }
    
    public static function generateSalt($length = 16) 
    {
        return bin2hex(random_bytes($length / 2));
    }
}
?>