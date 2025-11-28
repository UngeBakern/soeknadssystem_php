<?php
require_once '../includes/autoload.php';

/*
 * Registreringsside
 */

// Redirect if already logged in
if (Auth::isLoggedIn()) {

    $user = Auth::user();
    $role = $user['role'] ?? 'applicant';


    $redirect_url = ($role === 'employer' || $role === 'admin')
        ? '../dashboard/employer.php'
        : '../dashboard/applicant.php';
        
    redirect($redirect_url, 'Du er allerede logget inn.', 'info');
}

// Forhåndsvalg av rolle i select skjema
$role = $_GET['type'] ?? 'applicant';

// Behold verdier ved feil
$name = '';
$email = '';
$phone = '';
$birthdate = ''; 
$address = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $name             = Validator::clean($_POST['name']      ?? '');
    $email            = Validator::clean($_POST['email']     ?? '');
    $password         = $_POST['password']                      ?? '';
    $confirm_password = $_POST['confirm_password']              ?? '';
    $role             = $_POST['role'] ?? 'applicant';
    $phone            = Validator::clean($_POST['phone']     ?? '');
    $birthdate        = Validator::clean($_POST['birthdate'] ?? '');
    $address          = Validator::clean($_POST['address']   ?? '');

    // Whitelist for roller
    $allowed_roles = ['applicant', 'employer'];
    if (!in_array($role, $allowed_roles)) {
        $role = 'applicant';
    }

    // Validation
    if (!Validator::required($name) || !Validator::required($email) || !Validator::required($password)) {

        show_error('Navn, e-post og passord må fylles ut');
        
    } elseif (!Validator::validateEmail($email)) {

        show_error('Ugyldig e-postadresse');

    } elseif (!Validator::validatePassword($password)) {

        show_error('Passord må være minst 8 tegn og inneholde store bokstaver, små bokstaver og tall.');

    } elseif ($password !== $confirm_password) {

        show_error('Passordene stemmer ikke overens.');

    } elseif (User::findByEmail($email)) {

        show_error('E-postadressen er allerede registrert.');

    } elseif (!empty($birthdate) && !Validator::validateDate($birthdate)) {

        show_error('Ugyldig fødselsdato. Bruk formatet ÅÅÅÅ-MM-DD.');

    } elseif (!empty($phone) && !Validator::validatePhone($phone)) {

        show_error('Ugyldig telefonnummer.');

    } else {

        // Opprett ny bruker 
        $user_id = User::create([
            'name'          => $name,
            'email'         => $email,
            'password'      => $password,
            'role'          => $role,
            'phone'         => $phone ?: null,
            'birthdate'     => $birthdate ?: null,
            'address'       => $address ?: null
        ]);

        if ($user_id) {
            $user = User::findById($user_id);
            
            if($user) {
                Auth::login($user);
                csrf_regenerate();
            }

            $role = $user['role'] ?? 'applicant';

            // Redirect til dashboard
            $redirect_url = ($role === 'employer' || $role === 'admin')
                ? '../dashboard/employer.php'
                : '../dashboard/applicant.php';

            redirect($redirect_url, 'Velkommen! Din konto er opprettet.', 'success');

        } else { 

            show_error('Noe gikk galt ved registrering. Prøv igjen.');
        }
    }
}
?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrer deg - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="row justify-content-center w-100">
        <div class="col-md-5 col-lg-3 col-xl-3 my-5">
                <div class="card shadow">
            <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-3 fw-normal">Registrer deg</h1>
                            <p class="text-muted">Opprett din konto hos <?php echo APP_NAME; ?></p>
                        </div>

                        <?php render_flash_messages(); ?>

                        <form method="POST" action="" novalidate>
                            <?php echo csrf_field(); ?>

                            <div class="mb-3">
                                <label for="name" class="form-label">Fullt navn</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo Validator::sanitize($name ?? ''); ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">E-postadresse</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo Validator::sanitize($email ?? ''); ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="role" class="form-label">Jeg er en</label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="applicant" <?php echo $role === 'applicant' ? 'selected' : ''; ?>>
                                        Jobbsøker 
                                    </option>
                                    <option value="employer" <?php echo $role === 'employer' ? 'selected' : ''; ?>>
                                        Arbeidsgiver 
                                    </option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">Telefonnummer (valgfritt)</label>
                                <input type="tel" 
                                       class="form-control" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo Validator::sanitize($phone); ?>"
                                       placeholder="900 00 000">
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Passord</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Bekreft passord</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="confirm_password" 
                                       name="confirm_password" 
                                       required>
                                       <small class="text-muted">Minimum 8 tegn, med store bokstaver, små bokstaver og ett tall.</small>
                            </div>

                            <div class="mb-3">
                                <label for="birthdate" class="form-label">Fødselsdato</label>
                                <input type="date" 
                                    class="form-control" 
                                    id="birthdate" 
                                    name="birthdate" 
                                    value="<?php echo Validator::sanitize($birthdate); ?>"
                                    max="<?php echo date('Y-m-d'); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">Adresse</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="address" 
                                       name="address" 
                                       value="<?php echo Validator::sanitize($address); ?>"
                                        placeholder="Gate 1, 0123 Oslo"
                                        maxlength="255">
                                        <small class="text-muted">Valgfritt </small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>
                                Opprett konto
                            </button>
                        </form>

                        <div class="text-center">
                            <p>
                                Har du allerede en konto? 
                                <a href="login.php" class="text-decoration-none">Logg inn her</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
</body>
</html>
