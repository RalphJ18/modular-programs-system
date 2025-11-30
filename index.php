<?php
// index.php - modular loader (NiceAdmin-inspired)

$modules = [
    'bsit' => 'BSIT',
    'educ' => 'EDUC',
    'criminology' => 'Criminology',
];

$requested = isset($_GET['module']) ? $_GET['module'] : 'bsit';
$module = array_key_exists($requested, $modules) ? $requested : 'bsit';
$module_file = __DIR__ . '/modules/' . $module . '.php';
?>

<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Programs — Modular </title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Poppins:wght@400;600&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- AOS CSS -->
    <link href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
  </head>
  <body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom shadow-sm">
      <div class="container-fluid">
        <button class="btn btn-sm btn-outline-secondary me-2" id="sidebarToggle">
          <i class="bi bi-list"></i>
        </button>

        <a class="navbar-brand d-flex align-items-center" href="#">
          <img src="assets/img/logo.png" alt="Logo" class="me-2 logo-img" onerror="this.style.display='none'">
          <div>
            <div class="fw-bold">Modular System kuno</div>
            <small class="text-muted">Modular Programs Dashboard</small>
          </div>
        </a>

        <div class="collapse navbar-collapse">
          <ul class="navbar-nav ms-auto align-items-center">
            <li class="nav-item me-3">
              <a class="nav-link" href="#">Help</a>
            </li>
            <li class="nav-item">
              <a class="btn btn-primary btn-sm" href="#">
                <i class="bi bi-person-circle me-1"></i> Admin
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid">
      <div class="row">

        <!-- Sidebar -->
        <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse show">
          <div class="sidebar-sticky pt-3">

            <h6 class="sidebar-heading px-3 mb-2 text-muted">Programs</h6>

            <ul class="nav flex-column">
              <?php foreach ($modules as $key => $name): ?>
                <li class="nav-item">
                  <a class="nav-link <?php echo $key === $module ? 'active' : ''; ?> d-flex align-items-center"
                     href="?module=<?php echo htmlspecialchars($key); ?>">

                    <?php if ($key === 'bsit'): ?><i class="bi bi-laptop me-2"></i><?php endif; ?>
                    <?php if ($key === 'educ'): ?><i class="bi bi-book-half me-2"></i><?php endif; ?>
                    <?php if ($key === 'criminology'): ?><i class="bi bi-shield-lock me-2"></i><?php endif; ?>

                    <span><?php echo htmlspecialchars($name); ?></span>
                  </a>
                </li>
              <?php endforeach; ?>
            </ul>

            <hr>

            <div class="px-3 small text-muted">Quick Links</div>
            <ul class="nav flex-column mb-3 px-2">
              <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-gear me-2"></i> Settings</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
              </li>
            </ul>

          </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">

          <div class="d-flex justify-content-between flex-wrap align-items-center mb-3">
            <div>
              <h2 class="mb-0"><?php echo htmlspecialchars($modules[$module]); ?></h2>
              <small class="text-muted">Overview and details</small>
            </div>

            <div>
              <button class="btn btn-outline-secondary btn-sm me-2">
                <i class="bi bi-download"></i> Export
              </button>
              <button class="btn btn-success btn-sm">
                <i class="bi bi-pencil-square"></i> Edit
              </button>
            </div>
          </div>

          <div id="content-area">
            <?php
            if (file_exists($module_file)) {
                include $module_file;
            } else {
                echo '<div class="alert alert-danger">Module file not found.</div>';
            }
            ?>
          </div>

          <footer class="mt-5 small text-muted">
            © <?php echo date('Y'); ?> Okay nani Sir uy
          </footer>

        </main>
      </div>
    </div>

    <!-- JS Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>

    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>

    <script>
      AOS.init({
        duration: 600,
        once: true
      });
    </script>

  </body>
</html>
