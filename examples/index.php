<?php
	// Prerequisites
	include_once($_SERVER["DOCUMENT_ROOT"] . '/examples/inc/application_top.php');
	
	// Create the post instance
	$myPost = new Post;
	
	// Only show the published posts
	$myPost->SetValue('status', 'Published');
	
	// Setup the fields to pull from the post table
	$display[] = array('title', 'date_entered', 'author_id', 'body');
	
	// Create the author instance
	$myAuthor = new Author;
	
	// Join the author and post tables on the author_id
	$myPost->Join($myAuthor,'author_id','LEFT');
	
	// Setup the fields to pull from the author table
	$display[] = array('first_name','last_name','email');
		
	// Get the List
	$myPost->GetList($display, 'date_entered', 'DESC', 0 , 10);
		
	// Header
	define('PAGE_TITLE','Welcome');
	include_once('inc/header.php');
?>
<div id="main-info">
	<h1>Example blog written with PHPSimpl framework</h1>
</div>
<div id="data">
	<?php
		// If there is results returned
		if (count($myPost->results) > 0){
			echo '<dl id="posts">' . "\n";
			
			// Loop through each result
			foreach($myPost->results as $post){
				echo '<dt><a href="view.php?id=' . $post['post_id'] . '" title="' . htmlspecialchars($post['title']) . '">' . stripslashes(htmlspecialchars($post['title'])) . '</a></dt>' . "\n";
				echo '<dd>' . h(substr($post['body'],0,350)) . 
					"\n" . '<div class="details">Posted on ' .date("F j, Y \\a\\t g:i a", strtotime($post['date_entered'])) . 
					(($post['category'] != '')?' in ' . htmlspecialchars($post['category']):'') . 
					(($post['first_name'] != '')?' by <a href="mailto:' . htmlspecialchars($post['email']) . '" title="Email this Author">' . htmlspecialchars($post['first_name']) . ' ' . htmlspecialchars($post['last_name']) . '</a>':' by Anonymous') . '</div></dd>' . "\n";
			}
			
			echo '</dl>' . "\n";
		}else{
			echo '<p>Currently there are no posts, please <a href="manager/post.php">add some</a></p>' . "\n";
		}
	?>
</div>
<?php include_once('inc/footer.php'); ?>