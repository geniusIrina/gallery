<?php
$hello = "Добро пожаловать в галерею";
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная</title>
</head>
<body>
    <h1><?=$hello?></h1>
    <nav>
        <ul>
            <li><a href="gallery.php">Галерея</a></li>
            <li><a href="/calc.php">Калькулятор</a><br></li>
        </ul>
    </nav>
</body>
</html>