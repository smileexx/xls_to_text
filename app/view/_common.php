<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>XML to Text converter</title>
    <link href="/style/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
    <link href="/style/bootstrap/css/bootstrap-theme.min.css" type="text/css" rel="stylesheet">
    <link href="/style/style.css" type="text/css" rel="stylesheet">
</head>
<body>
<div id="main-wrapper" class="">
    <div id="main-menu" class="container">
        <div class="menu-logo inline-flex">
            <h1>XML parser</h1>
        </div>
        <ul class="main-menu-list inline-flex">
            <li class="main-menu-item">
                <a href="/">Main</a>
            </li>
            <li class="main-menu-item">
                <a href="/pub">Public</a>
            </li>
            <li class="main-menu-item">
                <a href="/dictionary">Dictionary</a>
            </li>
        </ul>
    </div>
    <div id="main-header" class="container">
        <!-- Some page header can be here -->
        <?php if ( isset($data['page_header']) ): ?>
            <h2><?php echo $data['page_header']; ?></h2>
        <?php endif; ?>
    </div>
    <div id="main-content">
        <?php include 'app/view/' . $content_view; ?>
    </div>
    <footer id="main-footer" class="footer">
        <div class="container">
            <p class="text-muted">powered by smileexx © 2017</p>
        </div>
    </footer>
</div>

</body>
</html>
