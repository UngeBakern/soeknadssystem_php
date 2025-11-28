<?php
/**
 * Filopplastingsklasse - Håndterer dokumentopplastinger
 */
class Upload {
    
    /** 
     * Last opp dokument for bruker 
     */
    public static function uploadDocument($file, $user_id, $document_type = 'other')
    {
        // Validerer filtypen
        $validation = self::validateDocument($file);
        if ($validation !== true) {
            return ['success' => false, 'message' => $validation]; 
        }

        // Sjekk antall opplastinger siste 5 minutter 
        $pdo = Database::connect(); 
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM documents 
            WHERE user_id = ?
            AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)
        ");
        $stmt->execute([$user_id]);
        $recent_uploads = $stmt->fetch()['count'];

        if ($recent_uploads >= 10) {
            return ['success' => false, 'message' => 'For mange opplastinger. Prøv igjen senere.'];
        }

        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total_count
            FROM documents
            WHERE user_id = ?
        ");
        $stmt->execute([$user_id]);
        $total_docs = $stmt->fetch()['total_count'];

        if ($total_docs >= 5) {
            return [
                'success' => false, 
                'message' => 'Du har nådd maksgrensen på 5 opplastede dokumenter. Vennligst slett et dokument før du laster opp et nytt.'
            ];
        }

        // Fjerner farlige tegn fra originalt filnavn   
        $original_name = basename($file['name']);// Fjerner path
        $original_name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $original_name); 

        // Kataloger
        $uploadDirFs = $_SERVER['DOCUMENT_ROOT'] . '/soeknadssystem/uploads/';
        $uploadDirWeb = 'uploads/';

        // Opprett mappe hvis den ikke finnes og en dobbelsjekk at ikke to forespørsler oppretter samme mappe. 
        if (!is_dir($uploadDirFs)) {
            @mkdir($uploadDirFs, 0755, true);
            if (!is_dir($uploadDirFs)) {
                return ['success' => false, 'message' => 'Kunne ikke opprette opplastingsmappe.'];
            }
        }

        // Generer unikt filnavn && Filnavn og Stier 
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = $user_id . '_' . time() . '_' . uniqid() . '.' . $ext;
        // Separate stier for database og filsystem
        $targetFsPath = $uploadDirFs . $new_filename; 
        $targetWebPath = $uploadDirWeb . $new_filename;
        
        // Flytt filen til opplastingsmappen
        if (!move_uploaded_file($file['tmp_name'], $targetFsPath)) {
           return ['success' => false, 'message' => 'Kunne ikke laste opp filen.']; 
        }            
            // Lagre i databasen 
            $document_id = self::saveDocumentToDatabase([
                'user_id' => $user_id,
                'filename' => $new_filename,
                'original_filename' => $original_name,
                'file_type' => $ext,
                'file_size' => $file['size'],
                'document_type' => $document_type, 
                'file_path' => $targetWebPath
            ]);
            
            if ($document_id) {
                return [
                    'success'       => true, 
                    'message'       => 'Dokumentet er lastet opp!',
                    'document_id'   => $document_id,
                    'file_path'     => $targetWebPath
                ];
            } else {
                // Slett filen hvis databaseinnsetting mislykkes
                @unlink($targetFsPath);
                return ['success' => false, 'message' => 'Kunne ikke lagre dokument i database.'];
            }
        }

        
    /** 
     * Valider dokument 
     */
    private static function validateDocument($file)
    {
        $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $max_file_size = 5242880; // 5MB

        // Sjekk om fil er lastet opp 
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
            return 'Ingen fil ble lastet opp eller det oppstod en feil under opplastingen.';
        }

        // Sjekk for dobbel-extension (virus.php.pdf)
        $filename_without_ext = pathinfo($file['name'], PATHINFO_FILENAME);
        if (preg_match('/\.(php|phtml|php3|php4|php5|exe|sh|bat|cmd)$/i', $filename_without_ext)) {
            return 'Ugyldig filnavn.';
        }

        // Sjekk filstørrelse 
        if ($file['size'] > $max_file_size) {
            return 'Filen er for stor. Maksimal tillatt størrelse er 5MB.';
        }

        // Sjekk filtype 
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            return 'Ugyldig filtype. Tillatte typer er: PDF, DOC, DOCX, JPG, JPEG, PNG.';
        }

        // Sikkerhet - MIME-type validering 
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowed_mime = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        ];
        if (!in_array($mime, $allowed_mime)) {
            return 'Ugyldig filtype.';
        }
        return true;
    }

    /**
     * Lagre dokumentinfo i database
     */
    private static function saveDocumentToDatabase($data) 
    {
        $pdo = Database::connect();
        
        try {
            $stmt = $pdo->prepare("
                INSERT INTO documents 
                (user_id, filename, original_filename, file_type, file_size, document_type, file_path, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            
            $stmt->execute([
                $data['user_id'],
                $data['filename'],
                $data['original_filename'],
                $data['file_type'],
                $data['file_size'],
                $data['document_type'],
                $data['file_path']
            ]);
            
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Hent alle dokumenter for en bruker
     */
    public static function getDocuments($user_id) 
    {
        $pdo = Database::connect();
        
        try {
            $stmt = $pdo->prepare("
                SELECT * FROM documents 
                WHERE user_id = ? 
                ORDER BY created_at DESC
            ");
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Slett dokument for en bruker
     */
    public static function deleteDocument($document_id, $user_id) 
    {
        $pdo = Database::connect();
        try {
            // Hent dokumentinfo
            $stmt = $pdo->prepare("SELECT * FROM documents WHERE id = ? AND user_id = ?");
            $stmt->execute([$document_id, $user_id]);
            $doc = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$doc) {
                return ['success' => false, 'message' => 'Dokument ikke funnet.'];
            }

            $allowed_ext = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            $file_ext = strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION));
            if (!in_array($file_ext, $allowed_ext)) {
                return ['success' => false, 'message' => 'Ugyldig filtype.'];
            }

            // validerer at file_path starter med 'uploads/'
            if (strpos($doc['file_path'], 'uploads/') !== 0) {
                return ['success' => false, 'message' => 'Ugyldig filsti.'];
            }

            $fsPath = $_SERVER['DOCUMENT_ROOT'] . '/soeknadssystem/' . $doc['file_path'];

            // Ekstra sikkerhet: sjekk at den endelige stien er unnenfor uploads/ 
            $uploadDir = realpath($_SERVER['DOCUMENT_ROOT'] . '/soeknadssystem/uploads/');
            $realFsPath = realpath($fsPath); 

            if ($realFsPath === false || strpos($realFsPath, $uploadDir ) !== 0) {
                return ['success' => false, 'message' => 'Ugyldig filsti.'];
            }

            // Slett fil fra server
            if (is_file($realFsPath)) {
                @unlink($realFsPath);
            }
            
            // Slett fra database
            $stmt = $pdo->prepare("DELETE FROM documents WHERE id = ? AND user_id = ?");
            $stmt->execute([$document_id, $user_id]);
            return ['success' => true, 'message' => 'Dokument slettet.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Kunne ikke slette dokument.'];
        }
    }

    /** 
     * Formater filstørrelse
     */
    public static function formatFileSize($bytes)
    { 
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /** 
     * Knytt et dokument til en søknad
     */
    public static function attachToApplication($document_id, $application_id, $user_id)
    {
        $pdo = Database::connect();

            // Sjekk at dokumentet tilhører brukeren
            $stmt = $pdo->prepare("
                SELECT id
                FROM documents 
                WHERE id = ? AND user_id = ?
            ");
            $stmt->execute([$document_id, $user_id]);
            
            if (!$stmt->fetch(PDO::FETCH_ASSOC)) {
                return false;
            }

            // Opprett kobling i application_documents
            $stmt = $pdo->prepare ("
                INSERT INTO application_documents (application_id, document_id)
                VALUES (?, ?)
            ");

            return $stmt->execute([$application_id, $document_id]);

    }

    /** 
     * Hent dokumenter knyttet til en søknad
     */
    public static function getDocumentsByApplication($application_id) 
    {
        $pdo = Database::connect();

        $stmt = $pdo->prepare("
            SELECT d.*
            FROM application_documents ad
            JOIN documents d ON ad.document_id = d.id 
            WHERE ad.application_id = ? 
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([$application_id]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


}

?>
