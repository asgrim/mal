<?php

/**
 * Central point for running all eBay service requests
 * 
 * Don't forget to add this line to application.ini:
 *   autoloaderNamespaces[] = "MAL_"
 * 
 * @author hello@jamestitcumb.com
 * @license GNU GPL v3, see LICENSE for more details
 * 
 * @example
 *   $server = APPLICATION_ENV == "production" ? MAL_Ebay_Engine::USING_LIVE : MAL_Ebay_Engine::USING_SANDBOX;
 *   
 *   $request = new MAL_Ebay_Service_Trading($ebay_user_token);
 *   $request->AddArgument(MAL_Ebay_Service_Trading::ARG_SELLER, "whateverseller")
 *           ->AddArgument(MAL_Ebay_Service_Trading::ARG_ENTRIES_PER_PAGE, 200);
 *           
 *   $ebay = new MAL_Ebay_Engine($server);
 *   $ebay->SetAppId($server, $app_id)
 *        ->SetDevId($server, $dev_id)
 *        ->SetCertId($server, $cert_id);
 *        
 *   $result = $ebay->Execute($request, 'GetSellerList');
 *   $result_xml = simplexml_load_string($result);
 *
 */
class MAL_Ebay_Engine
{
	/**
	 * Unused any more...
	 * @var string
	 */
	private $_base_ebay_url;
	
	/**
	 * Your App ID from eBay
	 * @var string
	 */
	private $_app_id;
	
	/**
	 * Your Dev ID from eBay
	 * @var string
	 */
	private $_dev_id;
	
	/**
	 * Your Cert ID from eBay
	 * @var string
	 */
	private $_cert_id;
	
	/**
	 * The last headers that were sent with the last request
	 * @var array
	 */
	private $_last_headers;
	
	/**
	 * Last request endpoint that was used
	 * @var string
	 */
	private $_last_endpoint;
	
	/**
	 * Last REQUEST XML that was used
	 * @var string
	 */
	private $_last_xml;
	
	/**
	 * Name of the last API used
	 * @var string
	 */
	private $_last_api_name;
	
	/**
	 * The last Service object used
	 * @var MAL_Ebay_Service
	 */
	private $_last_service;
	
	/**
	 * Which server to use - MAL_Ebay_Engine::USING_LIVE or MAL_Ebay_Engine::USING_SANDBOX are acceptable values 
	 * @var string
	 */
	private $_using_server;
	
	const USING_LIVE = "live";
	const USING_SANDBOX = "sandbox";
	
	/**
	 * Create the eBay client engine. Specify the server to use by specifying one of:
	 * 
	 *   MAL_Ebay_Engine::USING_LIVE
	 *   MAL_Ebay_Engine::USING_SANDBOX
	 *   
	 * for the $using_server value
	 *   
	 * @param string $using_server
	 */
	public function __construct($using_server = self::USING_SANDBOX)
	{
		$this->_using_server = $using_server;
	}
	
	/**
	 * Set the eBay App Id
	 * 
	 * @param string $type Server type - one of MAL_Ebay_Engine::USING_LIVE or MAL_Ebay_Engine::USING_SANDBOX
	 * @param string $app_id Your app ID from eBay
	 * @return MAL_Ebay_Engine
	 */
	public function SetAppId($type, $app_id)
	{
		$this->_app_id[$type] = $app_id;
		return $this;
	}
	
	/**
	 * Set the eBay Dev Id
	 * 
	 * @param string $type Server type - one of MAL_Ebay_Engine::USING_LIVE or MAL_Ebay_Engine::USING_SANDBOX
	 * @param string $app_id Your dev ID from eBay
	 * @return MAL_Ebay_Engine
	 */
	public function SetDevId($type, $dev_id)
	{
		$this->_dev_id[$type] = $dev_id;
		return $this;
	}
	
	/**
	 * Set the eBay Cert Id
	 * 
	 * @param string $type Server type - one of MAL_Ebay_Engine::USING_LIVE or MAL_Ebay_Engine::USING_SANDBOX
	 * @param string $app_id Your cert ID from eBay
	 * @return MAL_Ebay_Engine
	 */
	public function SetCertId($type, $cert_id)
	{
		$this->_cert_id[$type] = $cert_id;
		return $this;
	}
	
	/**
	 * Execute a request prepared by the MAL_Ebay_Service object passed as a parameter
	 * 
	 * Returns the XML returned as a string - parse with simplexml or whatever
	 * 
	 * @param MAL_Ebay_Service $svc The prepared service request object
	 * @param string $api_name Name of the API being called within the request object
	 * @param boolean $dump If dump is true, it will directly output the response XML rather than return it
	 * 
	 * @return string
	 */
	public function Execute(MAL_Ebay_Service $svc, $api_name, $dump = false)
	{
		$this->SetAPIName($api_name)
			->SetService($svc)
			->GenerateEndpoint()
			->GenerateHeaders()
			->GenerateXML();
		
		$response = $this->MakeRequest();
		
		if($dump)
		{
			$this->OutputXML($response);
		}
		
		return $response;
	}
	
	/**
	 * Set the API name being used internally
	 * @param string $api_name
	 * @return MAL_Ebay_Engine
	 */
	private function SetAPIName($api_name)
	{
		$this->_last_api_name = $api_name;
		return $this;
	}
	
	/**
	 * Set the service being used internally
	 * @param MAL_Ebay_Service $svc
	 * @return MAL_Ebay_Engine
	 */
	private function SetService(MAL_Ebay_Service $svc)
	{
		$this->_last_service = $svc;
		return $this;
	}
	
	/**
	 * Set the endpoint being used depending on the server being used
	 * @return MAL_Ebay_Engine
	 */
	private function GenerateEndpoint()
	{
		$this->_last_endpoint = $this->_last_service->GetEndpoint($this->_using_server);
		return $this;
	}
	
	/**
	 * Generate a list of request headers
	 * @return MAL_Ebay_Engine
	 */
	private function GenerateHeaders()
	{
		$this->_last_headers = array(
			"X-EBAY-SOA-GLOBAL-ID: EBAY-GB",
			"X-EBAY-SOA-OPERATION-NAME: {$this->_last_api_name}",
			"X-EBAY-SOA-SECURITY-APPNAME: {$this->_app_id[$this->_using_server]}",
			"X-EBAY-API-APP-NAME: {$this->_app_id[$this->_using_server]}",
			"X-EBAY-API-CERT-NAME: {$this->_cert_id[$this->_using_server]}",
			"X-EBAY-API-DEV-NAME: {$this->_dev_id[$this->_using_server]}",
			"X-EBAY-SOA-REQUEST-DATA-FORMAT: XML",
			"X-EBAY-SOA-RESPONSE-DATA-FORMAT: XML",
			"X-EBAY-API-COMPATIBILITY-LEVEL: 705",
			"X-EBAY-API-SITEID: 3",
			"X-EBAY-API-CALL-NAME: {$this->_last_api_name}",
		);
		return $this;
	}
	
	/**
	 * Generate the XML by using the specified API and the service object
	 * @return MAL_Ebay_Engine
	 */
	private function GenerateXML()
	{
		$this->_last_xml = $this->_last_service->GetRequestXML($this->_last_api_name);
		return $this;
	}
	
	/**
	 * Execute the actual XML request to eBay
	 * 
	 * Make sure that the internal values for _last_endpoint, _last_headers and _last_xml are set first...
	 * 
	 * No error checking yet ;)
	 * 
	 * @return string XML response - parse with SimpleXML or whatever
	 */
	private function MakeRequest()
	{
		$ch = curl_init();

		/*
		 * Prepare the cURL options
		 */
		curl_setopt($ch, CURLOPT_URL, $this->_last_endpoint); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->_last_headers); 
		curl_setopt($ch, CURLOPT_POST, true); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_last_xml); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
		/*
		 * Send the request
		 */
		$response = curl_exec ($ch);

		return $response;
	}
	
	/**
	 * Dump the XML with an application/xml header for debugging purposes
	 * NOTE: This does die();
	 * @param string $xml
	 */
	private function OutputXML($xml)
	{
		// Output Reply to Browser 
		header("Content-type: application/xml"); 
		echo $xml;
		die();
	}
}
