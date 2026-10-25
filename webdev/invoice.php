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
            document.getElementById('page-info').textContent = `Страница ${page}`;

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
                '<tr><td colspan="5">Загрузка...</td></tr>';

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
                page: page
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
                .then(function(calcs) {
                    if (currentRequest !== requestNumber || calcs === null) {
                        return;
                    }

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

            calcs.forEach(function(row) {
                const tr = document.createElement('tr');
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
    </div>
</body>
</html>
