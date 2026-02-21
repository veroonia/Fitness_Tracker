<script>
window.APP_CURRENT_USER = <?php echo json_encode($currentUser); ?>;
</script>
<script src="<?php echo htmlspecialchars($scriptFile ?? 'public/assets/js/app.js', ENT_QUOTES, 'UTF-8'); ?>"></script>
</body>
</html>
