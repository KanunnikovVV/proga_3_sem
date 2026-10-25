<?php
    session_start();

    // Проверка может ли пользователь выполнить действие
    if(!isset($_SESSION['name']) || !isset($_SESSION['login'])) {
        http_response_code(401);
        exit('Вы не вошли в аккаунт');
    }

    // Подключиться к БД
    $host = 'localhost';
    $username = 'root';
    $password_db = '';
    $db = 'calc';

    $mysqli = new mysqli($host, $username, $password_db, $db);

    // Обработка ошибки подключения к БД
    if($mysqli->connect_error) {
        die('Ошибка подключения к БД');
    }

    // Подготовить запрос

    $query = "INSERT INTO operation (login, operation, x, y, z) VALUES(?, ?, ?, ?, ?)";

    $login = $_SESSION['login'];
    $operation = $_REQUEST['operation'] ?? '';
    $x = $_REQUEST['x'] ?? null;
    $y = $_REQUEST['y'] ?? null;

    // Разрешаем только операции калькулятора и числовые аргументы.
    if(!in_array($operation, ['plus', 'minus', 'multiply'], true)
       || !is_numeric($x) || !is_numeric($y)) {
        http_response_code(400);
        exit('Некорректные данные операции');
    }

    $x = (float)$x;
    $y = (float)$y;

    $z = 0;

    // Провести вычисления

    if($operation === 'plus'){
        $z = $x + $y;
    }
    elseif($operation === 'multiply'){
        $z = $x * $y;
    }
    else {
        $z = $x - $y;
    }

    // Выполнение запроса
    // Работа по отправке запроса
    // Сообщаем mysqli какой запрос выполнить
    $result = $mysqli->prepare($query);

    // Прокидыаю внутрь запроса параметры вместо ?

    $result->bind_param('ssddd', $login, $operation, $x, $y, $z);
    // В случае ошибки записи PHP вернёт ошибку, а не ложный результат.
    if (!$result->execute()) {
        http_response_code(500);
        exit('Не удалось сохранить операцию');
    }

    $result->close();
    $mysqli->close();

    // Направить результат на клиент для вывода на интерфейсе

    echo($z);
?>