<?php
// Shared <head> for Tabler pages. Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'AAR9SPEED';
?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
<title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') ?> · AAR9SPEED</title>
<link rel="icon" href="img/logobebe.png" type="image/png">
<script>
    // Apply the saved theme before the page paints, so dark mode never flashes white.
    (function () {
        var theme = null;
        try { theme = localStorage.getItem('aar9-theme'); } catch (e) {}
        if (theme !== 'light' && theme !== 'dark') {
            theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        document.documentElement.setAttribute('data-bs-theme', theme);
    })();
</script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=IBM+Plex+Mono:wght@500&display=swap">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@1.6.1/dist/css/tabler.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@3.49.0/dist/tabler-icons.min.css">
<link rel="stylesheet" href="styles/tabler-theme.css">
