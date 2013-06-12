<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title><?php echo h($page_title); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo BASE; ?>/css/default.css">
	<script>var BASE = '<?php echo BASE; ?>';</script>
</head>
<body>
<?php echo $content_for_layout; ?>
<script src="<?php echo BASE; ?>/js/default.js"></script>
</body>
</html>
