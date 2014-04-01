<!DOCTYPE html>
<html lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="description" content="<?php echo !empty($site_descr) ? $site_descr : SITE_DESCR?>">
		<meta name="keywords" content="<?php echo !empty($site_keywords) ? $site_keywords : SITE_KEYWORDS?>">
		<meta name="author" content="">
		<link href="/dist/css/bootstrap.min.css" rel="stylesheet">
		<!--<link href="/dist/css/bootstrap-theme.min.css" rel="stylesheet">-->
		<link href="/css/style.css" rel="stylesheet">

		<link rel="stylesheet" href="/dist/fonts/font-awesome/css/font-awesome.min.css">
		<!--[if IE 7]>
		<link rel="stylesheet" href="/dist/fonts/font-awesome/css/font-awesome-ie7.min.css">
		<![endif]-->

		<!--[if lt IE 9]>
		<script src="/dist/js/html5shiv.js"></script>
		<script src="/dist/js/respond.min.js"></script>
		<![endif]-->

		<title><?php echo !empty($title) ? $title : SITE_NAME ?></title>

	</head>
	<body>
		<div class="top_block">
			<div class="container">
				<div class="row">
					<div class="col-md-3">
						<a href="/" class="logo"></a>
					</div>
					<div class="col-md-9">
						<div class="menu">
							<?php echo !empty($main_menu) ? $main_menu : '';?>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
