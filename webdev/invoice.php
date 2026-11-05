<?php
    session_start();

    // Страница доступна только после входа в аккаунт.
    if (!isset($_SESSION['name'])) {
        header('Location: login.html');
        exit;
    }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Счёт за услуги</title>
    <style>
        table {
            border-collapse: collapse;
        }
        td, th {
            border: 1px solid black;
            text-align: center;
            padding: 5px;
        }
        th {
            background-color: #52ccff;
            cursor: pointer; /* Добавлено: сортировка по нажатию на заголовок. */
        }
        th:hover {
            background-color: #689cfc;
        }
        th.asc, th.desc {
            background-color: #fcf268;
        }
        th.asc::after {
            content: ' ↑';
        }
        th.desc::after {
            content: ' ↓';
        }
        td {
            background-color: #bbcad6;
        }
        .filters {
            margin-block: 10px;
        }

        /* CSS-анимации и оформление из бонусного задания. */
        table {
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 8px;
            box-shadow: 0 4px 14px rgba(34, 38, 54, 0.14);
            margin-block: 14px;
        }

        th, td {
            border: 1px solid white;
            padding: 8px 12px;
        }

        th {
            background-color: #ebafd7;
            transition: background-color 0.3s ease;
        }

        th.asc, th.desc {
            background-color: #fcf268;
        }

        tbody td {
            background-color: #dadada;
            transition: background-color 0.25s ease;
        }

        tbody tr:nth-child(even) td {
            background-color: #eeeeee;
        }

        tbody tr:hover td {
            background-color: #dce7ff;
        }

        /* Новые строки мягко появляются после загрузки. */
        tbody tr {
            animation: rowAppear 0.35s ease both;
        }

        @keyframes rowAppear {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        button {
            padding: 6px 12px;
            border: 1px solid #9095a4;
            border-radius: 5px;
            background-color: #f4f4f4;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover:not(:disabled) {
            background-color: #dcd4ed;
        }

        button:active:not(:disabled) {
            transform: scale(0.95);
        }

        button:disabled {
            opacity: 0.45;
            cursor: not-allowed;
        }

        input, select {
            padding: 5px;
            border: 1px solid #9095a4;
            border-radius: 5px;
        }

        #page-info {
            margin-inline: 8px;
        }

        /* Вращающийся индикатор ожидания из примера преподавателя. */
        .spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 3px solid #dddddd;
            border-top-color: #333333;
            border-radius: 50%;
            vertical-align: middle;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        @media (prefers-reduced-motion: reduce) {
            th, td, button { transition: none; }
            tbody tr, .spinner { animation: none; }
        }
    </style>
    <script>
        // Добавлено на занятии: параметры сортировки и номер страницы.
        let sort = 'operation_date';
        let dir = 'DESC';
        let page = 1;

        // Добавлено для домашнего задания: номер последней страницы становится
        // известен только после того, как сервер вернул пустой массив [].
        let lastPage = null;
        let isLoading = false;
        let requestNumber = 0;

        // Добавлено с занятия: можно выбрать 5, 10 или 20 записей.
        let limit = 5;
        let totalPages = 1;

        function sortBy(column) {
            if (sort === column) {
                dir = (dir === 'ASC') ? 'DESC' : 'ASC';
            } else {
                sort = column;
                dir = 'ASC';
            }
            // После смены сортировки снова начинаем с первой страницы.
            page = 1;
            lastPage = null;
            showSort();
            loadData();
        }

        function showSort() {
            document.querySelectorAll('th').forEach(function(th) {
                th.className = '';
            });
            document.getElementById('header_' + sort).className =
                (dir === 'ASC') ? 'asc' : 'desc';
        }

        function prevPage() {
            if (isLoading || page <= 1) {
                return;
            }
            page = page - 1;
            loadData();
        }

        function nextPage() {
            if (isLoading || (lastPage !== null && page >= lastPage)) {
                return;
            }
            page = page + 1;
            loadData();
        }

        function showPages() {
            document.getElementById('page-info').textContent = `Страница ${page} из ${totalPages}`;

            // Домашнее задание: кнопки нельзя нажимать за границами списка.
            document.getElementById('prev').disabled = isLoading || page === 1;
            document.getElementById('next').disabled =
                isLoading || (lastPage !== null && page >= lastPage);
        }

        // Получаем записи из БД через API с учётом фильтров, сортировки и страницы.
        function loadData() {
            // Номер запроса позволяет игнорировать запоздавшие ответы, когда
            // пользователь быстро меняет сортировку или фильтры.
            const currentRequest = ++requestNumber;
            isLoading = true;
            showPages();
            document.getElementById('calcs').innerHTML =
                '<tr><td colspan="5"><span class="spinner"></span> Идёт загрузка...</td></tr>';

            // Сохраняем все четыре фильтра из твоей старой версии.
            const date_from = document.getElementById('date_from').value;
            const date_to = document.getElementById('date_to').value;
            const z_max = document.getElementById('z_max').value;
            const z_min = document.getElementById('z_min').value;

            const params = new URLSearchParams({
                date_from: date_from,
                date_to: date_to,
                z_max: z_max,
                z_min: z_min,
                sort: sort,
                dir: dir,
                page: page,
                limit: limit
            });

            // Используем fetch(), как в файле преподавателя.
            fetch('api/invoice_service.php?' + params.toString())
                .then(function(response) {
                    if (response.status === 401) {
                        window.location.href = 'login.html';
                        return null;
                    }
                    if (!response.ok) {
                        throw new Error('Ошибка ' + response.status + ': ' + response.statusText);
                    }
                    return response.json();
                })
                .then(function(data) {
                    if (currentRequest !== requestNumber || data === null) {
                        return;
                    }

                    // Новый ответ сервера: массив операций, общее число, страницы и стоимость.
                    const calcs = data.calcs;
                    totalPages = data.totalPages;
                    const totalElement = document.getElementById('total');
                    totalElement.textContent = `Всего операций: ${data.total}. `;
                    const sumElement = document.createElement('strong');
                    sumElement.textContent = `К оплате: ${data.sum} руб.`;
                    totalElement.appendChild(sumElement);

                    // Домашнее задание: пустой массив — признак, что дальше
                    // страниц нет. Возвращаемся на предыдущую страницу.
                    if (calcs.length === 0) {
                        if (page > 1) {
                            lastPage = page - 1;
                            page = page - 1;
                            loadData();
                            return;
                        }
                        // Нет операций даже на первой странице.
                        lastPage = 1;
                    }

                    showCalcs(calcs);
                    isLoading = false;
                    showPages();
                })
                .catch(function(error) {
                    if (currentRequest !== requestNumber) {
                        return;
                    }
                    console.error(error);
                    document.getElementById('calcs').innerHTML =
                        '<tr><td colspan="5">Ошибка загрузки данных</td></tr>';
                    isLoading = false;
                    showPages();
                });
        }

        // Сохраняем безопасный вывод строк из твоей старой версии.
        function showCalcs(calcs) {
            const tableBody = document.getElementById('calcs');
            tableBody.innerHTML = '';

            if (calcs.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="5">Ничего не найдено</td></tr>';
                return;
            }

            calcs.forEach(function(row, index) {
                const tr = document.createElement('tr');
                tr.style.animationDelay = (index * 0.05) + 's';
                [row.operation_date, row.x, row.y, row.operation, row.z].forEach(function(value) {
                    const td = document.createElement('td');
                    td.textContent = value;
                    tr.appendChild(td);
                });
                tableBody.appendChild(tr);
            });
        }

        function applyFilter() {
            // После изменения фильтров возвращаемся на страницу 1.
            page = 1;
            lastPage = null;
            loadData();
        }

        function resetFilter() {
            document.getElementById('date_from').value = '';
            document.getElementById('date_to').value = '';
            document.getElementById('z_max').value = '';
            document.getElementById('z_min').value = '';
            applyFilter();
        }

        function changeLimit() {
            limit = Number(document.getElementById('limit').value);
            page = 1;
            lastPage = null;
            loadData();
        }

        window.onload = function() {
            showSort();
            loadData();
        };
    </script>
</head>
<body>
    <a href="calc.php">Вернуться к калькулятору</a>
    <h1>За прошедший период вы совершили следующие операции</h1>

    <div class="filters">
        <h3>Фильтры</h3>
        <label for="date_from">С:</label>
        <input type="date" id="date_from">
        <label for="date_to">По:</label>
        <input type="date" id="date_to">
        <label for="z_max">Результат не больше:</label>
        <input type="number" id="z_max" step="any">
        <label for="z_min">Результат не меньше:</label>
        <input type="number" id="z_min" step="any">
        <button onclick="applyFilter()">Применить</button>
        <button onclick="resetFilter()">Сбросить</button>
    </div>

    <table>
        <thead>
            <tr>
                <th id="header_operation_date" onclick="sortBy('operation_date')">Время</th>
                <th id="header_x" onclick="sortBy('x')">x</th>
                <th id="header_y" onclick="sortBy('y')">y</th>
                <th id="header_operation" onclick="sortBy('operation')">Операция</th>
                <th id="header_z" onclick="sortBy('z')">Результат</th>
            </tr>
        </thead>
        <tbody id="calcs">
            <tr><td colspan="5">Загрузка...</td></tr>
        </tbody>
    </table>

    <!-- Добавлено на занятии и доработано по домашнему заданию. -->
    <div>
        <button id="prev" onclick="prevPage()" disabled>Назад</button>
        <span id="page-info">Страница 1</span>
        <button id="next" onclick="nextPage()" disabled>Вперёд</button>
        <select id="limit" onchange="changeLimit()" aria-label="Операций на странице">
            <option value="5">5 элементов</option>
            <option value="10">10 элементов</option>
            <option value="20">20 элементов</option>
        </select>
    </div>
    <p id="total"></p>
</body>
</html>
