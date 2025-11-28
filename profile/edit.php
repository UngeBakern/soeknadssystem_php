<?php
require_once '../includes/autoload.php';


/*
 * Rediger brukerprofil 
 */

// Sjekk om bruker er innlogget
auth_check();

// Hent bruker-ID fra session for å så bruke den id'en til å hente resten fra databasen
$user_id = Auth::id(); 
$user    = User::findById($user_id); 

if (!$user) {
    redirect('../auth/logout.php', 'Bruker ikke funnet. Logg inn på nytt', 'danger');
}

$user_type = $user['role'] ?? 'applicant';
$type_label = $user_type === 'employer' ? 'Arbeidsgiver' : 'Søker';

// HÅNDTER POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_check(); 

    $name       = Validator::clean($_POST['name']       ?? '');
    $email      = Validator::clean($_POST['email']      ?? '');
    $birthdate  = Validator::clean($_POST['birthdate']  ?? '');
    $phone      = Validator::clean($_POST['phone']      ?? '');
    $address    = Validator::clean($_POST['address']    ?? '');

    if (!Validator::required($name) || !Validator::required($email)) {

        show_error('Vennligst fyll ut både navn og e-post.');

    } elseif (!Validator::validateEmail($email)) {

        show_error('Epost-adressen er ikke gyldig.');

    } elseif (!empty($birthdate) && !Validator::validateDate($birthdate)) {

        show_error('Fødselsdatoen er ikke gyldig.');

    } elseif (!empty($phone) && !Validator::validatePhone($phone)) {

        show_error('Telefonnummeret er ikke gyldig.');

    } elseif (!empty($address) && !Validator::validateAddress($address)) {

        show_error('Adressen er ikke gyldig.');

    } else {
        
        $updated_user = [
            'name'      => $name,
            'email'     => $email, 
            'birthdate' => $birthdate ?: null, 
            'phone'     => $phone     ?: null,
            'address'   => $address   ?: null

        ];

        if (User::update($user_id, $updated_user)) {

            $_SESSION['user_name']  = $name;
            $_SESSION['user_email'] = $email;

            redirect('view.php', 'Profilen din er oppdatert!', 'success');

        } else { 

            show_error('Det oppstod en feil under oppdatering av profilen. Vennligst prøv igjen.');
        }
    }
        // Beholder verdier i skjema ved feil
        $user = array_merge($user, [
            'name'      => $name,
            'email'     => $email, 
            'birthdate' => $birthdate, 
            'phone'     => $phone, 
            'address'   => $address
        ]);
    }


$page_title = 'Rediger profil';
$body_class = 'bg-light';

include_once '../includes/header.php';
?>


    <!-- Page Header -->
    <div class="container py-5">
        <?php render_flash_messages(); ?>
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">
                <div class="mb-4 text-center">
                    <h1 class="h2 mb-2">Rediger profil</h1>
                    <p class="text-muted">Oppdater din brukerinfo</p>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <form method="post" action="" novalidate>
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="name" class="form-label">Navn</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?php echo Validator::sanitize($user['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-post</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo Validator::sanitize($user['email']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="birthdate" class="form-label">Fødselsdato</label>
                                <input type="date" class="form-control" id="birthdate" name="birthdate" max="<?php echo date('Y-m-d'); ?>"   value="<?php echo Validator::sanitize($user['birthdate'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" placeholder="900 00 000" value="<?php echo Validator::sanitize($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" class="form-control" id="address" name="address" value="<?php echo Validator::sanitize($user['address'] ?? ''); ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Brukertype</label>
                                <input type="text" class="form-control" value="<?php echo $type_label; ?>" disabled>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    Lagre endringer
                                </button>
                                <a href="view.php" class="btn btn-outline-secondary">
                                    Avbryt
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php include_once '../includes/footer.php'; ?>
