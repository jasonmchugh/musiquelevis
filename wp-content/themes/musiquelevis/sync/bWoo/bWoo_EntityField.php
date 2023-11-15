<?

/*
For new platform:
    -We should force having an initial/default val, otherwise it's not clear whether we thought about it or just forgot
    -Mode should be defined before default val, as it doesn't make sense to have a default val when RO anyways (except for setting it for the 1st time)
    -Maybe we shouldn't make RW/RO mode to be fixed and should be able to change it on runtime (ex would $this->lock()), or depending on situation...
*/

abstract class bWoo_EntityField_base
{
    const ACCEPTS_ANYTHING = null;
    const MODE_RW = 'editable';
    const MODE_RO = 'readOnly';
    
    private $_fieldName                     = null;
    private $_expectsInstanceOf             = null;  //Const "bWoo_EntityField_base::ACCEPTS_ANYTHING", or class name we want to limit it to, like a bWoo_Entity_base derived (ex bWoo_CategoryImage)
    private $_expectsSubclassOf_Entity_base = false; //True/false, ex bWoo_CategoryImage -> true
    private $_mode                          = bWoo_EntityField_base::MODE_RW; //Const of "bWoo_EntityField_base::MODE_x". For simplicity, if expect object, won't try to prevent editing it, but changes won't get saved
    private $_val                           = null;
    private $_isLocked                      = false; //Indicates from when a bWoo_EntityField_base::MODE_RO field should really become read-only (so we can have time to load them properly)
    private $_isChanged                     = false;
    
    
    protected function __construct($fieldName, $expectsInstanceOf, $mode, $defaultVal=null)
    {
        if (empty($fieldName)) { bWoo_Exception::throwEx("bWoo_EntityField::__construct(): Empty fieldName"); }
        
        $this->_fieldName                     = $fieldName;
        $this->_expectsInstanceOf             = $expectsInstanceOf;
        $this->_expectsSubclassOf_Entity_base = ($this->_expectsInstanceOf && is_subclass_of($this->_expectsInstanceOf,'bWoo_Entity_base'));
        $this->_mode                          = $mode;
        $this->_val                           = $defaultVal;
        $this->_isLocked                      = false;
        $this->_isChanged                     = false;
    }
    
    
    public function fieldName()                     { return $this->_fieldName; }
    public function get()                           { return $this->_val; }
    public function expectsInstanceOf()             { return $this->_expectsInstanceOf; }
    public function expectsSubclassOf_Entity_base() { return $this->_expectsSubclassOf_Entity_base; }
    public function mode()                          { return $this->_mode; }
    
    public function isLocked() //For non read-only fields holding a bWoo_Entity_base derived, we should call areFieldsLocked() on them ourself somewhere else
    {
        /*
        $areFieldsLocked = false;
        
        //In the case that the field holds a derived of a bWoo_Entity_base, check if any of its nested fields are changed
        if ($this->_expectsSubclassOf_Entity_base && $this->_val) { $areFieldsLocked = $this->_val->areFieldsLocked(); }
        
        if ($this->_mode === bWoo_EntityField_base::MODE_RO)
        {
            $this->_isLocked &= $areFieldsLocked;
            
            return $this->_isLocked;
        }
        
        return $areFieldsLocked;
        */
        
        return $this->_isLocked; //Maybe makes more sense just like this
    }
    public function lock() //Locks a read-only field, so it can't be edited anymore. For non read-only fields holding d a bWoo_Entity_base derived, we should call lockFields() on them ourself somewhere else
    {
        if ($this->_mode === bWoo_EntityField_base::MODE_RW) { bWoo_Exception::throwEx("bWoo_EntityField::lock(): Field '$this->_fieldName' is not read-only, so we can't lock"); }
        
        //In the case that the field holds a derived of a bWoo_Entity_base, also lock its nested fields
        if ($this->_expectsSubclassOf_Entity_base && $this->_val) { $this->_val->lockFields(); }
        
        $this->_isLocked = true;
    }
    public function unlock() //Unlock a read-only field. For non read-only fields holding d a bWoo_Entity_base derived, we should call lockFields() on them ourself somewhere else
    {
        if ($this->_mode === bWoo_EntityField_base::MODE_RW) { bWoo_Exception::throwEx("bWoo_EntityField::unlock(): Field '$this->_fieldName' is not read-only, so we can't unlock"); }
        
        //In the case that the field holds a derived of a bWoo_Entity_base, also lock its nested fields
        if ($this->_expectsSubclassOf_Entity_base && $this->_val) { $this->_val->unlockFields(); }
        
        $this->_isLocked = false;
    }
    
    public function isChanged() //For read-only fields holding a bWoo_Entity_base derived, we should call areFieldsChanged() on them ourself somewhere else
    {
        if ($this->_mode === bWoo_EntityField_base::MODE_RW)
        {
            //In the case that the field holds a derived of a bWoo_Entity_base, check if any of its nested fields are changed
            if ($this->_expectsSubclassOf_Entity_base && $this->_val) { $this->_isChanged &= $this->_val->areFieldsChanged(); }
        }
        
        return $this->_isChanged;
    }
    public function markChanges() //For read-only fields holding d a bWoo_Entity_base derived, we should call markFieldsChanges() on them ourself somewhere else
    {
        if ($this->_mode === bWoo_EntityField_base::MODE_RO) { bWoo_Exception::throwEx("bWoo_EntityField::markChanges(): Field '$this->_fieldName' is read-only, so we can't mark changes"); }
        
        //In the case that the field holds a derived of a bWoo_Entity_base, also mark its nested fields
        if ($this->_expectsSubclassOf_Entity_base && $this->_val) { $this->_val->markFieldsChanges(); }
        
        $this->_isChanged = false;
    }
    
    public function set($val)
    {
        if ($this->_isLocked) { bWoo_Exception::throwEx("bWoo_EntityField::set(): Field '$this->_fieldName' is read-only"); }
        
        if ($this->_expectsInstanceOf && $val!==null)
        {
            $valClass = get_class($val);
            if ($valClass !== $this->_expectsInstanceOf) { bWoo_Exception::throwEx("bWoo_EntityField::set(): Expected an instance of $this->_expectsInstanceOf for '$this->_fieldName', but received a $valClass"); }
        }
        else if (is_string($val))
        {
        	$val = bWooApi::replaceSmartQuotes($val);
        }
        
        $this->_val = $val;
        
        if ($this->_mode === bWoo_EntityField_base::MODE_RO) { $this->_isLocked  = true; }
        else                                                 { $this->_isChanged = true; }
    }
    
    public function debug($indentation, $tabPadLength)
    {
        $subIndentation = $indentation + $tabPadLength;
        $valOutput      = null;
        
        if      ($this->_val === null)                  { $valOutput = 'NULL';         }
        else if ($this->_val === '')                    { $valOutput = 'EMPTY STRING'; }
        else if ($this->_expectsSubclassOf_Entity_base) { $valOutput = $this->_val->debug($subIndentation); $valOutput = substr($valOutput,$subIndentation); }
        else                                            { $valOutput = print_r($this->_val,true); $valOutput = str_replace("\n","\n".str_repeat("\t",$subIndentation),$valOutput); }
        
        $output = $this->_fieldName;
        if      ($this->_mode===bWoo_EntityField_base::MODE_RO) { $output .= ' (read-only)'; }
        else if ($this->isChanged()) { $output .= '*'; }
        $output = str_repeat("\t",$indentation).str_pad($output,$tabPadLength*4).$valOutput;
        
        return $output;
    }
};
    class bWoo_EntityField extends bWoo_EntityField_base
    {
        public function __construct($fieldName, $mode, $initialVal=null)
        {
            parent::__construct($fieldName, $expectsInstanceOf=bWoo_EntityField_base::ACCEPTS_ANYTHING, $mode, $initialVal);
        }
    };
    class bWoo_EntityField_Object extends bWoo_EntityField_base
    {
        public function __construct($fieldName, $expectsInstanceOf, $mode, $initialVal=null)
        {
            parent::__construct($fieldName, $expectsInstanceOf, $mode, $initialVal);
        }
    };
    class bWoo_EntityField_CategoryImage extends bWoo_EntityField_base
    {
        public function __construct($fieldName, $initialVal=null)
        {
            parent::__construct($fieldName, $expectsInstanceOf='bWoo_CategoryImage', $mode=bWoo_EntityField_base::MODE_RW, $initialVal);
        }
    };
    class bWoo_EntityField_ProductDimensions extends bWoo_EntityField_base
    {
        public function __construct($fieldName, $initialVal=null)
        {
            parent::__construct($fieldName, $expectsInstanceOf='bWoo_ProductDimensions', $mode=bWoo_EntityField_base::MODE_RW, $initialVal);
        }
    };

?>