<?php
require_once '../includes/autoload.php';

/* 
 * Visning av jobber
 */

$user      = null;
$user_id   = null;
$user_role = null;

// Hvis innlogget hent bruker

if (Auth::isLoggedIn()) {
    $user      = Auth::user();
    $user_id   = $user['id'];
    $user_role = $user['role'];
}

    // Hent jobb-ID fra URL (GET)
    $job_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if (!$job_id) {
    redirect('list.php', 'Ugyldig jobb ID.', 'danger');
    }

    // Hent stillingen fra databasen
    $job = Job::findById($job_id); 

    if(!$job) {
    redirect('list.php', 'Jobben finnes ikke.', 'danger');
    }


// Sett sidevariabler 
$page_title = $job['title'] ?? 'Stillingsdetaljer';
$body_class = 'bg-light';

require_once '../includes/header.php';

?>
<div class="container py-5">
    <?php render_flash_messages(); ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Back button -->
            <div class="mb-3">
                <a href="list.php" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>
                    Tilbake til stillinger
                </a>
            </div>

            <!-- Jobb detaljer kort-->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-5">
                    <!-- Header -->
                    <div class="d-flex align-items-center mb-4">
                        <div class="me-3">
                            <img src="../assets/images/uialogo.jpeg" 
                                 alt="Logo" 
                                 style="width: 70px; height: 70px; object-fit: contain;"
                                 onerror="this.src='https://via.placeholder.com/70'">
                            </div>
                          <div>
                            <h1 class="h3 mb-1 fw-bold"><?php echo Validator::sanitize($job['title']); ?></h1>
                            <div class="text-muted mb-1">
                                <i class="fas fa-building me-1"></i>
                                <strong><?php echo Validator::sanitize($job['employer_name']); ?></strong>
                                <?php if (!empty($job['location'])): ?>
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo Validator::sanitize($job['location']); ?>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($job['deadline'])): ?>
                            <div class="small text-muted">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Søknadsfrist: <?php echo format_date($job['deadline']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <hr>

                    <!-- Stillingsbeskrivelse -->
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-info-circle text-primary me-2"></i>
                            Stillingsbeskrivelse
                        </h5>
                        <p class="text-muted"><?php echo nl2br(Validator::sanitize($job['description'])); ?></p>
                    </div>

                    <!-- Krav og kvalifikasjoner -->
                    <?php if (!empty($job['requirements'])): ?>
                    <div class="mb-4">
                        <h5 class="mb-3">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Krav og kvalifikasjoner
                        </h5>
                        <p class="text-muted"><?php echo nl2br(Validator::sanitize($job['requirements'])); ?></p>
                    </div>
                    <?php endif; ?>

                    <!-- Jobbdetaljer -->
                    <div class="row mb-4">
                        <?php if (!empty($job['job_type'])): ?>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Stillingstype</small>
                                <strong><?php echo Validator::sanitize($job['job_type']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($job['salary'])): ?>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Lønn</small>
                                <strong><?php echo Validator::sanitize($job['salary']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($job['subject'])): ?>
                        <div class="col-md-4 mb-3">
                            <div class="p-3 bg-light rounded">
                                <small class="text-muted d-block mb-1">Fag/område</small>
                                <strong><?php echo Validator::sanitize($job['subject']); ?></strong>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($job['education_level'])): ?>
                    <div class="mb-4">
                        <div class="p-3 bg-light rounded">
                            <small class="text-muted d-block mb-1">Utdanningsnivå</small>
                            <strong><?php echo Validator::sanitize($job['education_level']); ?></strong>
                        </div>
                    </div>
                    <?php endif; ?>

                    <hr>

                    <!-- Knapper -->
                    <div class="d-flex gap-2 flex-wrap">
                        <?php if ($user && $user_role === 'applicant'): ?>
                                <a href="apply.php?id=<?php echo $job['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>
                                    Søk på stillingen
                                </a>
                        <?php elseif (!$user): ?>
                            <a href="../auth/login.php" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Logg inn for å søke
                            </a>
                        <?php endif; ?>

                        <?php if (
                            $user && (
                                ($user_role === 'employer' && $job['employer_id'] == $user_id) 
                                || $user_role === 'admin'
                            )
                        ): ?>
                            <a href="edit.php?id=<?php echo $job['id']; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>
                                Rediger
                            </a>

                            <form method="POST" action="delete.php" style="display: inline;"
                                  onsubmit="return confirm('Er du sikker på at du vil slette denne stillingen?');">
                                  <?php echo csrf_field(); ?> 
                                <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                <button type="submit" class="btn btn-outline-danger">
                                    <i class="fas fa-trash me-2"></i>
                                    Slett
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Om arbeidsgiver -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h6 class="mb-3">
                        <i class="fas fa-building text-primary me-2"></i>
                        Om arbeidsgiver
                    </h6>
                    <p class="text-muted mb-2">
                        <?php echo Validator::sanitize($job['employer_name']); ?>
                    </p>
                    <p class="text-muted small mb-0">
                        <i class="fas fa-clock me-1"></i>
                        Publisert: <?php echo format_date($job['created_at']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>