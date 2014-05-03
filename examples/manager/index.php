<?php
	// Prerequisites
	include_once($_SERVER["DOCUMENT_ROOT"] . '/examples/manager/inc/application_top.php');
	
	// Grab some Information about the Blog
	$myAuthor = new Author;
	$authors = $myAuthor->GetList('count');
	
	$myPost = new Post;
	$posts = $myPost->GetList('count');
			
	// Header
	define('PAGE_TITLE','Welcome');
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1>Blog Manager</h1>
</div>
<div id="data">
	<p>Welcome to the Blog Manager, Please select an option on the right.</p>
	<ul>
		<li>Authors: <?php echo $authors['count']; ?></li>
		<li>Posts: <?php echo $posts['count']; ?></li>
	</ul>
</div>
<?php include_once('inc/footer.php'); ?>