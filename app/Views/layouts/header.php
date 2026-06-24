<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($title ?? 'FitTrack Studio', ENT_QUOTES, 'UTF-8'); ?></title>
    <?php
    $assetVersion = static function (string $path): string {
        $fullPath = dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $path);
        if (is_file($fullPath)) {
            $mtime = filemtime($fullPath);
            if ($mtime !== false) {
                return (string)$mtime;
            }
        }

        return (string)time();
    };
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="public/assets/css/style.css?v=<?php echo $assetVersion('public/assets/css/style.css'); ?>" />
    <?php if (!empty($extraStyleFile)): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars((string)$extraStyleFile, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $assetVersion((string)$extraStyleFile); ?>" />
    <?php endif; ?>
    <?php foreach (($extraStyleFiles ?? []) as $styleFile): ?>
        <link rel="stylesheet" href="<?php echo htmlspecialchars((string)$styleFile, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $assetVersion((string)$styleFile); ?>" />
    <?php endforeach; ?>
</head>
<body class="<?php echo htmlspecialchars($bodyClass ?? '', ENT_QUOTES, 'UTF-8'); ?>">
