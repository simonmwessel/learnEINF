<?php

use Random\RandomException;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED & ~E_WARNING);

session_start();

$defaultTheme = 'dark';
$theme = $_SESSION['theme'] ?? $_COOKIE['theme'] ?? $defaultTheme;

if (!isset($_SESSION['theme']) && !isset($_COOKIE['theme'])) {
    $theme = isset($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'])
        ? strtolower($_SERVER['HTTP_SEC_CH_PREFERS_COLOR_SCHEME'])
        : $defaultTheme;
}

// Handle Theme Update Request
if ($_SERVER['REQUEST_URI'] === '/update-theme' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require __DIR__.'/../src/Controllers/ThemeHandler.php';
    exit;
}

// CSRF Token
if (empty($_SESSION['csrf_token'])) {
    try {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (RandomException $e) {
        error_log('Failed to generate CSRF token: ' . $e->getMessage());
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    }
}

function getCsrfToken() {
    return $_SESSION['csrf_token'];
}

// Define path to JSON data
$dataFile = __DIR__ . '/../data/content.json';
if (!file_exists($dataFile)) {
    die('Error: Content not found.');
}

$jsonContent = file_get_contents($dataFile);
$content = json_decode($jsonContent, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    die('Error: Invalid JSON content.');
}

// Composer autoload
require __DIR__ . '/../vendor/autoload.php';

// Use Parsedown Extra for tables, etc.
$Parsedown = new ParsedownExtra();

// Convert Markdown to HTML, add .table classes, etc.
$processContent = function($text) use ($Parsedown) {
    return $Parsedown
        ? str_replace('<table>', '<table class="table table-sm table-bordered table-striped table-hover w-auto">', $Parsedown->text($text))
        : nl2br(htmlspecialchars($text));
};

?>
<!DOCTYPE html>
<html lang="de" class="dark-mode">
<head>
    <title>EINF</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#1c1e21">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="<?php echo htmlspecialchars(getCsrfToken()); ?>">

    <link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
    <link rel="manifest" href="assets/favicon/site.webmanifest">

    <link rel="stylesheet" href="assets/css/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/bootstrap-icons/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body class="<?php echo $theme === 'dark' ? 'dark-mode' : ''; ?> d-flex flex-column min-vh-100">

<!-- HEADER -->
<header class="bg-primary text-white text-center p-4 position-relative">
    <h1 class="mb-0">Einführung in die Informatik</h1>
    <!-- Dark Mode Toggle Button -->
    <button class="dark-mode-toggle" id="darkModeToggle">Toggle Theme</button>
</header>

<!-- MAIN CONTENT -->
<main class="container border-1 p-4 flex-fill">
    <?php if (!empty($content['categories'])): ?>
        <div class="accordion d-flex flex-column gap-3" id="categoryAccordion">
            <?php foreach ($content['categories'] as $catIndex => $category): ?>
                <?php
                $catId = 'cat-' . $catIndex;
                $catHeading = 'heading-' . $catId;
                $catCollapse = 'collapse-' . $catId;
                ?>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="<?php echo $catHeading; ?>">
                        <button class="accordion-button category-button collapsed"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#<?php echo $catCollapse; ?>"
                                aria-expanded="false"
                                aria-controls="<?php echo $catCollapse; ?>">
                            <?php echo htmlspecialchars($category['title']); ?>
                        </button>
                    </h2>
                    <div id="<?php echo $catCollapse; ?>"
                         class="accordion-collapse collapse"
                         aria-labelledby="<?php echo $catHeading; ?>">
                        <div class="accordion-body p-3">
                            <?php if (!empty($category['questions'])): ?>
                                <div class="accordion d-flex flex-column gap-3" id="questionAccordion-<?php echo $catId; ?>">
                                    <?php foreach ($category['questions'] as $qIndex => $qItem): ?>
                                        <?php
                                        $qId = $catId . '-q-' . $qIndex;
                                        $qHeading = 'heading-' . $qId;
                                        $qCollapse = 'collapse-' . $qId;
                                        ?>
                                        <div class="accordion-item question-item">
                                            <h2 class="accordion-header d-flex align-items-center" id="<?php echo $qHeading; ?>">
                                                <i class="favorite-star mb-0 me-3 ps-3 h5 bi bi-star text-warning"
                                                   style="cursor: pointer;"
                                                   data-qid="<?php echo $qId; ?>"></i>
                                                <button class="accordion-button collapsed flex-grow-1 question-button"
                                                        type="button"
                                                        data-bs-toggle="collapse"
                                                        data-bs-target="#<?php echo $qCollapse; ?>"
                                                        aria-expanded="false"
                                                        aria-controls="<?php echo $qCollapse; ?>">
                                                    <?php echo $processContent($qItem['question']); ?>
                                                </button>
                                            </h2>
                                            <div id="<?php echo $qCollapse; ?>"
                                                 class="accordion-collapse collapse"
                                                 aria-labelledby="<?php echo $qHeading; ?>">
                                                <div class="accordion-body question-body mt-3 mx-3 pb-0 overflow-auto">
                                                    <?php echo $processContent($qItem['answer']); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p>No questions available.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No categories available.</p>
    <?php endif; ?>
</main>


<footer class="bg-dark text-white text-center p-3">
    <p class="mb-0">&copy; Simon Wessel <?php echo date("Y"); ?></p>
</footer>

<script src="assets/js/bootstrap/bootstrap.bundle.min.js"></script>
<script src="assets/js/csrf.js"></script>
<script src="assets/js/favorites.js"></script>
<script src="assets/js/theme.js"></script>
</body>
</html>