<?php

define('UAS_APIDS_URL', UAS_BASE_URL . '/apids/');
define('UAS_APIDS_FEEDBACK_URL', UAS_BASE_URL . '/apids/feedback/');

class UrbanAirShipAndroidDeviceList implements Iterator, Countable
{
    private $_airship = null;
    private $_page = null;
    private $_position = 0;

    public function __construct($airship)
    {
        $this->_airship = $airship;
        
        $this->_load_page(UAS_APIDS_URL);
        
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
		    $this->_page->apids = array_merge($this->_page->apids, $result->apids);
		    $this->_page->next_page = $result->next_page;
		}
    }

    // Countable interface
    public function count()
    {
        return count($this->_page->apids);
    }

    // Iterator interface
    function rewind() 
    {
        $this->_position = 0;
    }

    function current()
    {
        return $this->_page->apids[$this->_position];
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
        if (!isset($this->_page->apids[$this->_position])) 
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

	
class UrbanAirShipAndroid
{
	private $_airship = null;

    function __construct($airship)
    {
        $this->_airship = $airship;
    }
    
    public function registerAPID($token, $alias = null, $tags = null)
	{
		//$url = UAS_APIDS_URL . '2df448b0-df2b-4212-bc61-ca818334ea88';
		
		$url = "https://device-api.urbanairship.com/api/apids/26445d20-83ba-42aa-82d6-fbbdec713f9b";
		
		$payload = array();
                        
        if ($alias != null)
        {
             $payload['alias'] = $alias;
        }
        /*
        if ($tags != null) 
        {
            $payload['tags'] = $tags;
        }
        */
        $payload['gcm_registration_id'] = $token;
        
        $restResponse = $this->_airship->makeCall($url, 'PUT', $payload);
                
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
                
        print_r($restResponse);
                
        return true;
	}
	
	public function getAPIDInfo($token)
	{
		$url = UAS_APIDS_URL . $token;
		
		$restResponse = $this->_airship->makeCall($url, 'GET');
        
        if ($restResponse['info']['http_code'] != 200) 
        {
            throw new AirshipFailure($restResponse['content'], $restResponse['info']['http_code']);
        }
        
		return json_decode($restResponse['content']);
	}
	
	public function getAPIDs()
	{
		return new UrbanAirShipAndroidDeviceList($this->_airship);
	}
}
	
?>