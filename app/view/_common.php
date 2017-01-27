<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Robins. XLS parser</title>
    <link href="/style/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="/style/bootstrap/css/bootstrap-theme.min.css" type="text/css" rel="stylesheet">
    <script src="/js/jquery.min.js"></script>
    <script src="/style/bootstrap/js/bootstrap.min.js"></script>
    <script src="/js/dictionary.js"></script>
    <link href="/style/style.css" type="text/css" rel="stylesheet">
</head>
<body>
<div id="main-wrapper" >
    <div id="main-menu" >
        <div class="menu-logo inline-flex">
            <h1>Парсер XLS прайсов</h1>
        </div>
        <ul class="main-menu-list inline-flex">
            <li class="main-menu-item">
                <a href="/">Главная</a>
            </li>
            <li class="main-menu-item">
                <a href="/dictionary">Словарь</a>
            </li>
            <li class="main-menu-item">
                <a href="/pub">Файлы</a>
            </li>
        </ul>
    </div>
    <div id="main-header" class="container">
        <!-- Some page header can be here -->
        <?php if ( isset($data['page_header']) ): ?>
            <h2><?php echo $data['page_header']; ?></h2>
        <?php endif; ?>
    </div>
    <div id="main-content" class="container">
        <?php include 'app/view/' . $content_view; ?>
    </div>
    <footer id="main-footer" class="footer">
        <div class="">
            <p class="text-muted">powered by smileexx © 2017</p>
        </div>
    </footer>
</div>

</body>
</html>
