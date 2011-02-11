<?php

/**
 * @license GNU GPL v3, see LICENSE for more details
 */
abstract class MAL_Ebay_Service
{
	private $_endpoint;
	private $_api;
	
	protected $_args;
	
	public function __construct()
	{
		$this->_args = array();
	}
	
	protected function AddAPI($api_name)
	{
		if(!is_array($this->_api))
		{
			$this->_api = array();
		}
		
		$this->_api[$api_name] = $api_name;
		return $this;
	}
	
	public function SetEndpoint($type, $endpoint)
	{
		$this->_endpoint[$type] = $endpoint;
		return $this;
	}
	
	public function GetEndpoint($type)
	{
		return $this->_endpoint[$type];
	}
	
	public function GetRequestXML($api_name)
	{
		// Call XML function
		$fn = 'GetXML_' . $this->_api[$api_name];
		return '<?xml version="1.0" encoding="utf-8"?>' . "\n" . $this->{$fn}($this->_args);
	}
	
	public function GetArgument($name, $default_value = null)
	{
		return isset($this->_args[$name]) ? $this->_args[$name] : $default_value;
	}
	
	public function AddArgument($name, $value)
	{
		return $this->SetArgument($name, $value);
	}
	
	public function SetArgument($name, $value)
	{
		$this->_args[$name] = $value;
		return $this;
	}
	
	public function RemoveArgument($name)
	{
		unset($this->_args[$name]);		
	}
}
