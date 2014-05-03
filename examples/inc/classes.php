<?php
class Post extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('post', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('title','body'));
		
		// Set the labels
		$this->SetLabels(array('author_id'=>'Author:'));
		
		// Set the examples
		$this->SetExamples(array('tags'=>'ex. PHP, MySQL, Cars, XML, PHPSimpl'));

		// Set the default
		$this->SetDefaults(array('status' => 'Draft'));
		
		// Set the Display
		$this->SetDisplay(array('title', 'author_id', 'tags', 'body'));
		
		$this->SetSetting('required_indicator', 'before');
		$this->SetSetting('label_ending', '');
	}
	
	/**
	 * Display Form
	 * 
	 * @param $display array
	 * @param $hidden array
	 * @param $options array
	 * @param $config array
	 * @param $omit array
	 * @return string
	 */
	public function Form($display='', $hidden=array(), $options=array(), $config=array(), $omit=array(), $multi=false){ 
		// Get a list of all the authors
		$myAuthor = new Author;
		$authors = $myAuthor->GetList(array('first_name', 'last_name'),'last_name','DESC');
		$author_list = array();
		
		// Format the author list how we would like
		foreach($authors as $author_id=>$author)
			$author_list[$author_id] = $author['first_name'] . ' ' . $author['last_name'];
		
		// Add State Options
		$this->SetOption('author_id', $author_list, 'Please Select');
	
		return parent::Form($display, $hidden, $options, $config, $omit, $multi);
	}
}

class Author extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('author', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('first_name','last_name','email'));
		
		// Set the Display
		$this->SetDisplay(array('first_name','last_name','email'));
	}
}

class Tag extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('tag', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('tag'));
	}
}

class PostTag extends DbTemplate {
	/**
	 * Class Constuctor
	 * 
	 * @param $data array
	 * @return null
	 */
	function __construct(){
		// Call the parent constructor
		parent::__construct('post_tag', DB_DEFAULT);
		
		// Set the required
		$this->SetRequired(array('tag_id', 'post_id'));
	}
	
	public function Sync($tag_list){
		// Requere a valid post_id
		$myPost = new Post;
		$myPost->SetPrimary($this->GetValue('post_id'));
		
		if (!$myPost->GetInfo())
			return false;
		
		// Get a list of all the tags for this post
		$tmpPostTag = new PostTag;
		$tmpPostTag->SetValue('post_id', $myPost->GetPrimary());
		$tmpTag = new Tag;
		$tmpPostTag->Join($tmpTag, 'tag_id', 'LEFT');
		$tmpPostTag->GetList();
		
		// Create an array worth using
		$old_tags = array();
		foreach($tmpPostTag->results as $tag)
			$old_tags[$tag['post_tag_id']] = $tag['tag'];
		
		// Split up the categories and make sure there is tags
		$new_tags = explode(',', strtolower($tag_list));
		foreach($new_tags as $key=>$data)
			$new_tags[$key] = trim($data);
		
		// Get the difference
		$diff_tags = array_diff($new_tags, $old_tags);
		
		// Add the different tags to the database
		foreach($diff_tags as $tag){
			// Save the Tag
			$tmpTag->ResetValues();
			$tmpTag->SetValue('tag', $tag);
			$item = $tmpTag->GetAssoc('tag', 'tag', 'ASC', 0, 1);
			if (count($item) == 1)
				$tmpTag->SetPrimary(key($item));
			else
				$tmpTag->Save();
			
			$tmpPostTag->ResetValues();
			$tmpPostTag->SetValue('post_id', $myPost->GetPrimary());
			$tmpPostTag->SetValue('tag_id', $tmpTag->GetPrimary());
			$tmpPostTag->Save();
		}
		
		// Get a list of the tags to remove
		$rem_tags = array_diff($old_tags, $new_tags);
		
		// Remove the non-needed tags
		foreach($rem_tags as $id=>$tag){
			$tmpPostTag->ResetValues();
			$tmpPostTag->SetPrimary($id);
			$tmpPostTag->Delete();
		}
	}
}
?>