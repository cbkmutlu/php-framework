<!DOCTYPE html>
<html>
<head>
    <title>test.php</title>
    <meta charset="UTF-8">
</head>
<body>

<?php foreach ($benchmark as $key => $value): ?>
<p><?= $key ?>: <?= $value ?></p>
<?php endforeach; ?>

</body>
</html>