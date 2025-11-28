<?php
require_once '../includes/autoload.php';

/*
 * Vis søknad (Søker / Arbeidsgiver eller Admin)
 */

// Sjekk innlogging
auth_check(['applicant', 'employer', 'admin']);

//Hent innlogget bruker 
$user      = Auth::user();
$user_id   = $user['id'];
$user_role = $user['role'];

// Hent søknads-ID
$application_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$application_id) {
    redirect('../dashboard/applicant.php', 'Ugyldig søknads-ID.', 'danger');
}

// Hent søknad med full info
$application = Application::findById($application_id);

if (!$application) {
    redirect('../dashboard/applicant.php', 'Søknaden finnes ikke.', 'danger');
}

/*
 * Tilgangssjekk - søknad
 * admin: alltid tilgang
 * søker: kun egne søknader
 * employer: kun søknader til egne jobber 
 */

if (
    !(
        ($user_role === 'admin') ||
        ($user_role === 'applicant' && $application['applicant_id'] == $user_id) ||
        ($user_role === 'employer'  && $application['employer_id']  == $user_id)
    )
) {
    redirect('../dashboard/applicant.php', 'Du har ikke tilgang til denne søknaden.', 'danger');
}

// Etter at vi har verifisert tilgang brukes disse i UI
$is_applicant = ($user_role === 'applicant' && $application['applicant_id'] == $user_id);
$is_employer  = ($user_role === 'employer' && $application['employer_id'] == $user_id);

// Hent vedlagte dokumenter etter sikkerhetssjekk
$attached_documents = Upload::getDocumentsByApplication($application['id']);

$status_badges = [
    'Mottatt'   => 'info',
    'Vurderes'  => 'warning',
    'Tilbud'    => 'success',
    'Avslått'   => 'danger'
];

$badge_color = $status_badges[$application['status']] ?? 'secondary';

$page_title = 'Min søknad - ' . Validator::sanitize($application['job_title'] ?? '');
$body_class = 'bg-light';

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back button -->
            <div class="mb-3">
                <a href="<?php echo $is_employer ? '../dashboard/employer.php' : '../dashboard/applicant.php'; ?>" 
                   class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Tilbake
                </a>
            </div>

            <?php render_flash_messages(); ?>

            <!-- Application Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            Søknadsdetaljer
                        </h4>
                        <span class="badge bg-<?php echo $badge_color; ?> fs-6">
                            <?php echo Validator::sanitize($application['status']); ?>
                        </span>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Job Info -->
                    <div class="mb-4 pb-4 border-bottom">
                        <h5 class="mb-3">
                            <i class="fas fa-briefcase text-primary me-2"></i>
                            Stilling
                        </h5>
                        <h6>
                            <a href="../jobs/view.php?id=<?php echo $application['job_id']; ?>" 
                               class="text-decoration-none">
                                <?php echo Validator::sanitize($application['job_title']); ?>
                            </a>
                        </h6>
                        <p class="text-muted mb-1">
                            <i class="fas fa-building me-2"></i>
                            <?php echo Validator::sanitize($application['company']); ?>
                        </p>
                        <?php if (!empty($application['location'])): ?>
                        <p class="text-muted mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            <?php echo Validator::sanitize($application['location']); ?>
                        </p>
                        <?php endif; ?>
                        <p class="text-muted small mb-0">
                            <i class="fas fa-calendar me-2"></i>
                            Søkt: <?php echo date('d.m.Y \k\l. H:i', strtotime($application['created_at'])); ?>
                        </p>
                    </div>

                    <!-- Applicant Info (for employer) -->
                    <?php if ($is_employer): ?>
                    <div class="mb-4 pb-4 border-bottom">
                        <h5 class="mb-3">
                            <i class="fas fa-user text-primary me-2"></i>
                            Søker
                        </h5>
                        <p class="mb-1">
                            <strong>Navn:</strong> <?php echo Validator::sanitize($application['applicant_name']); ?>
                        </p>
                        <p class="mb-1">
                            <strong>E-post:</strong> 
                            <a href="mailto:<?php echo Validator::sanitize($application['applicant_email']); ?>">
                                <?php echo Validator::sanitize($application['applicant_email']); ?>
                            </a>
                        </p>
                    </div>
                    <?php endif; ?>

                    <!-- Cover Letter -->
                    <div class="mb-4 pb-4 border-bottom">
                        <h5 class="mb-3">
                            <i class="fas fa-envelope-open-text text-primary me-2"></i>
                            Søknadsbrev
                        </h5>
                        <div class="bg-light p-3 rounded">
                            <p class="mb-0" style="white-space: pre-wrap;"><?php echo Validator::sanitize($application['cover_letter']); ?></p>
                        </div>
                    </div>

                    <!-- CV / Vedlegg -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-file-pdf text-primary me-2"></i>
                            CV / Vedlegg
                        </h5>
                        <?php if (!empty($attached_documents)): ?>
                            <ul class="list-group">
                                <?php foreach ($attached_documents as $doc): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?php echo Validator::sanitize($doc['original_filename']); ?></strong><br>
                                            <small class="text-muted">
                                                Type: <?php echo Validator::sanitize($doc['document_type']); ?>,
                                                Størrelse: <?php echo Upload::formatFileSize($doc['file_size']); ?>
                                            </small>
                                        </div>
                                        <a href="<?php echo APP_URL . '/' . Validator::sanitize($doc['file_path']); ?>"
                                        target="_blank"
                                        class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download me-1"></i>
                                            Åpne
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                                <?php elseif (!empty($application['cv_path'])): ?>
                            <!-- Fallback for gamle søknader som bare bruker cv_path -->
                            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                                <div>
                                    <i class="fas fa-file-pdf text-danger me-2 fs-4"></i>
                                    <span><?php echo Validator::sanitize(basename($application['cv_path'])); ?></span>
                                </div>
                                <a href="<?php echo APP_URL . '/' . Validator::sanitize($application['cv_path']); ?>" 
                                target="_blank"
                                class="btn btn-sm btn-primary">
                                    <i class="fas fa-download me-1"></i>
                                    Last ned
                                </a>
                                </div>
                                <?php else: ?>
                                    <p class="text-muted">Ingen vedlegg.</p>
                                    <?php endif; ?>
                                </div>

                    <!-- Actions for employer -->
                    <?php if ($is_employer && $application['status'] !== 'Avslått' && $application['status'] !== 'Tilbud'): ?>
                    <div class="mt-4 pt-4 border-top">
                        <h5 class="mb-3">Handlinger</h5>
                        <div class="d-flex gap-2">
                            <form method="POST" action="update_status.php" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                <input type="hidden" name="status" value="Tilbud">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>
                                    Gi tilbud
                                </button>
                            </form>
                            <form method="POST" action="update_status.php" class="d-inline">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="application_id" value="<?php echo $application['id']; ?>">
                                <input type="hidden" name="status" value="Avslått">
                                <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Er du sikker på at du vil avslå denne søknaden?');">
                                    <i class="fas fa-times me-1"></i>
                                    Avslå
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>