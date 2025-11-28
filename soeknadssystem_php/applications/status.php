<?php
require_once '../includes/autoload.php';

/*
 * Mine søknader 
 */

// Må være innlogget som søker 
auth_check(['applicant']);

// Hent innlogget bruker
$user      = Auth::user();
$user_id   = $user['id'];
$user_role = $user['role'];
$user_name = $user['name'];

// Hent alle søknader for innlogget bruker 
$applications = Application::getByApplicant($user_id);

// Status 
$status_badges = [
    'Mottatt'       => 'info',
    'Vurderes'      => 'warning',
    'Godkjent'      => 'success',
    'Avslått'       => 'danger'
];

// Sidevariabler 
$page_title =  'Mine Søknader';
$body_class =  'bg-light';

require_once '../includes/header.php';


?>
<div class="container py-5">
    <?php render_flash_messages(); ?>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="mb-4 text-center">
                <h1 class="h2 mb-2">Mine søknader</h1>
                <p class="text-muted">Her ser du status på dine innsendte søknader</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <?php if (empty($applications)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-paper-plane fa-2x text-muted mb-3"></i>
                            <h6>Ingen søknader ennå</h6>
                            <p class="text-muted mb-3">Du har ikke søkt på noen stillinger ennå.</p>
                            <a href="../jobs/list.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-search me-1"></i>
                                Utforsk stillinger
                            </a>
                        </div>
                    <?php else: ?>

                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Stilling</th>
                                    <th>Arbeidsgiver</th>
                                    <th>Sendt</th>
                                    <th>Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($applications as $app): ?>
                                    <?php
                                        $badge_color = $status_badges[$app['status']] ?? 'secondary';
                                    ?>
                                    <tr>
                                        <td><?php echo Validator::sanitize($app['job_title']); ?></td>
                                        <td><?php echo Validator::sanitize($app['employer_name']); ?></td>
                                        <td><?php echo format_date($app['created_at']); ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $badge_color; ?>">
                                                <?php echo Validator::sanitize($app['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="../applications/view.php?id=<?php echo $app['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">
                                                Se søknad
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>
