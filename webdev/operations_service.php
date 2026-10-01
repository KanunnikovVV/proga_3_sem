<?php

    session_start();

    // Проверка, может ли пользователь выполнить действие
    if(!isset($_SESSION['name'])) {

        echo("<h2>Вы не вошли в аккаунт! <br /> Через 3 секунды вы будете перенаправлены на страницу логина</h2> <br />");

        echo("<a href='login.html'>Войти в аккаунт</a>");

        echo("<meta http-equiv='refresh' content='3; url=login.html'></meta>");

        die();
    }


    // Подключение к БД
    $host = 'localhost';
    $username = 'root';
    $password_db = '';
    $db = 'calc';


    $mysqli = new mysqli(
        $host,
        $username,
        $password_db,
        $db
    );


    // Обработка ошибки подключения к БД
    if($mysqli->connect_error) {

        die('Ошибка подключения к БД');

    }


    // Запрос для сохранения операции в БД
    $query = "
        INSERT INTO operation
        (login, operation, x, y, z)
        VALUES (?, ?, ?, ?, ?)
    ";


    // Получаем данные
    $login = $_SESSION['login'];

    $operation = $_REQUEST['operation'];

    $x = $_REQUEST['x'];

    $y = $_REQUEST['y'];


    // Начальное значение результата
    $z = 0;


    // Выполнение операции

    if($operation === 'plus') {

        // Сложение
        $z = $x + $y;

    }

    elseif($operation === 'multiply') {

        // Умножение
        $z = $x * $y;

    }

    else {

        // Вычитание
        $z = $x - $y;

    }


    // Подготавливаем SQL-запрос
    $result = $mysqli->prepare($query);


    // Передаем параметры в запрос
    $result->bind_param(
        'ssddd',
        $login,
        $operation,
        $x,
        $y,
        $z
    );


    // Выполняем запрос
    $result->execute();


    // Закрываем запрос
    $result->close();


    // Закрываем подключение к БД
    $mysqli->close();


    // Отправляем результат обратно калькулятору
    echo($z);

?>