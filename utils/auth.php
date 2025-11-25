<?php
// auth.php
include 'config.php';

class Auth {
    protected $conn; // Changed from private to protected so child classes can access it
    
    public function __construct($connection) {
        $this->conn = $connection;
    }
    
    public function register($username, $email, $password) {
        // Check if email already exists
        if ($this->emailExists($email)) {
            throw new Exception("Email already exists. Please use a different email or login.");
        }
        
        // Check if username already exists
        if ($this->usernameExists($username)) {
            throw new Exception("Username already exists. Please choose a different username.");
        }
        
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);
        
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
    
    protected function emailExists($email) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    protected function usernameExists($username) {
        $stmt = $this->conn->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
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
        header("Location: ../index.php");
        exit();
    }
}

class ArtistAuth extends Auth {
    // No need for constructor since we inherit from parent
    
    public function registerArtist($user_id, $artist_name, $bio = '') {
        // Check if artist name already exists
        if ($this->artistNameExists($artist_name)) {
            throw new Exception("Artist name already exists. Please choose a different artist name.");
        }
        
        $stmt = $this->conn->prepare("INSERT INTO artists (user_id, artist_name, bio) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $artist_name, $bio);
        
        if ($stmt->execute()) {
            // Update user role to artist
            $update_stmt = $this->conn->prepare("UPDATE users SET role = 'artist' WHERE id = ?");
            $update_stmt->bind_param("i", $user_id);
            $update_stmt->execute();
            
            $_SESSION['role'] = 'artist';
            $_SESSION['artist_id'] = $this->conn->insert_id;
            $_SESSION['artist_name'] = $artist_name;
            return true;
        }
        return false;
    }
    
    private function artistNameExists($artist_name) {
        $stmt = $this->conn->prepare("SELECT id FROM artists WHERE artist_name = ?");
        $stmt->bind_param("s", $artist_name);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    }
    
    public function isArtist() {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'artist';
    }
    
    public function getArtistId() {
        return $_SESSION['artist_id'] ?? null;
    }
    
    public function getArtistName() {
        return $_SESSION['artist_name'] ?? null;
    }
}

// Initialize both auth objects with the database connection
$auth = new Auth($conn);
$artistAuth = new ArtistAuth($conn); // Pass the connection to ArtistAuth as well
?>