<?php

// declare(strict_types=1);
?>
<!DOCTYPE html>
<html>

<head>
	<title>Title</title>
	<style type="text/css">
		@import url('https://fonts.googleapis.com/css?family=Nunito:300,400,600');

		body {
			font-family: 'Nunito', sans-serif;
			font-size: 12px;
			background-color: #fdfdfd;
		}

		a {
			text-decoration: none;
			color: #bc5858;
		}

		#logo {
			position: relative;
			top: 125px;
			width: 28%;
			margin: 0 auto;
			text-align: center;
		}

		#container {
			position: relative;
			top: 150px;
			width: 28%;
			margin: 0 auto;
		}

		#container h3 {
			text-align: center;
			margin: 0;
			padding: 10px;
			font-size: 38px;
			border-bottom: 1px solid #ccc;
			color: #777;
			font-weight: 300;
		}

		#container>h3>span {
			font-size: 48px;
			color: #bc5858;
		}

		span.error_code {
			color: #bc5858;
		}

		#container p {
			margin: 0;
			padding: 10px;
			font-size: 12px;
		}

		#footer {
			position: relative;
			top: 170px;
			width: 28%;
			margin: 0 auto;
			font-size: 12px;
			font-weight: 600;
			color: #777;
		}

		#footer span.copyright {
			float: left;
		}

		#footer span.version {
			float: right;
		}
	</style>
	<link rel="shortcut icon" sizes="16x16" href="<?= PUBLIC_DIR ?>img/favicon.png" />
</head>

<body>
	<div id="logo">
		<img src="<?= PUBLIC_DIR ?>img/logo.png" width="150" />
	</div>
	<div id="container">
		<h3>PHP FRAMEWORK</h3>
	</div>
</body>

</html>