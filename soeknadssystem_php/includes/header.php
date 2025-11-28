<!DOCTYPE html>
<html lang="no">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Hjelpelærer Søknadssystem'; ?> - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/style.css" rel="stylesheet">
    <?php if (isset($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link href="<?php echo $css; ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="<?php echo $body_class ?? 'bg-light'; ?>">
    
    <?php if ($show_nav ?? true): ?>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Alle stillinger (alltid synlig) -->
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>/jobs/list.php">
                            Alle stillinger
                        </a>
                    </li>
                    
                    <?php if (is_logged_in()): ?>
                        <!-- Dashboard (kun for innloggede) -->
                        <li class="nav-item">
                            <?php 
                            $dashboard_url = has_role('employer') 
                                ? BASE_URL . '/dashboard/employer.php' 
                                : BASE_URL . '/dashboard/applicant.php'; 
                            ?>
                            <a class="nav-link" href="<?php echo $dashboard_url; ?>">
                                Dashboard
                            </a>
                        </li>
                        
                        <!-- Profil -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/profile/view.php">
                                Profil
                            </a>
                        </li>
                        <!-- Logg ut -->
                        <li class="nav-item">
                            <form method="POST" action="<?php echo BASE_URL; ?>/auth/logout.php" class="d-inline m-0">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="nav-link text-decoration-none bg-transparent border-0">
                                    Logg ut
                                </button>
                            </form>
                        </li>
                    <?php else: ?>
                        <!-- Logg inn (kun for utloggede) -->
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/auth/login.php">
                                <i class="fas fa-sign-in-alt me-1"></i>
                                Logg inn
                            </a>
                        </li>

                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <main class="flex-grow-1">