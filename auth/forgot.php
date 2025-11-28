<?php
require_once '../includes/autoload.php';


/*
 * 
 *
 *
 */

$error = '';
$success = '';
$email = '';

if ($_POST) {
    $email = sanitize_input($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Vennligst oppgi din e-postadresse.';
    } elseif (!validate_email($email)) {
        $error = 'Ugyldig e-postadresse.';
    } else {
        // Her ville man sendt e-post med tilbakestillingslenke
        $success = 'Hvis e-postadressen finnes, har du fått tilsendt instruksjoner.';
    }
}
?>
<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glemt passord - <?php echo APP_NAME; ?></title>
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
                            <h1 class="h3 mb-3 fw-normal">Glemt passord?</h1>
                            <p class="text-muted">Skriv inn din e-postadresse for å motta instruksjoner om tilbakestilling av passord.</p>
                        </div>
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success" role="alert">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                        <?php endif; ?>
                        <form method="POST" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="email" class="form-label">E-postadresse</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <div class="invalid-feedback">
                                    Vennligst oppgi en gyldig e-postadresse.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">
                                <i class="fas fa-paper-plane me-2"></i>
                                Send instruksjoner
                            </button>
                        </form>
                        <div class="text-center">
                            <a href="login.php" class="text-decoration-none">Tilbake til innlogging</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="../assets/js/main.js"></script>
</body>
</html>
