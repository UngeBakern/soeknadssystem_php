<?php
require_once '../includes/autoload.php';


/*
 * Liste over ledige stillinger
 */

// Default for ikke innloggede brukere
$user      = null; 
$user_id   = null;
$user_role = null;

// Hvis innlogget: hent bruker
if (Auth::isLoggedIn()) {
    $user       = Auth::user();
    $user_id    = $user['id'];
    $user_role  = $user['role'];
}

//Hvis innlogget søker: Filtrer bort jobber vedkommende allerede har søkt på.
if ($user && $user_role === 'applicant') {

    $jobs = Job::getAvailableForApplicant($user_id);

} else {

    $jobs = Job::getAll(['status' => 'active']);
}

// Sett sidevariabler
$page_title = 'Ledige Stillinger';
$body_class = 'bg-light';

include_once '../includes/header.php';
?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h1 class="h2 mb-3">Ledige stillinger</h1>
                    <p class="text-muted">
                        <?php echo count($jobs); ?> ledige hjelpelærerstillinger tilgjengelig
                    </p>
                </div>
            </div>
        </div>
    </div>
    <!-- Job Listings -->
    <div class="container pb-5">
         <?php render_flash_messages(); ?>
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="row">
                    <?php if (!empty($jobs)): ?>
                        <?php foreach ($jobs as $job): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 border-0 shadow-sm position-relative">
                                <!-- Logo/Organization Section -->
                                <div class="card-header bg-white border-0 p-4 text-center">
                                    <div class="organization-logo mb-3">
                                        <div class="d-inline-flex align-items-center justify-content-center organization-logo-size">
                                            <img src="../assets/images/uialogo.jpeg" 
                                                 alt="<?php echo Validator::sanitize($job['employer_name'] ?? 'Logo'); ?>" 
                                                 class="img-fluid">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Job Details Section -->
                                <div class="card-body p-4">
                                    <!-- Date and Location -->
                                    <div class="text-muted small mb-2">
                                        <?php echo date('d. M Y', strtotime($job['created_at'])); ?> | 
                                        <?php echo isset($job['location']) ? Validator::sanitize($job['location']) : 'Ikke oppgitt'; ?>
                                    </div>
                                    
                                    <!-- Job Title -->
                                    <h5 class="card-title mb-3 fw-bold">
                                        <?php echo Validator::sanitize($job['title'] ?? 'Ingen tittel'); ?>
                                    </h5>
                                    
                                    <!-- Company Name -->
                                    <div class="job-info mb-3">
                                        <div class="text-muted small mb-1">
                                            <strong><?php echo Validator::sanitize($job['employer_name'] ?? 'Ukjent arbeidsgiver'); ?></strong>
                                        </div>
                                        <div class="text-muted small">
                                            1 stilling
                                        </div>
                                    </div>
                                    
                                    <!-- Action Buttons -->
                                    <div class="d-grid gap-2">
                                        <a href="view.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary btn-sm">
                                            Se detaljer
                                        </a>
                                        <?php if (Auth::isLoggedIn()): ?>
                                            <a href="apply.php?id=<?php echo $job['id']; ?>" class="btn btn-primary btn-sm">
                                                Søk på stillingen
                                            </a>
                                        <?php else: ?>
                                            <a href="../auth/login.php" class="btn btn-primary btn-sm">
                                                Logg inn for å søke
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <!-- Favorite Heart Icon -->
                                <div class="position-absolute top-0 end-0 p-3">
                                    <button class="btn btn-link p-0 text-muted favorite-btn" title="Legg til i favoritter">
                                        <i class="far fa-heart"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="text-center py-5">
                                <i class="fas fa-briefcase fa-3x text-muted mb-3"></i>
                                <h4>Ingen stillinger funnet</h4>
                                <p class="text-muted mb-4">Det er for øyeblikket ingen ledige stillinger.</p>
                                <a href="../index.php" class="btn btn-outline-primary">
                                    Tilbake til forsiden
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../includes/footer.php'; ?>