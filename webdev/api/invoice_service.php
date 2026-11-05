<?php
    session_start();
    require __DIR__ . '/../config.php';

    if (!isset($_SESSION['name']) || !isset($_SESSION['login'])) {
        http_response_code(401);
        exit('Вы не вошли в аккаунт (или в сессии отсутствует login)');
    }

    // Настройки БД вынесены в config.php, как в файлах преподавателя.
    $host = $db_host;
    $username = $db_username;
    $password_db = $db_password;
    $db = $db_name;

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

    // Добавлено с занятия: число операций на странице выбирается пользователем.
    $limit = $app_limit;
    if (isset($_GET['limit']) && in_array($_GET['limit'], ['5', '10', '20'], true)) {
        $limit = (int) $_GET['limit'];
    }
    $page = 1;
    if (isset($_GET['page'])) {
        $page = max(1, (int) $_GET['page']);
    }
    $offset = ($page - 1) * $limit;

    // Исходный запрос и фильтры из твоей версии.
    // Фильтрация остаётся на стороне MySQL.
    $where = ' FROM operation WHERE login = ?';
    $types = 's';
    $params = [$login];

    if (isset($_GET['date_from']) && $_GET['date_from'] !== '') {
        $where .= ' AND operation_date >= ?';
        $types .= 's';
        $params[] = $_GET['date_from'];
    }
    if (isset($_GET['date_to']) && $_GET['date_to'] !== '') {
        // Включаем в выборку весь конечный день.
        $where .= ' AND operation_date < DATE_ADD(?, INTERVAL 1 DAY)';
        $types .= 's';
        $params[] = $_GET['date_to'];
    }

    if (isset($_GET['z_max']) && $_GET['z_max'] !== '') {
        if (!is_numeric($_GET['z_max'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не больше»');
        }
        $where .= ' AND z <= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_max'];
    }

    if (isset($_GET['z_min']) && $_GET['z_min'] !== '') {
        if (!is_numeric($_GET['z_min'])) {
            http_response_code(400);
            exit('Некорректное значение «Результат не меньше»');
        }
        $where .= ' AND z >= ?';
        $types .= 'd';
        $params[] = (float) $_GET['z_min'];
    }

    // Добавлено с занятия: количество записей и стоимость с учётом ВСЕХ фильтров.
    // Используем те же параметры WHERE, что и для самой таблицы.
    $countQuery = 'SELECT COUNT(*) AS total' . $where;
    $countResult = $mysqli->prepare($countQuery);
    if (!$countResult) {
        http_response_code(500);
        exit('Ошибка подготовки запроса подсчёта');
    }
    $countResult->bind_param($types, ...$params);
    $countResult->execute();
    $total = (int) $countResult->get_result()->fetch_assoc()['total'];
    $countResult->close();
    $totalPages = max(1, (int) ceil($total / $limit));
    $sum = $total * $price_per_op;

    // Прежняя сортировка и пагинация сохраняются.
    // Имена столбцов и направление безопасны, поскольку проверены выше.
    $query = 'SELECT operation_date, x, y, z, operation' . $where;
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

    // Для прошлого ДЗ конец списка по-прежнему определяется по пустому calcs: [].
    // В новом формате дополнительно возвращаются показатели, как у преподавателя.
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'calcs' => $calcs,
        'total' => $total,
        'totalPages' => $totalPages,
        'sum' => $sum,
    ], JSON_UNESCAPED_UNICODE);
