<?php
// auth.php
include 'config.php';

//main authentication class
class Auth {
    private $conn;
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function register($username, $email, $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    public function login($email, $password) {
        $stmt = $this->conn->prepare("SELECT id, username, password, role, subscription_expiry FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['subscription_expiry'] = $user['subscription_expiry'];
                return true;
            }
        }
        return false;
    }
    
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    public function isPremium() {
        if (!$this->isLoggedIn()) return false;
        
        $expiry = $_SESSION['subscription_expiry'];
        if ($expiry && strtotime($expiry) > time()) {
            return true;
        }
        return false;
    }
    
    public function logout() {
        session_destroy();
        header("Location: index.php");
        exit();
    }
}
//artist authentication class
class ArtistAuth extends Auth {
    public function registerArtist($user_id, $artist_name, $bio = '') {
        $stmt = $this->conn->prepare("INSERT INTO artists (user_id, artist_name, bio) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $artist_name, $bio);
        
        if ($stmt->execute()) {
            // Update user role to artist
            $update_stmt = $this->conn->prepare("UPDATE users SET role = 'artist' WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            
            $_SESSION['role'] = 'artist';
            $_SESSION['artist_id'] = $this->conn->insert_id;
            return true;
        }
        return false;
    }
    
    public function isArtist() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'artist';
    }
    
    public function getArtistId() {
        return $_SESSION['artist_id'] ?? null;
    }
}

//object instantiation
$auth = new Auth($conn);
$artistAuth = new ArtistAuth($conn);
?>