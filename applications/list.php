<?php
require_once '../includes/autoload.php';

// Sjekker innlogging og tilganger
auth_check(['employer', 'admin']);

// Innlogget bruker
$user      = Auth::user();
$user_id   = $user['id'];
$user_role = $user['role'];

// Hent job_id
$job_id = filter_input(INPUT_GET, 'job_id', FILTER_VALIDATE_INT);

if (!$job_id) {
    redirect('../dashboard/employer.php', 'Ugyldig stillings-ID.', 'danger');
}

// Hent jobb og sjekk tilgang
$job = Job::findById($job_id);

if (!$job) {
    redirect('../dashboard/employer.php', 'Stillingen finnes ikke.', 'danger');
}

// Sjekk at arbeidsgiver eier jobben
if (
    !(
        ($user_role === 'admin') || 
        ($user_role === 'employer' && $job['employer_id'] == $user_id)
    )
) {
    redirect('../dashboard/employer.php', 'Ingen tilgang.', 'danger');
}

// Hent alle søknader for denne jobben
$applications = Application::getByJobId($job_id);

$status_badges = [
    'Mottatt'   => 'info',
    'Vurderes'  => 'warning',
    'Tilbud'    => 'success',
    'Avslått'   => 'danger'
];

$page_title = 'Søknader - ' . Validator::sanitize($job['title']);
$body_class = 'bg-light';

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <!-- Back button -->
            <div class="mb-3">
                <a href="../dashboard/employer.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Tilbake til dashboard
                </a>
            </div>

            <?php render_flash_messages(); ?>

            <!-- Job Info Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h4 class="mb-2">
                                <i class="fas fa-briefcase text-primary me-2"></i>
                                <?php echo Validator::sanitize($job['title']); ?>
                            </h4>
                            <p class="text-muted mb-1">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo Validator::sanitize($job['location']); ?>
                            </p>
                            <p class="text-muted mb-0">
                                <i class="fas fa-users me-2"></i>
                                <?php echo count($applications); ?> søknad<?php echo count($applications) !== 1 ? 'er' : ''; ?>
                            </p>
                        </div>
                        <div>
                            <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-eye me-1"></i>
                                Se stilling
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Applications List -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0">
                        <i class="fas fa-paper-plane text-primary me-2"></i>
                        Mottatte søknader
                    </h5>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($applications)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <h6>Ingen søknader ennå</h6>
                            <p class="text-muted">Denne stillingen har ikke mottatt søknader.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Søker</th>
                                        <th>E-post</th>
                                        <th>Telefon</th>
                                        <th>Søkt dato</th>
                                        <th>Status</th>
                                        <th>Handlinger</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($applications as $app): ?>
                                        <?php
                                        $badge_color = $status_badges[$app['status']] ?? 'secondary';
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo Validator::sanitize($app['applicant_name']); ?></strong>
                                            </td>
                                            <td>
                                                <a href="mailto:<?php echo Validator::sanitize($app['applicant_email']); ?>">
                                                    <?php echo Validator::sanitize($app['applicant_email']); ?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo Validator::sanitize($app['applicant_phone'] ?? 'Ikke oppgitt'); ?>
                                            </td>
                                            <td><?php echo format_date($app['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $badge_color; ?>">
                                                    <?php echo Validator::sanitize($app['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view.php?id=<?php echo $app['id']; ?>" 
                                                   class="btn btn-sm btn-primary">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Se søknad
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>