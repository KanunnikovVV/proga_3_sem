<?php
    session_start();

    if (!isset($_SESSION['name']) || !isset($_SESSION['login'])) {
        http_response_code(401);
        exit('Вы не вошли в аккаунт (или в сессии отсутствует login)');
    }

    // Настройки БД — по образцу проекта знакомого.
    $host = 'localhost';
    $username = 'root';
    $password_db = '';
    $db = 'calc';

    $mysqli = new mysqli($host, $username, $password_db, $db);
    if ($mysqli->connect_error) {
        http_response_code(500);
        exit('Ошибка подключения к БД');
    }
    $mysqli->set_charset('utf8mb4');

    $login = $_SESSION['login'];

    // SQL-запрос строится на сервере: фильтры выполняет MySQL, не JavaScript.
    $query = 'SELECT operation_date, x, y, z, operation
              FROM operation
              WHERE login = ?';
    $types = 's';
    $params = [$login];

    // Существующие фильтры по периоду.
    if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
        $query .= ' AND operation_date >= ?';
        $types .= 's';
        $params[] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
        // Включаем в выборку весь конечный день, а не только полночь.
        $query .= ' AND operation_date < DATE_ADD(?, INTERVAL 1 DAY)';
        $types .= 's';
        $params[] = $_GET['date_to'];
    }

    // Новый фильтр «Результат не больше»: z <= введённого значения.
    if (isset($_GET['z_max']) && $_GET['z_max'] !== '') {
        if (!is_numeric($_GET['z_max'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не больше»');
        }
        $query .= ' AND z <= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_max'];
    }

    // Новый фильтр «Результат не меньше»: z >= введённого значения.
    if (isset($_GET['z_min']) && $_GET['z_min'] !== '') {
        if (!is_numeric($_GET['z_min'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не меньше»');
        }
        $query .= ' AND z >= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_min'];
    }

    $query .= ' ORDER BY operation_date';

    $result = $mysqli->prepare($query);
    if (!$result) {
        http_response_code(500);
        exit('Ошибка подготовки SQL-запроса');
    }
    $result->bind_param($types, ...$params);
    $result->execute();

    $calcs_db = $result->get_result();
    $calcs = [];
    while ($row = $calcs_db->fetch_assoc()) {
        $calcs[] = $row;
    }

    $result->close();
    $mysqli->close();

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($calcs, JSON_UNESCAPED_UNICODE);
