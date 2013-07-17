<?php

define('UAS_DEVICE_TOKEN_URL', UAS_BASE_URL . '/device_tokens/');
define('UAS_DEVICE_TOKEN_FEEDBACK_URL', UAS_BASE_URL . '/device_tokens/feedback/');

class UrbanAirShipIOSDeviceList implements Iterator, Countable
{
    private $_airship = null;
    private $_page = null;
    private $_position = 0;

    public function __construct($airship)
    {
        $this->_airship = $airship;
        
        $this->_load_page(UAS_DEVICE_TOKEN_URL);
        
        $this->_position = 0;
    }

    private function _load_page($url) 
    {
		$restResponse = $this->_airship->makeCall($url, 'GET');
		      
		if($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['info'], $restResponse['info']['http_code']);
        }
        
		$result = json_decode($restResponse['content']);
		
		if ($this->_page == null)
		{
		    $this->_page = $result;
		}
		else
		{
		    $this->_page->device_tokens = array_merge($this->_page->device_tokens, $result->device_tokens);
		    $this->_page->next_page = $result->next_page;
		}
    }

    // Countable interface
    public function count()
    {
        return $this->_page->device_tokens_count;
    }

    // Iterator interface
    function rewind() 
    {
        $this->_position = 0;
    }

    function current()
    {
        return $this->_page->device_tokens[$this->_position];
    }

    function key() 
    {
        return $this->_position;
    }

    function next()
    {
        ++$this->_position;
    }

    function valid() 
    {
        if (!isset($this->_page->device_tokens[$this->_position])) 
        {
            $next_page =  isset($this->_page->next_page) ? $this->_page->next_page : null;
            
            if ($next_page == null) 
            {
                return false;
            }
            else
            {
                $this->_load_page($next_page);
                return $this->valid();
            }
        }
        return true;
    }
}

	
class UrbanAirShipIOS
{
	private $_airship = null;

    function __construct($airship)
    {
        $this->_airship = $airship;
    }
    
    public function registerToken($token, $alias = null, $tags = null, $badge = null)
	{
		$url = UAS_DEVICE_TOKEN_URL . $token;
		
		$payload = array();
        
        if ($alias != null)
        {
            $payload['alias'] = $alias;
        }
        
        if ($tags != null) 
        {
            $payload['tags'] = $tags;
        }
        
        if ($badge != null) 
        {
            $payload['badge'] = $badge;
        }
        
        $restResponse = $this->_airship->makeCall($url, 'PUT', $payload);
                
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
        return true;
	}
	
	public function deregisterToken($token)
	{
		$url = UAS_DEVICE_TOKEN_URL . $token;
		
		$restResponse = $this->_airship->makeCall($url, 'DELETE');
        
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
        return true;
	}
	
	public function geDeviceTokenInfo($token)
	{
		$url = UAS_DEVICE_TOKEN_URL . $token;
		
		$restResponse = $this->_airship->makeCall($url, 'GET');
        
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
		return json_decode($restResponse['content']);
	}
	
	public function getDeviceTokens()
	{
		return new UrbanAirShipIOSDeviceList($this->_airship);
	}
	
	public function feedback($since)
	{
		$url = UAS_DEVICE_TOKEN_FEEDBACK_URL . '?' . 'since=' . rawurlencode($since->format('c'));
		
		$restResponse = $this->_airship->makeCall($url, 'GET');
		
		if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
		
		$results = json_decode($restResponse['content']);
				
        foreach($results as $item) 
        {
            $item->marked_inactive_on = new DateTime($item->marked_inactive_on, new DateTimeZone('UTC'));
        }
        
        return $results;
	}
}
	
?>