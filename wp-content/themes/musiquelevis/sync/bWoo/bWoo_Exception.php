<?

class bWoo_Exception extends Exception
{
    public $instance = null;
    
    public static function throwEx($message, $instance=null)
    {
        $e = new bWoo_Exception($message);
        $e->instance = null;
        
        throw $e;
    }
};

?>