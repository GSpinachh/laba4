<?php
// Простая форма авторизации администратора
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Простая проверка логина и пароля
    if ($_POST['username'] == 'admin' && $_POST['password'] == 'admin') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: add_product.php");
        exit;
    } else {
        echo "Неверный логин или пароль.";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация</title>
</head>
<body>

    <h1>Вход для администратора</h1>

    <form action="login.php" method="POST">
        <label for="username">Логин:</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Пароль:</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Войти</button>
    </form>

</body>
</html>
