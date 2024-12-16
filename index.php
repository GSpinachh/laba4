<?php
// Подключение к базе данных
$pdo = new PDO("mysql:host=localhost;dbname=user", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Получение всех категорий
$categoriesQuery = $pdo->query("SELECT * FROM categories WHERE parent_id IS NULL");
$categories = $categoriesQuery->fetchAll(PDO::FETCH_ASSOC);

// Получение всех товаров
$productsQuery = $pdo->query("SELECT * FROM products");
$products = $productsQuery->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>

    <!-- Добавим новогодний стиль -->
    <style>
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

        nav ul {
            list-style-type: none;
            padding: 0;
            text-align: center;
            margin: 20px 0;
        }

        nav ul li {
            display: inline-block;
            margin: 10px;
        }

        nav ul li a {
            text-decoration: none;
            color: #ffcd00;
            font-size: 18px;
            padding: 10px 20px;
            background-color: #3e5b77;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        nav ul li a:hover {
            background-color: #ffcd00;
            color: #2d3b5e;
        }

        .products {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
            padding: 30px;
        }

        .product-card {
            background-color: #3e5b77;
            padding: 20px;
            width: 250px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }

        .product-card:hover {
            transform: translateY(-10px);
        }

        .product-card img {
            width: 100%;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-card h2 {
            font-size: 20px;
            color: #ffcd00;
        }

        .product-card p {
            font-size: 16px;
            color: #ccc;
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

        /* Эффект для снежинок */
        @keyframes fall {
            0% {
                transform: translateY(0);
            }
            100% {
                transform: translateY(100vh);
            }
        }
    </style>

</head>
<body>

    <h1>Каталог товаров</h1>

    <!-- Навигация по категориям -->
    <nav>
        <ul>
            <?php foreach ($categories as $category): ?>
                <li><a href="index.php?category_id=<?= $category['id'] ?>"><?= htmlspecialchars($category['name']) ?></a></li>
            <?php endforeach; ?>
        </ul>
    </nav>

    <!-- Список товаров -->
    <div class="products">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <h2><a href="product.php?id=<?= $product['id'] ?>" style="color: white;"><?= htmlspecialchars($product['name']) ?></a></h2>
                <p><?= htmlspecialchars($product['description']) ?></p>
                <p>Цена: <?= $product['price'] ?> руб.</p>
                <?php
                // Получаем основное изображение
                $sqlMainImage = "SELECT image_name FROM product_images WHERE product_id = ? AND is_main = 1";
                $stmtMainImage = $pdo->prepare($sqlMainImage);
                $stmtMainImage->execute([$product['id']]);
                $mainImage = $stmtMainImage->fetchColumn();
                ?>
                <img src="images/<?= htmlspecialchars($mainImage) ?>" alt="Основное изображение">
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Снегопад -->
    <div id="snowflakes"></div>

    <script>
        // Создание снежинок для эффекта снегопада
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
