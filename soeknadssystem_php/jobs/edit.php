<?php
require_once '../includes/autoload.php';

/*
 * Rediger en eksisterende stilling
 */

// Må være arbeidsgiver eller admin
auth_check(['employer', 'admin']);

// Innlogget bruker 
$user      = Auth::user();
$user_id   = $user['id'];
$user_role = $user['role'];

// Hent jobb-ID fra URL
$job_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$job_id) {
    redirect('list.php', 'Ingen stilling valgt.', 'danger');
}

// Hent stillingen fra database
$job = Job::findById($job_id);

if (!$job) {
    redirect('list.php', 'Stillingen finnes ikke.', 'danger');
}

// Sjekk at bruker eier stillingen (eller er admin)
if  (
    !(
        ($user_role === 'admin') || 
        ($user_role === 'employer' && $job['employer_id'] == $user_id)
    )
) { 
    redirect('view.php?id=' . $job_id, 'Du har ikke tilgang til å redigere denne stillingen.', 'danger');
}

// HÅNDTER POST-REQUEST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    csrf_check();

    $title          = Validator::clean($_POST['title']            ?? '');
    $location       = Validator::clean($_POST['location']         ?? '');
    $job_type       = Validator::clean($_POST['job_type']         ?? '');
    $description    = Validator::clean($_POST['description']      ?? '');
    $requirements   = Validator::clean($_POST['requirements']     ?? '');
    $salary         = Validator::clean($_POST['salary']           ?? '');
    $deadline       = Validator::clean($_POST['deadline']         ?? '');
    $status         = Validator::clean($_POST['status']           ?? 'active');
    $subject        = Validator::clean($_POST['subject']          ?? '');
    $education_level= Validator::clean($_POST['education_level']  ?? '');

    // Whitelist for selectfelter 

    $allowed_status = ['active', 'inactive'];
    if (!in_array($status, $allowed_status, true)) {
        $status = 'active';
    }

    $allowed_job_types = ['Heltid', 'Deltid', 'Ekstrahjelp', 'Vikariat'];
    if (!in_array($job_type, $allowed_job_types, true)) {
        $job_type = '';
    }

    $allowed_subjects = ['Matematikk', 'Norsk', 'Engelsk', 'Naturfag', 'Samfunnsfag', 'Historie', 'Annet'];
    if (!in_array($subject, $allowed_subjects, true)) {
        $subject = '';
    }

    $allowed_education_levels = ['Barneskole', 'Ungdomsskole', 'Videregående', 'Høyere utdanning', 'Alle nivåer'];
    if (!in_array($education_level, $allowed_education_levels, true)) {
        $education_level = '';
    }

    // Validering
    if (!Validator::required($title)        || 
        !Validator::required($location)     || 
        !Validator::required($job_type)     || 
        !Validator::required($description)  || 
        !Validator::required($requirements) || 
        !Validator::required($deadline)) {

        show_error('Vennligst fyll ut alle obligatoriske felt.');

    } elseif (strlen($description) < 50) {

        show_error('Stillingsbeskrivelsen må være minst 50 tegn.');

    } elseif (!Validator::validateDate($deadline)) {

        show_error('Ugyldig datoformat for søknadsfrist.');

    } elseif (strtotime($deadline) < time()) {

        show_error('Søknadsfristen må være en fremtidig dato.');

    } else {

        // Ingen feil, oppdater stilling
        $updated_job = [
            'title'          => $title,
            'location'       => $location,
            'job_type'       => $job_type,
            'description'    => $description,
            'requirements'   => $requirements,
            'salary'         => $salary,
            'deadline'       => $deadline,
            'status'         => $status,
            'subject'        => $subject,
            'education_level'=> $education_level
        ];

        if (Job::update($job_id, $updated_job)) {
            redirect('view.php?id=' . $job_id, 'Stillingen er oppdatert!', 'success');
        } else {
            show_error('Det oppstod en feil under oppdateringen. Vennligst prøv igjen.');
        }
    }

        // Behold verdier i skjema ved feil 
        $job = array_merge($job, [
            'title'          => $title,
            'location'       => $location,
            'job_type'       => $job_type,
            'description'    => $description,
            'requirements'   => $requirements,
            'salary'         => $salary,
            'deadline'       => $deadline,
            'status'         => $status,
            'subject'        => $subject,
            'education_level'=> $education_level
        ]);

}

$page_title = 'Rediger stilling';
$body_class = 'dashboard-page';

require_once '../includes/header.php';
?>

<div class="container py-5">
    <?php render_flash_messages(); ?>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="mb-4">
                <h1 class="h2 mb-2">Rediger stilling</h1>
                <p class="text-muted">Oppdater informasjonen for stillingen</p>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="" novalidate>
                        <?php echo csrf_field(); ?>
                        <!-- Title -->
                        <div class="mb-3">
                            <label for="title" class="form-label">Stillingstittel *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?php echo Validator::sanitize($job['title'] ?? ''); ?>" required>
                        </div>

                        <!-- Company -->
                            <div class="mb-3">
                            <label class="form-label">Bedrift/Organisasjon</label>
                            <input type="text" 
                                   class="form-control bg-light" 
                                   value="<?php echo Validator::sanitize($job['company'] ?? ''); ?>" 
                                   readonly 
                                   tabindex="-1">
                            <small class="text-muted">
                                <i class="fas fa-lock me-1"></i>
                                Kan ikke endres
                            </small>
                        </div>

                        <!-- Location & Type -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location" class="form-label">Lokasjon *</label>
                                <input type="text" class="form-control" id="location" name="location" 
                                       value="<?php echo Validator::sanitize($job['location'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="job_type" class="form-label">Stillingstype *</label>
                                <select class="form-select" id="job_type" name="job_type" required>
                                    <option value="">Velg stillingstype</option>
                                    <option value="Heltid"      <?= (($job['job_type'] ?? '') === 'Heltid')      ? 'selected' : '' ?>>Heltid</option>
                                    <option value="Deltid"      <?= (($job['job_type'] ?? '') === 'Deltid')      ? 'selected' : '' ?>>Deltid</option>
                                    <option value="Ekstrahjelp" <?= (($job['job_type'] ?? '') === 'Ekstrahjelp') ? 'selected' : '' ?>>Ekstrahjelp</option>
                                    <option value="Vikariat"    <?= (($job['job_type'] ?? '') === 'Vikariat')    ? 'selected' : '' ?>>Vikariat</option>
                                </select>
                            </div>
                        </div>

                        <!-- Salary & Deadline -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="salary" class="form-label">Lønn</label>
                                <input type="text" class="form-control" id="salary" name="salary" 
                                       value="<?php echo Validator::sanitize($job['salary'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="deadline" class="form-label">Søknadsfrist *</label>
                                <input type="date" class="form-control" id="deadline" name="deadline" 
                                       value="<?php echo Validator::sanitize($job['deadline'] ?? ''); ?>"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                       <!-- min settes til morgendagens dato for å hindre at bruker velger dagens eller tidligere dato -->
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label for="description" class="form-label">Stillingsbeskrivelse *</label>
                            <textarea class="form-control" id="description" name="description" rows="5" required><?php echo Validator::sanitize($job['description'] ?? ''); ?></textarea>
                            <div class="form-text">Minst 50 tegn</div>
                        </div>

                        <!-- Requirements -->
                        <div class="mb-3">
                            <label for="requirements" class="form-label">Krav og kvalifikasjoner *</label>
                            <textarea class="form-control" id="requirements" name="requirements" rows="4" required><?php echo Validator::sanitize($job['requirements'] ?? ''); ?></textarea>
                        </div>

                        <!-- Subject & Education level -->
                        <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="subject" class="form-label">Fag/område</label>
                            <select class="form-select" id="subject" name="subject">
                            <option value="">Velg fag</option>
                            <option value="Matematikk"   <?= (($job['subject'] ?? '') === 'Matematikk')   ? 'selected' : '' ?>>Matematikk</option>
                            <option value="Norsk"        <?= (($job['subject'] ?? '') === 'Norsk')        ? 'selected' : '' ?>>Norsk</option>
                            <option value="Engelsk"      <?= (($job['subject'] ?? '') === 'Engelsk')      ? 'selected' : '' ?>>Engelsk</option>
                            <option value="Naturfag"     <?= (($job['subject'] ?? '') === 'Naturfag')     ? 'selected' : '' ?>>Naturfag</option>
                            <option value="Samfunnsfag"  <?= (($job['subject'] ?? '') === 'Samfunnsfag')  ? 'selected' : '' ?>>Samfunnsfag</option>
                            <option value="Historie"     <?= (($job['subject'] ?? '') === 'Historie')     ? 'selected' : '' ?>>Historie</option>
                            <option value="Annet"        <?= (($job['subject'] ?? '') === 'Annet')        ? 'selected' : '' ?>>Annet</option>
                        </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="education_level" class="form-label">Utdanningsnivå</label>
                            <select class="form-select" id="education_level" name="education_level">
                            <option value="">Velg nivå</option>
                            <option value="Barneskole"        <?= (($job['education_level'] ?? '') === 'Barneskole')        ? 'selected' : '' ?>>Barneskole</option>
                            <option value="Ungdomsskole"      <?= (($job['education_level'] ?? '') === 'Ungdomsskole')      ? 'selected' : '' ?>>Ungdomsskole</option>
                            <option value="Videregående"      <?= (($job['education_level'] ?? '') === 'Videregående')      ? 'selected' : '' ?>>Videregående</option>
                            <option value="Høyere utdanning"  <?= (($job['education_level'] ?? '') === 'Høyere utdanning')  ? 'selected' : '' ?>>Høyere utdanning</option>
                            <option value="Alle nivåer"       <?= (($job['education_level'] ?? '') === 'Alle nivåer')       ? 'selected' : '' ?>>Alle nivåer</option>
                        </select>
                        </div>
                        </div>

                        <!-- Status -->
                        <div class="mb-3">
                            <label for="status" class="form-label">Status *</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active"   <?= (($job['status'] ?? 'active') === 'active')   ? 'selected' : '' ?>>Aktiv</option>
                                <option value="inactive" <?= (($job['status'] ?? '')       === 'inactive') ? 'selected' : '' ?>>Inaktiv</option>
                            </select>
                        </div>

                        <!-- Buttons -->
                        <div class="d-flex justify-content-between">
                            <a href="view.php?id=<?php echo $job_id; ?>" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Avbryt
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Oppdater stilling
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>