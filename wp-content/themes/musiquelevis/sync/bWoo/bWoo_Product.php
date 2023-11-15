<?

/*
Code exs:
    #1:
        $product = new bWoo_Product();
        $product->setFieldVal('AAAAAAA', 'AAAAAAA');
        $product->setFieldVal('BBBB', new bWoo_ProductImage());
        $product->getFieldVal('BBBB')->setFieldVal('src','https://instantcomptant.bravad-dev.com/wp-content/uploads/2017/12/bwssportalain3.jpg')->setFieldVal('title','test2')->setFieldVal('alt','test3');
        $product->save($api);
        echo $product->debug();
    
    #2:
        $product = bWoo_Product::loadOne($api, 89);
        echo $product->debug();
        $product->delete($api);
    
    #3:
        $productSearchOptions = new bWoo_ProductSearchOptions();
        $productSearchOptions->slug = 'dasdasd';
        $productList = bWoo_Product::loadList($api, $productSearchOptions);
        echo $productList->debug();
    
    #4:
        $productSearchOptions = new bWoo_ProductSearchOptions();
        $productList = bWoo_Product::loadList($api, $productSearchOptions);
        
        $productList->current()->setFieldVal('slug', 'dasdasd_altered');
        $productList->current()->save($api);
        
        echo $productList->findId(28)->debug();
        echo $productList->atIndex(0)->debug();
        
    #5:
        $matches = $productList->findMatchingFieldVal('slug', 'bob');
        echo $matches->debug();
*/


class bWoo_ProductList extends bWoo_EntityWithPostIdList_base
{
    public function __construct() { parent::__construct($instancesClassName='bWoo_Product'); }
};



class bWoo_ProductSearchOptions extends bWoo_EntitySearchOptions_base
{
    const ORDER_BY_ID          = 'id';
    const ORDER_BY_DATE        = 'date';
    const ORDER_BY_INCLUDE     = 'include';
    const ORDER_BY_TITLE       = 'title';
    const ORDER_BY_SLUG        = 'slug';
    const ORDER_BY_DESCRIPTION = 'description';
    const ORDER_BY_COUNT       = 'count';
    
  //public $offset         = null;  //integer Offset the result set by a specific number of items (NOT USED, SINCE WE ALREADY HAVE "page"...)
    public $orderby        = bWoo_ProductSearchOptions::ORDER_BY_DATE; //Const of bWoo_ProductSearchOptions::ORDER_BY_x
    public $after          = null;  //Published  after a given ISO8601 date (Y-m-dTH:i:s or 2017-12-27T14:05:54Z ?)
    public $before         = null;  //Published before a given ISO8601 date
    public $parent         = null;  //Having a specific parent product id
    public $parent_exclude = null;  //Not having a specific parent product id
    public $type           = null;  //Const of "bWoo_Product::TYPE_x"
    public $status         = bWoo_Product::STATUS_ANY; //Const of "bWoo_Product::STATUS_x"
    public $sku            = null;
    public $featured       = null;  //Bool
    public $category       = null;  //Having a specific category id
    public $tag            = null;  //Having a specific tag id
    public $shipping_class = null;  //Having a specific shipping class id
    public $attribute      = null;  //with a specific attribute
    public $attribute_term = null;  //with a specific attribute term ID (required an assigned attribute).
    public $tax_class      = null;  //Const of "bWoo_Product::TAX_CLASS_x" or custom string
    public $in_stock       = null;  //Bool
    public $on_sale        = null;  //Bool
    public $min_price      = null;
    public $max_price      = null;
    
    
    public function __construct() { parent::__construct(); }
};



//http://woocommerce.github.io/woocommerce-rest-api-docs/?php#product-properties
class bWoo_Product extends bWoo_EntityWithPostId_base
{
    const TYPE_SIMPLE   = 'simple';
    const TYPE_GROUPED  = 'grouped';
    const TYPE_EXTERNAL = 'external';
    const TYPE_VARIABLE = 'variable';
    const STATUS_ANY     = 'any';
    const STATUS_DRAFT   = 'draft';
    const STATUS_PENDING = 'pending';
    const STATUS_PRIVATE = 'private';
    const STATUS_PUBLISH = 'publish';
    const CATALOG_VISIBILITY_VISIBLE = 'visible';
    const CATALOG_VISIBILITY_CATALOG = 'catalog';
    const CATALOG_VISIBILITY_SEARCH  = 'search';
    const CATALOG_VISIBILITY_HIDDEN  = 'hidden';
    const TAX_STATUS_TAXABLE  = 'taxable';
    const TAX_STATUS_SHIPPING = 'shipping';
    const TAX_STATUS_NONE     = 'none';
    const TAX_CLASS_STANDARD     = 'standard';
    const TAX_CLASS_REDUCED_RATE = 'reduced-rate';
    const TAX_CLASS_ZERO_RATE    = 'zero-rate';
    const BO_DISALLOWED    = 'no';
    const BO_ALLOW_W_NOTIF = 'notify';
    const BO_ALLOWED       = 'yes';
    
    
    //Should use bWoo_EntityField somehow but... some day!
    public $categories = array(); //Arr of bWoo_ProductCategory  instances
	public $images     = array(); //Arr of bWoo_ProductImage     instances
	public $attributes = array(); //Arr of bWoo_ProductAttribute instances
    
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->__construct_defineField(new bWoo_EntityField('name',                         bWoo_EntityField_base::MODE_RW, null)); //Product name (mandatory)
        $this->__construct_defineField(new bWoo_EntityField('slug',                         bWoo_EntityField_base::MODE_RW, null)); //An alphanumeric identifier for the resource unique to its type (displayed in urls)
        $this->__construct_defineField(new bWoo_EntityField('type',                         bWoo_EntityField_base::MODE_RW, bWoo_Product::TYPE_SIMPLE));    //Const of "bWoo_Product::TYPE_x"
        $this->__construct_defineField(new bWoo_EntityField('status',                       bWoo_EntityField_base::MODE_RW, bWoo_Product::STATUS_PUBLISH)); //Const of "bWoo_Product::STATUS_x"
        $this->__construct_defineField(new bWoo_EntityField('featured',                     bWoo_EntityField_base::MODE_RW, false)); //Bool if is a featured product
        $this->__construct_defineField(new bWoo_EntityField('catalog_visibility',           bWoo_EntityField_base::MODE_RW, bWoo_Product::CATALOG_VISIBILITY_VISIBLE)); //Const of "bWoo_Product::CATALOG_VISIBILITY_x"
        $this->__construct_defineField(new bWoo_EntityField('description',                  bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('short_description',            bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('sku',                          bWoo_EntityField_base::MODE_RW, null));  //Must be unique
        $this->__construct_defineField(new bWoo_EntityField('regular_price',                bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('sale_price',                   bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('date_on_sale_from',            bWoo_EntityField_base::MODE_RW, null));  //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_on_sale_from_gmt',        bWoo_EntityField_base::MODE_RW, null));  //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_on_sale_to',              bWoo_EntityField_base::MODE_RW, null));  //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_on_sale_to_gmt',          bWoo_EntityField_base::MODE_RW, null));  //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('virtual',                      bWoo_EntityField_base::MODE_RW, false)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('downloadable',                 bWoo_EntityField_base::MODE_RW, false)); //boolean If the product is downloadable
        $this->__construct_defineField(new bWoo_EntityField('download_limit',               bWoo_EntityField_base::MODE_RW, -1));    //Number of times downloadable files can be downloaded after purchase
        $this->__construct_defineField(new bWoo_EntityField('download_expiry',              bWoo_EntityField_base::MODE_RW, -1));    //Number of days until access to downloadable files expires
        $this->__construct_defineField(new bWoo_EntityField('external_url',                 bWoo_EntityField_base::MODE_RW, null));  //Only for external products
        $this->__construct_defineField(new bWoo_EntityField('button_text',                  bWoo_EntityField_base::MODE_RW, null));  //Product external button text. Only for external products
        $this->__construct_defineField(new bWoo_EntityField('tax_status',                   bWoo_EntityField_base::MODE_RW, bWoo_Product::TAX_STATUS_TAXABLE)); //Const of "bWoo_Product::TAX_STATUS_x"
        $this->__construct_defineField(new bWoo_EntityField('tax_class',                    bWoo_EntityField_base::MODE_RW, null));  //Const of "bWoo_Product::TAX_CLASS_x"
        $this->__construct_defineField(new bWoo_EntityField('manage_stock',                 bWoo_EntityField_base::MODE_RW, false)); //Bool (default false)
        $this->__construct_defineField(new bWoo_EntityField('stock_quantity',               bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('in_stock',                     bWoo_EntityField_base::MODE_RW, true));  //Bool (default true)
        $this->__construct_defineField(new bWoo_EntityField('backorders',                   bWoo_EntityField_base::MODE_RW, bWoo_Product::BO_DISALLOWED)); //Const of "bWoo_Product::BO_x"
        $this->__construct_defineField(new bWoo_EntityField('sold_individually',            bWoo_EntityField_base::MODE_RW, false)); //Bool, allows one item to be bought in a single order
        $this->__construct_defineField(new bWoo_EntityField('weight',                       bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('shipping_class',               bWoo_EntityField_base::MODE_RW, null));  //Shipping class slug (kind of tag) -> Go in "WooCommerce/Settings/Shipping/Shipping classes" (/wp-admin/admin.php?page=wc-settings&tab=shipping&section=classes)
        $this->__construct_defineField(new bWoo_EntityField('reviews_allowed',              bWoo_EntityField_base::MODE_RW, true));  //Bool
        $this->__construct_defineField(new bWoo_EntityField('upsell_ids',                   bWoo_EntityField_base::MODE_RW, null));  //Arr of up-sell products ids
        $this->__construct_defineField(new bWoo_EntityField('cross_sell_ids',               bWoo_EntityField_base::MODE_RW, null));  //Arr of cross-sell products ids
        $this->__construct_defineField(new bWoo_EntityField('parent_id',                    bWoo_EntityField_base::MODE_RW, null));  //Parent product id
        $this->__construct_defineField(new bWoo_EntityField('purchase_note',                bWoo_EntityField_base::MODE_RW, null));  //Optional note to send the customer after purchase
        $this->__construct_defineField(new bWoo_EntityField('grouped_products',             bWoo_EntityField_base::MODE_RW, null));  //Arr of grouped products ids
        $this->__construct_defineField(new bWoo_EntityField('menu_order',                   bWoo_EntityField_base::MODE_RW, null));  //Menu order, used to custom sort products (int)
        $this->__construct_defineField(new bWoo_EntityField_ProductDimensions('dimensions',                                 null)); //Instance of bWoo_ProductDimensions
        //LISTS (NOT YET SUPPORTED - Would probably need to change like such: abstract class bWoo_EntityList_base extends bWoo_EntityField_base implements Iterator ?)
	        /*
	        $this->__construct_defineField(new bWoo_EntityField('categories',         array(bWoo_ProductCategory));         //Arr of categories
	        $this->__construct_defineField(new bWoo_EntityField('images',             array(bWoo_ProductImage));            //Arr of images
	        $this->__construct_defineField(new bWoo_EntityField('attributes',         array(bWoo_ProductAttribute));        //Arr of attributes
	        $this->__construct_defineField(new bWoo_EntityField('downloads',          array(bWoo_ProductDownload));         //Arr of downloadable files
	        $this->__construct_defineField(new bWoo_EntityField('tags',               array(bWoo_ProductTag));              //Arr of tags
	        $this->__construct_defineField(new bWoo_EntityField('default_attributes', array(bWoo_ProductDefaultAttribute)); //Arr of default variation attributes
	        $this->__construct_defineField(new bWoo_EntityField('meta_data',          array(bWoo_ProductMeta));             //Arr of meta data
	        */
        //READ-ONLY STUFF
        $this->__construct_defineField(new bWoo_EntityField('permalink',          bWoo_EntityField_base::MODE_RO, null)); //Product URL
        $this->__construct_defineField(new bWoo_EntityField('date_created',       bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_created_gmt',   bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_modified',      bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('date_modified_gmt',  bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        $this->__construct_defineField(new bWoo_EntityField('price',              bWoo_EntityField_base::MODE_RO, null)); //Current product price
        $this->__construct_defineField(new bWoo_EntityField('price_html',         bWoo_EntityField_base::MODE_RO, null)); //Price formatted in HTML
        $this->__construct_defineField(new bWoo_EntityField('on_sale',            bWoo_EntityField_base::MODE_RO, null)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('purchasable',        bWoo_EntityField_base::MODE_RO, null)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('total_sales',        bWoo_EntityField_base::MODE_RO, null)); //As int... Qty or total $ ?!
        $this->__construct_defineField(new bWoo_EntityField('backorders_allowed', bWoo_EntityField_base::MODE_RO, null)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('backordered',        bWoo_EntityField_base::MODE_RO, null)); //Bool, if currently in BO
        $this->__construct_defineField(new bWoo_EntityField('shipping_required',  bWoo_EntityField_base::MODE_RO, null)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('shipping_taxable',   bWoo_EntityField_base::MODE_RO, null)); //Bool
        $this->__construct_defineField(new bWoo_EntityField('shipping_class_id',  bWoo_EntityField_base::MODE_RO, null)); //Shipping class id
        $this->__construct_defineField(new bWoo_EntityField('average_rating',     bWoo_EntityField_base::MODE_RO, null)); //Reviews average rating (string)
        $this->__construct_defineField(new bWoo_EntityField('rating_count',       bWoo_EntityField_base::MODE_RO, null)); //Nb of client reviews
        $this->__construct_defineField(new bWoo_EntityField('related_ids',        bWoo_EntityField_base::MODE_RO, null)); //Array of related product ids
        $this->__construct_defineField(new bWoo_EntityField('variations',         bWoo_EntityField_base::MODE_RO, null)); //Array of variation ids
    }
    
    
    public function save(bWooApi $api)
    {
        $postData = $this->_save_makeMainPostData();
        $response = null;
        
        try
        {
            if ($this->isNew()) { $response = $api->api_products()->create($postData);              } //Id will get set later in "_setFromStdObject()"
            else                { $response = $api->api_products()->update($this->id(), $postData); }
        }
        catch (Exception $e) { $this->_rethrowEx($e); }
        
        if ($response->jsonBody) { $this->_setFromStdObject($response->jsonBody,$canResetLockedFields=true); }
        else { $this->_throwEx('Response had no jsonBody'); }
    }
    public function delete(bWooApi $api)
    {
        if ($this->isNew()) { $this->_throwEx("Can't delete because it's a new instance"); }
        
        try                  { $api->api_products()->delete($this->id(), $force=true); } //On success, returns old product data w/o deletion info. On fail, throws an exception
        catch (Exception $e) { $this->_rethrowEx($e); }
    }
    
    protected function _setFromStdObject($stdObject, $canResetLockedFields)
    {
    	parent::_setFromStdObject($stdObject, $canResetLockedFields);
    	
    	if (!empty($stdObject->categories))
    	{
    		foreach ($stdObject->categories as $categoryStdObject)
    		{
    			$instance = new bWoo_ProductCategory();
    				$instance->_setFromStdObject($categoryStdObject, $canResetLockedFields);
    			$this->categories[] = $instance;
    		}
    	}
    	if (!empty($stdObject->images))
    	{
    		foreach ($stdObject->images as $imageStdObject)
    		{
    			$instance = new bWoo_ProductImage();
    				$instance->_setFromStdObject($imageStdObject, $canResetLockedFields);
    			$this->images[] = $instance;
    		}
    	}
    	if (!empty($stdObject->attributes))
    	{
    		foreach ($stdObject->attributes as $attributeStdObject)
    		{
    			$instance = new bWoo_ProductAttribute();
    				$instance->_setFromStdObject($attributeStdObject, $canResetLockedFields);
    			$this->attributes[] = $instance;
    		}
    	}
    }
    protected function _toArray_fields($onlyChangedFields)
    {
    	$arr = parent::_toArray_fields($onlyChangedFields);
    	
	    if (!empty($this->categories))
	    {
	    	$arr['categories'] = array();
	    	foreach ($this->categories as $instance)
	    	{
	    		//$arr['categories'][] = $instance->toArray();
	    		$arr['categories'][] = array('id'=>$instance->id()); //Actually, both works, as long as we either send an id, or all fields filled up
	    	}
	    }
		if (!empty($this->images))
		{
			$arr['images'] = array();
			foreach ($this->images as $instance)
			{
				$arr['images'][] = $instance->toArray();
			}
		}
		if (!empty($this->attributes))
		{
			$arr['attributes'] = array();
			foreach ($this->attributes as $instance)
			{
				//$arr['attributes'][] = $instance->toArray();
	    		$arr['attributes'][] = array
	    		(
					'id'        => $instance->id(),
					'position'  => $instance->getFieldVal('position'),
					'visible'   => $instance->getFieldVal('visible'),
					'variation' => $instance->getFieldVal('variation'),
					'options'   => $instance->getFieldVal('options')
    			);
			}
		}
    	
    	return $arr;
    }
    
    public static function loadOne(bWooApi $api, $id)
    {
        $response = null;
        
        try                  { $response = $api->api_products()->get($id); }
        catch (Exception $e) { bWoo_Entity_base::_rethrowEx($e); }
        
        if ($response->jsonBody)
        {
            $product = new bWoo_Product();
            $product->_setFromStdObject($response->jsonBody, $canResetLockedFields=true);
            
            return $product;
        }
        else { bWoo_Entity_base::_throwEx('Response had no jsonBody'); }
    }
    public static function loadList(bWooApi $api, bWoo_ProductSearchOptions $searchOptions)
    {
        $productList = new bWoo_ProductList();
        
        $stdObjectArr = bWoo_EntityWithPostId_base::_loadList($api, $api->api_products(), $searchOptions);
        foreach ($stdObjectArr as $stdObject)
        {
            $product = new bWoo_Product();
            $product->_setFromStdObject($stdObject, $canResetLockedFields=true);
            
            $productList->add($product);
        }
        
        return $productList;
    }
};
    class bWoo_ProductDimensions extends bWoo_EntityWithoutPostId_base
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('length', bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('width',  bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('height', bWoo_EntityField_base::MODE_RW, null));
        }
    };
    class bWoo_ProductDownload extends bWoo_ChildEntityWithPostId_base //NOTE: Id is a md5 hash
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('name', bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('file', bWoo_EntityField_base::MODE_RW, null)); //Url
        }
    };
    class bWoo_ProductCategory extends bWoo_ChildEntityWithPostId_base //NOTE: Maybe it's intended to be able to specify the id only...
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('name',   bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('slug',   bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('parent', bWoo_EntityField_base::MODE_RW, null));
        }
    };
    class bWoo_ProductTag extends bWoo_ChildEntityWithPostId_base //NOTE: Maybe it's intended to be able to specify the id only...
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('name', bWoo_EntityField_base::MODE_RO, null));
            $this->__construct_defineField(new bWoo_EntityField('slug', bWoo_EntityField_base::MODE_RO, null));
        }
    };
    class bWoo_ProductImage extends bWoo_ChildEntityWithPostId_base
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('src',      bWoo_EntityField_base::MODE_RW, null)); //Url, not DOC_ROOT
            $this->__construct_defineField(new bWoo_EntityField('name',     bWoo_EntityField_base::MODE_RW, null)); //NOTE: For categories, it's "title"
            $this->__construct_defineField(new bWoo_EntityField('alt',      bWoo_EntityField_base::MODE_RW, null)); //Alt text
            $this->__construct_defineField(new bWoo_EntityField('position', bWoo_EntityField_base::MODE_RW, null)); //Image position, where 0 means "main / is featured"
            //READ-ONLY STUFF
            $this->__construct_defineField(new bWoo_EntityField('date_created',      bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_created_gmt',  bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_modified',     bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_modified_gmt', bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        }
    };
    class bWoo_ProductAttribute extends bWoo_ChildEntityWithPostId_base //Id should be an attribute id
    {
        public function __construct()
        {
            parent::__construct();
            
            /*
            NOTE: If upon saving the product again we omit to pass them all, they'll disappear
            
            Either specify an existing attribute's id, or specify a name, but don't pass a name=NULL
            options can be a single string or array of selected things, but there are some problems:
            	-If we specify a string and it has spaces, it will split the string on spaces and merge it with | and keep distinct words + sort them...:
            		"DDD AAA BBB BBB CCC" -> "AAA | BBB | CCC | DDD"
            			-> Proof in /wp-content/plugins/woocommerce/includes/api/-wc-rest-products-controller.php::prepare_object_for_database()
            				$options = explode( WC_DELIMITER, $options );
        		-Arr members have to be localization, so we can't specify existing attribute sub items/terms id nor tags....
            */
            $this->__construct_defineField(new bWoo_EntityField('name',      bWoo_EntityField_base::MODE_RW, null));  //Required if ->id(null), otherwise leave null and don't add to post
            $this->__construct_defineField(new bWoo_EntityField('position',  bWoo_EntityField_base::MODE_RW, 0));     //Attr pos (int), doesn't seem to matter if we have multiple attrs with same pos
            $this->__construct_defineField(new bWoo_EntityField('visible',   bWoo_EntityField_base::MODE_RW, true)); //if visible on the "Additional information" tab in the product's page
            $this->__construct_defineField(new bWoo_EntityField('variation', bWoo_EntityField_base::MODE_RW, false)); //If can be used as a variation
            $this->__construct_defineField(new bWoo_EntityField('options',   bWoo_EntityField_base::MODE_RW, null));  //String or arr of available term names (not id nor tags...)
        }
    };
    class bWoo_ProductDefaultAttribute extends bWoo_ChildEntityWithPostId_base //Id should be an attribute id
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('name',   bWoo_EntityField_base::MODE_RW, null)); //Attr name
            $this->__construct_defineField(new bWoo_EntityField('option', bWoo_EntityField_base::MODE_RW, null)); //Selected attribute term name
        }
    };
    class bWoo_ProductMeta extends bWoo_ChildEntityWithPostId_base //Id is readonly meta id
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('key',   bWoo_EntityField_base::MODE_RW, null));
            $this->__construct_defineField(new bWoo_EntityField('value', bWoo_EntityField_base::MODE_RW, null));
        }
    };

?>