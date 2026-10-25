<?php
    session_start();

    if (!isset($_SESSION['name']) || !isset($_SESSION['login'])) {
        http_response_code(401);
        exit('Вы не вошли в аккаунт (или в сессии отсутствует login)');
    }

    // Настройки БД из твоего старого проекта.
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

    // Добавлено на занятии: сортируем только по разрешённым полям.
    $allowed = ['operation_date', 'operation', 'x', 'y', 'z'];
    $sort = 'operation_date';
    $dir = 'DESC';

    if (isset($_GET['sort']) && in_array($_GET['sort'], $allowed, true)) {
        $sort = $_GET['sort'];
    }
    if (isset($_GET['dir']) && $_GET['dir'] === 'ASC') {
        $dir = 'ASC';
    }

    // Добавлено на занятии: отдаём по 5 операций на страницу.
    $limit = 5;
    $page = 1;
    if (isset($_GET['page'])) {
        $page = max(1, (int) $_GET['page']);
    }
    $offset = ($page - 1) * $limit;

    // Исходный запрос и фильтры из твоей версии.
    // Фильтрация остаётся на стороне MySQL.
    $query = 'SELECT operation_date, x, y, z, operation
              FROM operation
              WHERE login = ?';
    $types = 's';
    $params = [$login];

    if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
        $query .= ' AND operation_date >= ?';
        $types .= 's';
        $params[] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
        // Включаем в выборку весь конечный день.
        $query .= ' AND operation_date < DATE_ADD(?, INTERVAL 1 DAY)';
        $types .= 's';
        $params[] = $_GET['date_to'];
    }

    if (isset($_GET['z_max']) && $_GET['z_max'] !== '') {
        if (!is_numeric($_GET['z_max'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не больше»');
        }
        $query .= ' AND z <= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_max'];
    }

    if (isset($_GET['z_min']) && $_GET['z_min'] !== '') {
        if (!is_numeric($_GET['z_min'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не меньше»');
        }
        $query .= ' AND z >= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_min'];
    }

    // Добавлено на занятии: ORDER BY + LIMIT/OFFSET.
    // Имена столбцов и направление безопасны, поскольку проверены выше.
    $query .= " ORDER BY $sort $dir LIMIT ? OFFSET ?";
    $types .= 'ii';
    $params[] = $limit;
    $params[] = $offset;

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

    // Если записей больше нет, сервер возвращает именно [] —
    // этот ответ используется в домашнем задании для блокировки «Вперёд».
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($calcs, JSON_UNESCAPED_UNICODE);
