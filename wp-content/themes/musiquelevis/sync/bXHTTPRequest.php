<?

/*
****************************************************************************************************************************************************************************************************************************
********** IMPORTANT - UPDATE CURL/OPENSSL ENOUGH TO BE ABLE TO USE TLS 1.2. CONFIRM WITH "bXHTTPRequest::serverCanDoTLSv1_2()" + https://www.digitalocean.com/community/questions/how-to-upgrade-curl-in-centos6 **********
		 	CONFIRM WITH *ALL* OF THE FOLLOWING:
				$bXHTTPRequest = new bXHTTPRequest("https://tlstest.paypal.com",                        bXHTTPRequest::METHOD_GET);
				$bXHTTPRequest = new bXHTTPRequest("https://www.howsmyssl.com",                         bXHTTPRequest::METHOD_GET);
				$bXHTTPRequest = new bXHTTPRequest("https://www.howsmyssl.com/a/check",                 bXHTTPRequest::METHOD_GET); //JSON way
				$bXHTTPRequest = new bXHTTPRequest("https://www.ssllabs.com/ssltest/viewMyClient.html", bXHTTPRequest::METHOD_GET);
				$bXHTTPRequest = new bXHTTPRequest("https://fancyssl.hboeck.de",                        bXHTTPRequest::METHOD_GET);
****************************************************************************************************************************************************************************************************************************

CHANGELOGS:
    1.1 Initial
    1.2 Prevent spinning forever in GET by adding an extra CRLF
    1.3 Added followRedirectionsCount to intercept redirections, enableRawLogs, lastRequestRaw, lastResponseRaw...
    1.4 Added support for method "PUT"
    2.0 Added support for TLS1.2, using() fsockopen vs cURL funcs (cURL is better for TLS), gzip compression, isSecureProtocol(), serverCURLVersion(), serverCanDoTLSv1_2(), expectResponseType(), postCharset()
    2.1 Minor fix for PUT method on CURL + added method DELETE, isMethodCustom() and isMethodPostable()

New usage example for TLS:
		$bXHTTPRequest = new bXHTTPRequest("https://tlstest.paypal.com", bXHTTPRequest::METHOD_GET);
		$bXHTTPRequest->using(bXHTTPRequest::USING_CURL);
			//OR: $bXHTTPRequest->using(bXHTTPRequest::USING_FSOCKOPEN);
	    $bXHTTPRequest->enableRawLogs(true);
	    $response           = $bXHTTPRequest->request();
	    $responseHeadersArr = $bXHTTPRequest->lastResponseHeaders();
		echo  "response(): "           .$response                            ."\n\n\n"
			. "lastRequestRaw(): "     .$bXHTTPRequest->lastRequestRaw()     ."\n\n\n"
			. "lastResponseRaw(): "    .$bXHTTPRequest->lastResponseRaw()    ."\n\n\n"
			. "lastResponseCode(): "   .$bXHTTPRequest->lastResponseCode()   ."\n\n\n"
	        . "lastResponseHeaders(): ".print_r($responseHeadersArr,true)    ."\n\n\n"
			. "lastResponseMessage(): ".$bXHTTPRequest->lastResponseMessage()."\n\n\n";

Usage example #1:
    $bXHTTPRequest = new bXHTTPRequest('https://clients.keybook.com/test.php?a=123', bXHTTPRequest::METHOD_GET);
    $response = $bXHTTPRequest->request();
    if ($response !== false)
    {
        ...
    }
    else
    {
        echo $bXHTTPRequest->lastResponseCode();
        echo $bXHTTPRequest->lastResponseMessage();
    }

Usage example #2:
    $bXHTTPRequest = new bXHTTPRequest('https://clients.keybook.com/test.php?a=123', bXHTTPRequest::METHOD_POST);
    $bXHTTPRequest->postData( array('firstName'=>'a','lastName'=>'b') );
    $bXHTTPRequest->addPostData('email','c');
    $response = $bXHTTPRequest->request();
    if ($response !== false)
    {
        ...
    }
    else
    {
        echo $bXHTTPRequest->lastResponseCode();
        echo $bXHTTPRequest->lastResponseMessage();
    }

Usage example #3:
    $bXHTTPRequest = new bXHTTPRequest('https://clients.keybook.com/test.php?a=123', bXHTTPRequest::METHOD_POST);
    $bXHTTPRequest->postContentType(bXHTTPRequest::POST_CONTENT_TYPE_XML);
    $bXHTTPRequest->postData( '<xml>...</xml>' );
    $response = $bXHTTPRequest->request();
    if ($response !== false)
    {
        ...
    }
    else
    {
        echo $bXHTTPRequest->lastResponseCode();
        echo $bXHTTPRequest->lastResponseMessage();
    }

Usage example #4, following redirections (bXHTTPRequest::REDIRECTIONS_MAX by default - ON):
    $bXHTTPRequest = new bXHTTPRequest('http://clients.keybook.com', bXHTTPRequest::METHOD_GET, $followRedirectionsCount=3);
    $bXHTTPRequest->enableRawLogs(true);
    $response        = $bXHTTPRequest->request();
    $redirectionLogs = $bXHTTPRequest->lastRedirectionLogs();
    var_dump($redirectionLogs);
    echo $response;

Getters / setters:
    echo $bXHTTPRequest->followRedirectionsCount(); //Default is bXHTTPRequest::REDIRECTIONS_MAX
    echo $bXHTTPRequest->postContentType();         //Default is bXHTTPRequest::POST_CONTENT_TYPE_FORM
    echo $bXHTTPRequest->port();                    //Default is bXHTTPRequest::PORT_STANDARD
    echo $bXHTTPRequest->secsTimeOut();             //Default is PHP's setting
    echo $bXHTTPRequest->enableRawLogs();           //Default is false
    
    echo $bXHTTPRequest->postContentType( bXHTTPRequest::POST_CONTENT_TYPE_FORM, bXHTTPRequest::POST_CONTENT_TYPE_XML or bXHTTPRequest::POST_CONTENT_TYPE_JSON );
    echo $bXHTTPRequest->port( bXHTTPRequest::PORT_STANDARD or bXHTTPRequest::PORT_SSL );
    echo $bXHTTPRequest->secsTimeOut(10);
    
    s( $bXHTTPRequest->lastRequestRaw()      );
    s( $bXHTTPRequest->lastResponseRaw()     );
    s( $bXHTTPRequest->lastResponseCode()    );
    s( $bXHTTPRequest->lastResponseMessage() );
    s( $bXHTTPRequest->lastResponseHeaders() );
    s( $bXHTTPRequest->lastRedirectionLogs() );

Methods:
    new bXHTTPRequest($url, bXHTTPRequest::METHOD_GET);
    new bXHTTPRequest($url, bXHTTPRequest::METHOD_POST);
    new bXHTTPRequest($url, bXHTTPRequest::METHOD_PUT);
    new bXHTTPRequest($url, bXHTTPRequest::METHOD_DELETE);
*/


class bXHTTPRequest
{
    const VERSION = '2.1';
    
    const USING_CURL      = 'curl';
    const USING_FSOCKOPEN = 'fsockopen'; //NOTE: As of now (2018-06-03), it doesn't seem possible to do TLSv1.2 calls with fsockopen, but ok with cURL though
    const PROTOCOL_HTTP  = 'http';
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_SSL   = 'ssl';
    const PROTOCOL_TLS   = 'tls';
    const METHOD_GET    = 'GET';
    const METHOD_POST   = 'POST';
    const METHOD_PUT    = 'PUT';
    const METHOD_DELETE = 'DELETE';
    const POST_CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';
    const POST_CONTENT_TYPE_XML  = 'application/xml'; //Or 'text/xml';
    const POST_CONTENT_TYPE_JSON = 'application/json';
    const POST_CHARSET_ISO_8859_1 = 'iso-8859-1';
    const POST_CHARSET_UTF8       = 'utf-8';
    const EXPECT_RESPONSE_TYPE_ALL  = '*/*';
    const EXPECT_RESPONSE_TYPE_TEXT = 'text/plain';
    const EXPECT_RESPONSE_TYPE_HTML = 'text/html';
    const EXPECT_RESPONSE_TYPE_XML  = 'application/xml'; //Or 'text/xml';
    const EXPECT_RESPONSE_TYPE_JSON = 'application/json';
    const EXPECT_RESPONSE_TYPE_PDF  = 'application/pdf';
    const PORT_STANDARD = 80;
    const PORT_SSL      = 443;
	const CURL_SSLVERSION_DEFAULT = 0; //NOTE: These consts would actually already be defined with proper PHP versions
	const CURL_SSLVERSION_TLSv1   = 1;
	const CURL_SSLVERSION_SSLv2   = 2;
	const CURL_SSLVERSION_SSLv3   = 3;
    const CURL_SSLVERSION_TLSv1_0 = 4;
    const CURL_SSLVERSION_TLSv1_1 = 5;
    const CURL_SSLVERSION_TLSv1_2 = 6;
    const SSL_CIPHERS_OPENSSL_TLSV1_2 = 'ECDHE-RSA-AES128-GCM-SHA256,ECDHE-ECDSA-AES128-SHA'; //NOTE: If the server's not using OpenSSL, the ciphers names will be written in a different way
    const COMPRESSION_GZIP = 'gzip,deflate';
    const REDIRECTIONS_MAX                 = 10;
    const REDIRECTIONS_CURL_NATIVE_HANDLER = false; //Controls whether cURL does all the things (better) and "hides" redirections, leaving "_lastRedirectionLogs" empty though. https://curl.haxx.se/libcurl/c/CURLOPT_FOLLOWLOCATION.html
    private static $_REDIRECT_HTTP_CODES = array(301, 302, 303, 307, 308);
    
    
    private $_protocol = null; //Ex http or https
    private $_host     = null; //Ex clients.keybook.com
    private $_path     = null; //Ex /bob.php?a=123
    private $_method   = null; //Const of "bXHTTPRequest::METHOD_x"
    
    private $_using                   = bXHTTPRequest::USING_CURL; //Const of "bXHTTPRequest::USING_x"
    private $_followRedirectionsCount = 0;    //Indicates if upon catching 300,301,302,307 etc, we should redirect to the final page, or just display that we got a 300
    private $_expectResponseType      = bXHTTPRequest::EXPECT_RESPONSE_TYPE_ALL; //Const of "bXHTTPRequest::EXPECT_RESPONSE_TYPE_x" for the "accept" header
    private $_postContentType         = bXHTTPRequest::POST_CONTENT_TYPE_FORM;   //Const of "bXHTTPRequest::POST_CONTENT_TYPE_x"
    private $_postCharset             = null; //Const of "bXHTTPRequest::POST_CHARSET_x"
    private $_postData                = null; //An array or raw string
    private $_port                    = null; //Const of "bXHTTPRequest::PORT_x"
    private $_secsTimeOut             = null; //PHP may decide not to use it though
    private $_enableRawLogs           = false; //Indicates if we want to put content in the "_lastX" vars
    
    
    
    //Always available response data
    private $_lastResponseCode    = null;
    private $_lastResponseHeaders = null;
    private $_lastResponseMessage = null;
    //Data available only if we enable full raw logs
    private $_lastRequestRaw      = null;
    private $_lastRedirectionLogs = null; //As an array of {isSecure, rawRequest, rawResponse}
    
    
    
    public function __construct($url, $method, $followRedirectionsCount=bXHTTPRequest::REDIRECTIONS_MAX) //Method must be a const of "bXHTTPRequest::METHOD_x"
    {
        //Get url parts. Expects something like "http(s)://a.b.c/something..."
        if (preg_match('/^((https?):)?(\/\/)?([^\/]+)(.*$)/', $url, $output_array))
        {
        	$this->_protocol = $output_array[2]; //Ex https
            $this->_host     = $output_array[4]; //Ex clients.keybook.com
            $this->_path     = $output_array[5]; //Ex /bob.php?a=123
            
            if (empty($this->_host))     { $this->_throw('__construct', "Wrong url '$url'"); }
            if (empty($this->_protocol)) { $this->_protocol = bXHTTPRequest::PROTOCOL_HTTP; }
            if (empty($this->_path))     { $this->_path = '/'; }
            
            $this->_method                  = $method;
            $this->_followRedirectionsCount = $followRedirectionsCount;
            
            //Set other default vals
            $this->_port        = ($this->isSecureProtocol() ? bXHTTPRequest::PORT_SSL : bXHTTPRequest::PORT_STANDARD);
            $this->_secsTimeOut = ini_get('default_socket_timeout');
        }
        else
        {
            $this->_throw('__construct', "Wrong url '$url'");
        }
    }
        private function _throw($method, $message)
        {
            throw new Exception("bHTTPRequest::$method(): $message");
        }
    
    
    
    //GETTERS / SETTERS
        public function isSecureProtocol() { return ($this->_protocol===bXHTTPRequest::PROTOCOL_HTTPS || $this->_protocol===bXHTTPRequest::PROTOCOL_SSL || $this->_protocol===bXHTTPRequest::PROTOCOL_TLS); }
        public static function serverCURLVersion()  { $infos = curl_version(); return $infos['version'];   } //Ex "7.19.7", "7.34.0", "7.60.0"
        public static function serverCanDoTLSv1_2() { return bXHTTPRequest::serverCURLVersion()>="7.34.0"; } //If the server can perform TLS 1.2 requests correctly or not - Requires updating CURL: https://www.digitalocean.com/community/questions/how-to-upgrade-curl-in-centos6
        
        public function isMethodCustom()   { return ($this->_method!==bXHTTPRequest::METHOD_GET && $this->_method!==bXHTTPRequest::METHOD_POST); } //For PUT, DELETE...
        public function isMethodPostable() { return ($this->_method!==bXHTTPRequest::METHOD_GET); } //For POST, PUT, DELETE...
        
        public function using($setter=null)                   { if($setter!==null){$this->_using                  =$setter;} return $this->_using;                   }
        public function followRedirectionsCount($setter=null) { if($setter!==null){$this->_followRedirectionsCount=$setter;} return $this->_followRedirectionsCount; }
        public function expectResponseType($setter=null)      { if($setter!==null){$this->_expectResponseType     =$setter;} return $this->_expectResponseType;      }
        public function port($setter=null)                    { if($setter!==null){$this->_port                   =$setter;} return $this->_port;                    }
        public function secsTimeOut($setter=null)             { if($setter!==null){$this->_secsTimeOut            =$setter;} return $this->_secsTimeOut;             }
        
        public function postContentType($setter=null)
        {
			if ($setter!==null) { $this->_postSettersAssertMethodIsNotGet(); $this->_postContentType = $setter; }
			
			return $this->_postContentType;
		}
        public function postCharset($setter=null)
        {
			if ($setter!==null) { $this->_postSettersAssertMethodIsNotGet(); $this->_postCharset = $setter; }
			
			return $this->_postCharset;
		}
        public function postData($setter=null) //String or array
        {
			if ($setter!==null) { $this->_postSettersAssertMethodIsNotGet(); $this->_postData = $setter; }
			
			return $this->_postData;
		}
        public function addPostData($key, $value) //Helper to add array like data
        {
            $this->_postSettersAssertMethodIsNotGet();
            
            if      ($this->_postData === null)   { $this->_postData = array(); }
            else if (is_string($this->_postData)) { $this->_throw('addPostData', "Can't add array-like post data when it already contains raw string data"); }
            
            $this->_postData[$key] = $value;
        }
        private function _postSettersAssertMethodIsNotGet()
        {
        	if (!$this->isMethodPostable()) { $this->_throw('postData', "Can't use POST/PUT/DELETE related methods in GET calls"); }
        }
        
        public function lastResponseCode()    { return $this->_lastResponseCode;    }
    	public function lastResponseHeaders() { return $this->_lastResponseHeaders; }
        public function lastResponseMessage() { return $this->_lastResponseMessage; }
        
        public function enableRawLogs($setter=null) { if($setter!==null){$this->_enableRawLogs=$setter;} return $this->_enableRawLogs; }
	        public function lastRequestRaw()  { return $this->_lastRequestRaw;  }
	        public function lastResponseRaw()
	        {
	        	$rawHeaders = '';
	        	foreach ($this->_lastResponseHeaders as $headerName => $headerValue)
	        	{
	        		$rawHeaders .= "$headerName: $headerValue\r\n";
	        	}
	        	
	        	return "HTTP 1.1/$this->_lastResponseCode\r\n$rawHeaders\r\n$this->_lastResponseMessage";
	        }
        	public function lastRedirectionLogs() { return $this->_lastRedirectionLogs; }
    
    
    
    //Performs the request and return its response, or FALSE in case of error
    public function request()
    {
        $this->_lastRequestRaw      = null;
        $this->_lastResponseCode    = null;
        $this->_lastResponseHeaders = null;
        $this->_lastResponseMessage = null;
        $this->_lastRedirectionLogs = null;
        
        $requestHeaders = array();
        $postDataString = null;
        $responseBody   = null;
        
        //Do common stuff for cURL and fsockopen ways
        {
	        $requestHeaders[] = "Host: $this->_host";
	        $requestHeaders[] = "Accept: $this->_expectResponseType";
	        $requestHeaders[] = "Accept-Encoding: ".bXHTTPRequest::COMPRESSION_GZIP;
	        $requestHeaders[] = "Connection: keep-alive";
	        $requestHeaders[] = "Cache-Control: max-age=0";
	        $requestHeaders[] = "Origin: null";
	        
	        if ($this->isSecureProtocol())
	        {
	            $requestHeaders[] = "Upgrade-Insecure-Requests: 1";
	        }
	        
	        //If it's a POST, PUT, etc...
	        if ($this->isMethodPostable())
	        {
	            $postDataString = (is_array($this->_postData) ? http_build_query($this->_postData) : $this->_postData);
	            
	            if ($this->isMethodCustom()) //For PUT, DELETE...
	            {
	            	$requestHeaders[] = "X-HTTP-Method-Override: $this->_method"; //NOTE: If it was with cURL, we'd also need to do: $cURLOptions[CURLOPT_CUSTOMREQUEST] = $this->_method;
	            }
	            
	            $contentType = $this->_postContentType;
	            if ($this->_postCharset) { $contentType .= "; charset=$this->_postCharset"; }
	            $requestHeaders[] = "Content-Type: $contentType";
	        }
	    }
        
        //Do the request and get its response, using the wanted library
        if ($this->_using === bXHTTPRequest::USING_CURL)
        {
        	$this->_request_using_curl($requestHeaders, $postDataString);
        }
        else if ($this->_using === bXHTTPRequest::USING_FSOCKOPEN)
        {
        	$this->_request_using_fsockopen($requestHeaders, $postDataString);
        }
        else
        {
        	$this->_throw('request', "Don't know how to use: $this->_using");
        }
        
        //If all was well, maybe we have something more to do now
        if ($this->_lastResponseMessage !== false)
        {
	        //Did we get a redirect and wanted to follow it ?
	        if ($this->_followRedirectionsCount>0 && in_array($this->_lastResponseCode,bXHTTPRequest::$_REDIRECT_HTTP_CODES) && !empty($this->_lastResponseHeaders['location']))
	        {
	            $newLocation = $this->_lastResponseHeaders['location'];
	            
	            //Check if we're moving to something like "/bob" and should change it to "http://bob" or "https://bob"
	            if (strpos($newLocation,'://') === false)
	            {
	            	$newLocation = $this->_request_getProtocolStringPartForReason('redirection').$this->_host.$newLocation;
	            }
	            
	            $this->_lastResponseMessage = $this->_request_followRedirection($newLocation);
	        }
        }
        
        return $this->_lastResponseMessage;
    }
    	//Returns something like "", "https://", "ssl://" or "http://" depending on why we get here
    	private function _request_getProtocolStringPartForReason($reason)
    	{
    		if ($reason === 'fsockopen_initialCall')
    		{
    			return $this->isSecureProtocol() ? bXHTTPRequest::PROTOCOL_SSL."://" : ''; //SSL seems better than TLS (https://www.howsmyssl.com 2018-06-01). For HTTP, seems we must not add "http://"
		    }
    		else if ($reason === 'curl_initialCall')
    		{
		        return "$this->_protocol://";
		    }
		    else if ($reason === 'redirection')
		    {
		    	return $this->isSecureProtocol() ? bXHTTPRequest::PROTOCOL_HTTPS."://" : bXHTTPRequest::PROTOCOL_HTTP."://";
		    }
		    
		    return "$this->_protocol://";
        }
        private function _request_followRedirection($newUrl)
        {
            $newMethod = null;
            
            //Check if we should keep post through redirections
            switch ($this->_lastResponseCode)
            {
                //KEEPING ORIGINAL METHOD
                    case 307: //Ex when http->https
                    case 308:
                        $newMethod = $this->_method;
                        break;
                //FALLING BACK TO GET
                    case 301:
                    case 302: //Ex redirection to another page
                    case 303:
                    default:
                        $newMethod = bXHTTPRequest::METHOD_GET;
                        break;
            }
            
            $bXHTTPRequest_redirection = new bXHTTPRequest($newUrl, $newMethod);
            //Transfer appropriate vars
            {
	            $bXHTTPRequest_redirection->_using                   = $this->_using;
	            $bXHTTPRequest_redirection->_followRedirectionsCount = $this->_followRedirectionsCount - 1;
	            $bXHTTPRequest_redirection->_expectResponseType      = $this->_expectResponseType;
	            $bXHTTPRequest_redirection->_secsTimeOut             = $this->_secsTimeOut;
	            $bXHTTPRequest_redirection->_enableRawLogs           = $this->_enableRawLogs;
	            //NOTE: We can't specify port, since we might flip between secure and unsecure
	            
	            if ($newMethod !== bXHTTPRequest::METHOD_GET)
	            {
	                $bXHTTPRequest_redirection->_postData = $this->_postData;
	            }
	        }
            
            $response = $bXHTTPRequest_redirection->request();
            
            if ($this->_enableRawLogs)
            {
                $this->_lastRedirectionLogs = array();
                $this->_lastRedirectionLogs[] = array
                (
                    'isSecure'    => $this->isSecureProtocol(),
                    'rawRequest'  => $this->lastRequestRaw(), 
                    'rawResponse' => $this->lastResponseRaw()
                );
                
                //If the new call also kept on doing redirections
                if ($bXHTTPRequest_redirection->_lastRedirectionLogs)
                {
                    $this->_lastRedirectionLogs = array_merge($this->_lastRedirectionLogs, $bXHTTPRequest_redirection->_lastRedirectionLogs);
                }
                //If the new call had no more room for redirection / got non redirection http status, add what happened in there anyways
                else
                {
                    $this->_lastRedirectionLogs[] = array
                    (
                        'isSecure'    => $bXHTTPRequest_redirection->isSecureProtocol(),
                        'rawRequest'  => $bXHTTPRequest_redirection->lastRequestRaw(),
                        'rawResponse' => $bXHTTPRequest_redirection->lastResponseRaw()
                    );
                }
            }
            
            //NOTE: Don't move this above the other code block, as we already had one request fully done before this func
            $this->_lastRequestRaw      = $bXHTTPRequest_redirection->_lastRequestRaw;
            $this->_lastResponseCode    = $bXHTTPRequest_redirection->_lastResponseCode;
            $this->_lastResponseHeaders = $bXHTTPRequest_redirection->_lastResponseHeaders;
            $this->_lastResponseMessage = $bXHTTPRequest_redirection->_lastResponseMessage;
            
            return $response;
        }
        
        /*
        Parse one header (since we may receive it multiple times), then return its original length (only useful for cURL though)
        Usage examples:
			$cURLOptions[CURLOPT_HEADERFUNCTION] = function($curl_unused, $currentHeader)
													{
														return $this->_request_using_x_parseResponseHeader($currentHeader);
													};
        */
        public function _request_using_x_parseResponseHeader($currentHeader)
        {
			$len           = strlen($currentHeader);
			$currentHeader = explode(':', $currentHeader, 2);
			
			if (count($currentHeader) < 2) { return $len; } // ignore invalid headers

			$headerName  = strtolower(trim($currentHeader[0]));
			$headerValue = trim($currentHeader[1]);
			
			$headerShouldBeStoredAsArr = false; //Always false to keep code simple everywhere we do "$this->_lastResponseHeaders['...']", but eventually we should adjust for recurring headers that should become an arr
			if ($headerShouldBeStoredAsArr)
			{
				if (!isset($this->_lastResponseHeaders[$headerName])) { $this->_lastResponseHeaders[$headerName] = array(); }
				$this->_lastResponseHeaders[$headerName][] = $headerValue;
			}
			else
			{
				$this->_lastResponseHeaders[$headerName] = $headerValue;
			}
			
			return $len;
		}
        
        private function _request_using_curl($requestHeaders, $postDataString=null)
        {
        	$responseBody = false;
	        $cURLOptions  = array();
	        
	        //Prepare the options
	        {
				$cURLOptions[CURLOPT_URL]            = $this->_request_getProtocolStringPartForReason('curl_initialCall')."$this->_host$this->_path";
		        $cURLOptions[CURLOPT_HEADER]         = 0; //Indicates that we don't want curl_exec() to return RESPONSE headers; we'll get them with CURLOPT_HEADERFUNCTION
		        $cURLOptions[CURLOPT_RETURNTRANSFER] = 1; //Indicates that we care about the response and wait for it
				$cURLOptions[CURLOPT_TIMEOUT]        = $this->_secsTimeOut;
				$cURLOptions[CURLOPT_ENCODING]       = bXHTTPRequest::COMPRESSION_GZIP;
				$cURLOptions[CURLOPT_FRESH_CONNECT]  = 0; //Default behavior - reuses existing / floating connections
		        
				//Request headers and post things
				{
		            if ($this->isMethodCustom())
		            {
		            	$cURLOptions[CURLOPT_CUSTOMREQUEST] = $this->_method; //NOTE: We also need to do $requestHeaders[] = "X-HTTP-Method-Override: $this->_method";
		            }
		            
			        $cURLOptions[CURLOPT_HTTPHEADER] = $requestHeaders; //As array
			        if ($this->_enableRawLogs)
			        {
			        	$cURLOptions[CURLINFO_HEADER_OUT] = 1; //So we can later do "curl_getinfo($ch, CURLINFO_HEADER_OUT);" to get what was actually sent, for "_lastRequestRaw"
					}
			        
			        if ($postDataString !== null)
			        {
				        $cURLOptions[CURLOPT_POST]       = 1;
				        $cURLOptions[CURLOPT_POSTFIELDS] = $postDataString;
				    }
			    }
		        
				if ($this->isSecureProtocol())
				{
					//TLS 1.2 support is -really- important with payment platforms, as of (2018-06-04)
					if (bXHTTPRequest::serverCanDoTLSv1_2())
					{
						$cURLOptions[CURLOPT_SSLVERSION]      = bXHTTPRequest::CURL_SSLVERSION_TLSv1_2;
						$cURLOptions[CURLOPT_SSL_CIPHER_LIST] = bXHTTPRequest::SSL_CIPHERS_OPENSSL_TLSV1_2; //NOTE: If the server's not using OpenSSL, the ciphers names will be written in a different way
					}
					else
					{
						$cURLOptions[CURLOPT_SSLVERSION] = bXHTTPRequest::CURL_SSLVERSION_DEFAULT;
					}
					
					//CURL default value things:
					$cURLOptions[CURLOPT_SSL_VERIFYPEER] = 1; //Default behavior
					$cURLOptions[CURLOPT_SSL_VERIFYHOST] = 2; //Default behavior
				}
				
				if (bXHTTPRequest::REDIRECTIONS_CURL_NATIVE_HANDLER)
				{
					$cURLOptions[CURLOPT_FOLLOWLOCATION] = 1;
					$cURLOptions[CURLOPT_MAXREDIRS]      = $this->_followRedirectionsCount;
				}
				else
				{
					$cURLOptions[CURLOPT_FOLLOWLOCATION] = 0; //Default behavior
				}
				
				/*
				Do that strange thing to get headers AND body response https://stackoverflow.com/questions/9183178/can-php-curl-retrieve-response-headers-and-body-in-a-single-request
				The header func will be called once for each received header line. Vals are stored as arrs in case something occurs multiple times
				NOTE: We need an intermediate pointer on $this for PHP < 5.4, and since it's ran as if it was "outside" the class, we can't have the called func private -_-
				*/
				{
					$this->_lastResponseHeaders = array();
					
					$areYouKiddingMe = $this;
					
					$cURLOptions[CURLOPT_HEADERFUNCTION] = function($curl_unused, $currentHeader) use ($areYouKiddingMe)
															{
																return $areYouKiddingMe->_request_using_x_parseResponseHeader($currentHeader);
															};
				}
			}
			
			//Do the request
			{
		        $ch = curl_init();
		        
		        foreach ($cURLOptions as $optionName => $optionVal) { curl_setopt($ch, $optionName, $optionVal); }
		        
		        $responseBody            = curl_exec($ch); //Note: response headers are catched via CURLOPT_HEADERFUNCTION
		        $this->_lastResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		        
		        if ($this->_enableRawLogs) { $this->_lastRequestRaw = curl_getinfo($ch, CURLINFO_HEADER_OUT).$postDataString; }
		        
		        if ($responseBody !== false)
		        {
		        	$this->_lastResponseMessage = $responseBody;
		        }
		        else
		        {
		        	$this->_lastResponseMessage = curl_error($ch);
		        }
		        
				curl_close($ch);
			}
        }
        
        
		private function _request_using_fsockopen($requestHeaders, $postDataString)
		{
			$url          = $this->_request_getProtocolStringPartForReason('fsockopen_initialCall').$this->_host;
			$socket       = null;
			$rawRequest   = null;
			$responseBody = false;
	        
	        //Build the request string
	        {
	        	//Even if PUT, we have to write POST here, but will use the "X-HTTP-Method-Override" to write PUT. If we don't do that, "Connection: keep-alive" will be ignored...
	        	$methodString = ($this->_method===bXHTTPRequest::METHOD_GET ? bXHTTPRequest::METHOD_GET : bXHTTPRequest::METHOD_POST);
	        	
				$rawRequest = "$methodString $this->_path HTTP/1.1\r\n"; //Makes something like "POST /bob.php HTTP/1.1";
	            if (count($requestHeaders) > 0)
	            {
	            	$rawRequest .= implode("\r\n", $requestHeaders)."\r\n";
	            }
	            
		        if ($this->isMethodPostable())
		        {
		        	$rawRequest .= "Content-Length: ".strlen($postDataString)."\r\n\r\n"
		                        .  $postDataString;
				}
				else
	        	{
	        		$rawRequest .= "\r\n"; //Even in GET, always add an extra CRLF, otherwise fread will spin forever, ex https://clients.keybook.com/webscripts/keybook_eggsquis.php?pin=siuqsgge&type=cmm&dataname=web_midi_Sherbrooke_Ouest
	        	}
				
	        	if ($this->_enableRawLogs) { $this->_lastRequestRaw = $rawRequest; }
			}
			
			//Get the response
	        if ($socket=@fsockopen($url,$this->_port,$this->_lastResponseCode,$this->_lastResponseMessage,$this->_secsTimeOut))
	        {
	            /*
	            NOTE FOR TLS1.2:
	            	If using PHP <5.6 or so, we can't specify things like such, so we may have to use CURL instead:
	            		stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT); //Constant would be undefined
	            */
        		
	            fwrite($socket, $rawRequest);
	            
	            $this->_request_using_fsockopen_readResponseHeaders($socket); //Required to get response byte length, otherwise doing while(!EOF) in SSL will wait forever on last byte...
	            
	            $this->_lastResponseMessage = bXHTTPRequest::_request_using_fsockopen_readResponseBody($socket, $this->_lastResponseHeaders);
	            
	            fclose($socket);
	            $socket = null;
	        }
		}
	        private function _request_using_fsockopen_readResponseHeaders($socket)
	        {
	            $rawResponseHeaders = '';
	            $read               = 0;
	            
	            while (true)
	            {
	                $rawResponseHeaders .= fread($socket, 1);
	                $read    += 1;
	                
	                if ($read >= 4 && $rawResponseHeaders[$read - 1] == "\n" && substr($rawResponseHeaders, -4) == "\r\n\r\n") { break; }
	            }
	            
	            //Parse
	            {
		            $lines    = explode("\r\n", $rawResponseHeaders);
		            $response = array_shift($lines);
		            list($proto, $this->_lastResponseCode, $this->_lastResponseMessage) = explode(' ', $response, 3);
		            
		            foreach ($lines as $currentHeader)
		            {
		                $this->_request_using_x_parseResponseHeader($currentHeader);
		            }
		        }
	        }
	        private static function _request_using_fsockopen_readResponseBody($socket, $responseHeaders)
	        {
	            $responseIsChunked = (isset($responseHeaders['transfer-encoding']) && stripos($responseHeaders['transfer-encoding'], 'chunked') !== false);
	            $contentLength     = (isset($responseHeaders['content-length']))                                                                  ? $responseHeaders['content-length'] : -1;
	            $isGzip            = (isset($responseHeaders['content-encoding'])  && $responseHeaders['content-encoding'] == 'gzip')             ? true : false;
	            $close             = (isset($responseHeaders['connection'])        && stripos($responseHeaders['connection'], 'close') !== false) ? true : false;
	            
	            $body = '';
	            
	            if ($contentLength >= 0)
	            {
	                $read = 0;
	                do
	                {
	                    $buf   = @fread($socket, $contentLength - $read);
	                    $read += strlen($buf);
	                    $body .= $buf;
	                } while ($read < $contentLength);
	            }
	            else if ($responseIsChunked)
	            {
	                $body = bXHTTPRequest::_request_using_fsockopen_readResponseBody_readChunked($socket);
	            }
	            else if ($close)
	            {
	                while (!feof($socket))
	                {
	                    $body .= fgets($socket, 1024);
	                }
	            }
	            
	            if ($isGzip)
	            {
	                $body = gzinflate(substr($body, 10));
	            }
	            
	            return $body;
	        }
	            private static function _request_using_fsockopen_readResponseBody_readChunked($socket)
	            {
	                $body = '';
	                
	                while (true)
	                {
	                    $data = '';
	                    
	                    do
	                    {
	                        $data .= fread($socket, 1);
	                    } while (strpos($data, "\r\n") === false);
	                    
	                    if (strpos($data, ' ') !== false)
	                    {
	                        list($chunksize, $chunkext) = explode(' ', $data, 2);
	                    }
	                    else
	                    {
	                        $chunksize = $data;
	                        $chunkext  = '';
	                    }
	                    
	                    $chunksize = (int)base_convert($chunksize, 16, 10);
	                    
	                    if ($chunksize === 0)
	                    {
	                        fread($socket, 2); // read trailing "\r\n"
	                        return $body;
	                    }
	                    else
	                    {
	                        $data    = '';
	                        $datalen = 0;
	                        while ($datalen < $chunksize + 2)
	                        {
	                            $data .= fread($socket, $chunksize - $datalen + 2);
	                            $datalen = strlen($data);
	                        }
	                        
	                        $body .= substr($data, 0, -2); // -2 to remove the "\r\n" before the next chunk
	                    }
	                }
	            }
};

?>