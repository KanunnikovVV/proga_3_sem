<?php
  session_start();
  // echo $_SESSION['name'];
  if(!isset($_SESSION['name'])) {
    echo("<h2>Вы не вошли в аккаунт! <br /> Через 3 секунды вы будете перенаправлены на страницу логина</h2> <br />");
    echo("<a href='login.html'>Войти в аккаунт</a>");
    // Правильный вариант
    // header('Location: login.html');
    // Ещё одинн способ
    echo("<meta http-equiv='refresh' content='3; url=login.html'></meta>");
    die();
  }
?>

<!doctype html>
<html lang="ru">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Калькулятор</title>

    <style>
      label {
        font-size: 18px;
        text-align: right;
        width: 90%;
      }

      input {
        width: 90%;
        margin-bottom: 10px;
        background-color: #ffffff;
        border: 1px black solid;
        padding-block: 5px;
      }

      .number {
      }

      #z {
      }

      .field {
        display: flex;
        flex-direction: column;
        width: 200px;
      }

      button {
        width: 180px;
        padding: 5px;
        background-color: #52ccff;
        border: none;
        border-radius: 5px;
      }
    </style>

    <script>
      // Отправляем вычисление на сервер: он считает результат и сохраняет его в MySQL.
      function saveOperation(operation, x, y) {
        if (!Number.isFinite(x) || !Number.isFinite(y)) {
          alert('Введите корректные числа X и Y');
          document.getElementById('z').value = '';
          return;
        }

        const params = new URLSearchParams({ operation: operation, x: x, y: y });

        fetch('api/operations_service.php', {
          method: 'POST',
          body: params
        })
        .then((response) => {
          if (response.status === 401) {
            window.location.href = 'login.html';
            throw new Error('Сессия авторизации истекла');
          }
          if (!response.ok) {
            throw new Error('Ошибка сервера: ' + response.status);
          }
          return response.text();
        })
        .then((result) => {
          const value = result.trim();
          if (value === '' || !Number.isFinite(Number(value))) {
            throw new Error('Сервер вернул некорректный результат');
          }
          document.getElementById('z').value = value;
        })
        .catch((error) => {
          console.error(error);
          document.getElementById('z').value = '';
          alert('Не удалось сохранить операцию: ' + error.message);
        });
      }

      function calculateSum() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        saveOperation('plus', x, y);
      }

      function calculateMinus() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        saveOperation('minus', x, y);
      }

      function calculateMultiply() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        saveOperation('multiply', x, y);
      }
    </script>
  </head>

  <body>
    <a href='logout.php'>Выйти из системы</a>
    <a href='invoice.php'>История операций</a>

    <h1>Калькулятор</h1>

    <div class="field">
      <label for="x">X</label>
      <input class="number" id="x" />
    </div>

    <div class="field">
      <label for="y">Y</label>
      <input class="number" id="y" />
    </div>

    <button onclick="calculateSum()">+</button>

    <button onclick="calculateMinus()">-</button>

    <button onclick="calculateMultiply()">*</button>

    <div class="field">
      <label for="z">Z</label>
      <input id="z" />
    </div>
  </body>
</html>