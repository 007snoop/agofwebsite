<?php
require __DIR__ . "/../accounts/auth.php";
require __DIR__ . "/../vendor/autoload.php";

// HTMLPurifier setup
$config = HTMLPurifier_Config::createDefault();
$config->set("HTML.AllowedElements", [
  'a',
  'abbr',
  'b',
  'blockquote',
  'br',
  'caption',
  'cite',
  'code',
  'col',
  'colgroup',
  'dd',
  'del',
  'div',
  'dl',
  'dt',
  'em',
  'h1',
  'h2',
  'h3',
  'h4',
  'h5',
  'h6',
  'i',
  'img',
  'ins',
  'li',
  'ol',
  'p',
  'pre',
  'q',
  's',
  'span',
  'strike',
  'strong',
  'sub',
  'sup',
  'table',
  'tbody',
  'td',
  'tfoot',
  'th',
  'thead',
  'tr',
  'u',
  'ul'
]);
$purifier = new HTMLPurifier($config);

// Fetch approved guides
$stmt = $pdo->query("SELECT g.id, g.title, g.content, u.username
FROM guides g
JOIN users u ON g.user_id = u.id
WHERE g.status = 'approved'
ORDER BY g.id DESC");
$approvedGuides = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Guides by Kinship</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Theme CSS -->
  <link rel="stylesheet" href="/theme.css">
</head>

<body>

  <?php include __DIR__ . '/../components/nav.php'; ?>

  <main class="container mt-5">
    <div class="text-center mb-4">
      <h1 class="header-text">Kinship Guides</h1>
      <p class="sub-header-text">Welcome to our library of guides.</p>
    </div>

    <?php if (!$approvedGuides): ?>
      <div class="alert alert-info text-center">No guides have been approved yet.</div>
    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
        <?php foreach ($approvedGuides as $guide): ?>
          <div class="col">
            <div class="card h-100 shadow-sm">
              <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($guide['title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">By <?= htmlspecialchars($guide['username']) ?></h6>
                <?php
                $snippet = substr(strip_tags($guide['content']), 0, 150) . '...';
                ?>
                <p class="card-text"><?= htmlspecialchars($snippet) ?></p>
                <a href="view.php?id=<?= $guide['id'] ?>" class="btn btn-primary">Read More</a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../script.js"></script>

</body>

</html>