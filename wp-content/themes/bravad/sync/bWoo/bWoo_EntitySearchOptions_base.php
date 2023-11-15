<?

abstract class bWoo_EntitySearchOptions_base
{
    const PER_PAGE_ALL = 'all';
    const CONTEXT_VIEW = 'view';
    const CONTEXT_EDIT = 'edit';
    const ORDER_ASC  = 'asc';
    const ORDER_DESC = 'desc';
    
    public $context  = bWoo_EntitySearchOptions_base::CONTEXT_EDIT; //Const of "bWoo_EntitySearchOptions_base::CONTEXT_x". Determines fields present in response
    public $per_page = bWoo_EntitySearchOptions_base::PER_PAGE_ALL; //Nb of records, or bWoo_EntitySearchOptions_base::PER_PAGE_ALL
    public $order    = bWoo_EntitySearchOptions_base::ORDER_ASC;    //Const of "bWoo_EntitySearchOptions_base::ORDER_x"
    public $page     = 1;
    
    public $search  = null; //Searchs, but we don't know on what
    public $exclude = null; //Array of ids
    public $include = null; //Array of ids
    public $slug    = null; //An alphanumeric identifier for the resource unique to its type (displayed in urls)
    
    
    protected function __construct() {}
    
    
    public function toArray() { return get_object_vars($this); }
};

?>