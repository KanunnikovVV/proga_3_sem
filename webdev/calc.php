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
      function calculateSum() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        let z = x + y;
        document.getElementById("z").value = z;
      }

      function calculateMinus() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        let z = x - y;
        document.getElementById("z").value = z;
      }

      function calculateMultiply() {
        let x = parseFloat(document.getElementById("x").value);
        let y = parseFloat(document.getElementById("y").value);
        let z = x * y;
        document.getElementById("z").value = z;
      }
    </script>
  </head>

  <body>
    <a href='logout.php'>Выйти из системы</a>

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

    <button onclick="calculateMultiply()">Умножить</button>

    <div class="field">
      <label for="z">Z</label>
      <input id="z" />
    </div>
  </body>
</html>