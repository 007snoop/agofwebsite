

<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index.php">A Group of Friends</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav"
      aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="/about.php">About</a></li>
        <li class="nav-item"><a class="nav-link" href="/guides/guide-landing.php">Guides</a></li>

        <?php $userId = checklogin(); ?>
        <?php if ($userId): ?>
          <li class="nav-item"><a class="nav-link" href="/dashboard/index.php">Dashboard</a></li>
          <li class="nav-item">
            <form method="POST" action="/accounts/logout.php" class="d-inline">
              <button type="submit" class="btn btn-link nav-link">Logout</button>
            </form>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="/accounts/register.php">Register</a></li>
          <li class="nav-item"><a class="nav-link" href="/accounts/login.php">Login</a></li>
        <?php endif; ?>
      </ul>

      <?php if ($userId):
        global $user;
      ?>
      <span class="navbar-text ms-auto">
        Logged in as: <?= htmlspecialchars($user["username"]) ?>
      </span>
      <?php endif; ?>
      
    </div>
  </div>
</nav>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const collapseEl = document.getElementById("mainNav");
    const bsCollapse = new bootstrap.Collapse(collapseEl, { toggle: false });

    // Close when clicking outside
    document.addEventListener("click", function (e) {
        if (collapseEl.classList.contains("show")) {
            // if click is outside collapse and hamburger button
            const toggler = document.querySelector(".navbar-toggler");
            if (!collapseEl.contains(e.target) && !toggler.contains(e.target)) {
                bsCollapse.hide();
            }
        }
    });

    // Optional: auto-close when clicking a nav link on mobile
    collapseEl.querySelectorAll(".nav-link").forEach(link => {
        link.addEventListener("click", function () {
            if (window.innerWidth < 992) { // match lg breakpoint
                bsCollapse.hide();
            }
        });
    });
});
</script>