    <!-- Footer -->
</main>  
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-muted"><?php echo APP_NAME; ?></h6>
                    <p class="text-muted small mb-0">Kobler sammen hjelpelærere og utdanningsinstitusjoner.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted small mb-0">
                        <a href="<?php echo BASE_URL; ?>/pages/about.php" class="text-muted me-3 text-decoration-none">Om oss</a>
                        <a href="<?php echo BASE_URL; ?>/pages/contact.php" class="text-muted me-3 text-decoration-none">Kontakt</a>
                        <a href="<?php echo BASE_URL; ?>/pages/support.php" class="text-muted text-decoration-none">Support</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!----<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>-->
    <!----<script src="<?php echo BASE_URL; ?>/assets/js/main.js"></script>--->
    
    <?php if (isset($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?php echo $js; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
