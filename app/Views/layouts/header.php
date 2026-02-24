<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($title ?? 'FitTrack Studio', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="public/assets/css/style.css" />
</head>
<body class="<?php echo htmlspecialchars($bodyClass ?? '', ENT_QUOTES, 'UTF-8'); ?>">
