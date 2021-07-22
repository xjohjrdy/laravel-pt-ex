<?php
/**
 * @since 1.0, 2018.07.25
 */
class ItaokeFadanDetailGetRequest
{
	/** 
	 * 需返回的字段列表
	 **/
	private $fields;
		
	private $apiParas = array();
	
	public function setFields($fields)
	{
		$this->fields = $fields;
		$this->apiParas["fields"] = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}
    
    public function setCloudGroupId($cloud_groupId)
    {
        $this->cloud_groupId = $cloud_groupId;
        $this->apiParas["cloud_groupId"] = $cloud_groupId;
    }
    
    public function getCloudGroupId()
    {
        return $this->cloud_groupId;
    }

    
	public function getApiMethodName()
	{
		return "itaoke.fadan.detail.get";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
        
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
