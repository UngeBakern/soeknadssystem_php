<?php
require_once '../includes/autoload.php';

/* 
 * Dashboard for arbeidsgiver
 */

// Autentisering og tilgangskontroll
auth_check(['employer', 'admin']);

// Innlogget bruker
$user      = Auth::user();
$user_id   = $user['id'];
$user_name = $user['name'] ?? 'Arbeidsgiver';

// Hent jobber opprettet av arbeidsgiveren man er innlogget som
$active_jobs = Job::getByEmployerId($user_id);
$archived_jobs = Job::getInactiveByEmployerId($user_id);

// Hent søkertall for alle jobber 
$job_ids = array_column($active_jobs, 'id');
$application_counts = Application::countByJobs($job_ids);

// Total antall søknader til bruk i statistikk 
$total_applications = array_sum($application_counts);

// Beregn statistikk
$stats = [
    'active_jobs'        => count($active_jobs),
    'archived_jobs'      => count($archived_jobs),
    'total_applications' => $total_applications,
    'new_applications'   => 0,
    'pending'            => 0
];

// Sett sidevariabler 
$page_title = 'Dashboard - Arbeidsgiver';
$body_class = 'bg-light';

require_once '../includes/header.php';
?>

<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="mb-4">
                <h1 class="h2 mb-2">Velkommen, <?php echo Validator::sanitize($user_name); ?>!</h1>
                <p class="text-muted">Administrer dine stillingsannonser og søknader</p>
            </div>

            <?php render_flash_messages(); ?>
        </div>
    </div>
</div>

<div class="container pb-5">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="card-title mb-3">Hurtighandlinger</h5>
                    <div class="d-grid gap-2">
                        <a href="../jobs/create.php" class="btn btn-primary">
                            Opprett ny stillingsannonse
                        </a>
                        <a href="#" class="btn btn-outline-primary" onclick="alert('Kommer snart!'); return false;">
                            Se alle søknader
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h5 class="card-title mb-4">Oversikt</h5>
                    <div class="row text-center">
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded">
                                <h3 class="text-primary mb-1"><?php echo $stats['active_jobs']; ?></h3>
                                <small class="text-muted">Aktive stillinger</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded">
                                <h3 class="text-success mb-1"><?php echo $stats['total_applications']; ?></h3>
                                <small class="text-muted">Totale søknader</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-warning bg-opacity-10 rounded">
                                <h3 class="text-warning mb-1"><?php echo $stats['pending']; ?></h3>
                                <small class="text-muted">Under vurdering</small>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="p-3 bg-info bg-opacity-10 rounded">
                                <h3 class="text-info mb-1"><?php echo $stats['archived_jobs']; ?></h3>
                                <small class="text-muted">Arkiverte</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <?php if (!empty($active_jobs)): ?>
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="card-title mb-0">Mine stillingsannonser</h5>
                    </div>
                        <div class="table-responsive">
                            <table class="table table-hover job-table">
                                <colgroup>
                                    <col class="col-title">
                                    <col class="col-location">
                                    <col class="col-date">
                                    <col class="col-status">
                                    <col class="col-applications">
                                    <col class="col-actions">
                                </colgroup>
                                <thead class="table-light">
                                    <tr>
                                        <th>Stilling</th>
                                        <th>Lokasjon</th>
                                        <th>Opprettet</th>
                                        <th>Status</th>
                                        <th>Søknader</th>
                                        <th>Handlinger</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($active_jobs as $job): ?>
                                        <?php 
                                        $application_count = Application::countByJob($job['id']);
                                        ?>
                                        <tr>
                                            <td>
                                                <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                                                   class="text-decoration-none text-dark">
                                                    <?php echo Validator::sanitize($job['title']); ?>
                                                </a>
                                            </td>
                                            <td><?php echo Validator::sanitize($job['location']); ?></td>
                                            <td><?php echo format_date($job['created_at']); ?></td>
                                            <td>
                                                <span class="badge bg-success">Aktiv</span>
                                            </td>
                                            <td>
                                                <a href="../applications/list.php?job_id=<?php echo $job['id']; ?>" 
                                                class="badge <?php echo $application_count > 0 ? 'bg-secondary' : 'bg-light text-dark'; ?> text-decoration-none">
                                                <?php echo $application_count; ?> søknad<?php echo $application_count !== 1 ? 'er' : ''; ?>
                                                </a>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex align-items-center gap-2">
                                                    <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                                                    class="btn btn-outline-secondary btn-sm"
                                                    title="Vis">
                                                        Vis
                                                    </a>
                                                    <a href="../jobs/edit.php?id=<?php echo $job['id']; ?>" 
                                                    class="btn btn-outline-primary btn-sm"
                                                    title="Rediger">
                                                        Rediger
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <h6>Ingen aktive stillinger</h6>
                            <p class="text-muted mb-3">Opprett din første stillingsannonse for å komme i gang.</p>
                            <a href="../jobs/create.php" class="btn btn-primary">
                                Opprett ny stilling
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($archived_jobs)): ?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="card-title mb-0">Arkiverte stillinger</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover job-table">
                        <colgroup>
                            <col class="col-title">
                            <col class="col-location">
                            <col class="col-date">
                            <col class="col-status">
                            <col class="col-applications">
                            <col class="col-actions">
                        </colgroup>
                        <thead class="table-light">
                            <tr>
                                <th>Stilling</th>
                                <th>Lokasjon</th>
                                <th>Arkivert</th>
                                <th>Status</th>
                                <th>Søknader</th>
                                <th>Handlinger</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($archived_jobs as $job): ?>
                                <?php 
                                $application_count = Application::countByJob($job['id']);
                                ?>
                                <tr class="text-muted">
                                    <td>
                                        <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                                           class="text-decoration-none text-muted">
                                            <?php echo Validator::sanitize($job['title']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo Validator::sanitize($job['location']); ?></td>
                                    <td><?php echo format_date($job['created_at']); ?></td>
                                    <td>
                                        <span class="badge bg-secondary">Inaktiv</span>
                                    </td>
                                    <td>
                                            <a href="../applications/list.php?job_id=<?php echo $job['id']; ?>" 
                                               class="badge <?php echo $application_count > 0 ? 'bg-primary' : 'bg-secondary'; ?> text-decoration-none"><?php echo $application_count; ?> søknad<?php echo $application_count !== 1 ? 'er' : ''; ?>
                                            </a>
                                    </td>
                                    <td class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <a href="../jobs/view.php?id=<?php echo $job['id']; ?>" 
                                        class="btn btn-outline-secondary btn-sm"
                                        title="Vis">
                                            Vis
                                        </a>
                                        <form method="POST" action="../jobs/reactivate.php" class="m-0 p-0">
                                            <input type="hidden" name="job_id" value="<?php echo $job['id']; ?>">
                                            <button type="submit" 
                                                    class="btn btn-outline-success btn-sm"
                                                    title="Reaktiver"
                                                    onclick="return confirm('Vil du reaktivere denne stillingen?');">
                                                Aktiver
                                            </button>
                                        </form>
                                    </div>
                                </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>
</div>
<?php include_once '../includes/footer.php'; ?>