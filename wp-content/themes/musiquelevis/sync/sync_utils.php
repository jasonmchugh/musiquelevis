<?

ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);
header('content-type: text/plain');

require_once('bXHTTPRequest.php');
require_once('bWoo/autoload.php');
require_once('InstantComptantSyncManager.php');


function buildSyncDefinesAndServerGlobalsForEnvironment($environment) //{dev|prod}
{
	$_SERVER['DOCUMENT_ROOT'] = str_replace("/wp-content/themes/musiquelevis/sync", '', __DIR__);
	
	if ($environment === 'dev')
	{
		$_SERVER['SERVER_NAME'] = 'instantcomptant.bravad-dev.com';
		
		define('DB_HOST',                     'localhost');
		define('DB_USER',                     'ab52337_instantc');
		define('DB_PWD',                      '_5%TUf+la-gn');
		define('DB_NAME',                     'ab52337_instantcomptant');
		define('TABLES_PREFIX',               'ic_');
		define('BWOO_STORE_URL',              'https://instantcomptant.bravad-dev.com');
		define('BWOO_CONSUMER_KEY',           'ck_fbcb961c8e6a7e9152e31bcf5a93f1f326116d48'); //Can be found in `{wp_}_woocommerce_api_keys`
		define('BWOO_CONSUMER_SECRET',        'cs_d8fa239c0117ed104628241b3504fda8031b1769');
		define('IS_SERVER_UTF8',              false);
		define('AVG_ITEM_SYNC_TIME',          20);
		define('CRON_RELEASE_ALL_OLD_LOCKS',  true);
	}
	else
	{
		$_SERVER['SERVER_NAME'] = 'instantcomptant.ca';
		
		/*define('DB_HOST',                    'localhost');
		define('DB_USER',                    'instantcomptant_kijiji');
		define('DB_PWD',                     'v[Ed+&#y$$kw');
		define('DB_NAME',                    'instantcomptant_kijiji');
		define('TABLES_PREFIX',              'ic_');
		define('BWOO_STORE_URL',             'https://kijiji.instantcomptant.ca');
		define('BWOO_CONSUMER_KEY',          'ck_a69b3fe2244060abadb87e40031e0fb713aaea3b'); //Can be found in `{wp_}_woocommerce_api_keys` //fac9a1577dbcc10fb1d27156c00e61af37dafb91cf542e9068991acd85abd87e
		define('BWOO_CONSUMER_SECRET',       'cs_00690aff2f24e29d2b6a4515920ed0a33a23e2e8');
		define('IS_SERVER_UTF8',             true);
		define('AVG_ITEM_SYNC_TIME',         20);
		define('CRON_RELEASE_ALL_OLD_LOCKS', true);*/
		
		define('DB_HOST',                    'localhost');
		define('DB_USER',                    'instantcomptant_online');
		define('DB_PWD',                     'TVm_9GhJ%Cd32rPF');
		define('DB_NAME',                    'instantcomptant_online');
		define('TABLES_PREFIX',              'ic_');
		define('BWOO_STORE_URL',             'https://instantcomptant.ca');
		//define('BWOO_CONSUMER_KEY',          'ck_a69b3fe2244060abadb87e40031e0fb713aaea3b'); //Can be found in `{wp_}_woocommerce_api_keys` //fac9a1577dbcc10fb1d27156c00e61af37dafb91cf542e9068991acd85abd87e
		//define('BWOO_CONSUMER_SECRET',       'cs_00690aff2f24e29d2b6a4515920ed0a33a23e2e8');

		define('BWOO_CONSUMER_KEY',          'ck_196b33ec898070b62a46c9657dcaa8c012ca7b37');
		define('BWOO_CONSUMER_SECRET',       'cs_c58a14e503ab8b7d906855a3bb5da0539a45cee2');
		define('IS_SERVER_UTF8',             true);
		define('AVG_ITEM_SYNC_TIME',         20);
		define('CRON_RELEASE_ALL_OLD_LOCKS', true);
	}
}


function cronId_start(WPDBConnection $db)
{
	$query = "INSERT INTO `{wp_}sync_cron_executions`() VALUES ();";
	$db->preparedQuery($query, array());
	
	$GLOBALS['cronId']          = $db->last_id();
	$GLOBALS['cronStartTime']   = floatval(microtime(true));
	$GLOBALS['cronMaxExecTime'] = 190;
	//$GLOBALS['cronMaxExecTime'] = intval(ini_get('max_execution_time'));
	
}
function cronId_get()
{
	return $GLOBALS['cronId'];
}
function cronId_startTime()
{
	return $GLOBALS['cronStartTime'];
}
function cronId_timeElapsed()
{
	$now      = floatval(microtime(true));
	$msecDiff = floatval($now - cronId_startTime());
    $msecDiff = intval($msecDiff*10000);
    $secDiff  = round($msecDiff/10000, 4);
    
    return $secDiff;
}
function cronId_maxExecTime()
{
	return $GLOBALS['cronMaxExecTime'];
}
function cronId_shouldStop()
{
	return cronId_timeElapsed()+AVG_ITEM_SYNC_TIME >= cronId_maxExecTime();
}
function cronId_stop(WPDBConnection $db)
{
	$query = "UPDATE `{wp_}sync_cron_executions`
			  SET `endDateTime` = NOW() 
			  WHERE `pk` = ?
			  LIMIT 1;";
	$db->preparedQuery($query, array(cronId_get()));
	$db->commit();
	
	cronId_releaseLocks($db, $cronId=cronId_get());
	
	$db->stop();
	exit;
}
function cronId_releaseLocks(WPDBConnection $db, $cronId='ALL')
{
	//If we want to run the cron for 1 min and we detect that an old one hasn't indicated that it's done, 5 mins later, it might have crashed, so release them
	if ($cronId === 'ALL')
    {
    	$cancelAfterXSecs = cronId_maxExecTime() * 5;
    	
    	$query = "UPDATE `{wp_}sync_cron_executions`
    			  SET `endDateTime` = NOW()-1
    			  WHERE `endDateTime` IS NULL
    			    AND (NOW() - `{wp_}sync_cron_executions`.`startDateTime`) > ?;";
		$db->preparedQuery($query, array($cancelAfterXSecs));
    }
    
	_cronId_releaseLocks_oneTable($db, 'sync_categories',     $cronId);
	_cronId_releaseLocks_oneTable($db, 'sync_products',       $cronId);
	_cronId_releaseLocks_oneTable($db, 'sync_inventories_in', $cronId);
}
	//If we're stucked with records that are still TODO but assigned to a cron id that has done running, release them
	function _cronId_releaseLocks_oneTable(WPDBConnection $db, $syncTableName, $cronId='ALL')
	{
		$params = array();
		$query = "UPDATE `{wp_}$syncTableName`
				  INNER JOIN `{wp_}sync_cron_executions` ON (`{wp_}$syncTableName`.`syncStatus_cronId` = `{wp_}sync_cron_executions`.`pk`)
				  SET `{wp_}$syncTableName`.`syncStatus_cronId` = NULL
				  WHERE `{wp_}$syncTableName`.`syncStatus` = 1";
	    if ($cronId === 'ALL')
	    {
	    	$query .= "  AND `{wp_}sync_cron_executions`.`endDateTime` IS NOT NULL ";
	    }
    	else
	    {
			$query   .= " AND `{wp_}$syncTableName`.`syncStatus_cronId` = ? ";
			$params[] = $cronId;
		}
		
		$db->preparedQuery($query, $params);
		$db->commit();
	}




function doSync($rebuildAllProductsSummaryInfos, $syncCategories, $syncProducts, $syncStocksIN, $syncStocksOUT, $syncImages, $queryLimit)
{
	try
	{
	    bWooApi::isServerUTF8(IS_SERVER_UTF8);
	    
	    $db = new WPDBConnection(DB_HOST, DB_USER, DB_PWD, DB_NAME, TABLES_PREFIX);
	    
		//Define cron vars
		cronId_start($db);
		
		//If we should release cron locks for old iterations that overlapped between 2 cron calls or when one couldn't finish its job
		if (CRON_RELEASE_ALL_OLD_LOCKS)
		{
			cronId_releaseLocks($db, $cronId='ALL');
		}
		
		//Log for the first time - mustn't do this before "cronId_start()"
		ICSyncManager::echoLog($logLevel=0,'','STARTING SYNC',$insertSpacerBefore=true);
	    
	    $acfManager  = new ACFManager($db);
	    $syncManager = new ICSyncManager($db, $queryLimit);
	    $api         = new bWooApi(BWOO_STORE_URL, BWOO_CONSUMER_KEY, BWOO_CONSUMER_SECRET);
	    
	    //NOTE: The following globals are just to be used in the "WP_x()" funcs below, not anywhere else
	    {
		    $GLOBALS['db']          = $db;
		    $GLOBALS['acfManager']  = $acfManager;
		    $GLOBALS['syncManager'] = $syncManager;
		    $GLOBALS['api']         = $api;
		}
		
	    //Do the sync
	    {
	    	if ($rebuildAllProductsSummaryInfos)
    		{
    			$syncManager->updateAllProductsSummaryInfos();
    		}
	    	
	        //First check if we need to create or update categories. We assume new branches will be sent to us in logical order, with parents first and children after
	        if ($syncCategories)
	        {
	        	$syncManager->sync_filemaker2wp_categories($syncImages);
	        }
	        
	        //Then products
	        if ($syncProducts)
	        {
		        $syncManager->sync_filemaker2wp_products($syncImages);
	    		$syncManager->update_LiveNbOfImagesInWP();
		    }
			
			 //Then check for products that were bought from some FileMakers
		    if ($syncStocksIN)
		    {
		        $syncManager->sync_filemaker2wp_productStocks();
		    }
	        
	        //Then tell what was bought here
		    if ($syncStocksOUT)
		    {
		        WP_notifyOrderedProducts($syncManager);
		    }
	    }    
	}
	catch (bWoo_Exception $e) { ICSyncManager::echoLog($logLevel=0, '', 'BWOO EXCEPTION: '.$e->getMessage()."\n{$e->getTraceAsString()}"); }
	catch (Exception      $e) { ICSyncManager::echoLog($logLevel=0, '',      'EXCEPTION: '.$e->getMessage()."\n{$e->getTraceAsString()}"); }

	ICSyncManager::echoLog($logLevel=0, '', 'DONE SYNCING');
	
	cronId_stop($db);
}




//FUNCS ACTUALLY USED FROM INSIDE ICSyncManager METHODS, AS CALLBACKS
function WP_createUpdateCategory(ic_sync_filemaker2wp_category $fileMaker_category)
{
    $idWPCategoryPost = null;
    $isNew            = null;
    $bWoo_category    = null;
    $success          = false;
    
    //Flag images to sync as failed, so we can easily leave this func with "return false;" without writing ugly code. We'll put them back to "done" if we can get up to the end without problem
    _WP_createUpdateCategory_setImagesToSync_status($fileMaker_category, ICSyncManager::SYNC_STATUS_ERROR);
    
    //Check if the category already exists in WP, then try to load it
    if ($idWPCategoryPost = $GLOBALS['syncManager']->searchCategoryPost_byFileMakerIdCategory($GLOBALS['acfManager'],$fileMaker_category->idCategory))
    {
    	$isNew = false;
    	
    	$bWoo_category = bWoo_Category::loadOne($GLOBALS['api'], $idWPCategoryPost);
    	if ($bWoo_category === null) { syncUtils_echoLog($logLevel=3,'ic_sync_categories',"WP category post #".$idWPCategoryPost." couldn't be loaded",$fileMaker_category->IdTransaction,$fileMaker_category); return false; }
    }
    //If it's a new category
    else
    {
    	$isNew = true;
    	
    	$bWoo_category = new bWoo_Category();
    	
    	//If it has a parent category, we also need to find its real idPost. Note: the parent must exist in advance to work, so sync has to receive categories in the right order
    	if ($fileMaker_category->idCategoryParent !== ICSyncManager::CATEGORY_ID_PARENT_NONE)
    	{
	    	$idWPParentCategoryPost = $GLOBALS['syncManager']->searchCategoryPost_byFileMakerIdCategory($GLOBALS['acfManager'],$fileMaker_category->idCategoryParent);
	    	if ($idWPParentCategoryPost === null) { syncUtils_echoLog($logLevel=3,'ic_sync_categories',"ERROR: WP parent category post with filemaker id #".$fileMaker_category->idCategoryParent." not found and must exist in advance",$fileMaker_category->IdTransaction,$fileMaker_category); return false; }
	    	
			$bWoo_category->setFieldVal('parent', $idWPParentCategoryPost);
	    }
    }
    
    //Common fields to update in creation/modification
    {
		$bWoo_category->setFieldVal('name',        $fileMaker_category->categoryName);
		$bWoo_category->setFieldVal('description', $fileMaker_category->categoryName);
		
		if ($fileMaker_category->mainImgToSync)
		{
			$bWoo_categoryImage = _WP_createUpdateCategory_makeCategoryImageObject_fromFileMakerImage($fileMaker_category->mainImgToSync);
			$bWoo_category->setFieldVal('image', $bWoo_categoryImage);
		}
		
	    if (!$fileMaker_category->isActive) { syncUtils_echoLog($logLevel=3,'ic_sync_categories',"Filemaker category #".$fileMaker_category->idCategory." can't be set inactive yet",$fileMaker_category->IdTransaction,$fileMaker_category); return false; }
	}
	
	try
	{
		$bWoo_category->save($GLOBALS['api']);
	    //If it was new, also store a link to filemaker version, using an ACF
	    if ($isNew) { $GLOBALS['syncManager']->setCategoryFileMakerCustomField($GLOBALS['acfManager'], $bWoo_category->id(), $fileMaker_category->idCategory); }
	    
        $success = true;
	}
	catch (Exception $e)
	{
		syncUtils_echoLog($logLevel=3,'ic_sync_categories',"Filemaker category #".$fileMaker_category->idCategory." encountered exception: ".$e->getMessage(),$fileMaker_category->IdTransaction,$fileMaker_category); return false;
	}
	
	//Now, we can indicate to each pic to sync, if it's ok to send them to "done" / "error" dirs. NOTE: It's not about if -this- image worked, but if this whole sync operation worked
	_WP_createUpdateCategory_setImagesToSync_status($fileMaker_category, $success?ICSyncManager::SYNC_STATUS_DONE:ICSyncManager::SYNC_STATUS_ERROR);
    
    return $success;
}
	function _WP_createUpdateCategory_makeCategoryImageObject_fromFileMakerImage(ic_sync_imgInfos $fileMakerImage)
	{
		$bWoo_categoryImage = new bWoo_CategoryImage();
		
		$bWoo_categoryImage->setFieldVal('src',   docRootImgPathToUrl($fileMakerImage->filePath));
		$bWoo_categoryImage->setFieldVal('title', $fileMakerImage->fileName);
		$bWoo_categoryImage->setFieldVal('alt',   $fileMakerImage->fileName);
		
		return $bWoo_categoryImage;
	}
	//NOTE: It's not about if -this- image worked, but if this whole sync operation worked
	function _WP_createUpdateCategory_setImagesToSync_status(ic_sync_filemaker2wp_category $fileMaker_category, $status)
	{
		if ($fileMaker_category->mainImgToSync) { $fileMaker_category->mainImgToSync->syncStatus = $status; }
	}





function WP_createUpdateProduct(ic_sync_filemaker2wp_product $fileMaker_product)
{
    $idWPProductPost = null;
    $isNew           = null;
    $bWoo_product    = null;
    $success         = false;
    
    //Flag images to sync as failed, so we can easily leave this func with "return false;" without writing ugly code. We'll put them back to "done" if we can get up to the end without problem
    _WP_createUpdateProduct_setImagesToSync_status($fileMaker_product, ICSyncManager::SYNC_STATUS_ERROR);
    
    //Check if the product already exist in WP, then try to load it
    if ($idWPProductPost = _WP_searchProductPost_byFileMakerUUID($fileMaker_product->UUIDProductUniverse))
    {
    	$isNew = false;
    	
    	$bWoo_product = bWoo_Product::loadOne($GLOBALS['api'], $idWPProductPost);
    	if ($bWoo_product === null) { syncUtils_echoLog($logLevel=3,'ic_sync_products',"WP product post #$idWPProductPost couldn't be loaded",$fileMaker_product->IdTransaction,$fileMaker_product); return false; }
    }
    //If it's a new product
    else
    {
    	$isNew = true;
    	
    	$bWoo_product = new bWoo_Product();
    }
    
    //Common fields to update in creation/modification
    {
    	$bWoo_product->setFieldVal('sku',                $fileMaker_product->sku); //Will break if we get 2 products with the same SKU, and it has to be like that
		$bWoo_product->setFieldVal('name',               $fileMaker_product->productName);
		$bWoo_product->setFieldVal('description',        $fileMaker_product->productDescription);
	  //$bWoo_product->setFieldVal('short_description',  $fileMaker_product->productDescription); //Not used
	  //$bWoo_product->setFieldVal('featured',           (bool)$fileMaker_product->isFeatured);
		$bWoo_product->setFieldVal('backorders',         bWoo_Product::BO_DISALLOWED);
		$bWoo_product->setFieldVal('type',               bWoo_Product::TYPE_SIMPLE);
		$bWoo_product->setFieldVal('manage_stock',       true);
		$bWoo_product->setFieldVal('stock_quantity',     $fileMaker_product->stockQty);
		$bWoo_product->setFieldVal('in_stock',           $fileMaker_product->stockQty > 0);
		$bWoo_product->setFieldVal('catalog_visibility', $fileMaker_product->isActive?bWoo_Product::CATALOG_VISIBILITY_VISIBLE:bWoo_Product::CATALOG_VISIBILITY_HIDDEN);
		$bWoo_product->setFieldVal('status',             $fileMaker_product->isActive?bWoo_Product::STATUS_PUBLISH            :bWoo_Product::STATUS_DRAFT);
	    $bWoo_product->setFieldVal('tax_status',         bWoo_Product::TAX_STATUS_TAXABLE);
        $bWoo_product->setFieldVal('tax_class',          bWoo_Product::TAX_CLASS_STANDARD);
        $bWoo_product->setFieldVal('regular_price',      number_format($fileMaker_product->productPrice,2,'.',''));
        $bWoo_product->setFieldVal('sale_price',         0<$fileMaker_product->productPricePromo&&$fileMaker_product->productPricePromo<$fileMaker_product->productPrice ? number_format($fileMaker_product->productPricePromo,2,'.','') : '');
		//Shipping
		{
			$shippingClassSlug = $GLOBALS['syncManager']->shippingClass_dbEnum2WCSettingsShippingClassSlug($fileMaker_product->idStore);
			if ($shippingClassSlug === null) { syncUtils_echoLog($logLevel=3,'ic_sync_products',"Couldn't find shipping class for idStore #".$fileMaker_product->idStore,$fileMaker_product->IdTransaction,$fileMaker_product); return false; }
	        $bWoo_product->setFieldVal('shipping_class', $shippingClassSlug);
		}
		
		//Attributes. NOTE: Always set them all, or next time they'll disappear
		{
			$bWoo_ProductAttribute_arr = array();
			
			//Filemaker ID - text
			{
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_FILEMAKERID_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', $fileMaker_product->UUIDProductUniverse);
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			//Store - single choice
			{
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_STORE_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', array($GLOBALS['syncManager']->shippingClass_dbEnum2ProductAttrTermLabel($fileMaker_product->idStore)));
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			//Product state - single choice
			{
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', array($GLOBALS['syncManager']->ic_attrTermId2Label_productState($fileMaker_product->productState)));
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			//Offer types - multiple choices
			{
				$offerTypeLabels = array();
				if ($fileMaker_product->isFeatured)  { $offerTypeLabels[] = $GLOBALS['syncManager']->ic_attrTermId2Label_offerType(ICSyncManager::PRODUCT_ATTR_TERMS_OFFERTYPE_FEATURED_WPSLUG);   }
				if ($fileMaker_product->isPromotion) { $offerTypeLabels[] = $GLOBALS['syncManager']->ic_attrTermId2Label_offerType(ICSyncManager::PRODUCT_ATTR_TERMS_OFFERTYPE_PROMOTIONS_WPSLUG); }
				
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_OFFERTYPE_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', $offerTypeLabels);
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			//Shipping enabled or not - text
			{
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_NOSHIPPING_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', $fileMaker_product->isShippable?0:1);
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			//Shipping price - text
			if ($fileMaker_product->shippingPrice !== null)
			{
				$bWoo_ProductAttribute = new bWoo_ProductAttribute();
					$bWoo_ProductAttribute->id(ICSyncManager::PRODUCT_ATTRS_SHIPPING_PRICE_POSTID);
					$bWoo_ProductAttribute->setFieldVal('options', $fileMaker_product->shippingPrice);
				$bWoo_ProductAttribute_arr[] = $bWoo_ProductAttribute;
			}
			
			$bWoo_product->attributes = $bWoo_ProductAttribute_arr;
		}
		
        /*
        Fields in bWoo_Product that aren't set yet / don't need to be set for now:
        	virtual
			downloadable
			download_limit
			download_expiry
			external_url
			button_text
			sold_individually
			weight
			reviews_allowed
			upsell_ids
			cross_sell_ids
			purchase_note
			grouped_products
			menu_order
	        date_on_sale_from
	        date_on_sale_from_gmt
	        date_on_sale_to
	        date_on_sale_to_gmt
	        downloads           -> array(bWoo_ProductDownload)
	        tags                -> array(bWoo_ProductTag)
	        default_attributes  -> array(bWoo_ProductDefaultAttribute)
	        meta_data           -> array(bWoo_ProductMeta) -> Ptete des custom fields à registerer comme ACF
        */
        
	    //Categories
	    {
		    if (strlen($fileMaker_product->productCategories) === 0) { syncUtils_echoLog($logLevel=3,'ic_sync_products',"WP product post #$idWPProductPost has no categories",$fileMaker_product->IdTransaction,$fileMaker_product); return false; }
		    
		    $bWoo_ProductCategory_arr = array();
			foreach (explode(',',$fileMaker_product->productCategories) as $fileMaker_idCategory)
			{
				$fileMaker_idCategory = trim($fileMaker_idCategory);
				
				if ($idWPCategoryPost = $GLOBALS['syncManager']->searchCategoryPost_byFileMakerIdCategory($GLOBALS['acfManager'],$fileMaker_idCategory))
				{
					//$category = bWoo_Category::loadOne($GLOBALS['api'], $idWPCategoryPost);
					
					//For this to work, what's important is the name & parent, it'd be used to check if it exists
					$bWoo_ProductCategory = new bWoo_ProductCategory();
					    $bWoo_ProductCategory->id($idWPCategoryPost);
					    /*
					    NOTE: If we want to have these fields, we must be sure to fill them + go in bWoo_Product::_toArray_fields() and change so not only id is passed
					    $bWoo_ProductCategory->setFieldVal('name',   $category->getFieldVal('name'));
					    $bWoo_ProductCategory->setFieldVal('slug',   $category->getFieldVal('slug'));
					    $bWoo_ProductCategory->setFieldVal('parent', $category->getFieldVal('parent'));
					    */
					$bWoo_ProductCategory_arr[] = $bWoo_ProductCategory;
				}
				else
				{
					syncUtils_echoLog($logLevel=3,'ic_sync_products',"ERROR: WP category post with filemaker id #".$fileMaker_idCategory." not found and must exist in advance",$fileMaker_product->IdTransaction,$fileMaker_product); return false;
				}
			}
			
			$bWoo_product->categories = $bWoo_ProductCategory_arr;
		}
		
        
		//Images
		{
			$bWoo_ProductImage_arr = array();
			
			if ($fileMaker_product->mainImgToSync)
			{
				$bWoo_productMainImage   = _WP_createUpdateProduct_makeProductImageObject_fromFileMakerImage($fileMaker_product->mainImgToSync, $position=0);
				$bWoo_ProductImage_arr[] = $bWoo_productMainImage;
			}
			
			foreach ($fileMaker_product->galleryImgsToSync as $galleryImgToSync)
			{
				$bWoo_productGalleryImage = _WP_createUpdateProduct_makeProductImageObject_fromFileMakerImage($galleryImgToSync, $position=$galleryImgToSync->ordinalSuffix);
				$bWoo_ProductImage_arr[]  = $bWoo_productGalleryImage;
			}
			
			if (count($bWoo_ProductImage_arr) > 0) { $bWoo_product->images = $bWoo_ProductImage_arr; }
		}
	}
	
	try
	{
		$bWoo_product->save($GLOBALS['api']);
		
		//ACF fields
		{
			$GLOBALS['acfManager']->post_setACFPostMeta($bWoo_product->id(), ICSyncManager::PRODUCT_ACF_BRAND, $fileMaker_product->productBrand);
			$GLOBALS['acfManager']->post_setACFPostMeta($bWoo_product->id(), ICSyncManager::PRODUCT_ACF_MODEL, $fileMaker_product->productModel);
			$GLOBALS['acfManager']->post_setACFPostMeta($bWoo_product->id(), ICSyncManager::PRODUCT_ACF_SIZE,  $fileMaker_product->productSize);
		}
		
        $success = true;
	}
	catch (Exception $e)
	{
		syncUtils_echoLog($logLevel=3,'ic_sync_products',"Filemaker product #".$fileMaker_product->UUIDProductUniverse." encountered exception: ".$e->getMessage(),$fileMaker_product->IdTransaction,$fileMaker_product); return false;
	}
	
	//Now, we can indicate to each pic to sync, if it's ok to send them to "done" / "error" dirs. NOTE: It's not about if -this- image worked, but if this whole sync operation worked
	_WP_createUpdateProduct_setImagesToSync_status($fileMaker_product, $success?ICSyncManager::SYNC_STATUS_DONE:ICSyncManager::SYNC_STATUS_ERROR);
    
    return $success;
}
	function _WP_searchProductPost_byFileMakerUUID($uuid)
	{
		$products = $GLOBALS['syncManager']->wooProductAttrs_searchProductBy_childTerm_label(ICSyncManager::PRODUCT_ATTRS_FILEMAKERID_WPSLUG, $uuid);
		
		if (count($products)) { return $products[0]; }
		
		return null;
	}
	function _WP_createUpdateProduct_makeProductImageObject_fromFileMakerImage(ic_sync_imgInfos $fileMakerImage, $position) //Main pic must have $position=0
	{
		$bWoo_productImage = new bWoo_ProductImage();
		
		$bWoo_productImage->setFieldVal('src',      docRootImgPathToUrl($fileMakerImage->filePath));
		$bWoo_productImage->setFieldVal('name',     $fileMakerImage->fileName);
		$bWoo_productImage->setFieldVal('alt',      $fileMakerImage->fileName);
		$bWoo_productImage->setFieldVal('position', $position);
		
		return $bWoo_productImage;
	}
	//NOTE: It's not about if -this- image worked, but if this whole sync operation worked
	function _WP_createUpdateProduct_setImagesToSync_status(ic_sync_filemaker2wp_product $fileMaker_product, $status)
	{
		if ($fileMaker_product->mainImgToSync)                               { $fileMaker_product->mainImgToSync->syncStatus = $status; }
		foreach ($fileMaker_product->galleryImgsToSync as $galleryImgToSync) {                 $galleryImgToSync->syncStatus = $status; }
	}



function docRootImgPathToUrl($docRootFilePath)
{
	$DOC_ROOT = $_SERVER['DOCUMENT_ROOT'];
	$absPath  = preg_replace("!^${DOC_ROOT}!", '', $docRootFilePath);
	
	return "https://$_SERVER[SERVER_NAME]$absPath";
}



function WP_updateProductStock(ic_sync_filemaker2wp_productStock $fileMaker_productStock)
{
    $idWPProductPost = null;
    $bWoo_product    = null;
    $success         = false;
    
    //Check if the product already exists in WP, then try to load it
    if ($idWPProductPost = _WP_searchProductPost_byFileMakerUUID($fileMaker_productStock->UUIDProductUniverse))
    {
    	$bWoo_product = bWoo_Product::loadOne($GLOBALS['api'], $idWPProductPost);
    	if ($bWoo_product === null) { syncUtils_echoLog($logLevel=3,'ic_sync_inventories_in',"WP product post #$idWPProductPost couldn't be loaded",$fileMaker_product->IdTransaction,$fileMaker_product); return false; }
    	
    	$finalQty = $bWoo_product->getFieldVal('stock_quantity') + $fileMaker_productStock->stockVariation;
    	
		$bWoo_product->setFieldVal('stock_quantity', $finalQty);
		$bWoo_product->setFieldVal('in_stock',       $finalQty > 0);
		
		try
		{
			$bWoo_product->save($GLOBALS['api']);
			
			$success = true;
		}
		catch (Exception $e)
		{
			syncUtils_echoLog($logLevel=3,'ic_sync_inventories_in',"Filemaker product #".$fileMaker_product->UUIDProductUniverse." encountered exception: ".$e->getMessage(),$fileMaker_product->IdTransaction,$fileMaker_product); return false;
		}
    }
    else
    {
    	syncUtils_echoLog($logLevel=3,'ic_sync_inventories_in',"ERROR: Filemaker product #".$fileMaker_productStock->UUIDProductUniverse." not found",$fileMaker_productStock->IdTransaction,$fileMaker_productStock); return false;
    }
    
    return $success;
}




function syncUtils_echoLog($logLevel, $tableName, $what, $IdTransaction=null, $varToDump=null)
{
    $message = " `$tableName`";
    if ($IdTransaction) { $message .= " #$IdTransaction"; }
    $message .= " - $what";
    if ($varToDump) { $message .= ' '.print_r($varToDump,true); }

    ICSyncManager::echoLog($logLevel, $tableName, $message);
}


function WP_notifyOrderedProducts(ICSyncManager $syncManager)
{
    syncUtils_echoLog($logLevel=1, 'ic_sync_inventories_out', 'Starting sync');
    
    $searchOptions = new bWoo_OrderSearchOptions();
    $searchOptions->status = 'processing';

    $orders = bWoo_Order::loadList($GLOBALS['api'], $searchOptions);
    $syncedOrders = $syncManager->sync_wp2filemaker_getSyncedOrders();
    
    if ($orders->getTotalRecordCount() > 0)
    {
    	//For each not-yet synced order
	    foreach($orders as $bWoo_order)
	    {
            $idOrder = $bWoo_order->getFieldVal('number');
            
            if(!$syncedOrders[$idOrder])
            {
               	syncUtils_echoLog($logLevel=2, 'ic_sync_inventories_out', 'Starting sync', $idOrder);
            	
                //Each of its detail line concerning a product
                foreach($bWoo_order->orderLines as $orderLine)
                {
                    $idWPProductPost = $orderLine->getFieldVal('product_id');
                    $sku             = $orderLine->getFieldVal('sku');
                    $quantity        = $orderLine->getFieldVal('quantity');
                    $stockVariation  = -$quantity;
                    $bWoo_product    = null;
                    
                    $bWoo_product = bWoo_Product::loadOne($GLOBALS['api'], $idWPProductPost);
                    if ($bWoo_product === null) { syncUtils_echoLog($logLevel=3,'ic_sync_inventories_out',"WP product post #$idWPProductPost couldn't be loaded",$sku); return false; }
                    
                    $finalQty = $bWoo_product->getFieldVal('stock_quantity');
                    
                    $orderedProduct = new ic_sync_wp2filemaker_orderedProduct();
                    $orderedProduct->idOrder             = $idOrder;
                    $orderedProduct->UUIDProductUniverse = _WP_notifyOrderedProducts_getProductUUID($bWoo_product);
                    $orderedProduct->stockVariation      = $stockVariation;
                    $orderedProduct->stockQty            = $finalQty;
                    
                    syncUtils_echoLog($logLevel=2, 'ic_sync_inventories_out', "About to notify ordered product ".print_r($orderedProduct,true));
                    $success = $syncManager->sync_wp2filemaker_notifyOrderedProduct($orderedProduct);
                    
                    syncUtils_echoLog($logLevel=2, 'ic_sync_inventories_out', "Notified ordered product ".print_r($orderedProduct,true));
                }
                
            	syncUtils_echoLog($logLevel=2, 'ic_sync_inventories_out', 'Done sync', $idOrder);
            }
	    }
	    
		syncUtils_echoLog($logLevel=1, 'ic_sync_inventories_out', "Done syncing");
	}
	else
	{
		syncUtils_echoLog($logLevel=1, 'ic_sync_inventories_out', "Nothing to sync");
	}
}
	//NOTE: This func is heavily hardcoded and should be replaced by something using ICSyncManager::PRODUCT_ATTRS_FILEMAKERID_WPSLUG...
	function _WP_notifyOrderedProducts_getProductUUID(bWoo_Product $product)
	{
		foreach($product->attributes as $attr)
		{
			if ($attr->id() === 5)
			{
				$options = $attr->getFieldVal('options');
				
				return (count($options) ? $options[0] : null);
			}
		}
		
		return null;
	}

?>