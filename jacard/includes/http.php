<?php
/**
 * @package MC
 * @subpackage MCHTTP
 * HTTP Request API
 */ 
class MCHTTP{
    var $_urls;

    var $_headers;
    var $_cookie;
    var $_cookies;
    var $_post_data;
    var $_redirection;
    var $_user_agent='';
    var $_response;
    var $_response_headers;
    var $_response_cookies;
    var $_content;
    var $_logs;
    var $_request_id;
    var $_code;
    var $last_url;

    function __construct(){
        $this->_headers=array();
        $this->_cookies=array();
        $this->_cookie='';
        $this->_post_data='';
        $this->_redirection=5;
        $this->_content='';
        $this->_response=array();
        $this->_response_headers=array();
        $this->_response_cookies=array();
        $this->_request_id;
        $this->_logs=array();
        $this->_code=0;
        $this->last_url='';
        
    }
    function request($url,$post='',$cookie='auto'){
        $this->_request_id++;
        $this->set_post_data($post);
        $this->last_url=$url;
        $this->_urls[]=$url;
        if($cookie=='auto'){
            $this->set_cookie_from_response();
        }elseif(!empty($cookie)){
            $this->set_cookie($cookie);
        }
        $this->build_cookie();
        
        $handle = curl_init();
        
        curl_setopt( $handle, CURLOPT_URL, $url);
		curl_setopt( $handle, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $handle, CURLOPT_USERAGENT, $this->_user_agent );
		curl_setopt( $handle, CURLOPT_MAXREDIRS, $this->_redirection );
        if($this->_post_data){
            curl_setopt( $handle, CURLOPT_POST, true );
            curl_setopt( $handle, CURLOPT_POSTFIELDS, $this->_post_data);
        }
        curl_setopt( $handle, CURLOPT_HEADER, true );
        if ( !ini_get('safe_mode') && !ini_get('open_basedir') ){
            curl_setopt ($handle, CURLOPT_COOKIEFILE,DIR  . 'includes/cookie.txt');
            curl_setopt($handle,CURLOPT_COOKIEJAR, DIR  . 'includes/cookie.txt');
            curl_setopt( $handle, CURLOPT_FOLLOWLOCATION, true );
        }
			
        if($this->_headers){
            curl_setopt( $handle, CURLOPT_HTTPHEADER, $this->_headers );
        }
        if($this->_cookie){
            //echo $this->_cookie;
            curl_setopt ($handle, CURLOPT_COOKIE, $this->_cookie);
        }
        
        $theResponse = curl_exec( $handle );

		if ( !empty($theResponse) ) {
			$headerLength = curl_getinfo($handle, CURLINFO_HEADER_SIZE);
			$theHeaders = trim( substr($theResponse, 0, $headerLength) );
			if ( strlen($theResponse) > $headerLength )
				$theBody = substr( $theResponse, $headerLength );
			else
				$theBody = '';
			if ( false !== strpos($theHeaders, "\r\n\r\n") ) {
				$headerParts = explode("\r\n\r\n", $theHeaders);
				$theHeaders = $headerParts[ count($headerParts) -1 ];
			}
			$theHeaders = $this->processHeaders($theHeaders);
		} else {
			$theHeaders = array( 'headers' => array(), 'cookies' => array() );
			$theBody = '';
		}
        $response = array();
		$this->_code=$response['code'] = curl_getinfo( $handle, CURLINFO_HTTP_CODE );
		$response['message'] = get_status_header_desc($response['code']);
        curl_close( $handle );
        
        // See #11305 - When running under safe mode, redirection is disabled above. Handle it manually.
		
        $this->_content=$theBody;
        if($theHeaders['headers']['content-encoding']=='gzip'){
            $this->_content=gzdecode($this->_content);
        }
        if($theHeaders['headers']['content-encoding']=='deflate'){
            $this->_content=gzinflate($this->_content);
        }
        $this->_response_headers=$theHeaders['headers'];
        $this->_response_cookies=$theHeaders['cookies'];
        $this->_response=$response;
        if ( !empty($theHeaders['headers']['location']) && (ini_get('safe_mode') || ini_get('open_basedir')) ) {
			if ( $this->_redirection-- > 0 ) {
				return $this->request($theHeaders['headers']['location'], $post,$cookie);
			} else {
			     return false;
				//die('Too many redirects.');
			}
		}
        return array('headers' => $theHeaders['headers'], 'body' => $theBody, 'response' => $response, 'cookies' => $theHeaders['cookies']);

    }
    public function isSuccessful()
    {
        $restype = floor($this->_code / 100);
        if ($restype == 2 || $restype == 1) { // Shouldn't 3xx count as success as well ???
            return true;
        }

        return false;
    }
    function visit($url,$post='',$cookie=''){
        $this->request($url,$post,$cookie);
        return $this->_content;
    }
    function read($url){
        return $this->visit($url);
    }
    function browse($url,$post=''){
        $this->request($url,$post);
        return $this->_content;
    }
    function content(){
        return $this->_content;
    }
    function history(){
        return $this->_logs;
    }
    function set_headers($headers){
        $r=mc_parse_args($headers);
        if ( isset($r['User-Agent']) ) {
			$this->_user_agent=$r['User-Agent'];
			unset($r['User-Agent']);
		} else if ( isset($r['user-agent']) ) {
			$this->_user_agent = $r['user-agent'];
			unset($r['user-agent']);
		}
        $headers = array();
        if ( !empty( $r ) ) {
			// cURL expects full header strings in each element
			foreach ( $r as $name => $value ) {
				$headers[] = "{$name}: $value";
			}
		}
        $this->_headers=$headers;
    }
    
    function get_response_code(){
        return $this->_response['code'];
    }
    function get_headers(){
        return $this->_headers;
    }
    
    function get_response_headers(){
        return $this->_response_headers;
    }
    function get_cookie(){
        return $this->_cookie;
    }
    function get_response_cookies(){
        return $this->_response_cookies;
    }
    function set_cookie($args){
        $r=mc_parse_args($args);
        if(!empty($r)){
            foreach($r as $k => $v){
                $this->_cookies[$k]=$v;
            }
        }
    }
    function build_cookie(){
        if(!empty($this->_cookies)){
            $cookie='';
            foreach($this->_cookies as $k=>$v){
                $cookie .= $k. '=' .urlencode($v).'; ';
            }
            $cookie = substr( $cookie, 0, -2 );
            $this->_cookie=$cookie;
        }
    }
    function set_cookie_from_response(){
        if($previous_cookies=$this->get_response_cookies()){
            foreach($previous_cookies as $cookie){
                if(!$cookie['expires']||$cookie['expires']>time()){
                    $this->set_cookie(array($cookie['name'] => $cookie['value']));
                }
            }
            return $this->_cookie;
        }
    }
    function set_post_data($data){
        $this->_post_data=$data;
    }
    function processHeaders($headers){
        // split headers, one per array element
		if ( is_string($headers) ) {
			// tolerate line terminator: CRLF = LF (RFC 2616 19.3)
			$headers = str_replace("\r\n", "\n", $headers);
			// unfold folded header fields. LWS = [CRLF] 1*( SP | HT ) <US-ASCII SP, space (32)>, <US-ASCII HT, horizontal-tab (9)> (RFC 2616 2.2)
			$headers = preg_replace('/\n[ \t]/', ' ', $headers);
			// create the headers array
			$headers = explode("\n", $headers);
		}

		$response = array('code' => 0, 'message' => '');

		// If a redirection has taken place, The headers for each page request may have been passed.
		// In this case, determine the final HTTP header and parse from there.
		for ( $i = count($headers)-1; $i >= 0; $i-- ) {
			if ( !empty($headers[$i]) && false === strpos($headers[$i], ':') ) {
				$headers = array_splice($headers, $i);
				break;
			}
		}

		$cookies = array();
		$newheaders = array();
		foreach ( (array) $headers as $tempheader ) {
			if ( empty($tempheader) )
				continue;

			if ( false === strpos($tempheader, ':') ) {
				list( , $response['code'], $response['message']) = explode(' ', $tempheader, 3);
				continue;
			}

			list($key, $value) = explode(':', $tempheader, 2);

			if ( !empty( $value ) ) {
				$key = strtolower( $key );
				if ( isset( $newheaders[$key] ) ) {
					if ( !is_array($newheaders[$key]) )
						$newheaders[$key] = array($newheaders[$key]);
					$newheaders[$key][] = trim( $value );
				} else {
					$newheaders[$key] = trim( $value );
				}
				if ( 'set-cookie' == $key ){
					$cookies[] = $this->parse_cookie( $value );
                }
			}
		}
        return array('response' => $response, 'headers' => $newheaders, 'cookies' => $cookies);
    }
    function parse_cookie($data){
        $r=array();
        if ( is_string( $data ) ) {
			// Assume it's a header string direct from a previous request
			$pairs = explode( ';', $data );

			// Special handling for first pair; name=value. Also be careful of "=" in value
			$name  = trim( substr( $pairs[0], 0, strpos( $pairs[0], '=' ) ) );
			$value = substr( $pairs[0], strpos( $pairs[0], '=' ) + 1 );
			$r['name']  = $name;
			$r['value'] = urldecode( $value );
			array_shift( $pairs ); //Removes name=value from items.

			// Set everything else as a property
			foreach ( $pairs as $pair ) {
				$pair = rtrim($pair);
				if ( empty($pair) ) //Handles the cookie ending in ; which results in a empty final pair
					continue;

				list( $key, $val ) = strpos( $pair, '=' ) ? explode( '=', $pair ) : array( $pair, '' );
				$key = strtolower( trim( $key ) );
				if ( 'expires' == $key )
					$val = strtotime( $val );
				$r[$key] = $val;
			}
		}
        return $r;
    }
    function reset(){
        $this->_headers=array();
        $this->_cookies=array();
        $this->_cookie='';
        $this->_post_data='';
        $this->_redirection=5;
        $this->_content='';
        $this->_response=array();
        $this->_response_headers=array();
        $this->_response_cookies=array();
        $this->_request_id;
        $this->_logs=array();
        $this->_code=0;
        $this->last_url='';
        return $this;
    }
}