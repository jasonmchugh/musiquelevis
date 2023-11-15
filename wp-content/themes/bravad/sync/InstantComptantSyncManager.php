<?

class ICSyncManager
{
    const LOG_FILE = 'sync_log.txt';

    const SYNC_STATUS_TODO  = 1;
    const SYNC_STATUS_DONE  = 2;
    const SYNC_STATUS_ERROR = 3;
    
    const IMG_DIR_CAT_TODO   = '/wp-content/uploads/sync_images/categories';
    const IMG_DIR_CAT_DONE   = '/wp-content/uploads/sync_images/categories/done';
    const IMG_DIR_CAT_ERROR  = '/wp-content/uploads/sync_images/categories/error';
    const IMG_DIR_PROD_TODO  = '/wp-content/uploads/sync_images/products';
    const IMG_DIR_PROD_DONE  = '/wp-content/uploads/sync_images/products/done';
    const IMG_DIR_PROD_ERROR = '/wp-content/uploads/sync_images/products/error';
    
  //const CATEGORY_ID_PARENT_NONE    = '0000000000';
  //const CATEGORY_ID_PARENT_NONE    = '0';
    const CATEGORY_ID_PARENT_NONE    = '00000000';
    const CATEGORY_ACF_FILEMAKER_TAG = 'category_acf_filemaker_id';
    
    //Product ACF tags
    const PRODUCT_ACF_BRAND = 'brand';
    const PRODUCT_ACF_MODEL = 'model';
    const PRODUCT_ACF_SIZE  = 'size';
    //For each product attribute, their WP post ID & slug
	const PRODUCT_ATTRS_FILEMAKERID_POSTID    = 5;
	const PRODUCT_ATTRS_FILEMAKERID_WPSLUG    = 'filemaker-id';
	const PRODUCT_ATTRS_STORE_POSTID          = 1;
	const PRODUCT_ATTRS_STORE_WPSLUG          = 'succursale';
	const PRODUCT_ATTRS_PRODUCTSTATE_POSTID   = 3;
	const PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG   = 'product-state';
	const PRODUCT_ATTRS_OFFERTYPE_POSTID      = 2;
	const PRODUCT_ATTRS_OFFERTYPE_WPSLUG      = 'type-doffre';
	const PRODUCT_ATTRS_NOSHIPPING_POSTID     = 6;
	const PRODUCT_ATTRS_NOSHIPPING_WPSLUG     = 'no-shipping';
	const PRODUCT_ATTRS_SHIPPING_PRICE_POSTID = 7;
	const PRODUCT_ATTRS_SHIPPING_PRICE_WPSLUG = 'shipping';
    
    //For each "product state" attribute, each terms' enum value in `ic_sync_products`.`productState` and matching slug in WP
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_NEW_DBENUM         = 1;
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_NEW_WPSLUG         = 'product-state-new';
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_USED_DBENUM        = 2;
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_USED_WPSLUG        = 'product-state-used';
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_PARTS_DBENUM       = 3;
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_PARTS_WPSLUG       = 'product-state-parts';
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_DBENUM = 4;
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_WPSLUG = 'product-state-refurbished';
    const PRODUCT_ATTR_TERMS_PRODUCTSTATE_UNKNOWN_LABEL = 'Inconnu';
    //For each "offer type" attribute, each terms' matching slug in WP. Note that they come from `ic_sync_products`.`isFeatured` and `ic_sync_products`.`isPromotion`, so they don't have enum counterparts
    const PRODUCT_ATTR_TERMS_OFFERTYPE_FEATURED_WPSLUG    = 'produits-vedettes';
    const PRODUCT_ATTR_TERMS_OFFERTYPE_PROMOTIONS_WPSLUG  = 'promotions';
    const PRODUCT_ATTR_TERMS_OFFERTYPE_EXCLUSIVITE_WPSLUG = 'exclusivite';        //Apparently not used for now
    const PRODUCT_ATTR_TERMS_OFFERTYPE_FIXED_WPSLUG       = 'remis-a-neuf';       //Apparently not used for now
    const PRODUCT_ATTR_TERMS_OFFERTYPE_ONLINEONLY_WPSLUG  = 'en-ligne-seulement'; //Apparently not used for now
	
	
    private $_db    = null; //WPDBConnection instance
    private $_limit = null;
    
    
    public function __construct(WPDBConnection $db, $limit)
    {
        $this->_db    = $db;
        $this->_limit = $limit;
    }


    private function _echoLog($logLevel, $tableName, $what, $IdTransaction=null, $varToDump=null)
    {
        $message = " `$tableName`";
        if ($IdTransaction) { $message .= " #$IdTransaction"; }
        $message .= " - $what";
        if ($varToDump) { $message .= ' '.print_r($varToDump,true); }

        ICSyncManager::echoLog($logLevel, $tableName, $message);
    }
	public static function echoLog($logLevel, $tableName, $what, $insertSpacerBefore=false)
	{
	    $message = '';
	    
	    if ($insertSpacerBefore) { $message .= "--------------------------------------------------\n"; }
	    
	    $header          = "Cron id:".cronId_get()."|".date('Y-m-d H:i:s');
	    $tabs            = str_repeat("\t", $logLevel+1);
	    $nextRowsPadding = str_repeat(" ",strlen($header)).$tabs."\t";
	    
	    $what = str_replace("\n", "\n{$nextRowsPadding}", $what);
	    
	    $message .= "{$header}{$tabs}{$what}\n"
	    		 .  "{$nextRowsPadding}Time elapsed: ".cronId_timeElapsed()." secs\n";
	    
	    echo $message;
	    
	    file_put_contents(__DIR__.'/'.ICSyncManager::LOG_FILE, $message, FILE_APPEND);
	}

    private function _getRecordsToSynch($tableName)
    {
    	//Assign rows to synch to this cron instance
    	$query = "UPDATE `$tableName`
    			  SET `syncStatus_cronId` = ?
                  WHERE `syncStatus` = ? 
                    AND `syncStatus_cronId` IS NULL ";
        if ($this->_limit) { $query .= "LIMIT $this->_limit;"; }
		$this->_db->preparedQuery($query, array(cronId_get(),ICSyncManager::SYNC_STATUS_TODO));
		$this->_db->commit();
    	
    	//Only synch these
        $query = "SELECT *
                  FROM `$tableName`
                  WHERE `syncStatus_cronId` = ?
                  ORDER BY `IdTransaction` ASC ";
        return $this->_db->preparedQuery($query, array(cronId_get()));
    }
    
    private function _checkHasImgsToSync($docRootDir_todo, $prefix)
    {
        $foundImgs = array();
        
        $imgsDir = "$_SERVER[DOCUMENT_ROOT]$docRootDir_todo";
        foreach (glob("$imgsDir/$prefix*") as $filePath) //Ex if $prefix="1111-2222", finds files like "1111-2222.png", "1111-2222_.png", "1111-2222_1.png", "1111-2222notRelated_1.png"
        {
        	/*
            Find out what doesn't work, ex for "1111-2222":
            	"1111-2222.png"					-> ".png"
            	"1111-2222_.png"				-> "_.png"
            	"1111-2222_1.png"				-> "_1.png"
            	"1111-2222notRelated_1.png" 	-> "notRelated_1.png"
            */
        	$fileSize      = filesize($filePath);
            $fileName      = basename($filePath);                //File name with extension
            $remainingPart = substr($fileName, strlen($prefix)); //From the above, gives out ".png", "_.png", "_1.png", "notRelated_1.png"
            
            if ($fileSize === 0) { continue; } //Skip empty files, otherwise we get "bWoo_Product::save(): Caught a WC_API_Client_HTTP_Exception: Error: Fichier vide téléchargé. [woocommerce_product_image_upload_error]"
            
            if (preg_match("/^(_\d*)?\.[a-z]+$/i", $remainingPart, $output_array))
            {
                $ordinalSuffix = null; //Considering ".png", "_.png", "_0.png", "_1.png", would return null, null, 0, 1
                $isMain        = null;
                
                if (preg_match("/([^_]+)\.[a-z]+$/i", $remainingPart, $output_array)) { $ordinalSuffix = $output_array[1]; }
                $isMain = preg_match("/^(_[01]?)?\.[a-z]+$/i", $remainingPart, $output_array); //Considering ".png", "_.png", "_0.png", "_1.png"
            	
                $foundImgs[] = new ic_sync_imgInfos($filePath, $ordinalSuffix, $isMain, ICSyncManager::SYNC_STATUS_TODO);
            }
        }
        
        //Make sure that they come in good order, like "50.png", "50_.png", "50_0.png", "50_1.png", "50_2.png" ...
        sort($foundImgs); //NOTE: If ever we have more than 9 pics, 10 will come before 2 -> 1, 10, 11, 12, 2, 3, 4... Could use usort()
        
        return $foundImgs;
    }

    private function _updateSyncStatus($tableName, $IdTransaction, $success)
    {
        $syncStatus     = ($success ? ICSyncManager::SYNC_STATUS_DONE : ICSyncManager::SYNC_STATUS_ERROR);
        $syncStatusText = ($success ?               'SYNC_STATUS_DONE':               'SYNC_STATUS_ERROR');
        
        $query = "UPDATE `$tableName`
                  SET `syncStatus`              = ?,
                      `transactionModifiedDate` = NOW()
                  WHERE `IdTransaction` = ?
                  LIMIT 1;";
        $this->_db->preparedQuery($query, array($syncStatus,$IdTransaction));

        $this->_echoLog($logLevel=3, $tableName, "syncStatus updated to: $syncStatusText", $IdTransaction);
    }
    private function _updateSynchedImgsCounts($tableName, $IdTransaction, $synchedImgsCount_success, $synchedImgsCount_failure)
    {
        $query = "UPDATE `$tableName`
                  SET `QtyImageSynch_ok`   = ?,
                      `QtyImageSynch_fail` = ?
                  WHERE `IdTransaction` = ?
                  LIMIT 1;";
        $this->_db->preparedQuery($query, array($synchedImgsCount_success,$synchedImgsCount_failure,$IdTransaction));
        
        $this->_echoLog($logLevel=3, $tableName, "sync imgs ok: {$synchedImgsCount_success}, failed: {$synchedImgsCount_failure}", $IdTransaction);
    }

    //Moves a "/sync_images/C_bob123.png" to "/sync_images_done/12345_C_bob123.png", depending on status. Returns true or an error message
    private function _cleanupAfterSync($tableName, ic_sync_imgInfos $imgInfos, $IdTransaction, $docRootDir_done, $docRootDir_error) //To keep track of pk
    {
        $destDirPath = null;
        $destDirName = null;
        $wasAnError  = true;
        
        switch ($imgInfos->syncStatus)
        {
            case ICSyncManager::SYNC_STATUS_DONE:
                $destDirName = $docRootDir_done;
                break;
            case ICSyncManager::SYNC_STATUS_ERROR:
                $destDirName = $docRootDir_error;
                break;
            default:
                $this->_echoLog($logLevel=3, $tableName, 'Unknown syncStatus: '.print_r($imgInfos->syncStatus,true), $IdTransaction);
        }
        
        if ($destDirName)
        {
	        $destDirPath = "$_SERVER[DOCUMENT_ROOT]$destDirName";
	        if (!is_dir($destDirPath)) { mkdir($destDirPath); }

	        $destFileName = $IdTransaction.'_'.$imgInfos->fileName;
	        if (rename($imgInfos->filePath, "$destDirPath/$destFileName"))
	        {
	            $this->_echoLog($logLevel=3, $tableName, "Moved $imgInfos->fileName in $destDirName/ as $destFileName", $IdTransaction);
	            
	            if ($imgInfos->syncStatus===ICSyncManager::SYNC_STATUS_DONE) { $wasAnError=false; }
	        }
	        else
	        {
	            $this->_echoLog($logLevel=3, $tableName, "Couldn't move $imgInfos->fileName in $destDirName/ as $destFileName", $IdTransaction);
	        }
	    }
	    
	    return !$wasAnError;
    }
    
    //Check if we reached synch limit for script execution
    private function _checkReachedSyncLimit($tableName, $syncedRowCount, $currentRowsToSync)
    {
        if (cronId_shouldStop())
        {
            $this->_echoLog($logLevel=1, $tableName, "Reached sync time limit - Exiting script");
            cronId_stop($GLOBALS['db']);
        }
        else if ($this->_limit !== null && $syncedRowCount >= $this->_limit)
        {
            $this->_echoLog($logLevel=1, $tableName, "Reached sync limit of $this->_limit - Exiting script");
            cronId_stop($GLOBALS['db']);
        }
        else if ($syncedRowCount == $currentRowsToSync)
        {
            $this->_echoLog($logLevel=1, $tableName, 'Done syncing');
        }
    }
    
    
    
    public function sync_filemaker2wp_categories($syncImages)
    {
        $this->_echoLog($logLevel=1, 'ic_sync_categories', 'Starting sync');

        $records = $this->_getRecordsToSynch('{wp_}sync_categories');
        if (count($records) > 0)
        {
        	$current_syncCount = 0;
        	
            foreach ($records as $record)
            {				// sleep for 10 seconds				sleep(10);
                $category = new ic_sync_filemaker2wp_category($record);

                $this->_sync_filemaker2wp_categories_one($category, $syncImages);

                $this->_db->commit();
                $current_syncCount++;
                
            	//Check if we reached synch limit for categories, as we shouldn't parse products, as long as we have categories remaining
            	$this->_checkReachedSyncLimit('ic_sync_categories', $current_syncCount, count($records));
            }
            
        	//Check if we reached synch limit for categories, as we shouldn't parse products, as long as we have categories remaining
        	$this->_checkReachedSyncLimit('ic_sync_categories', $current_syncCount, count($records));
        }
        else { $this->_echoLog($logLevel=1, 'ic_sync_categories', 'Nothing to sync'); }
    }
        private function _sync_filemaker2wp_categories_one(ic_sync_filemaker2wp_category $category, $syncImages)
        {
            $this->_echoLog($logLevel=2, 'ic_sync_categories', "\n");
            $this->_echoLog($logLevel=2, 'ic_sync_categories', 'Starting sync', $category->IdTransaction);

            //For categories, we should only get one img, but we never know... And only use the ones that "seems" the most likely to be main ones
            if ($syncImages)
            {
                $foundImgs = $this->_checkHasImgsToSync(ICSyncManager::IMG_DIR_CAT_TODO, $prefix=$category->idCategory);
                if (count($foundImgs) > 0)
                {
                    $firstFoundImg = $foundImgs[0];
                    if ($firstFoundImg->isMain)
                    {
                        $category->mainImgToSync = $firstFoundImg;
                        $this->_echoLog($logLevel=3, 'ic_sync_categories', "Found main img: $firstFoundImg->fileName", $category->IdTransaction);
                    }
                    else { $this->_echoLog($logLevel=3, 'ic_sync_categories', "Found img not respecting main pattern: $firstFoundImg->fileName", $category->IdTransaction); }

                    if (count($foundImgs) > 1) { $this->_echoLog($logLevel=3, 'ic_sync_categories', "Had more than 1 img to sync, but can only use 1", $category->IdTransaction); }
                }
                else { $this->_echoLog($logLevel=3, 'ic_sync_categories', 'No imgs found', $category->IdTransaction); }
            }
            
            $success = WP_createUpdateCategory($category);
            
            $this->_echoLog($logLevel=3, 'ic_sync_categories', 'WP sync state: '.($success?'OK':'ERROR'), $category->IdTransaction);

            $this->_updateSyncStatus('{wp_}sync_categories', $category->IdTransaction, $success);

            if ($category->mainImgToSync) { $this->_cleanupAfterSync('ic_sync_categories',$category->mainImgToSync,$category->IdTransaction,ICSyncManager::IMG_DIR_CAT_DONE,ICSyncManager::IMG_DIR_CAT_ERROR); }

            $this->_echoLog($logLevel=3, 'ic_sync_categories', 'Done sync', $category->IdTransaction);
        }
	function searchCategoryPost_byFileMakerIdCategory(ACFManager $acfManager, $fileMaker_idCategory)
	{
		return $acfManager->post_searchWithACFValue_one(ICSyncManager::CATEGORY_ACF_FILEMAKER_TAG, $fileMaker_idCategory);
	}
	function setCategoryFileMakerCustomField(ACFManager $acfManager, $idWPCategoryPost, $fileMaker_idCategory)
	{
		$acfManager->post_setACFValue($idWPCategoryPost, ICSyncManager::CATEGORY_ACF_FILEMAKER_TAG, $fileMaker_idCategory);
	}
    
    
    
    
    
    public function sync_filemaker2wp_products($syncImages)
    {
        $this->_echoLog($logLevel=1, 'ic_sync_products', 'Starting sync');

        $records = $this->_getRecordsToSynch('{wp_}sync_products');
        if (count($records) > 0)
        {
            $current_syncCount = 0;
        	
            foreach ($records as $record)
            {				// sleep for 10 seconds				sleep(10);
                $product = new ic_sync_filemaker2wp_product($record);

                $this->_sync_filemaker2wp_products_one($product, $syncImages);

                $this->_db->commit();
                $current_syncCount++;
                
	            //Check if we reached synch limit for products, as we shouldn't parse inventories, as long as we have products remaining
	            $this->_checkReachedSyncLimit('ic_sync_products', $current_syncCount, count($records));
            }
            
            //Check if we reached synch limit for products, as we shouldn't parse inventories, as long as we have products remaining
            $this->_checkReachedSyncLimit('ic_sync_products', $current_syncCount, count($records));
        }
        else { $this->_echoLog($logLevel=1, 'ic_sync_products', 'Nothing to sync'); }
    }
        private function _sync_filemaker2wp_products_one(ic_sync_filemaker2wp_product $product, $syncImages)
        {
            $this->_echoLog($logLevel=2, 'ic_sync_products', "\n");
            $this->_echoLog($logLevel=2, 'ic_sync_products', 'Starting sync', $product->IdTransaction);

            //Products may have one main img and other secondary/gallery imgs
            if ($syncImages)
            {
                $foundImgs = $this->_checkHasImgsToSync(ICSyncManager::IMG_DIR_PROD_TODO, $prefix=$product->UUIDProductUniverse);
                
                if (count($foundImgs))
                {
                    foreach ($foundImgs as $foundImg)
                    {
                        if ($foundImg->isMain && $product->mainImgToSync === null) //For cases where we have multiples imgs matching the regex
                        {
                            $product->mainImgToSync = $foundImg;
                            $this->_echoLog($logLevel=3, 'ic_sync_products', "Found main img: $foundImg->fileName", $product->IdTransaction);
                        }
                        else
                        {
                            $product->galleryImgsToSync[] = $foundImg;
                            $this->_echoLog($logLevel=3, 'ic_sync_products', "Found gallery img: $foundImg->fileName", $product->IdTransaction);
                        }
                    }

                    if ($product->mainImgToSync === null) { $this->_echoLog($logLevel=3, 'ic_sync_products', 'No main img found', $product->IdTransaction); }
                }
                else { $this->_echoLog($logLevel=3, 'ic_sync_products', 'No imgs found', $product->IdTransaction); }
            }

            $success = WP_createUpdateProduct($product);

            $this->_echoLog($logLevel=3, 'ic_sync_products', 'WP sync state: '.($success?'OK':'ERROR'), $product->IdTransaction);

            $this->_updateSyncStatus('{wp_}sync_products', $product->IdTransaction, $success);

            //Cleanup sync images - move them to other dirs
            {
            	$synchedImgsCount_success = 0;
            	$synchedImgsCount_failure = 0;
            	
                if ($product->mainImgToSync)
            	{
            		if ($this->_cleanupAfterSync('ic_sync_products',$product->mainImgToSync,$product->IdTransaction,ICSyncManager::IMG_DIR_PROD_DONE,ICSyncManager::IMG_DIR_PROD_ERROR))
            		{
            			$synchedImgsCount_success++;
            		}
            		else { $synchedImgsCount_failure++; }
            	}
                foreach ($product->galleryImgsToSync as $galleryImgToSync)
            	{
            		if ($this->_cleanupAfterSync('ic_sync_products',$galleryImgToSync,$product->IdTransaction,ICSyncManager::IMG_DIR_PROD_DONE,ICSyncManager::IMG_DIR_PROD_ERROR))
            		{
            			$synchedImgsCount_success++;
            		}
            		else { $synchedImgsCount_failure++; }
            	}
            	
            	$this->_updateSynchedImgsCounts('{wp_}sync_products', $product->IdTransaction, $synchedImgsCount_success, $synchedImgsCount_failure);
            }
            
            $this->_updateProductSummaryInfos($product->UUIDProductUniverse);
            
            $this->_echoLog($logLevel=3, 'ic_sync_products', 'Done sync', $product->IdTransaction);
        }
    
    
    private function _updateProductSummaryInfos($UUIDProductUniverse)
    {
    	$product_createdDate           = null;
		$product_qtyImageSynch_todo    = 0;
		$product_qtyImageSynch_ok      = 0;
		$product_qtyImageSynch_fail    = 0;
    	$product_stockQty              = 0;
    	$product_mostRecentUpdate      = null;
    	$product_updatesCount          = 0;
    	$product_syncStatusCount_todo  = 0;
		$product_syncStatusCount_done  = 0;
		$product_syncStatusCount_error = 0;
    	
    	$in_sumStockVariation     = 0;
		$in_mostRecentUpdate      = null;
		$in_updatesCount          = 0;
    	$in_syncStatusCount_todo  = 0;
		$in_syncStatusCount_done  = 0;
		$in_syncStatusCount_error = 0;
		
    	$out_sumStockVariation     = 0;
		$out_mostRecentUpdate      = null;
		$out_updatesCount          = 0;
    	$out_syncStatusCount_todo  = 0;
		$out_syncStatusCount_done  = 0;
		$out_syncStatusCount_error = 0;
		
		//Products
    	{
	    	$query = "SELECT MIN(`transactionCreatedDate`) AS `createdDate`, MAX(`transactionModifiedDate`) AS `mostRecentUpdate`, SUM(`QtyImage`) AS `qtyImageSynch_todo`, SUM(`QtyImageSynch_ok`) AS `qtyImageSynch_ok`, SUM(`QtyImageSynch_fail`) AS `qtyImageSynch_fail`, COUNT(*) AS `updatesCount`,
	    					 SUM(IF(`syncStatus`=1,1,0)) AS `syncStatusCount_todo`,
	    					 SUM(IF(`syncStatus`=2,1,0)) AS `syncStatusCount_done`,
	    					 SUM(IF(`syncStatus`=3,1,0)) AS `syncStatusCount_error`
	    			  FROM `{wp_}sync_products`
	    			  WHERE `UUIDProductUniverse` = ?
	    			  GROUP BY `UUIDProductUniverse`;";
			$result = $this->_db->preparedQuery($query, array($UUIDProductUniverse));
			if (count($result))
			{
				$record = $result[0];
				$product_createdDate           = $record['createdDate'];
				$product_qtyImageSynch_todo    = $record['qtyImageSynch_todo'] ? $record['qtyImageSynch_todo'] : 0;
				$product_qtyImageSynch_ok      = $record['qtyImageSynch_ok']   ? $record['qtyImageSynch_ok']   : 0;
				$product_qtyImageSynch_fail    = $record['qtyImageSynch_fail'] ? $record['qtyImageSynch_fail'] : 0;
				$product_mostRecentUpdate      = $record['mostRecentUpdate'];
				$product_updatesCount          = $record['updatesCount'];
				$product_syncStatusCount_todo  = $record['syncStatusCount_todo']  ? $record['syncStatusCount_todo']  : 0;
				$product_syncStatusCount_done  = $record['syncStatusCount_done']  ? $record['syncStatusCount_done']  : 0;
				$product_syncStatusCount_error = $record['syncStatusCount_error'] ? $record['syncStatusCount_error'] : 0;
			}
			
	    	$query = "SELECT `stockQty`
	    			  FROM `{wp_}sync_products`
	    			  WHERE `UUIDProductUniverse` = ?
	    			  ORDER BY `idTransaction` DESC
	    			  LIMIT 1;";
			$result = $this->_db->preparedQuery($query, array($UUIDProductUniverse));
			if (count($result))
			{
				$record = $result[0];
				$product_stockQty = $record['stockQty'];
			}
		}
		
		//Inventory in - note that for some reason, JP sends stockVariation negatively...
    	{
	    	$query = "SELECT SUM(`stockVariation`) AS `sumStockVariation`, MAX(`transactionCreatedDate`) AS `mostRecentCreated`, MAX(`transactionModifiedDate`) AS `mostRecentUpdate`, COUNT(*) AS `updatesCount`,
	    					 SUM(IF(`syncStatus`=1,1,0)) AS `syncStatusCount_todo`,
	    					 SUM(IF(`syncStatus`=2,1,0)) AS `syncStatusCount_done`,
	    					 SUM(IF(`syncStatus`=3,1,0)) AS `syncStatusCount_error`
	    			  FROM `{wp_}sync_inventories_in`
	    			  WHERE `UUIDProductUniverse` = ?
	    			  GROUP BY `UUIDProductUniverse`;";
			$result = $this->_db->preparedQuery($query, array($UUIDProductUniverse));
			if (count($result))
			{
				$record = $result[0];
				$in_sumStockVariation     = -$record['sumStockVariation'];
				$in_mostRecentUpdate      = $record['mostRecentCreated']; //NOTE: Don't use mostRecentUpdate because it's always empty
				$in_updatesCount          = $record['updatesCount'];
				$in_syncStatusCount_todo  = $record['syncStatusCount_todo']  ? $record['syncStatusCount_todo']  : 0;
				$in_syncStatusCount_done  = $record['syncStatusCount_done']  ? $record['syncStatusCount_done']  : 0;
				$in_syncStatusCount_error = $record['syncStatusCount_error'] ? $record['syncStatusCount_error'] : 0;
			}
		}
		
		//Inventory out
    	{
	    	$query = "SELECT SUM(`stockVariation`) AS `sumStockVariation`, MAX(`transactionCreatedDate`) AS `mostRecentCreated`, MAX(`transactionModifiedDate`) AS `mostRecentUpdate`, COUNT(*) AS `updatesCount`,
	    					 SUM(IF(`syncStatus`=1,1,0)) AS `syncStatusCount_todo`,
	    					 SUM(IF(`syncStatus`=2,1,0)) AS `syncStatusCount_done`,
	    					 SUM(IF(`syncStatus`=3,1,0)) AS `syncStatusCount_error`
	    			  FROM `{wp_}sync_inventories_out`
	    			  WHERE `UUIDProductUniverse` = ?
	    			  GROUP BY `UUIDProductUniverse`;";
			$result = $this->_db->preparedQuery($query, array($UUIDProductUniverse));
			if (count($result))
			{
				$record = $result[0];
				$out_sumStockVariation     = $record['sumStockVariation'];
				$out_mostRecentUpdate      = $record['mostRecentCreated']; //NOTE: Don't use mostRecentUpdate because it's always empty
				$out_updatesCount          = $record['updatesCount'];
				$out_syncStatusCount_todo  = $record['syncStatusCount_todo']  ? $record['syncStatusCount_todo']  : 0;
				$out_syncStatusCount_done  = $record['syncStatusCount_done']  ? $record['syncStatusCount_done']  : 0;
				$out_syncStatusCount_error = $record['syncStatusCount_error'] ? $record['syncStatusCount_error'] : 0;
			}
		}
		
		
		//Do the query
		{
			$modifiedDate         = max($product_mostRecentUpdate, $in_mostRecentUpdate, $out_mostRecentUpdate);
			$totalInventoryChange = $in_sumStockVariation + $out_sumStockVariation;
			$totalUpdatesCount          = $product_updatesCount          + $in_updatesCount          + $out_updatesCount;
			$totalSyncStatusCount_todo  = $product_syncStatusCount_todo  + $in_syncStatusCount_todo  + $out_syncStatusCount_todo;
			$totalSyncStatusCount_done  = $product_syncStatusCount_done  + $in_syncStatusCount_done  + $out_syncStatusCount_done;
			$totalSyncStatusCount_error = $product_syncStatusCount_error + $in_syncStatusCount_error + $out_syncStatusCount_error;
			
			$query = "INSERT INTO `{wp_}sync_products_status` (`UUIDProductUniverse`, `QtyImageSynch_todo`, `QtyImageSynch_ok`, `QtyImageSynch_fail`, `stockQty`, `inventoryChangesQty`, `createdDate`, `modifiedDate`, `updatesCount`, `syncStatusCount_todo`, `syncStatusCount_done`, `syncStatusCount_error`)
					  VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
					  ON DUPLICATE KEY UPDATE
						`QtyImageSynch_todo`    = VALUES(`QtyImageSynch_todo`),
						`QtyImageSynch_ok`      = VALUES(`QtyImageSynch_ok`),
						`QtyImageSynch_fail`    = VALUES(`QtyImageSynch_fail`),
						`stockQty`              = VALUES(`stockQty`),
						`inventoryChangesQty`   = VALUES(`inventoryChangesQty`),
						`createdDate`           = VALUES(`createdDate`),
						`modifiedDate`          = VALUES(`modifiedDate`),
						`updatesCount`          = VALUES(`updatesCount`),
						`syncStatusCount_todo`  = VALUES(`syncStatusCount_todo`),
						`syncStatusCount_done`  = VALUES(`syncStatusCount_done`),
						`syncStatusCount_error` = VALUES(`syncStatusCount_error`);";
			try
			{
				$this->_db->preparedQuery($query, array($UUIDProductUniverse,$product_qtyImageSynch_todo,$product_qtyImageSynch_ok,$product_qtyImageSynch_fail,$product_stockQty,$totalInventoryChange,$product_createdDate,$modifiedDate,$totalUpdatesCount,$totalSyncStatusCount_todo,$totalSyncStatusCount_done,$totalSyncStatusCount_error));
			}
			catch (Exception $e)
			{
				$this->_echoLog($logLevel=5, 'ic_sync_products_status', 'Got query problem updating status: '.$e->getMessage(), $UUIDProductUniverse);
			}
		}
		
		$this->_echoLog($logLevel=4, 'ic_sync_products_status', 'Updated summary infos', $UUIDProductUniverse);
    }
    	public function updateAllProductsSummaryInfos()
    	{
			$this->_echoLog($logLevel=1, 'updateAllProductsSummaryInfos', 'About to rebuild all products summary infos');
			
	    	$query = "SELECT DISTINCT `UUIDProductUniverse`
	    			  FROM `{wp_}sync_products`;";
			$result = $this->_db->preparedQuery($query, array());
			foreach ($result as $record)
			{
				$this->_updateProductSummaryInfos($record['UUIDProductUniverse']);
			}
			
			$this->_echoLog($logLevel=1, 'updateAllProductsSummaryInfos', 'Done rebuilding all products summary infos');
    	}
    
    	//Data coming from /wp-content/themes/bravad/templates/nombre.php, callable to https://kijiji.instantcomptant.ca/bravad-nombre-images/
    	public function update_LiveNbOfImagesInWP()
    	{
    		$this->_echoLog($logLevel=1, 'update_LiveNbOfImagesInWP', 'Start');
    		
    		$wpData = null;
    		$wpProductQties = array();
    		$bXHTTPRequest = new bXHTTPRequest('https://kijiji.instantcomptant.ca/bravad-nombre-images/?v='.date('U'), bXHTTPRequest::METHOD_GET);
		    $response = $bXHTTPRequest->request();
		    if ($response !== false)
		    {
		        if ($wpData = json_decode($response)) //[{sku,total}]
		        {
			        foreach ($wpData as $wpDataOne)
			        {
			        	$wpProductQties[$wpDataOne->sku] = $wpDataOne->total;
			        }
		        }
		        else
		        {
		        	$this->_echoLog($logLevel=1, 'update_LiveNbOfImagesInWP', 'ERROR - Not decodable JSON');
		        }
		    }
		    else
		    {
		    	$this->_echoLog($logLevel=1, 'update_LiveNbOfImagesInWP', 'ERROR - bXHTTPRequest: '.$bXHTTPRequest->lastResponseCode().' '.$bXHTTPRequest->lastResponseMessage());
		    }
    		
    		$products = array();
    		$query = "SELECT DISTINCT `UUIDProductUniverse`, `sku`
    				  FROM `{wp_}sync_products`;";
			$result = $this->_db->preparedQuery($query, array());
			foreach ($result as $record)
			{
				$UUIDProductUniverse = $record['UUIDProductUniverse'];
				$sku                 = $record['sku'];
				
				$products[] = array
				(
					'UUIDProductUniverse' => $UUIDProductUniverse,
					'sku'                 => $sku,
					'total'               => isset($wpProductQties[$sku]) ? $wpProductQties[$sku] : null //Happens when /templates/nombre.php can't find them (probably because they don't even exist yet)
				);
			}
			
    		foreach ($products as $productInfo)
    		{
    			if ($productInfo['total']===null) { continue; } //Skip products that we can't get infos on how many pics they have. Happens when /templates/nombre.php can't find them (probably because they don't even exist yet)
    			
    			$query = "UPDATE `{wp_}sync_products_status`
    					  SET `LiveNbOfImagesInWP` = ?
    					  WHERE `UUIDProductUniverse` = ?
    					  LIMIT 1;";
			  	$this->_db->preparedQuery($query, array($productInfo['total'],$productInfo['UUIDProductUniverse']));
    		}
    		
    		$this->_echoLog($logLevel=1, 'update_LiveNbOfImagesInWP', 'End');
    	}
    
    /*
    Usage example:
    	wooProductAttrs_listDefs()
    		->
	    		succursale
	    		type-doffre
	    		product-state
	    		sync-status
	    		filemaker-id
	    		no-shipping
	We should have used the following API though: /wp-json/wc/v2/products/attributes/<attribute_id>/terms
    */
    public function wooProductAttrs_listDefs()
    {
    	$attrDefs = array();
    	
    	$query = "SELECT *
    			  FROM `{wp_}woocommerce_attribute_taxonomies`;";
		$records = $this->_db->preparedQuery($query, array());
		foreach ($records as $record)
		{
			$attrDef = array
			(
				'id'    => $record['attribute_id'],
				'tag'   => $record['attribute_label'],
				'label' => $record['attribute_label'],
				'type'  => $record['attribute_type']
			);
			$attrDefs[] = $attrDef;
		}
		
		return $attrDefs;
    }
    /*
    Usage ex:
    	wooProductAttrs_listChildTermDefs(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG)
    	or wooProductAttrs_listChildTermDefs('product-state')
    		->
				74	74	product-state-new			Neuf
				75	75	product-state-used			Usagé
				76	76	product-state-parts			Pour pièce
				77	77	product-state-refurbished	Reconditionné
	We should have used the following API though: /wp-json/wc/v2/products/attributes/<attribute_id>/terms
    */
    public function wooProductAttrs_listChildTermDefs($attrDefTag)
    {
    	$attrDefs = array();
    	
    	$query = "SELECT `taxonomy`.`term_taxonomy_id`, `terms`.`term_id`, `terms`.`slug`, `terms`.`name`
				  FROM `{wp_}term_taxonomy` AS `taxonomy`
				  INNER JOIN `{wp_}terms` AS `terms` ON (`taxonomy`.`term_id`=`terms`.`term_id`)
				  WHERE `taxonomy`.`taxonomy` = ?;";
		$records = $this->_db->preparedQuery($query, array('pa_'.$attrDefTag));
		foreach ($records as $record)
		{
			$attrDef = array
			(
				'term_taxonomy_id' => $term_taxonomy_id,
				'term_id'          => $term_id,
				'slug'             => $slug,
				'label'            => $name
			);
			$attrDefs[] = $attrDef;
		}
		
		return $attrDefs;
    }
    /*
    Usage ex:
    	wooProductAttrs_childTerm_id(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_TAG)
    	or wooProductAttrs_childTerm_id('product-state', 'product-state-refurbished')
			-> 77
	We should have used the following API though: /wp-json/wc/v2/products/attributes/<attribute_id>/terms
    */
    public function wooProductAttrs_childTerm_id($attrDefTag, $attrTermSlug)
    {
    	return $this->_wooProductAttrs_childTerm_x($attrDefTag, $attrTermSlug, '`taxonomy`.`term_taxonomy_id`'); //Thought it should have been `terms`.`term_id` though...
    }
    /*
    Usage ex:
    	wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_TAG)
    	or wooProductAttrs_childTerm_label('product-state', 'product-state-refurbished')
			-> "Reconditionné"
    */
    public function wooProductAttrs_childTerm_label($attrDefTag, $attrTermSlug)
    {
    	return $this->_wooProductAttrs_childTerm_x($attrDefTag, $attrTermSlug, '`terms`.`name`');
    }
	    private function _wooProductAttrs_childTerm_x($attrDefTag, $attrTermSlug, $fieldName)
	    {
	    	$query = "SELECT $fieldName AS `value`
					  FROM `{wp_}term_taxonomy` AS `taxonomy`
					  INNER JOIN `{wp_}terms` AS `terms` ON (`taxonomy`.`term_id`=`terms`.`term_id`)
					  WHERE `taxonomy`.`taxonomy` LIKE ? 
					    AND `terms`.`slug` = ?
					  LIMIT 1;";
	//				  echo "\npa_$attrDefTag|".utf8_encode($attrTermSlug)."\n$query";
			$records = $this->_db->preparedQuery($query, array('%pa_'.$attrDefTag, utf8_encode($attrTermSlug))); //Sometimes it's "order_pa_xxx" and other times "pa_xxx"
			if (count($records) > 0)
			{
				return $records[0]['value'];
			}
			
			return null;
	    }
    /*
    Usage ex:
    	wooProductAttrs_searchProductBy_childTerm_label(ICSyncManager::PRODUCT_ATTRS_FILEMAKERID_WPSLUG, '011C3CF7-9DA5-2A46-9F21-0EE0B0829EF1')
    	or wooProductAttrs_searchProductBy_childTerm_label('filemaker-id', '011C3CF7-9DA5-2A46-9F21-0EE0B0829EF1')
	Returns an arr of product_idPost
	Intended for textual-type attrs, but might work for list-type ones too
    */
    public function wooProductAttrs_searchProductBy_childTerm_label($attrDefTag, $attrTermLabel)
    {
    	$products = array();
    	
    	/*
    	NOT GOOD:
		    $query = "SELECT DISTINCT `productTermVals`.`object_id` AS `product_idPost` 
					  FROM `{wp_}termmeta`                 AS `productAttrDefs`
					  INNER JOIN `{wp_}terms`              AS `terms`           ON (`productAttrDefs`.`term_id` = `terms`.`term_id`) 
					  INNER JOIN `{wp_}term_relationships` AS `productTermVals` ON (`productAttrDefs`.`term_id` = `productTermVals`.`term_taxonomy_id`) 
					  WHERE `productAttrDefs`.`meta_key` LIKE ?
					    AND `terms`.`name` = ?;";
			echo $this->_db->debugPreparedQuery($query, array('%pa_'.$attrDefTag, utf8_encode($attrTermLabel)));;exit;
			$records = $this->_db->preparedQuery($query, array('%pa_'.$attrDefTag, utf8_encode($attrTermLabel))); //Sometimes it's "order_pa_xxx" and other times "pa_xxx"
			if (count($records) > 0)
			{
				$products[] = $records[0]['product_idPost'];
			}
		*/
    	
	    $query = "SELECT DISTINCT `productTermVals`.`object_id` AS `product_idPost` 
				  FROM `{wp_}term_taxonomy`            AS `productAttrDefs`
				  INNER JOIN `{wp_}terms`              AS `terms`           ON (`productAttrDefs`.`term_id` = `terms`.`term_id`) 
				  INNER JOIN `{wp_}term_relationships` AS `productTermVals` ON (`productAttrDefs`.`term_id` = `productTermVals`.`term_taxonomy_id`) 
				  WHERE `productAttrDefs`.`taxonomy` LIKE ?
				    AND `terms`.`name` = ?;";
		$records = $this->_db->preparedQuery($query, array('%pa_'.$attrDefTag, utf8_encode($attrTermLabel))); //Sometimes it's "order_pa_xxx" and other times "pa_xxx"
		if (count($records) > 0)
		{
			$products[] = $records[0]['product_idPost'];
		}
		
		return $products;
    }
	    
	    

	    
    
    //Takes an id from `ic_sync_products`.`productState` (or $fileMaker_product->productState) and get its label (because Woo can't link attrs to products with attr term's tag... -_-
    public function ic_attrTermId2Label_productState($dbEnumVal) //Const of "ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_x_DBENUM"
    {
    	switch ($dbEnumVal)
    	{
    		case ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_NEW_DBENUM:         return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_NEW_WPSLUG);
    		case ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_USED_DBENUM:        return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_USED_WPSLUG);
    		case ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_PARTS_DBENUM:       return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_PARTS_WPSLUG);
    		case ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_DBENUM: return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_PRODUCTSTATE_WPSLUG, ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_REFURBISHED_WPSLUG);
    	}
    	
    	return ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_UNKNOWN_LABEL;
    }
    //Same as the above, except that we don't have enums in sync tables, so we use the WP slugs straight away
    public function ic_attrTermId2Label_offerType($offerType_term_wpSlug) //Const of "ICSyncManager::PRODUCT_ATTR_TERMS_OFFERTYPE_x_WPSLUG"
    {
    	return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_OFFERTYPE_WPSLUG, $offerType_term_wpSlug);
    }
    
    //Takes a `ic_sync_products`.`idStore` (or $fileMaker_product->idStore) and convert it to something like "sherbrooke-belvedere"
    public function shippingClass_dbEnum2WCSettingsShippingClassSlug($dbEnumVal)
    {
    	return $this->_shippingClass_dbEnum2_x($dbEnumVal, '`wc_settings_shipping_classes_slug`');
	}
	//Like the above, but to get its product attribute term counterpart
    public function shippingClass_dbEnum2ProductAttrTermLabel($dbEnumVal)
    {
    	$attrTermSlug = $this->_shippingClass_dbEnum2_x($dbEnumVal, '`pa_attributeTermSlug`');
    	
    	return $this->wooProductAttrs_childTerm_label(ICSyncManager::PRODUCT_ATTRS_STORE_WPSLUG, $attrTermSlug);
	}
		private function _shippingClass_dbEnum2_x($dbEnumVal, $fieldName)
	    {
	    	$query = "SELECT $fieldName AS `value`
					  FROM `{wp_}sync_stores`
					  WHERE `fileMaker_idStore` = ?
					  LIMIT 1;";
		    $records = $this->_db->preparedQuery($query, array($dbEnumVal));
		    if (count($records) > 0)
		    {
		    	return $records[0]['value'];
		    }
		    
		    return null;
		}
    
    
    public function sync_filemaker2wp_productStocks()
    {
        $this->_echoLog($logLevel=1, 'ic_sync_inventories_in', 'Starting sync');
        
        $records = $this->_getRecordsToSynch('{wp_}sync_inventories_in');
        if (count($records) > 0)
        {
            $current_syncCount = 0;
        	
            foreach ($records as $record)
            {			        // sleep for 10 seconds				sleep(10);				
                $productStock = new ic_sync_filemaker2wp_productStock($record);
                
                $this->_sync_filemaker2wp_productStocks($productStock);
                
                $this->_db->commit();
                $current_syncCount++;
                
            	$this->_checkReachedSyncLimit('ic_sync_inventories_in', $current_syncCount, count($records));
            }
            
            $this->_checkReachedSyncLimit('ic_sync_inventories_in', $current_syncCount, count($records));
        }
        else { $this->_echoLog($logLevel=1, 'ic_sync_inventories_in', 'Nothing to sync'); }
    }
        private function _sync_filemaker2wp_productStocks(ic_sync_filemaker2wp_productStock $productStock)
        {
            $this->_echoLog($logLevel=2, 'ic_sync_inventories_in', 'Starting sync', $productStock->IdTransaction);
            
            $success = WP_updateProductStock($productStock);
            
            $this->_echoLog($logLevel=3, 'ic_sync_inventories_in', 'WP sync state: '.($success?'OK':'ERROR'), $productStock->IdTransaction);
            
            $this->_updateSyncStatus('{wp_}sync_inventories_in', $productStock->IdTransaction, $success);
            
            $this->_updateProductSummaryInfos($productStock->UUIDProductUniverse);
            
            $this->_echoLog($logLevel=3, 'ic_sync_inventories_in', 'Done sync', $productStock->IdTransaction);
        }
    
    
    
    
    
    public function sync_wp2filemaker_notifyOrderedProduct(ic_sync_wp2filemaker_orderedProduct $orderedProduct)
    {
        $query = "INSERT `{wp_}sync_inventories_out` (`idOrder`, `UUIDProductUniverse`, `stockVariation`, `stockQty`)
                  VALUES (?, ?, ?, ?);";
        $this->_db->preparedQuery($query, array($orderedProduct->idOrder,$orderedProduct->UUIDProductUniverse,$orderedProduct->stockVariation,$orderedProduct->stockQty));
        
        $this->_updateProductSummaryInfos($orderedProduct->UUIDProductUniverse);
        
        $this->_echoLog($logLevel=3, $tableName='ic_sync_inventories_out', "Inserted and generated PK: ".$this->_db->last_id());
        
        $this->_db->commit();
    }    

    public function sync_wp2filemaker_getSyncedOrders()
    {
        $idOrders = array();

        $query = "SELECT DISTINCT idOrder
                  FROM `{wp_}sync_inventories_out`";
        $records = $this->_db->preparedQuery($query);

        if (count($records) > 0)
        {   
            foreach ($records as $record)
            {
                $idOrders[$record['idOrder']] = $record['idOrder'];
            }
        }

        return $idOrders;
    }
};
    class ic_sync_imgInfos
    {
        public $filePath      = null; //DOC_ROOT
        public $fileName      = null; //Automatically evaluated
        public $ordinalSuffix = null;
        public $isMain        = false;
        public $syncStatus    = null; //Const of "ICSyncManager::SYNC_STATUS_x"


        public function __construct($filePath, $ordinalSuffix, $isMain, $syncStatus)
        {
            $this->filePath      = $filePath;
            $this->ordinalSuffix = $ordinalSuffix;
            $this->isMain        = $isMain;
            $this->syncStatus    = $syncStatus;

            $this->fileName = basename($this->filePath);
        }
    };
    class ic_sync_filemaker2wp_category
    {
        public $IdTransaction            = null; //pk
        public $idCategory               = null; //Unique through all filemaker stores (varchar 10)
        public $categoryName             = null;
        public $idCategoryParent         = null; //Another `idCategory`, or ICSyncManager::CATEGORY_ID_PARENT_NONE
        public $isActive                 = null;
        public $syncStatus               = null; //Const of "ICSyncManager::SYNC_STATUS_x"
        public $transactionCreatedDate   = null;
        public $transactionModifiedDate  = null; //Only used when changing syncStatus
        
        public $mainImgToSync = null; //Instance of ic_sync_imgInfos


        public function __construct($record)
        {
        	if (!empty($record))
        	{
	            $this->IdTransaction            = $record['IdTransaction'];
	            $this->idCategory               = $record['idCategory'];
	            $this->categoryName             = $record['categoryName'];
	            $this->idCategoryParent         = $record['idCategoryParent'];
	            $this->isActive                 = $record['isActive'];
	            $this->syncStatus               = $record['syncStatus'];
	            $this->transactionCreatedDate   = $record['transactionCreatedDate'];
	            $this->transactionModifiedDate  = $record['transactionModifiedDate'];
	        }
        }
    };
    class ic_sync_filemaker2wp_product
    {
        public $IdTransaction           = null; //pk
        public $sku                     = null; //Must be unique
        public $productName             = null; //Sometimes filled
        public $productDescription      = null; //Sometimes filled
        public $productCategories       = null; //Ex: 123,456,789
        public $productState            = null; //Const of "ICSyncManager::PRODUCT_ATTR_TERMS_PRODUCTSTATE_x"
        public $productBrand            = null;
        public $productModel            = null;
        public $productSize             = null;
        public $productPrice            = null;
        public $productPricePromo       = null;
        public $idStore                 = null; //Id of the filemaker store that pushed this record
        public $stockQty                = null; //Fixes stock qty, not a ±qty variation
        public $isPromotion             = null;
        public $isFeatured              = null;
        public $isActive                = null;
        public $isShippable             = null;
        public $shippingPrice           = null;
        public $syncStatus              = null; //Const of "ICSyncManager::SYNC_STATUS_x"
        public $transactionCreatedDate  = null;
        public $transactionModifiedDate = null; //Only used when changing syncStatus
        public $UUIDProductUniverse     = null; //Actual id used in other product related sync tables

        public $mainImgToSync     = null;    //Instance of ic_sync_imgInfos, or null
        public $galleryImgsToSync = array(); //Array of ic_sync_imgInfos


        public function __construct($record=null)
        {
        	if (!empty($record))
        	{
	            $this->IdTransaction           = $record['IdTransaction'];
	            $this->sku                     = $record['sku'];
	            $this->productName             = $record['productName'];
	            $this->productDescription      = $record['productDescription'];
	            $this->productCategories       = $record['productCategories'];
	            $this->productState            = $record['productState'];
	            $this->productBrand            = $record['productBrand'];
	            $this->productModel            = $record['productModel'];
	            $this->productSize             = $record['productSize'];
	            $this->productPrice            = $record['productPrice'];
	            $this->productPricePromo       = $record['productPricePromo'];
	            $this->idStore                 = $record['idStore'];
	            $this->stockQty                = $record['stockQty'];
	            $this->isPromotion             = $record['isPromotion'];
	            $this->isFeatured              = $record['isFeatured'];
	            $this->isActive                = $record['isActive'];
	            $this->isShippable             = $record['isShippable'];
	            $this->shippingPrice           = $record['shippingPrice'];
	            $this->syncStatus              = $record['syncStatus'];
	            $this->transactionCreatedDate  = $record['transactionCreatedDate'];
	            $this->transactionModifiedDate = $record['transactionModifiedDate'];
	            $this->UUIDProductUniverse     = $record['UUIDProductUniverse'];
	        }
        }
    };
    class ic_sync_filemaker2wp_productStock
    {
        public $IdTransaction           = null; //pk
        public $UUIDProductUniverse     = null; //Actual id used in other product related sync tables
        public $stockVariation          = null; //Ex if we initially had 10, +2 -> 12, -2 -> 8
        public $syncStatus              = null; //Const of "ICSyncManager::SYNC_STATUS_x"
        public $transactionCreatedDate  = null;
        public $transactionModifiedDate = null; //Only used when changing syncStatus


        public function __construct($record)
        {
            if (!empty($record))
        	{
		        $this->IdTransaction           = $record['IdTransaction'];
	            $this->UUIDProductUniverse     = $record['UUIDProductUniverse'];
	            $this->stockVariation          = $record['stockVariation'];
	            $this->syncStatus              = $record['syncStatus'];
	            $this->transactionCreatedDate  = $record['transactionCreatedDate'];
	            $this->transactionModifiedDate = $record['transactionModifiedDate'];
	        }
        }
    };
    class ic_sync_wp2filemaker_orderedProduct
    {
        public $idOrder             = null; //Actual id of the order which the product was bought
        public $UUIDProductUniverse = null; //Actual id used in other product related sync tables
        public $stockVariation      = null; //Ex if we initially had 10, +2 -> 12, -2 -> 8
        public $stockQty            = null; //Resulting final qty after
        //NOTE: Has other fields, but not required in -our- insert
        
        
        public function __construct() {}
    };

?>