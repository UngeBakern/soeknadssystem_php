<?php
/**
 * User Class - Enkel brukerklasse
 */
class User {
    /**
     * Finn bruker basert på e-post
     */
    public static function findByEmail($email) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Finn bruker basert på ID
     */
    public static function findById($id) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Opprett ny bruker
     */
    public static function create($data) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, role, password_hash, birthdate, address, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");

            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['role'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['birthdate'],
                $data['address']
            ]);

            // Returner den nye brukerens ID
            return $pdo->lastInsertId();

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lagre tilbakestillingstoken for glemt passord 
     */
    public static function saveResetToken($user_id, $token, $expires) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
                UPDATE users 
                SET reset_token = ?,
                    reset_token_expires = ? 
                WHERE id = ?
            "); 
            return $stmt->execute([$token, $expires, $user_id]);
        } catch (PDOException $e) {
            error_log("User::saveResetToken() " . $e->getMessage());
            return false;
        }
    }

    /**
     * Oppdater brukerinfo
     */
    public static function update($id, $data)
    {
        $pdo = Database::connect();

        try {

            $stmt = $pdo->prepare("
            UPDATE users
            SET 
                name  = :name, 
                email = :email, 
                phone = :phone, 
                birthdate = :birthdate, 
                address = :address
            WHERE id = :id
            ");

            $result = $stmt->execute([
                'name'      => $data['name']        ?? '',
                'email'     => $data['email']       ?? '',
                'phone'     => $data['phone']       ?? '',
                'birthdate' => $data['birthdate']   ?? '',
                'address'   => $data['address']     ?? '',
                'id'        => $id
            ]);

            return $result;

        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Slett bruker
     */
    public static function delete($id)
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return false;
        }
    }

}
?>