<?php
  session_start();

  // Проверяем, вошел ли пользователь в аккаунт
  if(!isset($_SESSION['name'])) {
    echo("<h2>Вы не вошли в аккаунт! <br /> Через 3 секунды вы будете перенаправлены на страницу логина</h2> <br />");
    echo("<a href='login.html'>Войти в аккаунт</a>");

    // Через 3 секунды перенаправляем пользователя на страницу авторизации
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

      .field {
        display: flex;
        flex-direction: column;
        width: 200px;
      }

      button {
        width: 180px;
        padding: 5px;
        margin-bottom: 5px;
        background-color: #52ccff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
      }
    </style>

    <script>
      function calculate(operation) {

        // Получаем значения X и Y
        let x = document.getElementById("x").value;
        let y = document.getElementById("y").value;

        // Проверяем, что числа введены
        if (x === "" || y === "") {
          alert("Введите X и Y");
          return;
        }

        // Формируем данные для отправки в сервис
        let data = new URLSearchParams();

        data.append("x", x);
        data.append("y", y);
        data.append("operation", operation);

        // Отправляем запрос в operations_service.php
        fetch("operations_service.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded"
          },
          body: data
        })
        .then(response => response.text())
        .then(result => {

          // Выводим полученный результат в поле Z
          document.getElementById("z").value = result;

        })
        .catch(error => {
          console.error("Ошибка:", error);
        });
      }
    </script>

  </head>

  <body>

    <a href="logout.php">Выйти из системы</a>

    <h1>Калькулятор</h1>

    <div class="field">
      <label for="x">X</label>
      <input
        class="number"
        id="x"
        type="number"
        step="any"
      />
    </div>

    <div class="field">
      <label for="y">Y</label>
      <input
        class="number"
        id="y"
        type="number"
        step="any"
      />
    </div>

    <!-- Сложение -->
    <button onclick="calculate('plus')">
      +
    </button>

    <!-- Умножение -->
    <button onclick="calculate('multiply')">
      Умножить
    </button>

    <div class="field">
      <label for="z">Z</label>
      <input id="z" readonly />
    </div>

  </body>
</html>