<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
	<link href='/dist/calendar/fullcalendar.css' rel='stylesheet' />
    <link href="/dist/css/bootstrap.min.css" rel="stylesheet">
	<!--<link href="/dist/css/bootstrap-theme.min.css" rel="stylesheet">-->
	<link href="/dist/css/style.css" rel="stylesheet">

	<link rel="stylesheet" href="/dist/fonts/font-awesome/css/font-awesome.min.css">
	<!--[if IE 7]>
		<link rel="stylesheet" href="/dist/fonts/font-awesome/css/font-awesome-ie7.min.css">
	<![endif]-->
    
	<!--[if lt IE 9]>
      <script src="/dist/js/html5shiv.js"></script>
      <script src="/dist/js/respond.min.js"></script>
    <![endif]-->
	
	<title><?php echo $title ?></title>

  </head>
  <body>
	<?php echo !empty($top_menu) ? $top_menu : '';?>
    <div class="container main_block">