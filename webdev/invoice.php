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
        }
        td {
            background-color: #bbcad6;
        }
        .filters {
            margin-block: 10px;
        }
    </style>
    <script>
        // Получаем записи из БД через серверный API с учётом выбранных фильтров.
        function loadData() {
            document.getElementById('calcs').innerHTML =
                '<tr><td colspan="5">Загрузка...</td></tr>';

            const date_from = document.getElementById('date_from').value;
            const date_to = document.getElementById('date_to').value;
            const z_max = document.getElementById('z_max').value;
            const z_min = document.getElementById('z_min').value;

            const params = new URLSearchParams({
                date_from: date_from,
                date_to: date_to,
                z_max: z_max,
                z_min: z_min
            });

            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'api/invoice_service.php?' + params.toString(), true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    const calcs = JSON.parse(xhr.responseText);
                    showCalcs(calcs);
                } else if (xhr.status === 401) {
                    window.location.href = 'login.html';
                } else {
                    document.getElementById('calcs').innerHTML =
                        '<tr><td colspan="5">Ошибка загрузки</td></tr>';
                    alert('Ошибка ' + xhr.status + ': ' + xhr.responseText);
                }
            };
            xhr.onerror = function() {
                document.getElementById('calcs').innerHTML =
                    '<tr><td colspan="5">Ошибка соединения</td></tr>';
            };
            xhr.send();
        }

        // Показываем строки, которые уже отфильтровала база данных.
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

        function resetFilter() {
            document.getElementById('date_from').value = '';
            document.getElementById('date_to').value = '';
            document.getElementById('z_max').value = '';
            document.getElementById('z_min').value = '';
            loadData();
        }

        window.onload = loadData;
    </script>
</head>
<body>
    <a href="calc(3).php">Вернуться к калькулятору</a>
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
        <button onclick="loadData()">Применить</button>
        <button onclick="resetFilter()">Сбросить</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Время</th>
                <th>x</th>
                <th>y</th>
                <th>Операция</th>
                <th>Результат</th>
            </tr>
        </thead>
        <tbody id="calcs">
            <tr><td colspan="5">Загрузка...</td></tr>
        </tbody>
    </table>
</body>
</html>
