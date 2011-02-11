<?php

class MAL_Ebay_Service_Finding extends MAL_Ebay_Service
{
	const ARG_KEYWORDS = "__arg_ebay_finding_keywords";
	const ARG_ENTRIES_PER_PAGE = "__arg_ebay_finding_entriesPerPage";
	const ARG_CATEGORY_ID = "__arg_ebay_finding_categoryId";
	const ARG_SELLER = "__arg_ebay_finding_seller";
	
	public function __construct()
	{
		parent::__construct();
		
		$this->SetEndpoint(MAL_Ebay_Engine::USING_LIVE, "http://svcs.ebay.com/services/search/FindingService/v1")
			->SetEndpoint(MAL_Ebay_Engine::USING_SANDBOX, "http://svcs.sandbox.ebay.com/services/search/FindingService/v1")
			->AddAPI('findItemsByKeywords')
			->AddAPI('findItemsAdvanced');
	}
	
	protected function GetXML_findItemsByKeywords()
	{
		$keywords = $this->GetArgument(self::ARG_KEYWORDS);
		$entries_per_page = $this->GetArgument(self::ARG_ENTRIES_PER_PAGE, 10);
		
		$xml  = "<findItemsByKeywordsRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">";
		$xml .= "  <keywords>{$keywords}</keywords>";
		$xml .= "  <paginationInput>";
		$xml .= "    <entriesPerPage>{$entries_per_page}</entriesPerPage>";
		$xml .= "  </paginationInput>";
		$xml .= "</findItemsByKeywordsRequest>";
		
		return $xml;
	}
	
	protected function GetXML_findItemsAdvanced()
	{
		$keywords = $this->GetArgument(self::ARG_KEYWORDS);
		$categoryId = $this->GetArgument(self::ARG_CATEGORY_ID);
		$entries_per_page = $this->GetArgument(self::ARG_ENTRIES_PER_PAGE, 10);
		$seller = $this->GetArgument(self::ARG_SELLER);
		
		$xml  = "<findItemsAdvancedRequest xmlns=\"http://www.ebay.com/marketplace/search/v1/services\">";
		
		if($keywords)
		{
			$xml .= "  <keywords>{$keywords}</keywords>";
		}
		
		if($categoryId)
		{
			$xml .= "  <categoryId>{$categoryId}</categoryId>";
		}
		
		if($seller)
		{
			$xml .= "  <ItemFilter>";
			$xml .= "    <name>Seller</name>";
			$xml .= "    <value>{$seller}</value>";
			$xml .= "  </ItemFilter>";
		}
		
		$xml .= "  <paginationInput>";
		$xml .= "    <entriesPerPage>{$entries_per_page}</entriesPerPage>";
		$xml .= "  </paginationInput>";
		$xml .= "</findItemsAdvancedRequest>";
		
		return $xml;
	}
}