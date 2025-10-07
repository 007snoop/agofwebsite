<?php
require __DIR__ . "/../accounts/auth.php";
$userId = checklogin();
if (!$userId) {
  header("Location: /accounts/login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title']);
  $content = trim($_POST['content']);

  if (!empty($title) && !empty($content)) {

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
    $config->set("CSS.AllowedProperties", [
      'color',
      'background-color',
      'font-size',
      'text-align',
      'width',
      'height',
      'max-width',
      'max-height'
    ]);
    $config->set("URI.AllowedSchemes", ["http" => true, "https" => true, "data" => true]);
    $purifier = new HTMLPurifier($config);
    $cleanContent = $purifier->purify($content);

    $stmt = $pdo->prepare("INSERT INTO guides (user_id, title, content, status)
        VALUES (?,?,?, 'pending')");
    $stmt->execute([$userId, $title, $content]);

    $success = "Your guide has been submitted and is pending review.";
  } else {
    $error = "Please fill in all fields.";
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Submit Guide</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Summernote CSS -->
  <link href="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.css" rel="stylesheet">
  <!-- Theme CSS -->
  <link rel="stylesheet" href="/theme.css">
</head>

<body>
  <?php include __DIR__ . '/../components/nav.php'; ?>

  <main class="container mt-5">
    <div class="card shadow-sm p-4">
      <h2 class="header-text mb-4">Submit a Guide</h2>

      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <form method="POST" id="submit-guide">
        <div class="mb-3">
          <label for="title" class="form-label">Guide Title</label>
          <input type="text" name="title" id="title" class="form-control" placeholder="Enter title" required>
        </div>

        <div class="mb-3">
          <label for="content" class="form-label">Guide Content</label>
          <textarea id="content" name="content" class="form-control" rows="8" placeholder="Write your guide here..."
            required></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Submit Guide</button>
      </form>
    </div>
  </main>

  <!-- JS -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/summernote@0.9.0/dist/summernote-bs4.min.js"></script>
  <script>
    $(document).ready(function () {
      // Initialize Summernote
      $('#content').summernote({
        height: 400,
        toolbar: [
          ['style', ['bold', 'italic', 'underline', 'clear']],
          ['font', ['strikethrough', 'superscript', 'subscript']],
          ['fontsize', ['fontsize']],
          ['color', ['color']],
          ['para', ['ul', 'ol', 'paragraph']],
          ['insert', ['link', 'picture', 'video']],
          ['view', ['fullscreen', 'codeview', 'help']]
        ],
        callbacks: {
          onImageUpload: function (files) {
            for (let i = 0; i < files.length; i++) {
              uploadImage(files[i]);
            }
          },
          onInit: function () {
            // Limit image width when editing
            $('.note-editable img').css({
              'max-width': '100%',
              'height': 'auto'
            });
          }
        }
      });

      // Image upload handler
      function uploadImage(file) {
        const MAX_SIZE_MB = 10;

        if (file.size > MAX_SIZE_MB * 1024 * 1024) {
          alert(`File too large. Maximum size is ${MAX_SIZE_MB} MB.`);
          return;
        }

        const data = new FormData();
        data.append('file', file);

        fetch('upload_image.php', {
          method: 'POST',
          body: data
        })
          .then(async res => {
            let json;
            try {
              json = await res.json();
            } catch {
              throw { error: `Server returned invalid response (HTTP ${res.status}) File may be too big. Max 10 MB` };
            }

            if (!res.ok) throw json; // If HTTP code is error, throw JSON error

            return json;
          })
          .then(response => {
            if (response.location) {
              $('#content').summernote('insertImage', response.location, function ($image) {
                $image.css('max-width', '100%').css('height', 'auto');
              });
            } else if (response.error) {
              alert("Upload failed: " + response.error);
            }
          })
          .catch(err => {
            console.error(err);
            let msg = err.error || 'Unknown error';
            alert('Upload error: ' + msg);
          });
      }

      // Force sync content before submitting
      $('form').on('submit', function () {
        const code = $('#content').summernote('code');
        $('#content').val(code);
      });


      $(document).on('shown.bs.modal', '.note-modal', function () {
        $(this).find('[data-dismiss="modal"]').each(function () {
          $(this)
            .attr('data-bs-dismiss', 'modal')
            .removeAttr('data-dismiss');
        });
      });
    });
  </script>
</body>

</html>