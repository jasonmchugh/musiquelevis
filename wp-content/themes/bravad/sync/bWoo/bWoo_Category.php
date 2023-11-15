<?

/*
Code exs:
    #1:
        $category = new bWoo_Category();
        $category->setFieldVal('name', 'dasdads');
        $category->setFieldVal('slug', 'dasdads');
        $category->setFieldVal('parent', 'dasdads');
        $category->setFieldVal('description', 'dasdads');
        $category->setFieldVal('display', 'dasdads');
        $category->setFieldVal('menu_order', 'dasdads');
        $category->setFieldVal('count', 'dasdads');
        $category->setFieldVal('image', new bWoo_CategoryImage());
        $category->getFieldVal('image')->setFieldVal('src','https://instantcomptant.bravad-dev.com/wp-content/uploads/2017/12/bwssportalain3.jpg')->setFieldVal('title','test2')->setFieldVal('alt','test3');
        $category->save($api);
        echo $category->debug();
    
    #2:
        $category = bWoo_Category::loadOne($api, 89);
        echo $category->debug();
        $category->delete($api);
    
    #3:
        $categorySearchOptions = new bWoo_CategorySearchOptions();
        $categorySearchOptions->slug = 'dasdasd';
        $categoryList = bWoo_Category::loadList($api, $categorySearchOptions);
        echo $categoryList->debug();
    
    #4:
        $categorySearchOptions = new bWoo_CategorySearchOptions();
        $categoryList = bWoo_Category::loadList($api, $categorySearchOptions);
        
        $categoryList->current()->setFieldVal('slug', 'dasdasd_altered');
        $categoryList->current()->save($api);
        
        echo $categoryList->findId(28)->debug();
        echo $categoryList->atIndex(0)->debug();
        
    #5:
        $matches = $categoryList->findMatchingFieldVal('slug', 'appareils-de-son');
        echo $matches->debug();
*/


class bWoo_CategoryList extends bWoo_EntityWithPostIdList_base
{
    public function __construct() { parent::__construct($instancesClassName='bWoo_Category'); }
};



class bWoo_CategorySearchOptions extends bWoo_EntitySearchOptions_base
{
    const ORDER_BY_ID          = 'id';
    const ORDER_BY_INCLUDE     = 'include';
    const ORDER_BY_NAME        = 'name';
    const ORDER_BY_SLUG        = 'slug';
    const ORDER_BY_TERM_GROUP  = 'term_group';
    const ORDER_BY_DESCRIPTION = 'description';
    const ORDER_BY_COUNT       = 'count';
    
    public $orderby    = bWoo_CategorySearchOptions::ORDER_BY_NAME; //Const of bWoo_CategorySearchOptions::ORDER_BY_x
    public $hide_empty = false; //Whether to hide resources not assigned to any products. Default is false.
    public $parent     = null;  //Having a specific parent category id
    public $product    = null;  //Having a specific product id
    
    
    public function __construct() { parent::__construct(); }
};



//http://woocommerce.github.io/woocommerce-rest-api-docs/?php#product-category-properties
class bWoo_Category extends bWoo_EntityWithPostId_base
{
    const ARCHIVE_DISPLAY_TYPE_DEFAULT       = 'default';
    const ARCHIVE_DISPLAY_TYPE_PRODUCTS      = 'products';
    const ARCHIVE_DISPLAY_TYPE_SUBCATEGORIES = 'subcategories';
    const ARCHIVE_DISPLAY_TYPE_BOTH          = 'both';
    
    public function __construct()
    {
        parent::__construct();
        
        $this->__construct_defineField(new bWoo_EntityField('name',                bWoo_EntityField_base::MODE_RW, null)); //Category name (mandatory)
        $this->__construct_defineField(new bWoo_EntityField('slug',                bWoo_EntityField_base::MODE_RW, null)); //An alphanumeric identifier for the resource unique to its type (displayed in urls)
        $this->__construct_defineField(new bWoo_EntityField('parent',              bWoo_EntityField_base::MODE_RW, null)); //The ID for the parent of the resource
        $this->__construct_defineField(new bWoo_EntityField('description',         bWoo_EntityField_base::MODE_RW, null)); //HTML description of the resource
        $this->__construct_defineField(new bWoo_EntityField('display',             bWoo_EntityField_base::MODE_RW, bWoo_Category::ARCHIVE_DISPLAY_TYPE_DEFAULT)); //Const of "bWoo_Category::ARCHIVE_DISPLAY_TYPE_x" (default is "bWoo_Category::ARCHIVE_DISPLAY_TYPE_DEFAULT")
        $this->__construct_defineField(new bWoo_EntityField('menu_order',          bWoo_EntityField_base::MODE_RW, null)); //Menu order idx, used to custom sort the resource
        $this->__construct_defineField(new bWoo_EntityField('count',               bWoo_EntityField_base::MODE_RW, null)); //Number of published products for the resource
        $this->__construct_defineField(new bWoo_EntityField_CategoryImage('image',                                 null)); //Instance of bWoo_CategoryImage. NOTE: Once it's set, can't be deleted or '' via Woo commerce's api
    }
    
    
    public function save(bWooApi $api)
    {
        $postData = $this->_save_makeMainPostData();
        $response = null;
        
        try
        {
        	if ($this->isNew()) { $response = $api->api_categories()->create($postData);              } //Id will get set later in "_setFromStdObject()"
            else                { $response = $api->api_categories()->update($this->id(), $postData); }
        }
        catch (Exception $e) { $this->_rethrowEx($e); }
        
        if ($response->jsonBody) { $this->_setFromStdObject($response->jsonBody,$canResetLockedFields=true); }
        else { $this->_throwEx('Response had no jsonBody'); }
    }
    public function delete(bWooApi $api)
    {
        if ($this->isNew()) { $this->_throwEx("Can't delete because it's a new instance"); }
        
        try                  { $api->api_categories()->delete($this->id(), $force=true); } //On success, returns old category data w/o deletion info. On fail, throws an exception
        catch (Exception $e) { $this->_rethrowEx($e); }
    }
    
    public static function loadOne(bWooApi $api, $id)
    {
        $response = null;
        
        try                  { $response = $api->api_categories()->get($id); }
        catch (Exception $e) { bWoo_Entity_base::_rethrowEx($e); }
        
        if ($response->jsonBody)
        {
            $category = new bWoo_Category();
            $category->_setFromStdObject($response->jsonBody, $canResetLockedFields=true);
            
            return $category;
        }
        else { bWoo_Entity_base::_throwEx('Response had no jsonBody'); }
    }
    public static function loadList(bWooApi $api, bWoo_CategorySearchOptions $searchOptions)
    {
        $categoryList = new bWoo_CategoryList();
        
        $stdObjectArr = bWoo_EntityWithPostId_base::_loadList($api, $api->api_categories(), $searchOptions);
        foreach ($stdObjectArr as $stdObject)
        {
            $category = new bWoo_Category();
            $category->_setFromStdObject($stdObject, $canResetLockedFields=true);
            
            $categoryList->add($category);
        }
        
        return $categoryList;
    }
};
    //http://woocommerce.github.io/woocommerce-rest-api-docs/?php#product-category-image-properties
    class bWoo_CategoryImage extends bWoo_ChildEntityWithPostId_base
    {
        public function __construct()
        {
            parent::__construct();
            
            $this->__construct_defineField(new bWoo_EntityField('src',   bWoo_EntityField_base::MODE_RW, null)); //Url, not DOC_ROOT
            $this->__construct_defineField(new bWoo_EntityField('title', bWoo_EntityField_base::MODE_RW, null)); //NOTE: For products, it's "name"
            $this->__construct_defineField(new bWoo_EntityField('alt',   bWoo_EntityField_base::MODE_RW, null)); //Alt text
            //READ-ONLY STUFF
            $this->__construct_defineField(new bWoo_EntityField('date_created',      bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_created_gmt',  bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_modified',     bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
            $this->__construct_defineField(new bWoo_EntityField('date_modified_gmt', bWoo_EntityField_base::MODE_RO, null)); //Y-m-dTH:i:s
        }
        
        
        public function save(bWooApi $api)                { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
        public function delete(bWooApi $api)              { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
        public static function loadOne(bWooApi $api, $id) { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
    };

?>