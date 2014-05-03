<?php
	// Prerequisites
	include_once($_SERVER["DOCUMENT_ROOT"] . '/examples/inc/application_top.php');
	
	// Create the instances of both post and author
	$myPost = new Post;
	$myAuthor = new Author;
	
	// Setup the return fields
	$display = array('title','author_id','category','is_published','body');
	
	// Set the requested primary key and get its info
	if ($_GET['id'] != ''){
		// Set the primary key
		$myPost->SetPrimary((int)$_GET['id']);
		
		// Try to get the posts information
		if ($myPost->GetInfo()){
			// If valid set the author primary key
			$myAuthor->SetPrimary($myPost->GetValue('author_id'));
			
			// If the author is not in the database reset the class
			if (!$myAuthor->GetInfo())
				$myAuthor->ResetValues();
		}else{
			// If the post was not found set an error and reset the post class
			SetAlert('Invalid Post, please try again');
			$myPost->ResetValues();
		}
	}
	
	// Display the Header
	define('PAGE_TITLE',(($myPost->GetPrimary() != '')?'View Article':'Error'));
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1><?php echo PAGE_TITLE; ?></h1>
</div>
<div id="data">
	<div id="notifications">
	<?php
		// Report errors to the user
		Alert(GetAlert('error'));
		Alert(GetAlert('success'),'success');
	?>
	</div>
	
	<ul id="options">
		<li class="back"><a href="index.php">Return to Article List</a></li>
	</ul>
	
	<?php
		// Make sure there is a post to view
		if ($myPost->GetPrimary() != ''){
			// Show the post information
			echo '<div id="view-post">' . "\n";
			echo '<h1>' . stripslashes(htmlspecialchars($myPost->GetValue('title'))) . '</h1>';
			echo '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($myPost->GetValue('date_entered'))) . (($myPost->GetValue('category') != '')?' in ' . htmlspecialchars($myPost->GetValue('category')):'') . (($myAuthor->GetValue('author_id') != '')?' by <a href="mailto:' . htmlspecialchars($myAuthor->GetValue('email')) . '" title="Send Email to Author">' . htmlspecialchars($myAuthor->GetValue('first_name')) . ' ' . htmlspecialchars($myAuthor->GetValue('last_name')) . '</a>':' by Anonymous') . '</div>';
			echo '<div id="post">' . nl2br(stripslashes(htmlspecialchars($myPost->GetValue('body')))) . '</div>';
			echo '</div>' . "\n";
		}
	?>
</div>
<?php include_once('inc/footer.php'); ?>