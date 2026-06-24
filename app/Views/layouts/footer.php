<script>
window.APP_CURRENT_USER = <?php echo json_encode($currentUser); ?>;
</script>
<?php
$resolvedScriptFile = (string)($scriptFile ?? 'public/assets/js/app.js');
$scriptVersion = static function (string $path): string {
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
<script src="<?php echo htmlspecialchars($resolvedScriptFile, ENT_QUOTES, 'UTF-8'); ?>?v=<?php echo $scriptVersion($resolvedScriptFile); ?>"></script>
</body>
</html>
