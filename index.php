<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Поисковый парсер</title>
    <link rel="stylesheet" href="resources/css/search.css">
</head>

<body>
    <div class="container">
        <h1>Веб-парсер</h1>
        <div class="sub">Введите URL — данные будут загружены и отданы в JSON</div>

        <form class="search-form" method="GET" action="./scraping.php" enctype="multipart/form-data">
            <div class="input-wrapper">
                <span class="input-icon">🔗</span>
                <input type="text" name="url" placeholder="https://example.com/article" required>
            </div>
            <button type="submit">Получить JSON</button>
        </form>

        <div class="note">
            ⚡ После отправки начнётся скачивание файла (если ваш PHP-обработчик настроен на вывод JSON).
        </div>
    </div>
</body>

</html>