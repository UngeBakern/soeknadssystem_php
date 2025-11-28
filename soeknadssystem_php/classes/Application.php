<?php
/**
 * Application Class - Enkel søknadsklasse
 */
class Application {

    /**
     * Opprett ny søknad 
     */
    public static function create($data) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            INSERT INTO applications (job_id, applicant_id, cover_letter, cv_path, status, created_at)
            VALUES (?, ?, ?, ?, 'Mottatt', NOW())
            ");
            $stmt->execute([
                $data['job_id'],
                $data['applicant_id'],
                $data['cover_letter'],
                $data['cv_path']
            ]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Database error in create: " . $e->getMessage());
            return false;
        }
    }
    /**
     * Slett søknad 
     */
    public static function delete($id)
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("DELETE FROM applications WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Database error in delete: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Sjekk om bruker allerede har søkt på stilling
     */
    public static function hasApplied($job_id, $user_id) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM applications
            WHERE job_id = :job_id AND applicant_id = :user_id
            ");
            $stmt->execute([
                ':job_id' => $job_id,
                ':user_id'=> $user_id
            ]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Database error in hasApplied: " . $e->getMessage());
            return false; 
        }
    }

    /**
     * Hent alle søknader
     */
    public static function getAll() 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->query("
            SELECT 
                applications.*,
                jobs.title as job_title,
                jobs.location, 
                employer.name as employer_name,
                applicant.name as applicant_name
            FROM applications
            LEFT JOIN jobs ON applications.job_id = jobs.id
            LEFT JOIN users as employer ON jobs.employer_id = employer.id
            LEFT JOIN users as applicant ON applications.user_id = applicant.id
            ORDER BY applications.created_at DESC");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Hent søknader for en spesifikk bruker (søker)
     */
    public static function getByApplicant($applicant_id) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.job_id,
                a.applicant_id,
                a.cover_letter,
                a.cv_path,
                a.status,
                a.created_at,

                j.title    AS job_title,
                j.location AS job_location,
                j.company  AS company,
                j.employer_id, 
                u.name AS employer_name

                FROM applications a
                LEFT JOIN jobs j ON a.job_id = j.id
                LEFT JOIN users u ON j.employer_id = u.id
                WHERE a.applicant_id = ?
                ORDER BY a.created_at DESC
            ");
            $stmt->execute([$applicant_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Status 
     */
    public static function getByApplicantAndStatus($user_id, $status) {

        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
                SELECT a.*, 
                    j.title     AS job_title, 
                    j.location  AS location, 
                    u.name      AS employer_name
                FROM applications a 
                JOIN jobs j  ON a.job_id = j.id
                JOIN users u ON j.employer_id = u.id
                WHERE a.applicant_id = ?
                AND a.status = ?
                ORDER BY a.created_at DESC
            ");

            $stmt->execute([$user_id, $status]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tall antall søknader for en spesifikk jobb 
     */
    public static function countByJob($jobId) {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT COUNT(*) AS count
            FROM applications
            WHERE job_id = :job_id
            ");
            $stmt->execute(['job_id' => $jobId]);
            return (int)$stmt->fetchColumn();

        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Hent antall søknader for flere jobber 
     */
    public static function countByJobs($job_ids) {
        if (empty($job_ids)) {
            return [];
        }
        
        $pdo = Database::connect();

        try {
            $placeholders = str_repeat('?,', count($job_ids) - 1) . '?';
            $stmt = $pdo->prepare("
            SELECT job_id, COUNT(*) AS count
            FROM applications
            WHERE job_id IN ($placeholders)
            GROUP BY job_id
            ");
            $stmt->execute($job_ids);

            $counts = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $counts[$row['job_id']] = (int)$row['count'];
            }

            return $counts;

        } catch (Exception $e) {
            return [];
        }
    }


    /*
     * Hent søknad etter ID med full informasjon
     */
    public static function findById($id) {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.job_id,
                a.applicant_id,
                a.cover_letter,
                a.cv_path,
                a.status,
                a.created_at,
                j.title AS job_title,
                j.company,
                j.location, 
                j.employer_id,
                u_employer.name as employer_name,
                u_applicant.name as applicant_name,
                u_applicant.email as applicant_email
            FROM applications a
            LEFT JOIN jobs j ON a.job_id = j.id
            LEFT JOIN users u_employer ON j.employer_id = u_employer.id
            LEFT JOIN users u_applicant ON a.applicant_id = u_applicant.id
            WHERE a.id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Hent alle søknader for en spesifikk jobb (for arbeidsgiver)
     */
    public static function getByJobId($job_id) {
        $pdo = Database::connect();
    
    try {
        $stmt = $pdo->prepare("
            SELECT 
                a.id,
                a.job_id,
                a.applicant_id,
                a.cover_letter,
                a.cv_path,
                a.status,
                a.created_at,
                u.name AS applicant_name,
                u.email AS applicant_email,
                u.phone AS applicant_phone
            FROM applications a
            LEFT JOIN users u ON a.applicant_id = u.id
            WHERE a.job_id = ?
            ORDER BY a.created_at DESC
        ");
        $stmt->execute([$job_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Database error in getByJobId: " . $e->getMessage());
        return [];
    }
}

}

?>
