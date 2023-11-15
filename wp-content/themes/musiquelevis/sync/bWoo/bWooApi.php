<?

class bWooApi
{
    private $_wp_storeUrl  = null; //Ex https://instantcomptant.bravad-dev.com
    private $_wp_key       = null; //Ex ck_fbcb961c8e6a7e9152e31bcf5a93f1f326116d48, can be found in `{wp_}_woocommerce_api_keys`
    private $_wp_secret    = null; //Ex cs_d8fa239c0117ed104628241b3504fda8031b1769, can be found in `{wp_}_woocommerce_api_keys`
    private $_wc_apiClient = null; //Instance of WC_API_Client
    private static $_isServerUTF8 = false;
    
    
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
    
    
    public function api_categories() { return $this->_wc_apiClient->product_categories; }
    public function api_products()   { return $this->_wc_apiClient->products;           }
    public function api_orders()     { return $this->_wc_apiClient->orders;             }
    
    public static function isServerUTF8($isServerUTF8) { bWooApi::$_isServerUTF8 = $isServerUTF8; }
    public static function setPostInGoodUTF8Encoding(&$postData)
    {
    	bWooApi::_utf8_encode_decode_recursive($postData, $what=bWooApi::$_isServerUTF8?'encode':'decode');
    }
	    private static function _utf8_encode_decode_recursive(&$array, $what) //$what = {encode|decode}
	    {
	        $wantUTF8 = ($what == 'encode');
	        
	        array_walk_recursive($array, function(&$item, $key, $wantUTF8)
	        {
	            $isUTF8 = (mb_detect_encoding($item, 'utf-8', true));
	            
	            if ($wantUTF8 && !$isUTF8)
	            {
	                $item = utf8_encode($item);
	            }
	            else if (!$wantUTF8 && $isUTF8)
	            {
	                $item = utf8_decode($item);
	            }
	        }, $wantUTF8);
	    }
    public static function replaceSmartQuotes($string)
    {
    	$chr_map = array
    	(
			// Windows codepage 1252
			"\xC2\x82" => "'",
			"\xC2\x84" => '"',
			"\xC2\x8B" => "'",
			"\xC2\x91" => "'",
			"\xC2\x92" => "'",
			"\xC2\x93" => '"',
			"\xC2\x94" => '"',
			"\xC2\x9B" => "'",
			
			// Regular Unicode    
			"\xC2\xAB"     => '"',
			"\xC2\xBB"     => '"',
			"\xE2\x80\x98" => "'",
			"\xE2\x80\x99" => "'",
			"\xE2\x80\x9A" => "'",
			"\xE2\x80\x9B" => "'",
			"\xE2\x80\x9C" => '"',
			"\xE2\x80\x9D" => '"',
			"\xE2\x80\x9E" => '"',
			"\xE2\x80\x9F" => '"',
			"\xE2\x80\xB9" => "'",
			"\xE2\x80\xBA" => "'",
			
			//Other MS things
			chr(145) => "'",
			chr(146) => "'",
			chr(147) => '"',
			chr(148) => '"',
			chr(151) => '-'
		);
		
		$chr = array_keys  ($chr_map); // but: for efficiency you should
		$rpl = array_values($chr_map); // pre-calculate these two arrays
		
		$string = html_entity_decode($string, ENT_QUOTES, "UTF-8");
		$string = str_replace($chr, $rpl, $string);
		
		return $string;
    }
};

?>