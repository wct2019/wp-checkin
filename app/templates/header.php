<!doctype html>
<html lang="ja" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="utf-8"/>
    <title>WP Checkin - <?php echo getenv('WORDCAMP_NAME'); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="description" content="A Reception Tool for WordCamp Tokyo 2023" />

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
    <link href='/assets/css/style.css?s=<?= filemtime(dirname(__DIR__) . '/public/assets/css/style.css') ?>'
          rel="stylesheet" type="text/css">
	<script src="https://kit.fontawesome.com/1f8da2ddf2.js" crossorigin="anonymous"></script>


</head>
<body data-spy="scroll" data-target="#navbar-main">

<nav class="navbar navbar-dark bg-success">
	<a class="navbar-brand" href="#">
		<img src="/assets/img/wapuu.png" width="30" height="30" alt="Wapuu">
        <?php echo getenv('WORDCAMP_NAME'); ?> Reception
	</a>
</nav>
	<div class="container container-main">
