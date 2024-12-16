<?php
// Подключаемся к базе данных
$pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получаем ID товара из URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($productId <= 0) {
    die('Неверный ID товара');
}

// Запрос для получения информации о товаре
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$productId]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die('Товар не найден');
}

// Получаем основное изображение
$sqlMainImage = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 1";
$stmtMainImage = $pdo->prepare($sqlMainImage);
$stmtMainImage->execute([$productId]);
$mainImage = $stmtMainImage->fetchColumn();

// Получаем дополнительные изображения
$sqlGalleryImages = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 0";
$stmtGalleryImages = $pdo->prepare($sqlGalleryImages);
$stmtGalleryImages->execute([$productId]);
$galleryImages = $stmtGalleryImages->fetchAll(PDO::FETCH_ASSOC);

// Обработка отправки комментария
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['comment'], $_POST['user_name'])) {
    $comment = htmlspecialchars($_POST['comment']);
    $userName = htmlspecialchars($_POST['user_name']);
    $parentId = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : NULL;

    // Проверка, оставлял ли уже этот пользователь комментарий
    $checkComment = "SELECT COUNT(*) FROM comments WHERE product_id = ? AND user_name = ?";
    $stmtCheck = $pdo->prepare($checkComment);
    $stmtCheck->execute([$productId, $userName]);
    $commentCount = $stmtCheck->fetchColumn();

    if ($commentCount > 0) {
        die('Вы уже оставляли комментарий для этого товара');
    }

    // Добавляем комментарий в базу данных
    $sqlInsertComment = "INSERT INTO comments (product_id, parent_id, user_name, content) VALUES (?, ?, ?, ?)";
    $stmtInsertComment = $pdo->prepare($sqlInsertComment);
    $stmtInsertComment->execute([$productId, $parentId, $userName, $comment]);
}

// Получаем комментарии
$sqlComments = "SELECT * FROM comments WHERE product_id = ? AND parent_id IS NULL ORDER BY id DESC";
$stmtComments = $pdo->prepare($sqlComments);
$stmtComments->execute([$productId]);
$comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

// Функция для отображения комментариев и их ответов
function displayComments($comments, $pdo) {
    foreach ($comments as $comment) {
        echo "<div class='comment'>";
        echo "<strong>" . htmlspecialchars($comment['user_name']) . ":</strong><p>" . nl2br(htmlspecialchars($comment['content'])) . "</p>";

        // Форма для ответа
        echo "<form method='POST'>
                <input type='hidden' name='parent_id' value='" . $comment['id'] . "'>
                <input type='text' name='user_name' placeholder='Ваше имя' required>
                <textarea name='comment' placeholder='Ваш ответ' required></textarea>
                <button type='submit'>Ответить</button>
              </form>";

        // Выводим ответы на комментарий
        $sqlReplies = "SELECT * FROM comments WHERE parent_id = ? ORDER BY id ASC";
        $stmtReplies = $pdo->prepare($sqlReplies);
        $stmtReplies->execute([$comment['id']]);
        $replies = $stmtReplies->fetchAll(PDO::FETCH_ASSOC);

        if ($replies) {
            echo "<div class='replies'>";
            displayComments($replies, $pdo);  // Рекурсивный вызов для отображения ответов
            echo "</div>";
        }

        echo "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Товар - <?= htmlspecialchars($product['name']) ?></title>
    <style>
        /* Новогодний стиль */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #2d3b5e;
            color: white;
            margin: 0;
            padding: 0;
            overflow-x: hidden;
            position: relative;
        }

        h1 {
            text-align: center;
            padding: 30px;
            background-color: #2e8b57;
            margin: 0;
            color: white;
        }

        h2 {
            color: #ffcd00;
            text-align: center;
            padding: 10px;
        }

        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
        }

        .gallery img {
            width: 200px;
            height: auto;
            border-radius: 8px;
            border: 2px solid #fff;
        }

        .comment {
            background-color: #4e6e8e;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .comment strong {
            color: #ffcd00;
        }

        .comment p {
            color: #ddd;
        }

        textarea, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            background-color: #fff;
            color: #333;
        }

        button {
            padding: 10px 20px;
            background-color: #ffcd00;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #ffd700;
        }

        /* Стили для эффекта падающего снега */
        .snowflake {
            position: absolute;
            top: -10px;
            z-index: 9999;
            font-size: 30px;
            color: #fff;
            user-select: none;
            pointer-events: none;
        }

        @keyframes fall {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(100vh);
            }
        }

        #snowflakes {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 9999;
        }

    </style>
</head>
<body>

    <h1><?= htmlspecialchars($product['name']) ?></h1>
    <p><?= htmlspecialchars($product['description']) ?></p>
    <p>Цена: <?= htmlspecialchars($product['price']) ?> руб.</p>

    <!-- Основное изображение -->
    <img src="images/<?= htmlspecialchars($mainImage) ?>" alt="Основное изображение товара">

    <!-- Галерея изображений -->
    <div class="gallery">
        <?php foreach ($galleryImages as $image): ?>
            <img src="images/<?= htmlspecialchars($image['image_name']) ?>" alt="Изображение товара">
        <?php endforeach; ?>
    </div>

    <h2>Комментарии</h2>

    <!-- Форма для добавления комментария -->
    <form method="POST">
        <input type="text" name="user_name" placeholder="Ваше имя" required>
        <textarea name="comment" placeholder="Ваш комментарий" required></textarea>
        <button type="submit">Отправить</button>
    </form>

    <!-- Отображаем комментарии -->
    <?php displayComments($comments, $pdo); ?>

    <!-- Падающие снежинки -->
    <div id="snowflakes"></div>
    <script>
        let snowflakes = document.getElementById('snowflakes');

        for (let i = 0; i < 100; i++) {
            let snowflake = document.createElement('div');
            snowflake.classList.add('snowflake');
            snowflake.innerHTML = '❄';  // Снежинка

            let startPosition = Math.random() * 100;  // Начальная позиция по оси X
            let delay = Math.random() * 5 + 's';  // Задержка начала анимации
            let animationDuration = Math.random() * 5 + 5 + 's';  // Длительность анимации

            snowflake.style.left = `${startPosition}%`;
            snowflake.style.animation = `fall ${animationDuration} linear ${delay} infinite`;

            snowflakes.appendChild(snowflake);
        }
    </script>
</body>
</html>
