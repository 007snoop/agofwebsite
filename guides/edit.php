<?php
require __DIR__ . "/../accounts/auth.php";
require __DIR__ . "/../vendor/autoload.php";

$userId = checklogin();

if (!$userId) {
    header("Location: /accounts/login.php");
    exit;
}

$guideId = isset($_GET["id"]) ? intval($_GET["id"]) : 0;
if ($guideId <= 0) {
    die("Invalid guide ID");
}

$stmt = $pdo->prepare("SELECT * FROM guides WHERE id = ?");
$stmt->execute([$guideId]);
$guide = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$guide) {
    die("Guide not found");
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$currUser = $stmt->fetch(PDO::FETCH_ASSOC);
$role = $currUser["role"] ?? 'member';

$isOwner = ($guide["user_id"] == $userId);
$isAdmin = in_array($role, ['officer', 'committee']);
if (!($isOwner || $isAdmin)) {
    http_response_code(403);
    die('Access Denied');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $content = $_POST['content'] ?? '';

    if ($isAdmin && isset($_POST['status']) && in_array($_POST['status'], ['pending', 'approved', 'rejected'])) {
        $status = $_POST['status'];
    } else {
        $status = 'pending';
    }

    if ($title === '' || $content === '') {
        $error = "Please fill in all fields";
    } else {
        $config = HTMLPurifier_Config::createDefault();

        $config->set("HTML.Allowed", "p,b,strong,i,em,u,a[href|title|target],ul,ol,li,br,img[src|alt|width|height|style],h1,h2,h3,div,span,table,thead,tbody,tr,td,th,pre,code");
        $config->set("CSS.AllowedProperties", null);
        $config->set("URI.AllowedSchemes", ["http" => true, "https" => true, "data" => true]);
        $purifier = new HTMLPurifier($config);
        $cleanContent = $purifier->purify($content);

        $stmt = $pdo->prepare("UPDATE guides SET title = ?, content = ?, status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$title, $cleanContent, $status, $guideId]);


        $success = 'Guide saved.';

        $stmt = $pdo->prepare('SELECT * FROM guides WHERE id = ?');
        $stmt->execute([$guideId]);
        $guide = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Edit Guide</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/../theme.css" />
</head>

<body>
    <?php include __DIR__ . '/../components/nav.php'; ?>

    <div class="container my-4" style="max-width:900px;">
        <h2>Edit Guide</h2>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" id="edit-guide">
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input name="title" class="form-control" value="<?= htmlspecialchars($guide['title']) ?>" required>
            </div>

            <?php if ($isAdmin): ?>
                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="pending" <?= $guide['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $guide['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $guide['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                    <div class="form-text">Admins can change status directly.</div>
                </div>
            <?php endif; ?>

            <div class="mb-3">
                <label class="form-label">Content</label>
                <!-- we output stored HTML directly into the editor (it was sanitized when saved) -->
                <textarea id="content" name="content" class="form-control" rows="12"><?= $guide['content'] ?></textarea>
            </div>

            <button class="btn btn-primary" type="submit">Save</button>
            <a class="btn btn-secondary" href="/guides/view.php?id=<?= $guideId ?>">Cancel</a>
        </form>
    </div>

    <!-- scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.js"></script>
    <script>
        $(function () {
            $('#content').summernote({
                height: 350
            });

            // Ensure the summernote contents are placed into textarea on submit
            $('#edit-guide').on('submit', function () {
                // summernote writes HTML into the textarea automatically, but force it to be safe:
                var code = $('#content').summernote('code');
                $('#content').val(code);
            });
        });
    </script>
</body>