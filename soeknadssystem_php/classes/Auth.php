<?php
/**
 * Auth Class - Enkel autentiseringsklasse
 */
class Auth {
    /**
     * Sjekk om bruker er innlogget
     */
    public static function isLoggedIn() 
    {
        return !empty($_SESSION['user_id']);
    }
    
    /**
     * Hent brukerrolle
     */
    public static function getRole() {
        return $_SESSION['role'] ?? null;
    }


    /**
     * Sjekk brukerrolle
     */
    public static function hasRole($role) 
    {
        return self::getRole() === $role;
    }

    /**
     * Logg inn bruker og setter session verdier
     */
    public static function login($user) {
        session_regenerate_id(true);

        $_SESSION['user_id']        = $user['id'];
        $_SESSION['user_name']      = $user['name'];
        $_SESSION['user_email']     = $user['email'];
        $_SESSION['role']           = $user['role'];
        $_SESSION['logged_in_at']   = time();
    }

    /**
     * Logg ut bruker
     */
    public static function logout() {

        // Tøm session data
        $_SESSION = [];

        // Regenerer session-ID
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        
    }

    /**
     * Autentiser bruker med epost og passord
     */
    public static function attempt($email, $password) {

        $user = User::findByEmail($email);

        if (!$user) {
            return false; 
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false; 
        }

        return $user;
    }

    /**
     * Verifiser passord mot hash
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * Hash passord
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Hent bruker ID
     */
    public static function id() {
        return $_SESSION['user_id'] ?? null; 
    }

    /**
     * Hent brukerdata fra session
     */
    public static function user() {
        if (!self::isLoggedIn()) {
            return null; 
        }

        return [
            'id'     => $_SESSION    ['user_id'], 
            'name'   => $_SESSION    ['user_name']   ?? '',
            'email'  => $_SESSION   ['user_email']  ?? '',
            'role'   => $_SESSION    ['role']        ?? '',
        ];
    }



}
?>