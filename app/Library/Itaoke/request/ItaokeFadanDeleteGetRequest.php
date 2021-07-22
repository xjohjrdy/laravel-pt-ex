<?php
/**
 * TOP API: taobao.tbk.sc.order.get request
 * 
 * @author auto create
 * @since 1.0, 2018.07.25
 */
class ItaokeFadanDeleteGetRequest
{
	/** 
	 * 需返回的字段列表
	 **/
	private $fields;
	
	/** 
	 * 订单查询类型，创建时间“create_time”，或结算时间“settle_time”
	 **/
	private $orderQueryType;
	
	/** 
	 * 第几页，默认1，1~100
	 **/
	private $pageNo;
	
	/** 
	 * 页大小，默认20，1~100
	 **/
	private $pageSize;
	
	/** 
	 * 订单查询时间范围,单位:秒,最小60,最大600,默认60
	 **/
	private $span;
	
	/** 
	 * 订单查询开始时间
	 **/
	private $startTime;
	
	
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
		return "itaoke.fadan.delete.get";
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
