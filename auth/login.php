<?php
require_once '../includes/autoload.php';

/*
 * Logg inn side
 */

// Redirect hvis logget inn 
if (Auth::isLoggedIn()) {

    $user = Auth::user(); 
    $role = $user['role'] ?? 'applicant';

    if ($role === 'employer' || $role === 'admin') {

        $redirect_url = '../dashboard/employer.php';

    } else {

        $redirect_url = '../dashboard/applicant.php';
    }
    
    redirect($redirect_url, 'Du er allerede logget inn.', 'info');
}

$email = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    csrf_check();

    $email      = Validator::clean($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';

    if (!Validator::required($email) || !Validator::required($password)) {

        show_error('Både e-post og passord må fylles ut');

    } elseif (!Validator::validateEmail($email)) {

        show_error('Ugyldig e-postadresse.');

    } else {

        $user = Auth::attempt($email, $password);

        if ($user) {

            Auth::login($user);
            csrf_regenerate();

            $user = Auth::user(); 
            $role = $user['role'] ?? 'applicant';

        if ($role === 'employer' || $role === 'admin') {

            $redirect_url = '../dashboard/employer.php';

        } else {

            $redirect_url = '../dashboard/applicant.php';
        }
    
        redirect($redirect_url, 'Velkommen!', 'success');
        
       
        } else {

            show_error('Ugyldig e-post eller passord');
        }
    }
}

?>

<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logg inn - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center">
        <div class="row justify-content-center w-100">
            <div class="col-md-5 col-lg-3 col-xl-3">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h1 class="h3 mb-3 fw-normal">Logg inn</h1>
                            <p class="text-muted">Velkommen tilbake til <?php echo APP_NAME; ?></p>
                            <?php render_flash_messages(); ?>
                        </div>

                        <form method="POST" action="login.php" novalidate>
                            <?php echo csrf_field(); ?>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-postadresse</label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo Validator::sanitize($email); ?>"
                                       required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Passord</label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password" 
                                       required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Logg inn
                            </button>
                        </form>

                        <div class="text-center">
                            <p class="mb-2">
                                <a href="forgot.php" class="text-decoration-none">Glemt passord?</a>
                            </p>
                            <p>
                                Har du ikke konto? 
                                <a href="register.php" class="text-decoration-none">Registrer deg her</a>
                            </p>
                        </div>

                        <!-- Demo credentials -->
                        <div class="mt-4 p-3 bg-info bg-opacity-10 rounded">
                            <h6>Demo-kontoer:</h6>
                            <small class="text-muted">
                                <strong>Arbeidsgiver:</strong> employer@example.com<br>
                                <strong>Søker:</strong> applicant@example.com<br>
                                <strong>Passord:</strong> password
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>