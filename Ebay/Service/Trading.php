<?php

/**
 * @license GNU GPL v3, see LICENSE for more details
 */
class MAL_Ebay_Service_Trading extends MAL_Ebay_Service
{
	const ARG_KEYWORDS = "__arg_ebay_trading_keywords";
	const ARG_ENTRIES_PER_PAGE = "__arg_ebay_trading_EntriesPerPage";
	const ARG_CATEGORY_ID = "__arg_ebay_trading_categoryId";
	const ARG_SELLER = "__arg_ebay_trading_seller";
	const ARG_AUTH_TOKEN = "__arg_ebay_trading_eBayAuthToken";
	const ARG_START_TIME_RANGE = "__arg_ebay_trading__maps_to_startTimeFrom_and_startTimeTo___StartTimeRange"; // MySQL format YYYY-MM-DD HH:MM:SS :)
	
	public function __construct($token)
	{	
		parent::__construct();
		
		if(!isset($token) || !$token)
		{
			throw new Exception("Token must be specified when using eBay Trading API");
		}
		
		$this->SetEndpoint(MAL_Ebay_Engine::USING_LIVE, "https://api.ebay.com/ws/api.dll")
			->SetEndpoint(MAL_Ebay_Engine::USING_SANDBOX, "https://api.sandbox.ebay.com/ws/api.dll")
			->AddAPI('GetSellerList')
			->AddArgument(self::ARG_AUTH_TOKEN, $token);
	}
	
	protected function GetXML_GetSellerList()
	{
		$entries_per_page = $this->GetArgument(self::ARG_ENTRIES_PER_PAGE, 10);
		$seller = $this->GetArgument(self::ARG_SELLER);
		$auth_token = $this->GetArgument(self::ARG_AUTH_TOKEN);
		$category_id = $this->GetArgument(self::ARG_CATEGORY_ID);
		
		$start_time_range = $this->GetArgument(self::ARG_START_TIME_RANGE);
		if(!is_array($start_time_range))
		{
			$start_time_range = array();
			$start_time_range["FROM"] = date("Y-m-d H:i:s", strtotime("-119 days"));
			$start_time_range["TO"] = date("Y-m-d H:i:s");
		}
		
		$ebay_date_format = "Y-m-d\TH:i:s\.000\Z";
		$start_time_range["FROM_EBAY"] = date($ebay_date_format, strtotime($start_time_range["FROM"]));
		$start_time_range["TO_EBAY"] = date($ebay_date_format, strtotime($start_time_range["TO"]));
		
		$xml  = "<GetSellerListRequest xmlns=\"urn:ebay:apis:eBLBaseComponents\">";
		if($category_id)
		{
			$xml .= "  <CategoryID>{$category_id}</CategoryID>";
		}
		$xml .= "  <UserID>{$seller}</UserID>";
		$xml .= "  <StartTimeFrom>{$start_time_range["FROM_EBAY"]}</StartTimeFrom>";
		$xml .= "  <StartTimeTo>{$start_time_range["TO_EBAY"]}</StartTimeTo>";
		$xml .= "  <GranularityLevel>Fine</GranularityLevel>";
		$xml .= "  <OutputSelector>ItemArray.Item.ItemId</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.ListingDetails.EndTime</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.ListingDetails.ViewItemURL</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.SellingStatus.CurrentPrice</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.SubTitle</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.Title</OutputSelector>";
		$xml .= "  <OutputSelector>ItemArray.Item.PictureDetails.GalleryURL</OutputSelector>";
		$xml .= "  <OutputSelector>PaginationResult</OutputSelector>";
		$xml .= "  <RequesterCredentials>";
		$xml .= "    <eBayAuthToken>{$auth_token}</eBayAuthToken>";
		$xml .= "  </RequesterCredentials>";
		$xml .= "  <Pagination>";
		$xml .= "    <EntriesPerPage>{$entries_per_page}</EntriesPerPage>";
		$xml .= "  </Pagination>";
		$xml .= "</GetSellerListRequest>";
		
		return $xml;
	}
}
