<?php

/**
 * Central point for running all eBay service requests
 * 
 * Don't forget to add this line to application.ini:
 *   autoloaderNamespaces[] = "Ebay_"
 * 
 * @author hello@jamestitcumb.com
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
	private $_base_ebay_url;
	private $_app_id;
	private $_dev_id;
	private $_cert_id;
	private $_last_headers;
	private $_last_endpoint;
	private $_last_xml;
	private $_last_api_name;
	private $_last_service;
	private $_using_server;
	
	const USING_LIVE = "live";
	const USING_SANDBOX = "sandbox";
	
	public function __construct($using_server = self::USING_SANDBOX)
	{
		$this->_using_server = $using_server;
	}
	
	public function SetAppId($type, $app_id)
	{
		$this->_app_id[$type] = $app_id;
		return $this;
	}
	
	public function SetDevId($type, $dev_id)
	{
		$this->_dev_id[$type] = $dev_id;
		return $this;
	}
	
	public function SetCertId($type, $cert_id)
	{
		$this->_cert_id[$type] = $cert_id;
		return $this;
	}
	
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
	
	private function SetAPIName($api_name)
	{
		$this->_last_api_name = $api_name;
		return $this;
	}
	
	private function SetService(MAL_Ebay_Service $svc)
	{
		$this->_last_service = $svc;
		return $this;
	}
	
	private function GenerateEndpoint()
	{
		$this->_last_endpoint = $this->_last_service->GetEndpoint($this->_using_server);
		return $this;
	}
	
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
	
	private function GenerateXML()
	{
		$this->_last_xml = $this->_last_service->GetRequestXML($this->_last_api_name);
		return $this;
	}
	
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
	
	private function OutputXML($xml)
	{
		// Output Reply to Browser 
		header("Content-type: application/xml"); 
		echo $xml;
		die();
	}
}