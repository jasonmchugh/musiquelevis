<?

class bWoo_OrderList extends bWoo_EntityWithPostIdList_base
{
    public function __construct() { parent::__construct($instancesClassName='bWoo_Order'); }
};



class bWoo_OrderSearchOptions extends bWoo_EntitySearchOptions_base
{
    const ORDER_BY_DATE    = 'date';
    const ORDER_BY_ID      = 'id';
    const ORDER_BY_INCLUDE = 'include';
    const ORDER_BY_TITLE   = 'title';
    const ORDER_BY_SLUG    = 'slug';
    
    
	public $orderby        = bWoo_OrderSearchOptions::ORDER_BY_DATE; //Const of bWoo_OrderSearchOptions::ORDER_BY_x
	public $after          = null; //string	Limit response to resources published after a given ISO8601 compliant date.
	public $before         = null; //string	Limit response to resources published before a given ISO8601 compliant date.
	public $parent         = null; //array	Limit result set to those of particular parent IDs.
	public $parent_exclude = null; //array	Limit result set to all items except those of a particular parent ID.
	public $status         = null; //string	Limit result set to orders assigned a specific status. Options: any, pending, processing, on-hold, completed, cancelled, refunded and failed. Default is any.
	public $customer       = null; //integer Limit result set to orders assigned a specific customer.
	public $product        = null; //integer Limit result set to orders assigned a specific product.
	public $dp             = null; //integer Number of decimal points to use in each resource. Default is 2.
    
    
    public function __construct() { parent::__construct(); }
};



class bWoo_Order extends bWoo_EntityWithPostId_base
{
    public $orderLines = array(); //Arr of bWoo_OrderLine instances
    
    
    public function __construct()
    {
        parent::__construct();
        
        $this->__construct_defineField(new bWoo_EntityField('name',                 bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('parent_id',            bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('number',               bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('order_key',            bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('created_via',          bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('version',              bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('status',               bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('currency',             bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_created',         bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_created_gmt',     bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_modified',        bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_modified_gmt',    bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('discount_total',       bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('discount_tax',         bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('shipping_total',       bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('shipping_tax',         bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('cart_tax',             bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('total',                bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('total_tax',            bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('prices_include_tax',   bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('customer_id',          bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('customer_ip_address',  bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('customer_user_agent',  bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('customer_note',        bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('billing',              bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('shipping',             bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('payment_method',       bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('payment_method_title', bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('transaction_id',       bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_paid',            bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_paid_gmt',        bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_completed',       bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('date_completed_gmt',   bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('cart_hash',            bWoo_EntityField_base::MODE_RW, null));
		$this->__construct_defineField(new bWoo_EntityField('meta_data',            bWoo_EntityField_base::MODE_RW, null));
    }
    
    
    public function save(bWooApi $api)
    {
        return false;
    }
    public function delete(bWooApi $api)
    {
        return false;
    }
    
    protected function _setFromStdObject($stdObject, $canResetLockedFields)
    {
    	parent::_setFromStdObject($stdObject, $canResetLockedFields);
    	
    	if (!empty($stdObject->line_items))
    	{
    		foreach ($stdObject->line_items as $lineItemStdObject)
    		{
    			$instance = new bWoo_OrderLine();
    				$instance->_setFromStdObject($lineItemStdObject, $canResetLockedFields);
    			$this->orderLines[] = $instance;
    		}
    	}
    }
    protected function _toArray_fields($onlyChangedFields)
    {
    	$arr = parent::_toArray_fields($onlyChangedFields);
    	
	    if (!empty($this->orderLines))
	    {
	    	$arr['line_items'] = array();
	    	foreach ($this->orderLines as $instance)
	    	{
	    		$arr['line_items'][] = $instance->toArray();
	    	}
	    }
	    
    	return $arr;
    }
    
    public static function loadOne(bWooApi $api, $id)
    {
        $response = null;
        
        try                  { $response = $api->api_orders()->get($id); }
        catch (Exception $e) { bWoo_Entity_base::_rethrowEx($e); }
        
        if ($response->jsonBody)
        {
            $order = new bWoo_Order();
            $order->_setFromStdObject($response->jsonBody, $canResetLockedFields=true);
            
            return $order;
        }
        else { bWoo_Entity_base::_throwEx('Response had no jsonBody'); }
    }
    public static function loadList(bWooApi $api, bWoo_OrderSearchOptions $searchOptions)
    {
        $orderList = new bWoo_OrderList();
        
        $stdObjectArr = bWoo_EntityWithPostId_base::_loadList($api, $api->api_orders(), $searchOptions);
        foreach ($stdObjectArr as $stdObject)
        {
            $order = new bWoo_Order();
            $order->_setFromStdObject($stdObject, $canResetLockedFields=true);
            
            $orderList->add($order);
        }
        
        return $orderList;
    }
};



class bWoo_OrderLine extends bWoo_EntityWithPostId_base
{
    public function __construct()
    {
        parent::__construct();
        
        $this->__construct_defineField(new bWoo_EntityField('name',           bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('product_id',     bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('variation_id',   bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('quantity',       bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('tax_class',      bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('subtotal',       bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('subtotal_tax',   bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('total',          bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('total_tax',      bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('taxes',          bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('meta_data',      bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('sku',            bWoo_EntityField_base::MODE_RW, null));
        $this->__construct_defineField(new bWoo_EntityField('price',          bWoo_EntityField_base::MODE_RW, null));
    }
    
    
    public function save(bWooApi $api)
    {
        return false;
    }
    public function delete(bWooApi $api)
    {
        return false;
    }
    
    public static function loadOne(bWooApi $api, $id)
    {
        return false;
    }
};

?>