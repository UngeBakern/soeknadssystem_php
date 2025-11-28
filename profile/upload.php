<?php
require_once '../includes/autoload.php';

/**
 * Upload side som lar brukere laste opp dokumenter som CV, vitnemål, attester osv.
 * Dokumentene lagres sikkert på serveren og knyttes til brukerens profil.
 * Brukeren må være innlogget for å få tilgang til denne siden.
 * Dokumentene valideres for filtype og størrelse før opplasting.
 */

// Sjekk om bruker er innlogget
auth_check(['applicant']);

$user_id = Auth::id();

// Håndter filopplasting
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload'])) {

    csrf_check();
    
    if (isset($_FILES['document']) && $_FILES['document']['error'] === UPLOAD_ERR_OK) {
   
        $result = Upload::uploadDocument(
            $_FILES['document'],
            $user_id,
            $_POST['document_type'] ?? 'other'
        );
    
        if ($result['success']) {
            redirect('upload.php', $result['message'], 'success');
        } else {
            redirect('upload.php', $result['message'], 'danger');
        }

    } else {
        redirect('upload.php', 'Ingen fil valgt eller feil ved opplasting.', 'danger');
    }
}

// Håndter sletting 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {

    csrf_check();
    
    $document_id = filter_input(INPUT_POST, 'document_id', FILTER_VALIDATE_INT);

    if ($document_id) {
        $result = Upload::deleteDocument($document_id, $user_id);

        if ($result['success']) {
            redirect('upload.php', $result['message'], 'success');
        } else {
            redirect('upload.php', $result['message'], 'danger');
        }
    }
    
    redirect('upload.php', 'Ugyldig dokument-ID.', 'danger');
}

// Hent brukerens dokumenter
$documents = Upload::getDocuments($user_id);

// Sett sidevariabler
$page_title = 'Last opp dokumenter';
$body_class = 'bg-light';

include_once '../includes/header.php';
?>

<div class="container py-5">
    <?php render_flash_messages(); ?>
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="fas fa-upload fa-3x text-primary mb-3"></i>
                        <h1 class="h3 mb-3 fw-normal">Last opp dokumenter</h1>
                        <p class="text-muted">
                            Her kan du laste opp relevante dokumenter, som CV, vitnemål eller attester.
                        </p>
                    </div>

                    <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label for="document_type" class="form-label">Dokumenttype</label>
                            <select class="form-select" id="document_type" name="document_type" required>
                                <option value="">Velg type...</option>
                                <option value="cv">CV / Søknad</option>
                                <option value="certificate">Attest / Sertifikat</option>
                                <option value="transcript">Vitnemål / Karakterutskrift</option>
                                <option value="other">Annet</option>
                            </select>
                            <div class="invalid-feedback">
                                Vennligst velg dokumenttype.
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="document" class="form-label">Velg dokument</label>
                            <input type="file" 
                                   class="form-control" 
                                   id="document" 
                                   name="document" 
                                   accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                   required>
                            <div class="invalid-feedback">
                                Vennligst velg et dokument.
                            </div>
                            <small class="text-muted">
                                Tillatte formater: PDF, DOC, DOCX, JPG, PNG (maks 5MB)
                            </small>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" name="upload" class="btn btn-primary">
                                <i class="fas fa-upload me-2"></i>
                                Last opp
                            </button>
                            <a href="../dashboard/applicant.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Tilbake til dashboard
                            </a>
                        </div>
                    </form>

                    <hr class="my-4">
                    <h6 class="mb-3">Mine dokumenter</h6>
                    
                    <?php if (empty($documents)): ?>
                        <div class="list-group">
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-folder-open fa-2x mb-2"></i>
                                <p class="mb-0">Ingen dokumenter lastet opp ennå</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($documents as $doc): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-file-<?php echo $doc['file_type'] === 'pdf' ? 'pdf' : 'alt'; ?> fa-2x text-primary me-3"></i>
                                                <div>
                                                <h6 class="mb-0">
                                                <a href="../<?php echo Validator::sanitize($doc['file_path']); ?>" 
                                                target="_blank" 
                                                class="text-decoration-none text-dark fw-semibold">
                                                <?php echo Validator::sanitize($doc['original_filename']); ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <?php 
                                                $types = [
                                                    'cv' => 'CV/Søknad',
                                                    'certificate' => 'Attest',
                                                    'transcript' => 'Vitnemål',
                                                    'other' => 'Annet'
                                                ];
                                                echo ($types[$doc['document_type']] ?? 'Dokument') . ' • '; 
                                                echo Upload::formatFileSize($doc['file_size']) . ' • ';
                                                echo date('d.m.Y H:i', strtotime($doc['created_at']));
                                                ?>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="btn-group" role="group">
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Er du sikker på at du vil slette dette dokumentet?');">
                                              <?php echo csrf_field(); ?>
                                            <input type="hidden" name="document_id" value="<?php echo $doc['id']; ?>">
                                            <button type="submit" 
                                                    name="delete" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Slett dokument">Slett 
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>