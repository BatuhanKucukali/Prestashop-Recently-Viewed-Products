<?php 

  class BlockLastVisitCookie
  {

  	public $cookie_name = 'ps_cookie_products';

  	public $max_limit;

  	public $cookie;

  	public function __construct(Cookie $cookie, $limit)
  	{
		$this->max_limit = $limit;
  		$this->cookie = $cookie;
  	}

  	private function encode($data)
  	{
  		return base64_encode($data);
  	}	

  	private function decode($data)
  	{
  		return base64_decode($data);
  	}

  	private function cookieArray($data)
  	{
  		return explode(',', $data);
  	}

  	private function cookieString($data)
  	{
  		return implode(',', $data);
  	}

  	public function readData($data)
  	{
  		$read_cookie = $this->cookie->__get($data);
  		if(!isset($read_cookie))
  				return false;
  		$read_cookie = $this->decode($read_cookie);
  		return array_filter($this->cookieArray($read_cookie));
  	}

  	public function saveData($data)
  	{
  		$save_data = $this->cookieString(array_unique($data));
  		$save_data = $this->encode($save_data);
  		return $this->cookie->__set($this->cookie_name, $save_data);
  	}

  	public function setData($data)
  	{	
  		$cookie_data = $this->readData($this->cookie_name);
  		
  		if(count($cookie_data) >= $this->max_limit)
  		{
  			array_unshift($cookie_data, $data);
  			$cookie_data = array_slice($cookie_data, 0, $this->max_limit);
  		    $this->saveData($cookie_data);
  		
  		}else
  		{
  		   if(!$cookie_data)
  		   		$cookie_data = array();
  		   	
  		   array_unshift($cookie_data, $data);
  		   $this->saveData($cookie_data);
  		}
  	}

}