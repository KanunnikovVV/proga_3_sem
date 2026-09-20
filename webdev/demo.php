<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Демо</title>
    <style>
        h2 {
            text-align: center;
            padding-block: 40px;
        }
        .night {
            background-color: black;
            color: white;
        }
        .evening {
            background-color: darkblue;
            color: white;
        }
        .afternoon {
            color: darkgreen;
        }
        .morning {
            background-color: orange;
            color: black;
        }

        .weekend {
            animation: blink-red 1s infinite;
        }

        @keyframes blink-red {
            0%, 100% {
                color: black;
            }
            50% {
                color: red;
            }
        }

    </style>
</head>
<body>
    <?php
        // $x = 2;
        // $y = 2;
        // $z = $x + $y;
        // echo "Это работает PHP!";
        // echo "$x+$y=$z";
        // echo "<h1>$x+$y=$z</h1>";
        date_default_timezone_set('Europe/Moscow');
        // date('w'): 0 — воскресенье, 6 — суббота.
        $days = ['воскресенье', 'понедельник', 'вторник', 'среда', 'четверг', 'пятница', 'суббота'];
        $day = (int) date('w');
        echo "<h2>Сегодня " . $days[$day] . "</h2>";
        if ($day === 0 || $day === 6) {
            echo "<h2 class='weekend'>Сегодня выходной! 🎆</h2>";
        }
        // date_default_timezone_set('Asia/Yakutsk');
        // date_default_timezone_set('America/Los_Angeles');
        //date_default_timezone_set('Europe/London');
        $now = date('H:i:s');
        echo "<h2>Вы зашли на страницу в $now</h2>";
        $hour = date('H');
        if($hour < 6) {
            echo "<h2 class='night'>Доброй ночи!</h2>";
        }
        if($hour >= 6 && $hour < 12) {
            echo "<h2 class='morning'>Доброе утро!</h2>";
        }
        if($hour >= 12 and $hour < 18) {
            echo "<h2 class='afternoon'>Добрый день!</h2>";
        }
        if($hour >= 18) {
            echo "<h2 class='evening'>Добрый вечер!</h2>";
        }

        echo "<pre>";
        // print_r($_SERVER);
        echo "Ваш браузер: " . $_SERVER['HTTP_USER_AGENT'] . "<br>";
        echo "Ваш IP-адрес: " . $_SERVER['REMOTE_ADDR'] . "<br>";
        echo "Ваш URL: " . $_SERVER['REQUEST_URI'] . "<br>";
        echo "</pre>";

        $phrase = ['Не забудьте сохранить файл', 'Обязательно ставьте точку с запятой', 'PHP выполяется на сервере!', 'Кавычки снаружи двойные, а внутри одинарные'];
        $index = rand(0, count($phrase) - 1);
        echo "<h2>Совет дня: " . $phrase[$index] . "</h2>";
        echo "<h2>";
        for($i=1; $i <= $hour; $i++) {
            echo "*";
        }
        echo "</h2>";



    ?>
</body>
</html>
