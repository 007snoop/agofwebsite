<?php
require __DIR__ . "/../accounts/auth.php";
require __DIR__ . "/../vendor/autoload.php";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    die("Invalid guide ID");
}

$guideId = (int) $_GET["id"];

$stmt = $pdo->prepare("SELECT g.title, g.content, u.username
FROM guides g
JOIN users u ON g.user_id = u.id
WHERE g.id = ? AND g.status = 'approved'");
$stmt->execute([$guideId]);
$guide = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$guide) {
    die("Guide not found or not approved yet.");
}

// HTMLPurifier config for Summernote content
$config = HTMLPurifier_Config::createDefault();
$config->set("HTML.AllowedElements", [
    'a','abbr','b','blockquote','br','caption','cite','code','col','colgroup','dd','del','div','dl','dt','em','h1','h2','h3','h4','h5','h6',
    'i','img','ins','li','ol','p','pre','q','s','span','strike','strong','sub','sup','table','tbody','td','tfoot','th','thead','tr','u','ul'
]);

$config->set("HTML.AllowedAttributes", [
    'a.href','a.title','img.src','img.alt','img.width','img.height',
    'div.class','div.style','span.class','span.style','p.class','p.style',
    'table.class','table.style','td.class','td.style','th.class','th.style'
]);

$config->set("CSS.AllowedProperties", null); // allow all inline styles
$config->set("URI.AllowedSchemes", ["http" => true, "https" => true, "data" => true]);

$purifier = new HTMLPurifier($config);
$cleanContent = $purifier->purify($guide["content"]);

$stmt = $pdo->prepare("SELECT g.title, g.content, g.user_id, u.username FROM guides g JOIN users u ON g.user_id = u.id WHERE g.id = ? AND g.status = 'approved'");
$stmt->execute([$guideId]);

$guide = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($guide['title']) ?> - Kinship</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Global theme CSS -->
    <link rel="stylesheet" href="/theme.css">
</head>
<body>

<?php include __DIR__ . '/../components/nav.php'; ?>

<main class="container my-5">
    <div class="card shadow-sm p-4">
        <h1 class="mb-3"><?= htmlspecialchars($guide['title']) ?></h1>
        <p class="text-muted"><em>By <?= htmlspecialchars($guide['username']) ?></em></p>
        <hr>
        <div class="guide-content">
            <?= $cleanContent ?>
        </div>
        <a href="guide-landing.php" class="btn btn-secondary mt-4">Back to Guides</a>
        <?php 
        $userId = checklogin();
        $canEdit = $userId && ($userId == $guide['user_id'] || hasRole(['officer', 'committee']));
        if ($canEdit) {
            echo '<p><a class="btn btn-secondary mt-4" href="/guides/edit.php?id='.$guideId.'">Edit</a></p>';
        }
        ?>
    </div>
     <button id="back-to-top" class="btn btn-secondary">Back to Top</button>
</main>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Back to Top functionality
    const backBtn = document.getElementById('back-to-top');
    backBtn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
  </script>
<script src="/script.js"></script>

</body>
</html>