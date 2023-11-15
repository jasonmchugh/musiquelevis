<?

abstract class bWoo_EntityList_base implements Iterator
{
    private $_instancesClassName = null; //Should be a class name, derived of bWoo_Entity_base
    private $_instances          = null; //Array of "$instancesClassName"
    private $_position           = 0;
    
    
    protected function __construct($instancesClassName)
    {
        $this->_instancesClassName = $instancesClassName;
        $this->_instances          = array();
    }
    
    
    public function getTotalRecordCount() { return count($this->_instances); }
    public function add(bWoo_Entity_base $instance)
    {
        if (!is_object($instance)) { bWoo_Exception::throwEx("bWoo_List_base::add(): No object received"); }
        
        $instanceClass = get_class($instance);
        if ($instanceClass !== $this->_instancesClassName) { bWoo_Exception::throwEx("bWoo_List_base::add(): Expected instance of $this->_instancesClassName, received a $instanceClass"); }
        
        $this->_instances[] = $instance;
    }
    
    public function atIndex($idx)
    {
        if ($idx >= count($this->_instances)) { bWoo_Exception::throwEx("bWoo_List_base::findId(): Index out of bounds"); }
        
        return $this->_instances[$idx];
    }
    public function findId($id)
    {
        foreach ($this->_instances as $instance)
        {
            if ($instance->id() == $id) { return $instance; }
        }
        
        bWoo_Exception::throwEx("bWoo_List_base::findId(): Instance #$id not found");
    }
    //Returns a new bWoo_List_base derived
    public function findMatchingFieldVal($fieldName, $fieldVal)
    {
        $className = get_class($this); //Ex bWoo_CategoryList
        $list      = new $className();
        
        foreach ($this->_instances as $instance)
        {
            if ($instance->getFieldVal($fieldName) == $fieldVal) { $list->add($instance); }
        }
        
        return $list;
    }
    
    public function debug($indentation=0)
    {
        $output = '';
        
        $output = get_class($this)."\n"
                . str_repeat("\t",$indentation+1)."instancesClassName: $this->_instancesClassName\n"
                . str_repeat("\t",$indentation+1)."instances:\n";
        foreach ($this->_instances as $instance)
        {
            $output .= $instance->debug($indentation+2)."\n";
        }
        
        return $output;
    }
    
    //Iterator implements
        public function rewind()  { $this->_position = 0; }
        public function current() { if ($this->valid()) { return $this->_instances[$this->_position]; } return null; }
        public function key()     { return $this->_position; }
        public function next()    { ++$this->_position; }
        public function valid()   { return isset($this->_instances[$this->_position]); }
};



abstract class bWoo_EntityWithoutPostIdList_base extends bWoo_EntityList_base
{
    protected function __construct($instancesClassName) { parent::__construct($instancesClassName); }
};



abstract class bWoo_EntityWithPostIdList_base extends bWoo_EntityList_base
{
    protected function __construct($instancesClassName) { parent::__construct($instancesClassName); }
    
    
    public function findId($id)
    {
        foreach ($this->_instances as $instance) { if ($instance->id()==$id){return $instance;} }
        
        bWoo_Exception::throwEx("bWoo_List_base::findId(): Instance #$id not found");
    }
};

?>