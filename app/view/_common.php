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
    <div id="main-header" class="container">
        <div class="jumbotron page-header">
            <h1>XML parser</h1>
        </div>
    </div>
    <div id="main-content">
        <?php include 'app/view/' . $content_view; ?>
    </div>
    <footer id="main-footer" class="footer">
        <div class="container">
            <p class="text-muted">powered by smileexx © 2016</p>
        </div>
    </footer>
</div>

</body>
</html>
