<?

abstract class bWoo_Entity_base
{
    private $_fields = null; //Named array of bWoo_EntityField_base derived instances
    
    
    protected static function _throwEx($message)
    {
        $className  = get_called_class();
        $methodName = debug_backtrace()[1]['function'];
        
        bWoo_Exception::throwEx("$className::$methodName(): $message");
    }
    protected static function _rethrowEx(Exception $e)
    {
        $className          = get_called_class();
        $methodName         = debug_backtrace()[1]['function'];
        $exceptionClassName = get_class($e); //We mainly expect a WC_API_Client_HTTP_Exception, WC_API_Client_Exception or Exception
        $message			= "$className::$methodName(): Caught a $exceptionClassName: ".$e->getMessage();
        
        if ($e instanceof WC_API_Client_HTTP_Exception)
        {
	        $message .= "\n".$e->get_response()->body;
        }
        
        bWoo_Exception::throwEx($message);
    }
    
    
    protected function __construct()
    {
        $this->_fields = array();
    }
        protected function __construct_defineField(bWoo_EntityField_base $field)
        {
            if (isset($this->_fields[$field->fieldName()])) { $this->_throwEx('Field "'.$field->fieldName().'" already exists'); }
            
            $this->_fields[$field->fieldName()] = $field;
        }
    
    
    public function getFields() { return $this->_fields; }
    public function getField($fieldName)
    {
        if (!isset($this->_fields[$fieldName])) { $this->_throwEx("Field '$fieldName' doesn't exist"); }
        
        return $this->_fields[$fieldName];
    }
    public function getFieldVal($fieldName)       { return $this->getField($fieldName)->get(); }
    public function setFieldVal($fieldName, $val) { $this->getField($fieldName)->set($val); return $this; } //Chainable
    
    public function setFromStdObject($stdObject) //Set all received fields at once
    {
        $this->_setFromStdObject($stdObject, $canResetLockedFields=false);
    }
        protected function _setFromStdObject($stdObject, $canResetLockedFields) //Set all fields at once. We also need to know if we're in the constructor or not, to be able to set read-only fields correctly
        {
            if (!is_object($stdObject)) { $this->_throwEx("Object expected"); }
            
            foreach ($this->_fields as $fieldName => $field)
            {
                //If the field is found in the received object, use its value
                if (isset($stdObject->$fieldName))
                {
                    if ($canResetLockedFields || $field->mode()===bWoo_EntityField_base::MODE_RW)
                    {
                        if ($canResetLockedFields && $field->isLocked()) { $field->unlock(); }
                        
                        //If the bWoo_EntityField's value is a bWoo_Entity_base derived instance
                        if ($field->expectsSubclassOf_Entity_base())
                        {
                            if ($field->get() === null)
                            {
                                $className = $field->expectsInstanceOf();
                                $field->set(new $className()); //Ex new bWoo_CategoryImage()
                            }
                            
                            $field->get()->_setFromStdObject($stdObject->$fieldName, $canResetLockedFields);
                        }
                        else
                        {
                            $field->set($stdObject->$fieldName);
                        }
                        
                        if ($canResetLockedFields && $field->mode()===bWoo_EntityField_base::MODE_RO) { $field->lock(); }
                    }
                }
            }
            
            if ($canResetLockedFields) { $this->markFieldsChanges(); }
        }
    
    public function isFieldChanged($fieldName) { return $this->getField($fieldName)->isChanged(); }
    public function areFieldsChanged()
    {
        $areFieldsChanged = false;
        foreach ($this->_fields as $field) { $areFieldsChanged |= $field->isChanged(); } //Note that we go through the whole loop, so all is updated equally
        return $areFieldsChanged;
    }
    public function markFieldsChanges()
    {
    	foreach ($this->_fields as $field)
    	{
	        if      ($field->mode() === bWoo_EntityField_base::MODE_RW)        { $field->markChanges();              }
	        else if ($field->expectsSubclassOf_Entity_base() && $field->get()) { $field->get()->markFieldsChanges(); } //Check if the field should hold an inner instance of a bWoo_Entity_base or not
	    }
    }
    
    public function isFieldLocked($fieldName) { return $this->getField($fieldName)->isLocked(); }
    public function areFieldsLocked()
    {
        $areFieldsLocked = false;
        foreach ($this->_fields as $field) { $areFieldsLocked |= $field->isLocked(); } //Note that we go through the whole loop, so all is updated equally
        return $areFieldsLocked;
    }
    public function lockFields()
    {
        foreach ($this->_fields as $fieldName => $field)
        {
            if      ($field->mode() === bWoo_EntityField_base::MODE_RO)        { $field->lock();              }
            else if ($field->expectsSubclassOf_Entity_base() && $field->get()) { $field->get()->lockFields(); } //Check if the field should hold an inner instance of a bWoo_Entity_base or not
        }
    }
    public function unlockFields()
    {
        foreach ($this->_fields as $fieldName => $field)
        {
            if      ($field->mode() === bWoo_EntityField_base::MODE_RO)        { $field->unlock();              }
            else if ($field->expectsSubclassOf_Entity_base() && $field->get()) { $field->get()->unlockFields(); } //Check if the field should hold an inner instance of a bWoo_Entity_base or not
        }
    }
    
    
    public function toArray()
    {
        return $this->_toArray_fields($onlyChangedFields=false);
    }
        protected function _toArray_fields($onlyChangedFields) //If $onlyChangedFields=true, exclude most of read-only fields. Check isChanged() logic for cases that would still get through
        {
            $arr = array();
            
            foreach ($this->_fields as $fieldName => $field)
            {
                if (!$onlyChangedFields || $field->isChanged())
                {
                    //Check if the field should hold an inner instance of a bWoo_Entity_base or not
                    if (!$field->expectsSubclassOf_Entity_base()) { $arr[$fieldName] = $field->get(); }
                    else if ($field->get())                       { $arr[$fieldName] = $field->get()->toArray($includeId,$onlyChangedFields); }
                }
            }
            
            return $arr;
        }
    
    public function debug($indentation=0)
    {
        $output = '';
        
        $output = str_repeat("\t",$indentation).get_class($this).($this->areFieldsChanged()?'*':'').')';
        
        $output .= $this->_debug_fields($indentation);
        
        return $output;
    }
        protected function _debug_fields($indentation)
        {
            $output = '';
            
            $padLength = 0;
            foreach ($this->_fields as $fieldName => $field)
            {
                if (strlen($fieldName) > $padLength) { $padLength = strlen($fieldName); }
            }
            
            $tabPadLength = intval($padLength/4) + 2;
            foreach ($this->_fields as $field)
            {
                $output .= "\n".$field->debug($indentation+1, $tabPadLength);
            }
        
            return $output;
        }
};



abstract class bWoo_EntityWithoutPostId_base extends bWoo_Entity_base
{
    protected function __construct() { parent::__construct(); }
};



abstract class bWoo_EntityWithPostId_base extends bWoo_Entity_base
{
    private $_id = null; //WP post id
    
    
    protected function __construct() { parent::__construct(); }
    
    
    public function id($id=null) { if ($id!==null){$this->_id=$id;} return $this->_id; }
    public function isNew() { return $this->_id === null; }
    
    protected function _setFromStdObject($stdObject, $canResetLockedFields) //Base override
    {
        parent::_setFromStdObject($stdObject, $canResetLockedFields);
        
        if (isset($stdObject->id)) { $this->_id = $stdObject->id; }
    }
    public function toArray() //Base override
    {
        $arr = parent::toArray();
        
        if ($this->_id!==null) { $arr['id'] = $this->_id; }
        
        return $arr;
    }
    
    abstract public function save(bWooApi $api);
        protected function _save_makeMainPostData()
        {
            $arr = $this->_toArray_fields($onlyChangedFields=true);
            
            if ($this->_id!==null) { $arr['id'] = $this->_id; }
            
            bWooApi::setPostInGoodUTF8Encoding($arr);
            
            return $arr;
        }
    abstract public function delete(bWooApi $api);
    abstract public static function loadOne(bWooApi $api, $id);
    
    public function debug($indentation=0) //Base override
    {
        $output = '';
        
        $output = str_repeat("\t",$indentation).get_class($this).' ('.($this->_id?"#$this->_id":'new').($this->areFieldsChanged()?'*':'').')';
        
        $output .= $this->_debug_fields($indentation);
        
        return $output;
    }
    
    //Returns instances as an array of stdObject
    protected static function _loadList(bWooApi $api, WC_API_Client_Resource $api_xxx, bWoo_EntitySearchOptions_base $searchOptions) //Ex where to achieve "$api->api_categories()->...", we'll do "_loadList($api,$api->api_categories())"
    {
        $pageCount        = null;
        $stdObjectArr     = array();
        $wantsAll         = ($searchOptions->per_page === bWoo_EntitySearchOptions_base::PER_PAGE_ALL);
        $searchOptionsArr = $searchOptions->toArray();
        
        //Since we have no way to say we want to get all things, limit things to 100 and go around that limitation
        if ($wantsAll) {  $searchOptionsArr['per_page'] = 100; }
        
        while ($pageCount===null || $searchOptions->page <= $pageCount)
        {
            $response = null;
            
            try
            {
                $response = $api_xxx->get(null, $searchOptionsArr);
            }
            catch (Exception $e) { bWoo_Entity_base::_rethrowEx($e); }
            
            if ($pageCount === null) { $pageCount = intval($response->headers['X-WP-TotalPages']); }
            
            if (is_array($response->jsonBody))
            {
                foreach ($response->jsonBody as $stdObject) { $stdObjectArr[] = $stdObject; }
            }
            else { bWoo_Entity_base::_throwEx('Response had no jsonBody'); }
            
            $searchOptions->page++;
            
            if (!$wantsAll) { break; }
        }
        
        return $stdObjectArr;
    }
};


abstract class bWoo_ChildEntityWithPostId_base extends bWoo_EntityWithPostId_base
{
    protected function __construct() { parent::__construct(); }
    
    
    public function save(bWooApi $api)                { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
    public function delete(bWooApi $api)              { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
    public static function loadOne(bWooApi $api, $id) { bWoo_Entity_base::_throwEx("This entity can't use the API by itself"); }
};

?>