<?php

define('UAS_SERVER', 'go.urbanairship.com');
define('UAS_BASE_URL', 'https://go.urbanairship.com/api');
define('UAS_PUSH_URL', UAS_BASE_URL . '/push/');
define('UAS_BROADCAST_URL',  UAS_BASE_URL . '/push/broadcast/');
define('UAS_PUSH_RESPONSE',  UAS_BASE_URL . '/reports/responses/');

require_once(dirname(__FILE__) . '/UrbanAirShipIOS.php');
require_once(dirname(__FILE__) . '/UrbanAirShipAndroid.php');

// Raise when we get a 401 from the server.
class Unauthorized extends Exception {
}

// Raise when we get an error response from the server.
// args are (status code, message).
class AirShipFailure extends Exception {
}

class UrbanAirShip
{
	private $APP_MASTER_SECRET;
	private $APP_KEY;

	function __construct($key, $secret)
	{
		$this->APP_KEY = $key;
		$this->APP_MASTER_SECRET = $secret;
	}
	
	public function makeCall($url, $method, $payload = null)
	{
		if (count($payload) != 0) 
        {
            $body = json_encode($payload);
            $content_type = 'application/json';
        }
        else
        {
            $body = '';
            $content_type = null;
        }
	
		$session = curl_init($url); 
		
		curl_setopt($session, CURLOPT_USERPWD, $this->APP_KEY . ':' . $this->APP_MASTER_SECRET); 
		curl_setopt($session, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($session, CURLOPT_POSTFIELDS, $body); 
		curl_setopt($session, CURLOPT_HEADER, false); 
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		
		if($content_type != null)
		{
			curl_setopt($session, CURLOPT_HTTPHEADER, array('Content-Type:application/json')); 
		}
		
		$result = array();
				
		$result['content'] = curl_exec($session); 
		$result['info'] = curl_getinfo($session);
		
		if($result['info']['http_code'] == 401)
		{
			throw new Unauthorized();
		}
		
		return $result;
	}
	
	public function registerDevice($type, $identifier, $alias = null, $tags = null, $badge = null)
	{
		switch(strtolower($type))
		{
			case 'ios':
				$service = new UrbanAirShipIOS($this);
				return $service->registerToken($identifier, $alias, $tags, $badge);
			break;
				
			case 'android':
				$service = new UrbanAirShipAndroid($this);
				return $service->registerAPID($identifier, $alias, $tags);
			break;
				
			case 'blackberry':
				break;
		}		
	}
	
	public function deregisterDevice($type, $identifier)
	{
		switch(strtolower($type))
		{
			case 'ios':
				$service = new UrbanAirShipIOS($this);
				return $service->deregisterToken($identifier);
			break;
				
			case 'android':
				// no need to do this, happens automatically
			break;
				
			case 'blackberry':
				break;
		}	
	}
	
	public function getDeviceInfo($type, $identifier)
	{
		switch(strtolower($type))
		{
			case 'ios':
				$service = new UrbanAirShipIOS($this);
				return $service->geDeviceTokenInfo($identifier);
			break;
				
			case 'android':
				$service = new UrbanAirShipAndroid($this);
				return $service->getAPIDInfo($identifier);
			break;
				
			case 'blackberry':
				break;
		}	
	}
	
	public function getDeviceList($type)
	{
		switch(strtolower($type))
		{
			case 'ios':
				$service = new UrbanAirShipIOS($this);
				return $service->getDeviceTokens();
			break;
				
			case 'android':
				$service = new UrbanAirShipAndroid($this);
				return $service->getAPIDs();
			break;
				
			case 'blackberry':
				break;
		}	
	}
	
	public function getFeedback($type)
	{
		switch(strtolower($type))
		{
			case 'ios':
				$service = new UrbanAirShipIOS($this);
				return $service->feedback();
			break;
				
			case 'android':
				break;
				
			case 'blackberry':
				break;
		}	
	}
	
	public function push($payload, $deviceTokens = null, $apids = null, $aliases=null, $tags=null)
	{
		$url = UAS_PUSH_URL;
		
		if($deviceTokens != null)
        {
            $payload['device_tokens'] = $deviceTokens;
        }
        
        if($apids != null)
        {
            $payload['apids'] = $apids;
        }
        
        if($aliases != null)
        {
            $payload['aliases'] = $aliases;
        }
        
        if($tags != null) 
        {
            $payload['tags'] = $tags;
        }
                       
        $restResponse = $this->makeCall($url, 'POST', $payload);
        
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
        return json_decode($restResponse['content']);	
	}
	
	public function broadcast($payload, $excludeTokens = null)
	{
		$url = UAS_BROADCAST_URL;
		
		if ($excludeTokens != null)
        {
            $payload['exclude_tokens'] = $excludeTokens;
        }                
                
        $restResponse = $this->makeCall($url, 'POST', $payload);
        
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
		return json_decode($restResponse['content']);		
	}
	
	public function getPushResponses($pushId, $pushType = null)
	{
		$url = UAS_PUSH_RESPONSE . $pushId;
		
		if ($pushType != null)
        {
           $url .= '?push_type=' . $pushType;
        }
		                              
        $restResponse = $this->makeCall($url, 'GET');
                     
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
        return json_decode($restResponse['content']);	
	}
}

?>