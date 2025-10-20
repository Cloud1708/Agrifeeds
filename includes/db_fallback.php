<?php
// Fallback database connection for troubleshooting
class database_fallback {
    
    function opencon(): PDO {
        // Try multiple connection methods
        $host = 'mysql.hostinger.com';
        $dbname = 'u689218423_agrifeeds';
        $username = 'u689218423_agrifeeds';
        $password = '@Afrifeeds12345';
        
        $connectionMethods = [
            // Method 1: Full DSN with all options
            [
                'dsn' => "mysql:host=$host;dbname=$dbname;charset=utf8mb4;port=3306",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4",
                    PDO::ATTR_TIMEOUT => 30,
                    PDO::MYSQL_ATTR_CONNECT_TIMEOUT => 30
                ]
            ],
            // Method 2: Without port
            [
                'dsn' => "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            ],
            // Method 3: Minimal options
            [
                'dsn' => "mysql:host=$host;dbname=$dbname",
                'options' => [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                ]
            ],
            // Method 4: No options
            [
                'dsn' => "mysql:host=$host;dbname=$dbname",
                'options' => []
            ]
        ];
        
        foreach ($connectionMethods as $index => $method) {
            try {
                error_log("Trying connection method " . ($index + 1));
                $pdo = new PDO($method['dsn'], $username, $password, $method['options']);
                
                // Test the connection
                $pdo->query("SELECT 1");
                error_log("Connection method " . ($index + 1) . " successful");
                return $pdo;
                
            } catch (PDOException $e) {
                error_log("Connection method " . ($index + 1) . " failed: " . $e->getMessage());
                continue;
            }
        }
        
        throw new PDOException("All connection methods failed");
    }
    
    function checkUsernameExists($username) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT COUNT(*) FROM USER_ACCOUNTS WHERE User_Name = ?");
            $stmt->execute([$username]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("checkUsernameExists error: " . $e->getMessage());
            return false;
        }
    }
    
    function loginUser($username, $password) {
        try {
            $con = $this->opencon();
            $stmt = $con->prepare("SELECT * FROM USER_ACCOUNTS WHERE User_Name = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user && password_verify($password, $user['User_Password'])) {
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("loginUser error: " . $e->getMessage());
            return false;
        }
    }
}
?>
