<?php
require_once '../includes/autoload.php';

/**
 * Lar søkere søke med søknadsbrev.
 * Og eventuel vedlegg som allerede er lastet opp på profilsiden
 */

// Sjekk innlogging
auth_check(['applicant']);

// innlogget bruker
$user       = Auth::user();
$user_id    = $user['id'];

// Hent stilling basert på ID i URL og kun tillat INT 
$job_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$job_id) {
    redirect('list.php', 'Ugyldig stilling', 'danger');
}

$job = Job::findById($job_id);

if (!$job) {
    redirect('list.php', 'Stillingen finnes ikke', 'danger');
}

if ($job['status'] !== 'active') {
    redirect('view.php?id=' . $job_id, 'Denne stillingen er ikke lenger aktiv', 'danger');
}

// Sjekk om søknadsfrist har passert
if (!empty($job['deadline']) && strtotime($job['deadline']) < time()) {
    redirect('view.php?id=' . $job_id, 'Søknadsfristen for denne stillingen har passert', 'danger');
}

// Sjekk om bruker allerede har søkt
if (Application::hasApplied($job_id, $user_id)) {
    redirect('view.php?id=' . $job_id, 'Du har allerede søkt på denne stillingen', 'danger');
}

$cover_letter = '';
$user_documents = Upload::getDocuments($user_id); 

// Håndter søknadsinnsending
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $cover_letter = Validator::clean($_POST['cover_letter'] ?? '');

    // Valgte eksisterende dokumenter (checkboxer)
    $document_ids = $_POST['document_ids'] ?? [];
    if (!is_array($document_ids)) {
        $document_ids = [];
    }

    // Validering – kun søknadsbrev er påkrevd
    if (!Validator::required($cover_letter)) {

        show_error('Søknadsbrev er påkrevd.');

    } elseif (strlen($cover_letter) < 100) {

        show_error('Søknadsbrevet må være minst 100 tegn.');

    } else {

        // 2) Opprett selve søknaden (alltid, uansett vedlegg)
        $application_data = [
            'job_id'        => $job_id,
            'applicant_id'  => $user_id,
            'cover_letter'  => $cover_letter,
            'cv_path'       => null // Bakoverkompatibilitet
        ];

        $application_id = Application::create($application_data);

        if ($application_id) {

            // Knytt alle valgte eksisterende dokumenter
            if (!empty($document_ids)) {
                foreach ($document_ids as $doc_id) {
                    $doc_id = filter_var($doc_id, FILTER_VALIDATE_INT);
                    if ($doc_id) {
                        Upload::attachToApplication($doc_id, $application_id, $user_id);
                    }
                }
            }

            // TODO: Send e-post til arbeidsgiver
            redirect('../dashboard/applicant.php', 'Din søknad er sendt!', 'success');
        } else {
            show_error('Kunne ikke opprette søknad. Prøv igjen.');
        }
    }
}

$page_title = 'Søk på stilling';
$body_class = 'bg-light';
include_once '../includes/header.php';
?>

<div class="container my-5">
    <?php render_flash_messages(); ?>
    <!-- Stillingsinfo øverst -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="mb-3"><?php echo Validator::sanitize($job['title']); ?></h3>

                    <p class="text-muted mb-4">
                        <i class="fas fa-building me-2"></i>
                        <?php echo Validator::sanitize($job['company']); ?>
                    </p>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-map-marker-alt text-primary me-2 mt-1"></i>
                                <div>
                                    <small class="text-muted d-block">Lokasjon</small>
                                    <strong><?php echo Validator::sanitize($job['location']); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-briefcase text-primary me-2 mt-1"></i>
                                <div>
                                    <small class="text-muted d-block">Type</small>
                                    <strong><?php echo Validator::sanitize($job['job_type']); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="d-flex align-items-start">
                                <i class="fas fa-calendar text-primary me-2 mt-1"></i>
                                <div>
                                    <small class="text-muted d-block">Søknadsfrist</small>
                                    <strong><?php echo format_date($job['deadline']); ?></strong>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($job['salary'])): ?>
                            <div class="col-md-3">
                                <div class="d-flex align-items-start">
                                    <i class="fas fa-money-bill-wave text-primary me-2 mt-1"></i>
                                    <div>
                                        <small class="text-muted d-block">Lønn</small>
                                        <strong><?php echo Validator::sanitize($job['salary']); ?></strong>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Stillingsbeskrivelse -->
                    <?php if (!empty($job['description'])): ?>
                        <hr class="my-4">
                        <h5 class="mb-3">Om stillingen</h5>
                        <div class="job-description">
                            <?php echo nl2br(Validator::sanitize($job['description'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Krav -->
                    <?php if (!empty($job['requirements'])): ?>
                        <hr class="my-4">
                        <h5 class="mb-3">Krav og kvalifikasjoner</h5>
                        <div class="job-requirements">
                            <?php echo nl2br(Validator::sanitize($job['requirements'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <a href="view.php?id=<?php echo $job_id; ?>" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>
                        Se full stillingsannonse
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Søknadsskjema under -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0 text-primary">
                        <i class="fas fa-paper-plane me-2"></i>
                        Send søknad
                    </h5>
                </div>
                
                <div class="card-body p-4">

                    <form method="POST" action="" novalidate>
                        <?php echo csrf_field(); ?>

                        <!-- Søknadsbrev -->
                        <div class="mb-4">
                            <label for="cover_letter" class="form-label fw-bold">
                                Søknadsbrev <span class="text-danger">*</span>
                            </label>
                            <textarea 
                                class="form-control" 
                                id="cover_letter" 
                                name="cover_letter" 
                                rows="12"
                                minlength="100"
                                placeholder="Skriv ditt søknadsbrev her... (minimum 100 tegn)"
                                required><?php echo Validator::sanitize($cover_letter); ?></textarea>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Fortell hvorfor du er den rette kandidaten for stillingen.
                            </div>
                            <div class="invalid-feedback">
                                Søknadsbrevet må være minst 100 tegn.
                            </div>
                            <div id="charCount" class="form-text text-end mt-1">
                                <span id="currentCount">0</span> / 100 tegn
                            </div>
                        </div>

                        <?php if (!empty($user_documents)): ?>
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                Velg eksisterende dokumenter (valgfritt)
                            </label>
                            <div class="list-group">
                                <?php foreach ($user_documents as $doc): ?>
                                    <label class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <input 
                                                type="checkbox" 
                                                name="document_ids[]" 
                                                value="<?php echo Validator::sanitize($doc['id']); ?>" 
                                                class="form-check-input me-2"
                                            >
                                            <span><?php echo Validator::sanitize($doc['original_filename']); ?></span><br>
                                            <small class="text-muted">
                                                <?php echo Validator::sanitize($doc['document_type']); ?> ·
                                                <?php echo Upload::formatFileSize($doc['file_size']); ?> ·
                                                <?php echo date('d.m.Y H:i', strtotime($doc['created_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="<?php echo APP_URL . '/' . ltrim(Validator::sanitize($doc['file_path']), '/'); ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-eye me-1"></i>
                                            Vis
                                        </a>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">
                                Du kan gjenbruke dokumenter du allerede har lastet opp på profilsiden (f.eks. CV, attester).
                            </div>
                        </div>
                    <?php endif; ?>
                        <!-- Info-boks -->
                        <div class="alert alert-info border-info bg-light">
                            <h6 class="alert-heading">
                                <i class="fas fa-info-circle me-2"></i>
                                Viktig informasjon
                            </h6>
                            <ul class="mb-0 ps-4">
                                <li>Søknaden kan ikke endres etter innsending</li>
                                <li>Du vil motta e-post når søknaden er mottatt</li>
                                <li>Du kan følge statusen i ditt dashboard</li>
                            </ul>
                        </div>
                        
                        <!-- Knapper -->
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="view.php?id=<?php echo $job_id; ?>" class="btn btn-light border">
                                <i class="fas fa-times me-1"></i>
                                Avbryt
                            </a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane me-1"></i>
                                Send søknad
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>