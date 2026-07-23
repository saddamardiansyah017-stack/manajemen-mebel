<?php
class User {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function authenticate($username, $password) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $user = $this->db->single();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                return $user;
            }
        }
        return false;
    }

    public function getAllUsers() {
        $this->db->query('SELECT * FROM users');
        return $this->db->resultSet();
    }

    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function addUser($data) {
        $this->db->query('INSERT INTO users (username, password, role, created_at, updated_at) VALUES (:username, :password, :role, NOW(), NOW())');
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        $this->db->bind(':role', $data['role']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function updateUser($data) {
        if (!empty($data['password'])) {
            $this->db->query('UPDATE users SET username = :username, password = :password, role = :role, updated_at = NOW() WHERE id = :id');
            $this->db->bind(':password', password_hash($data['password'], PASSWORD_DEFAULT));
        } else {
            $this->db->query('UPDATE users SET username = :username, role = :role, updated_at = NOW() WHERE id = :id');
        }
        
        $this->db->bind(':username', $data['username']);
        $this->db->bind(':role', $data['role']);
        $this->db->bind(':id', $data['id']);

        $this->db->execute();
        return $this->db->rowCount();
    }

    public function deleteUser($id) {
        $this->db->query('DELETE FROM users WHERE id = :id');
        $this->db->bind(':id', $id);

        $this->db->execute();
        return $this->db->rowCount();
    }
}
