<?php 
require_once __DIR__ ."/../accounts/auth.php";

global $user;

$userId = checklogin();
if (!$userId) {
    header("Location: /accounts/login.php");
    exit;
}

$stmt = $pdo->prepare("SELECT id, title, status, created_at, updated_at FROM guides WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$guides = $stmt->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Guides - A Group of Friends</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/theme.css">
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<main class="container my-5">
    <h2 class="mb-4 text-center">My Guides</h2>

    <?php if (empty($guides)): ?>
        <div class="alert alert-info text-center">
            You haven't submitted any guides yet.
        </div>
    <?php else: ?>
        <div class="list-group">
            <?php foreach ($guides as $guide): ?>
                <div class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong><?= htmlspecialchars($guide['title']) ?></strong><br>
                        <small>Status: 
                            <?php if ($guide['status'] === 'approved'): ?>
                                <span class="text-success">Approved</span>
                            <?php elseif ($guide['status'] === 'pending'): ?>
                                <span class="text-warning">Pending</span>
                            <?php else: ?>
                                <span class="text-danger">Rejected</span>
                            <?php endif; ?>
                        </small>
                    </div>

                    <div>
                        <a href="view_guide.php?id=<?= $guide['id'] ?>" class="btn btn-sm btn-secondary">View</a>
                        <?php if ($guide['status'] !== 'approved'): ?>
                            <a href="/../guides/edit.php?id=<?= $guide['id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>