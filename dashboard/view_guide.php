<?php
require __DIR__ . "/../accounts/auth.php";

$userId = checklogin();

$guideId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;

if ($guideId <= 0)
    die("Invalid guide ID");

$stmt = $pdo->prepare("SELECT * FROM guides g LEFT JOIN users u ON g.user_id = u.id WHERE g.id = ?");
$stmt->execute([$guideId]);
$guide = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$guide)
    die("Guide not found");

$isOwner = ($guide["user_id"] == $userId);

if (!$isOwner && $guide["status"] !== 'approved') {
    http_response_code(403);
    die("You do not have permission to view this guide");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($guide['title']) ?> - Guide</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/theme.css">
</head>

<body>
    <?php include __DIR__ . '/../components/nav.php'; ?>

    <main class="container mt-5">
        <div class="card shadow-sm p-4">
            <h1 class="header-text mb-3"><?= htmlspecialchars($guide['title']) ?></h1>
            <p class="text-muted">
                Written by <strong><?= htmlspecialchars($guide['username']) ?></strong>
                on <?= date('F j, Y', strtotime($guide['created_at'])) ?>
            </p>

            <?php if ($guide['status'] !== 'approved'): ?>
                <div class="alert alert-warning mb-3">
                    This guide is currently <strong><?= htmlspecialchars($guide['status']) ?></strong>.
                    <?php if ($isOwner): ?>
                        Only you can view it until it's approved.
                    <?php else: ?>
                        You do not have permission to view unapproved guides.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <hr>

            <div class="guide-content">
                <?= $guide['content'] // trusted from Summernote ?>
            </div>
        </div>

        <div class="mt-4">
            <a href="/dashboard/my_guides.php" class="btn btn-secondary">← Back to My Guides</a>
            <a href="/guides/guide-landing.php" class="btn btn-outline-primary">All Guides</a>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>