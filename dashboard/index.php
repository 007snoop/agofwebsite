<?php
require_once __DIR__ . '/../accounts/auth.php';
$userId = checklogin();

if (!$userId) {
    header('Location: /accounts/login.php');
    exit;
}

$stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmtUser->execute([$userId]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

// optional role check
if (!in_array($user['role'], ['officer', 'committee', 'member'])) {
    die("Access denied");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= htmlspecialchars($user['username']) ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Theme CSS -->
    <link rel="stylesheet" href="/theme.css">
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<main class="container my-5">
    <div class="card shadow-sm p-4">
        <h1 class="mb-4">Welcome, <?= htmlspecialchars($user['username']) ?>!</h1>

        <nav class="mb-4">
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action active">Dashboard Home</a>

                <?php if (hasRole('member') || hasRole('officer') || hasRole('committee')): ?>
                    <a href="/guides/submit.php" class="list-group-item list-group-item-action">Submit Guide</a>
                <?php endif; ?>

                <?php if (hasRole('officer') || hasRole('committee')): ?>
                    <a href="review_guides.php" class="list-group-item list-group-item-action">Review Guides</a>
                <?php endif; ?>


                <a class="list-group-item list-group-item-action" href="/dashboard/my_guides.php">My Guides</a>
                

                <?php if (hasRole('committee')): ?>
                    <a href="user_management.php" class="list-group-item list-group-item-action">User Management</a>
                <?php endif; ?>
            </div>
        </nav>

        <section>
            <p>Use the navigation above to access your dashboard features.</p>
        </section>
    </div>
</main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/script.js"></script>

</body>
</html>