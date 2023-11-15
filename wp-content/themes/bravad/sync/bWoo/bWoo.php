<?

class bWooApi
{
    private $_wp_storeUrl  = null; //Ex https://instantcomptant.bravad-dev.com
    private $_wp_key       = null; //Ex ck_fbcb961c8e6a7e9152e31bcf5a93f1f326116d48
    private $_wp_secret    = null; //Ex cs_d8fa239c0117ed104628241b3504fda8031b1769
    private $_wc_apiClient = null; //Instance of WC_API_Client
    
    
    private function _throwEx($method,$message) { bWoo_Exception::throwEx("bWoo::$method(): $message", $this); }
    
    
    public function __construct($wp_storeUrl, $wp_key, $wp_secret)
    {
        if (empty($wp_storeUrl) || empty($wp_key) || empty($wp_secret)) { bWoo_Exception::throwEx('__construct','Invalid params'); }
        
        $this->_wp_storeUrl = $wp_storeUrl;
        $this->_wp_key      = $wp_key;
        $this->_wp_secret   = $wp_secret;
        
        $options = array
        (
            'debug'           => true,
            'return_as_array' => false,
            'validate_url'    => false,
            'timeout'         => 30,
            'ssl_verify'      => false
        );
        
        try
        {
            $this->_wc_apiClient = new WC_API_Client($this->_wp_storeUrl, $this->_wp_key, $this->_wp_secret, $options);
        }
        catch (Exception $e)
        {
            bWoo_Exception::throwEx('__construct','WC_API_Client exception: '.$e->getMessage());
        }
    }
    
    
    
};

?>