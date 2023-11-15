<?

class bWoo_ACF
{
	
	SELECT `acfFields`.`ID`, `acfFields`.`post_title`, `acfFields`.`post_excerpt`
	FROM `ic_posts` AS `acfGroups`
	INNER JOIN `ic_posts` AS `acfFields` ON (`acfGroups`.`ID`=`acfFields`.`post_parent` AND `acfFields`.`post_type`="acf-field")
	WHERE `acfGroups`.`post_type`    = "acf-field-group"
	  AND `acfGroups`.`post_excerpt` = "categories-produits";
    
};

?>