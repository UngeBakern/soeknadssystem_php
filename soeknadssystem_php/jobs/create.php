<?php
require_once '../includes/autoload.php';

/*
 * Opprett ny stillingsannonse
 */

// Sjekk at bruker er innlogget og er arbeidsgiver 
auth_check(['employer', 'admin']);

// Innlogget bruker 
$user       = Auth::user();
$user_id    = $user['id'];
$company    = $user['name'] ?? 'Ukjent arbeidsgiver';

// Standardverdier for felt brukes ved valideringsfeil 
$title          = '';
$description    = '';
$requirements   = '';
$location       = '';
$salary         = '';
$job_type       = '';
$subject        = '';
$education_level= '';
$deadline       = '';
$hours_per_week = '';

// Håndter POST-request  
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $title          = Validator::clean($_POST['title']                ?? '');
    $description    = Validator::clean($_POST['description']          ?? '');
    $requirements   = Validator::clean($_POST['requirements']         ?? '');
    $location       = Validator::clean($_POST['location']             ?? '');
    $salary         = Validator::clean($_POST['salary']               ?? '');
    $job_type       = Validator::clean($_POST['job_type']             ?? '');
    $subject        = Validator::clean($_POST['subject']              ?? '');
    $education_level= Validator::clean($_POST['education_level']      ?? '');
    $deadline       = Validator::clean($_POST['deadline']             ?? '');
    $hours_per_week = Validator::clean($_POST['hours_per_week']       ?? '');
    //TODO Lagre hours_per_week i databasen senere.
    
    // Whitelist på select felter 
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

        // Opprett ny jobb
        $new_job = [
            'employer_id'    => $user_id,
            'title'          => $title,
            'company'        => $company,
            'description'    => $description,
            'requirements'   => $requirements,
            'location'       => $location,
            'salary'         => $salary ?: null,
            'job_type'       => $job_type,
            'subject'        => $subject ?: null,
            'education_level'=> $education_level ?: null,
            'deadline'       => $deadline,
            'status'         => 'active'   
        ];

        $result = Job::create($new_job);

        if ($result) {
            redirect('../dashboard/employer.php', 'Stilling opprettet og publisert!', 'success');
        } else {
            show_error('Det oppstod en feil under opprettelsen av stillingen. Vennligst prøv igjen.');
        }
    }
}

// Sett sidevariabler 
$page_title = 'Opprett ny stilling';
$body_class = 'bg-light';

require_once '../includes/header.php';
?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="mb-4 text-center">
                    <h1 class="h2 mb-2">Opprett ny stilling</h1>
                    <p class="text-muted">Fyll ut informasjonen under for å publisere din stillingsutlysning</p>
                </div>

                <?php render_flash_messages(); ?>

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="POST" action="" novalidate>
                            <?php echo csrf_field(); ?>

                          <!-- Basic Information -->
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label for="title" class="form-label" style="font-size:0.95rem;">
                                        <i class="fas fa-tag me-1"></i>
                                        Stillingstittel *
                                    </label>
                                    <input type="text" class="form-control form-control-sm" id="title" name="title" 
                                           value="<?php echo Validator::sanitize($title) ?>"
                                           placeholder="F.eks. Hjelpelærer i PHP" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <label for="company" class="form-label">
                                        <i class="fas fa-building me-1"></i>
                                        Bedrift/Organisasjon
                                    </label>
                                    <input type="text" class="form-control" id="company" name="company" 
                                           value="<?php echo Validator::sanitize($company) ?>" readonly>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="location" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Lokasjon *
                                    </label>
                                    <input type="text" class="form-control" id="location" name="location" 
                                           value="<?php echo Validator::sanitize($location) ?>"
                                           placeholder="F.eks. Universitetet i Agder" required>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="job_type" class="form-label">
                                        <i class="fas fa-clock me-1"></i>
                                        Stillingstype *
                                    </label>
                                    <select class="form-select" id="job_type" name="job_type" required>
                                        <option value="">Velg stillingstype</option>
                                        <option value="Heltid"      <?php echo $job_type === 'Heltid' ? 'selected' : ''; ?>>Heltid</option>
                                        <option value="Deltid"      <?php echo $job_type === 'Deltid' ? 'selected' : ''; ?>>Deltid</option>
                                        <option value="Ekstrahjelp" <?php echo $job_type === 'Ekstrahjelp' ? 'selected' : ''; ?>>Ekstrahjelp</option>
                                        <option value="Vikariat" <?php echo $job_type === 'Vikariat' ? 'selected' : ''; ?>>Vikariat</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="hours_per_week" class="form-label">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        Timer per uke
                                    </label>
                                    <input type="number" class="form-control" id="hours_per_week" name="hours_per_week" 
                                           value="<?php echo Validator::sanitize($hours_per_week) ?>"
                                           min="1" max="40" placeholder="F.eks. 20">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="salary" class="form-label">
                                        <i class="fas fa-money-bill-wave me-1"></i>
                                        Lønn
                                    </label>
                                    <input type="text" class="form-control" id="salary" name="salary" 
                                           value="<?php echo Validator::sanitize($salary) ?>"
                                           placeholder="F.eks. 200-250 kr/time eller Etter avtale">
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="deadline" class="form-label">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Søknadsfrist *
                                </label>
                                <input type="date" class="form-control" id="deadline" name="deadline" 
                                       value="<?php echo Validator::sanitize($deadline) ?>"
                                       min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                       <!-- min settes til morgendagens dato for å hindre at bruker velger dagens eller tidligere dato -->
                            </div>

                            <!-- Job Description -->
                            <div class="mb-2">
                                <label for="description" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>
                                    Stillingsbeskrivelse *
                                </label>
                                <textarea class="form-control" id="description" name="description" rows="5" 
                                          placeholder="Beskriv stillingen, arbeidsoppgaver og hva dere ser etter..." required><?php echo Validator::sanitize($description) ?></textarea>
                                <div class="form-text">Minst 50 tegn</div>
                            </div>

                            <!-- Requirements -->
                            <div class="mb-2">
                                <label for="requirements" class="form-label">
                                    <i class="fas fa-list-check me-1"></i>
                                    Krav og kvalifikasjoner *
                                </label>
                                <textarea class="form-control" id="requirements" name="requirements" rows="4" 
                                          placeholder="Liste opp krav til utdanning, erfaring og andre kvalifikasjoner..." required><?php echo Validator::sanitize($requirements) ?></textarea>
                                <div class="form-text">F.eks. utdanningsnivå, relevant erfaring, språkkrav</div>
                            </div>

                            <!-- Additional Information -->
                            <div class="card bg-light border-0 mb-2">
                                <div class="card-body p-2">
                                    <h6 class="card-title mb-2" style="font-size:1rem;">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Tilleggsinformasjon
                                    </h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <label for="subject" class="form-label">Fag/område</label>
                                            <select class="form-select" id="subject" name="subject">
                                                <option value="">Velg fag</option>
                                                <option value="Matematikk"   <?= $subject === 'Matematikk'   ? 'selected' : '' ?>>Matematikk</option>
                                                <option value="Norsk"        <?= $subject === 'Norsk'        ? 'selected' : '' ?>>Norsk</option>
                                                <option value="Engelsk"      <?= $subject === 'Engelsk'      ? 'selected' : '' ?>>Engelsk</option>
                                                <option value="Naturfag"     <?= $subject === 'Naturfag'     ? 'selected' : '' ?>>Naturfag</option>
                                                <option value="Samfunnsfag"  <?= $subject === 'Samfunnsfag'  ? 'selected' : '' ?>>Samfunnsfag</option>
                                                <option value="Historie"     <?= $subject === 'Historie'     ? 'selected' : '' ?>>Historie</option>
                                                <option value="Annet"        <?= $subject === 'Annet'        ? 'selected' : '' ?>>Annet</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label for="education_level" class="form-label">Utdanningsnivå</label>
                                            <select class="form-select" id="education_level" name="education_level">
                                                <option value="">Velg nivå</option>
                                                <option value="Barneskole"        <?= $education_level === 'Barneskole'        ? 'selected' : '' ?>>Barneskole</option> 
                                                <option value="Ungdomsskole"      <?= $education_level === 'Ungdomsskole'      ? 'selected' : '' ?>>Ungdomsskole</option>
                                                <option value="Videregående"      <?= $education_level === 'Videregående'      ? 'selected' : '' ?>>Videregående</option>
                                                <option value="Høyere utdanning"  <?= $education_level === 'Høyere utdanning'  ? 'selected' : '' ?>>Høyere utdanning</option>
                                                <option value="Alle nivåer"       <?= $education_level === 'Alle nivåer'       ? 'selected' : '' ?>>Alle nivåer</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted" style="font-size:0.95rem;">
                                    <i class="fas fa-asterisk me-1" style="font-size: 0.7rem;"></i>
                                    Obligatoriske felt
                                </small>
                                <div class="d-flex align-items-center justify-content-end">
                                    <a href="../dashboard/employer.php" class="btn btn-outline-secondary me-2">
                                        <i class="fas fa-arrow-left me-2"></i>
                                        Avbryt
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane me-2"></i>
                                        Publiser stilling
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../includes/footer.php'; ?>