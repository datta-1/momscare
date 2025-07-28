<?php
require_once 'config/database.php';

class User {
    private $conn;
    private $table = 'users';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user
    public function create($full_name, $email, $phone, $password, $age = null, $weeks_pregnant = null, $pre_existing_conditions = null) {
        $query = "INSERT INTO " . $this->table . " 
                  (full_name, email, phone, password_hash, age, weeks_pregnant, pre_existing_conditions) 
                  VALUES (:full_name, :email, :phone, :password_hash, :age, :weeks_pregnant, :pre_existing_conditions)";
        
        $stmt = $this->conn->prepare($query);
        
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt->bindParam(':full_name', $full_name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':phone', $phone);
        $stmt->bindParam(':password_hash', $password_hash);
        $stmt->bindParam(':age', $age);
        $stmt->bindParam(':weeks_pregnant', $weeks_pregnant);
        $stmt->bindParam(':pre_existing_conditions', $pre_existing_conditions);
        
        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // Login user
    public function login($email, $password) {
        $query = "SELECT id, full_name, email, password_hash FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($password, $row['password_hash'])) {
                return $row;
            }
        }
        return false;
    }

    // Get user by ID
    public function getById($id) {
        $query = "SELECT id, full_name, email, phone, age, weeks_pregnant, pre_existing_conditions, created_at 
                  FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update user profile
    public function update($id, $data) {
        $fields = [];
        $params = [':id' => $id];
        
        foreach($data as $key => $value) {
            if($key !== 'id') {
                $fields[] = "$key = :$key";
                $params[":$key"] = $value;
            }
        }
        
        if(empty($fields)) return false;
        
        $query = "UPDATE " . $this->table . " SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        
        return $stmt->execute($params);
    }

    // Check if email exists
    public function emailExists($email) {
        $query = "SELECT id FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
}

class Session {
    private $conn;
    private $table = 'user_sessions';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create session
    public function create($user_id) {
        $session_token = bin2hex(random_bytes(32));
        $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $query = "INSERT INTO " . $this->table . " (user_id, session_token, expires_at) 
                  VALUES (:user_id, :session_token, :expires_at)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':session_token', $session_token);
        $stmt->bindParam(':expires_at', $expires_at);
        
        if($stmt->execute()) {
            return $session_token;
        }
        return false;
    }

    // Validate session
    public function validate($session_token) {
        $query = "SELECT s.user_id, u.full_name, u.email 
                  FROM " . $this->table . " s 
                  JOIN users u ON s.user_id = u.id 
                  WHERE s.session_token = :session_token AND s.expires_at > NOW()";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_token', $session_token);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Delete session
    public function destroy($session_token) {
        $query = "DELETE FROM " . $this->table . " WHERE session_token = :session_token";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':session_token', $session_token);
        
        return $stmt->execute();
    }
}

class ChatMessage {
    private $conn;
    private $table = 'chat_messages';

    public function __construct($db) {
        $this->conn = $db;
    }

    // Save message
    public function save($user_id, $message, $sender) {
        $query = "INSERT INTO " . $this->table . " (user_id, message, sender) 
                  VALUES (:user_id, :message, :sender)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':message', $message);
        $stmt->bindParam(':sender', $sender);
        
        return $stmt->execute();
    }

    // Get user chat history
    public function getHistory($user_id, $limit = 50) {
        $query = "SELECT message, sender, timestamp 
                  FROM " . $this->table . " 
                  WHERE user_id = :user_id 
                  ORDER BY timestamp DESC 
                  LIMIT :limit";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
}
?>
