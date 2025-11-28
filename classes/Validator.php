<?php
/**
 * Validator Class - Enkel valideringsklasse
 */
class Validator {

    /**
     * Valider e-post format
     */
    public static function validateEmail($email) 
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Valider påkrevd felt
     */
    public static function required($value) {
    return !empty(trim($value));
    }

    /**
     * Valider passord
     */
    public static function validatePassword($password) {

        if(strlen($password) < 8) {
            return false;
        }

        if(!preg_match('/[A-Z]/', $password)) {
            return false;
        }

        if(!preg_match('/[a-z]/', $password)) {
            return false;
        }

        if(!preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    /**
     * Sanitize input
     */
    public static function sanitize($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Clean input
     */
    public static function clean($data) {
        if (!is_string($data)) {
            return $data;
        }
        $data = trim($data);
        $data = str_replace(["\r\n", "\r"], "\n", $data); // Normaliser linjeskift
        return $data;
    }

    /**
     * Valider datoformat 
     */
    public static function validateDate($date, $format = 'Y-m-d') {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
    }

    /**
     * Valider norsk telefonnummer
     */
    public static function validatePhone($phone) {
        // Fjerner mellomrom og spesialtegn
        $cleaned = preg_replace('/[\s\-\(\)]/', '', $phone);

        // Sjekker 8 siffer eller +47 + 8. 
        return preg_match('/^(\+47)?[0-9]{8}$/', $cleaned);
    }

    /**
     * Valider adresse
     */
    public static function validateAddress($address) {
        if (empty(trim($address))) {
            return true;
        }
        // Sjekker lengde for å unngå databasekaos
        $length = mb_strlen($address);
        if ($length < 5 || $length > 255) {
        return false;
        }

        // Tillater bokstaver, tall, mellomrom, komma, punktum, bindestrek og skråstrek
        if (!preg_match('/^[a-zA-Z0-9æøåÆØÅ\s,.\-\/]+$/u', $address)) {
        return false;
        }

        return true; 
    }
}

?>
