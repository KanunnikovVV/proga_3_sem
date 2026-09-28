<?php
session_start();

$correctLogin = 'admin';
$correctPassword = '12345';

$login = $_POST['login'] ?? '';
$pwd = $_POST['pwd'] ?? '';

$isCorrect = (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    is_string($login) &&
    is_string($pwd) &&
    $login === $correctLogin &&
    $pwd === $correctPassword
);

if ($isCorrect) {
    session_regenerate_id(true);
    $_SESSION['name'] = $login;
}
?>

<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результат входа</title>
</head>
<body>
    <?php if ($isCorrect): ?>
        <h1>Добро пожаловать, admin!</h1>
        <p>Авторизация работает.</p>
    <?php else: ?>
        <h1>Неправильный логин или пароль!</h1>
        <a href="login.html">Вернуться к форме</a>
    <?php endif; ?>
</body>
</html>