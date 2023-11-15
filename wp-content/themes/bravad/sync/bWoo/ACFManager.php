<?

/*
$groups 	 = $acfManager->getACFGroups();
$groupFields = $acfManager->getACFGroupFields('categories-produits');

$acfManager->post_setACFValue(83, 'category_acf_filemaker_id', 'boqwdb');

$value = $acfManager->post_getACFValue(83, 'category_acf_filemaker_id');

$idPost = $acfManager->post_searchWithACFValue('category_acf_filemaker_id', 'boqwdb');
*/


//Class for ACF plugin (custom fields not managed in woo commerce directly)
class ACFManager
{
	private $_db = null; //Instance of WPDBConnection
	
	
	public function __construct(WPDBConnection $db)
	{
		$this->_db = $db;
	}
	
	
	/*
	Ex would return something like:
		68	"Categories : Produits"		"categories-produits"
		288	"Produits"					"produits"
	*/
	public function getACFGroups()
	{
		$groups = array();
		
		$query = "SELECT `acfGroups`.`ID`, `acfGroups`.`post_title`, `acfGroups`.`post_excerpt`
				  FROM `{wp_}posts` AS `acfGroups`
				  WHERE `acfGroups`.`post_type` = 'acf-field-group';";
		$records = $this->_db->preparedQuery($query, array());
		foreach ($records as $record)
		{
			$groups[] = array
			(
				'id'   => $record['ID'],
				'name' => $record['post_title'],
				'tag'  => $record['post_excerpt']
			);
		}
		
		return $groups;
	}
	/*
	Ex:
		getACFGroupFields("categories-produits")
			->
				69	"Icône"				"icone"
				430	"ID File Maker"		"category_acf_filemaker_id"
	*/
	public function getACFGroupFields($groupTag)
	{
		$fields = array();
		
		$query = "SELECT `acfFields`.`ID`, `acfFields`.`post_title`, `acfFields`.`post_excerpt`
				  FROM `{wp_}posts` AS `acfGroups`
				  INNER JOIN `{wp_}posts` AS `acfFields` ON (`acfGroups`.`ID`=`acfFields`.`post_parent` AND `acfFields`.`post_type`='acf-field')
				  WHERE `acfGroups`.`post_type`    = 'acf-field-group'
				    AND `acfGroups`.`post_excerpt` = ?;";
		$records = $this->_db->preparedQuery($query, array($groupTag));
		foreach ($records as $record)
		{
			$fields[] = array
			(
				'id'   => $record['ID'],
				'name' => $record['post_title'],
				'tag'  => $record['post_excerpt']
			);
		}
		
		return $fields;
	}
	
	
    //Ex: post_setACFPostMeta($idProduct=123, "brand", "bob")
    public function post_setACFPostMeta($idPost, $acfSingleFieldTag, $value)
    {
    	$this->post_unsetACFPostMeta($idPost, $acfSingleFieldTag);
    	
    	$query = "INSERT INTO `{wp_}postmeta` (`post_id`, `meta_key`, `meta_value`)
				  VALUES (?, ?, ?);";
	    $this->_db->preparedQuery($query, array($idPost,$acfSingleFieldTag,$value));
    }
	    //Ex: post_unsetACFPostMeta($idProduct=123, "brand")
	    public function post_unsetACFPostMeta($idPost, $acfSingleFieldTag)
	    {
	    	$query = "DELETE FROM `{wp_}postmeta`
	    			  WHERE `post_id`  = ?
	    			    AND `meta_key` = ?;";
			$this->_db->preparedQuery($query, array($idPost,$acfSingleFieldTag));
	    }
    //Ex: post_setACFValue($idCategory=123, "category_acf_filemaker_id", "bob")
    public function post_setACFValue($idPost, $acfGroupFieldTag, $value)
    {
    	$this->post_unsetACFValue($idPost, $acfGroupFieldTag);
    	
		$query = "INSERT INTO `{wp_}termmeta` (`term_id`,`meta_key`,`meta_value`)
				  VALUES (?, ?, ?);";
	    $this->_db->preparedQuery($query, array($idPost,$acfGroupFieldTag,$value));
    }
	    //Ex: post_unsetACFValue($idCategory=123, "category_acf_filemaker_id")
	    public function post_unsetACFValue($idPost, $acfGroupFieldTag)
	    {
	    	$query = "DELETE FROM `{wp_}termmeta`
	    			  WHERE `term_id`  = ?
	    			    AND `meta_key` = ?;";
			$this->_db->preparedQuery($query, array($idPost,$acfGroupFieldTag));
	    }
	/*
	Ex: post_getACFValue($idCategory=123, "category_acf_filemaker_id")
		-> "bob"
	*/
    public function post_getACFValue($idPost, $acfGroupFieldTag)
    {
    	$query = "SELECT `meta_value`
    			  FROM `{wp_}termmeta`
    			  WHERE `term_id`  = ?
    			    AND `meta_key` = ?
    			  LIMIT 1;";
		$records = $this->_db->preparedQuery($query, array($idPost,$acfGroupFieldTag));
		if (count($records) > 0)
		{
			return $records[0]['meta_value'];
		}
		
		return null;
    }
    
	/*
	Ex:
		post_searchWithACFValue("category_acf_filemaker_id", "bob")
			-> [123]
	*/
    public function post_searchWithACFValue($acfGroupFieldTag, $value)
    {
    	$posts = array();
    	
    	$query = "SELECT `term_id`
    			  FROM `{wp_}termmeta`
    			  WHERE `meta_key`   = ?
    			    AND `meta_value` = ?;";
		$records = $this->_db->preparedQuery($query, array($acfGroupFieldTag,$value));
		foreach ($records as $record)
		{
			$posts[] = $record['term_id'];
		}
		
		return $posts;
    }
    /*
	Ex:
		post_searchWithACFValue_one("category_acf_filemaker_id", "bob")
			-> 123
	*/
    public function post_searchWithACFValue_one($acfGroupFieldTag, $value)
    {
    	$idWPPost = null;
    	
	    $matchingWPPosts = $this->post_searchWithACFValue($acfGroupFieldTag, $value);
	    if (count($matchingWPPosts)) { $idWPPost = $matchingWPPosts[0]; }
	    
	    return $idWPPost;
    }
};

?>