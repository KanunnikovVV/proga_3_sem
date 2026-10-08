<?php
    session_start();

    // Проверка может ли пользователь выполнить действие
    if(!isset($_SESSION['name'])) {
        echo("<h2>Вы не вошли в аккаунт! <br /> Через 3 секунды вы будете перенаправлены на страницу логина</h2> <br />");
        echo("<a href='login.html'>Войти в аккаунт</a>");
        echo("<meta http-equiv='refresh' content='3; url=login.html'></meta>");
        die();
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
    $operation = $_REQUEST['operation'];
    $x = $_REQUEST['x'];
    $y = $_REQUEST['y'];

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
    $result->execute();

    $result->close();
    $mysqli->close();

    // Направить результат на клиент для вывода на интерфейсе

    echo($z);
?>