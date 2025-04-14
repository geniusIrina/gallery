<?php
$message = $_GET["message"] ?? null;

$messages = [
    'ok' => 'Файл успешно загружен.',
    'error_image' => 'Ошибка: можно загружать только изображения (jpg, png, gif, webp).',
    'error_size' => 'Ошибка: размер файла не должен превышать 5 МБ.',
    'error_extension' => 'Ошибка: загрузка php-файлов запрещена!',
    'error_upload' => 'Ошибка: произошла ошибка при загрузке вашего файла.'
];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image'])) {
    $targetDir = "images/";
    $targetFile = $targetDir . basename($_FILES['image']['name']);
    $uploadOk = 1;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        header('Location: gallery.php?message=error_upload');
        exit;
    }

    if (getimagesize($_FILES['image']['tmp_name']) === false) {
        header('Location: gallery.php?message=error_image');
        exit;
    }

    if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
        header('Location: gallery.php?message=error_size');
        exit;
    }

    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
        header('Location: gallery.php?message=error_extension');
        exit;
    }

    if (file_exists($targetFile)) {
        header('Location: gallery.php?message=error_extension');
        exit;
    }

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        header('Location: gallery.php?message=ok');
        exit;
    } else {
        header('Location: gallery.php?message=error_upload');
        exit;
    }
}

$images = array_diff(scandir('images'), ['.', '..']);

function createThumbnailImage($filepath, $thumb_width) {
    if (!file_exists($filepath)) {
        return '';
    }

    $image_info = getimagesize($filepath);
    if (!$image_info) {
        return '';
    }

    $width = $image_info[0];
    $height = $image_info[1];
    $thumb_height = ($height / $width) * $thumb_width;

    $thumb = imagecreatetruecolor($thumb_width, $thumb_height);
    $img = null;

    switch ($image_info['mime']) {
        case 'image/jpeg':
            $img = imagecreatefromjpeg($filepath);
            break;
        case 'image/png':
            $img = imagecreatefrompng($filepath);
            break;
        case 'image/gif':
            $img = imagecreatefromgif($filepath);
            break;
        case 'image/webp':
            $img = imagecreatefromwebp($filepath);
            break;
        default:
            return '';
    }

    if (!$img) {
        return '';
    }

    imagecopyresampled($thumb, $img, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
    
    ob_start();
    imagejpeg($thumb);
    $imageData = ob_get_contents();
    ob_end_clean();

    imagedestroy($img);
    imagedestroy($thumb);

    return "data:image/jpeg;base64," . base64_encode($imageData);
}

?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Галерея изображений</title>
</head>
<body>
    <?php include 'menu.php'; ?>

    <h2>Галерея изображений</h2>

    <?php if ($message && isset($messages[$message])): ?>
        <div class="message">
            <?= htmlspecialchars($messages[$message]); ?>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label for="image">Выберите изображение для загрузки:</label>
        <input type="file" name="image" id="image" required>
        <button type="submit">Загрузить</button>
    </form>

    <div class="image-gallery">
        <?php foreach ($images as $image): ?>
            <?php
                $path = "images/" . $image;

                if (file_exists($path)): ?>
                    <a href="<?= $path; ?>" target="_blank">
                        <img src="<?= createThumbnailImage($path, 250); ?>" alt="<?= htmlspecialchars($image); ?>" width="250">
                    </a>
                <?php endif; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>