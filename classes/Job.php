<?php
/**
 * Job Class - Enkel stillingsklasse
 */
class Job {
    /**
     * Hent alle stillinger i databasen basert på om de er aktive 
     * 
     */
    public static function getAll() 
    {   
        $pdo = Database::connect();

        try {
            $stmt = $pdo->query("
            SELECT jobs.*, users.name as employer_name 
            FROM jobs 
            LEFT JOIN users ON jobs.employer_id = users.id
            WHERE COALESCE(jobs.status, 'active') = 'active'
            ORDER BY jobs.created_at DESC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Finn stilling basert på ID 
     */
    public static function findById($id) 
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT 
                jobs.*, 
                users.name as employer_name,
                users.email as employer_email
            FROM jobs 
            LEFT JOIN users ON jobs.employer_id = users.id 
            WHERE jobs.id = ?
            LIMIT 1
            ");
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Oprett ny jobb
     */
    public static function create($data)
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            INSERT INTO jobs 
            (employer_id, title, company, description, requirements, location, salary, job_type, subject, education_level, deadline, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ");

            $result = $stmt->execute([
                $data['employer_id'],
                $data['title'],
                $data['company']            ?? '',
                $data['description']        ?? '',
                $data['requirements']       ?? '',
                $data['location']           ?? '',
                $data['salary']             ?? '',
                $data['job_type']           ?? '',
                $data['subject']            ?? '',
                $data['education_level']    ?? '',
                $data['deadline']           ?: null,
                $data['status']             ?? 'active'
            ]);

            if ($result) {
                return $pdo->lastInsertId();
            }
            return false; 
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Oppdater jobb men sender ikke inn company fordi den ikke skal endres.
     */
    public static function update($id, $data)
    {
        $pdo = Database::connect();

        try {

            $stmt = $pdo->prepare("
            UPDATE jobs 
            SET title = ?,  
                location = ?,
                job_type = ?, 
                description = ?, 
                requirements = ?, 
                salary = ?, 
                deadline = ?, 
                status = ?,
                subject = ?,
                education_level =?, 
                updated_at = NOW()
            WHERE id = ?
            ");

            error_log("SQL prepared");

            $result = $stmt->execute([
                $data['title'],
                $data['location'],
                $data['job_type'],
                $data['description'],
                $data['requirements'],
                $data['salary'],
                $data['deadline'],
                $data['status'] ?? 'active',
                $data['subject'] ?? null,
                $data['education_level'] ?? null,
                $id
            ]);

            return $result;

        } catch (PDOException $e) {
            return false; 
        }
    }

    /**
     * Slett jobb (soft delete)
     */
    public static function delete($id)
    {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            UPDATE jobs
            SET status = 'deleted', updated_at = NOW()
            WHERE id = ?
            ");
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0; // Returner true hvis en rad ble oppdatert
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Hent stillinger for en arbeidsgiver
     */
    public static function getByEmployerId($employerId) {
        $pdo = Database::connect();

        try {
            $stmt = $pdo->prepare("
            SELECT jobs.*, users.name as employer_name
            FROM jobs
            LEFT JOIN users ON jobs.employer_id = users.id
            WHERE jobs.employer_id = ?
            AND COALESCE(jobs.status, 'active') = 'active'
            ORDER BY jobs.created_at DESC
            ");
            $stmt->execute([$employerId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Hent inaktive stillinger for en arbeidsgiver 
     */
    public static function getInactiveByEmployerId($employer_id) {

        $pdo = Database::connect(); 

        try {
            $stmt = $pdo->prepare("
            SELECT jobs.*, users.name as employer_name
            FROM jobs 
            LEFT JOIN users ON jobs.employer_id = users.id
            WHERE jobs.employer_id = ?
            AND jobs.status = 'inactive'
            ORDER BY jobs.created_at DESC
            ");
            $stmt->execute([$employer_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Feil ved henting av inaktive stillinger: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Hent tilgjengelige stillinger for en søker (ikke søkt på enda) 
     */

    public static function getAvailableForApplicant($applicant_id) {
        
    $pdo = Database::connect();

    try {
        $stmt = $pdo->prepare("
            SELECT 
                j.*,
                u.name AS employer_name
            FROM jobs j
            LEFT JOIN users u ON j.employer_id = u.id
            WHERE j.status = 'active'
              AND (
                  j.deadline IS NULL
                  OR j.deadline = ''
                  OR j.deadline >= CURDATE()
              )
              AND NOT EXISTS (
                  SELECT 1
                  FROM applications a
                  WHERE a.job_id = j.id
                    AND a.applicant_id = ?
              )
            ORDER BY j.created_at DESC
        ");

        $stmt->execute([$applicant_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Feil ved henting av tilgjengelige stillinger for søker: " . $e->getMessage());
        return [];
    }
}
}


