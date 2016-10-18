<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title><?php echo TITLE; echo (defined('PAGE_TITLE'))?' - ' . PAGE_TITLE:''; ?></title>
<link rel="stylesheet" type="text/css" href="css/main.css" media="all" />
<link rel="stylesheet" type="text/css" href="<?php echo ADDRESS . WS_SIMPL . WS_SIMPL_CSS; ?>calendar.css" media="all" />
<script src="<?php echo ADDRESS . WS_SIMPL . WS_SIMPL_JS; ?>calendar.js" type="text/javascript"></script>
</head>
<body>
<div id="container">
	<div id="header">
		<h1><?php echo TITLE; ?></h1>
	</div>
	
	<div id="main-menu">
		<ul>
			<li><a href="index.php" title="Manager Home">Manager Home</a></li>
			<li><a href="authors.php" title="Manage Authors">Manage Authors</a></li>
			<li><a href="posts.php" title="Manage Posts">Manage Posts</a></li>
			<li><a href="../" title="View Blog" class="last">View Blog</a></li>
		</ul>
	</div>
	
	<div id="content">