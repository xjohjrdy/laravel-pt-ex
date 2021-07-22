<?php
/**
 * TOP API: taobao.tbk.coupon.convert request
 * 
 * @author auto create
 * @since 1.0, 2019.07.04
 */
class TbkCouponConvertRequest
{
	/** 
	 * 推广位id，mm_xx_xx_xx pid三段式中的第三段
	 **/
	private $adzoneId;
	
	/** 
	 * 淘客商品id
	 **/
	private $itemId;
	
	/** 
	 * 营销计划链接中的me参数
	 **/
	private $me;
	
	/** 
	 * 1：PC，2：无线，默认：１
	 **/
	private $platform;
	
	/** 
	 * 淘宝客推广链接(测试中请勿使用)
	 **/
	private $url;
	
	/** 
	 * 物料块id(测试中请勿使用)
	 **/
	private $xId;
	
	private $apiParas = array();
	
	public function setAdzoneId($adzoneId)
	{
		$this->adzoneId = $adzoneId;
		$this->apiParas["adzone_id"] = $adzoneId;
	}

	public function getAdzoneId()
	{
		return $this->adzoneId;
	}

	public function setItemId($itemId)
	{
		$this->itemId = $itemId;
		$this->apiParas["item_id"] = $itemId;
	}

	public function getItemId()
	{
		return $this->itemId;
	}

	public function setMe($me)
	{
		$this->me = $me;
		$this->apiParas["me"] = $me;
	}

	public function getMe()
	{
		return $this->me;
	}

	public function setPlatform($platform)
	{
		$this->platform = $platform;
		$this->apiParas["platform"] = $platform;
	}

	public function getPlatform()
	{
		return $this->platform;
	}

	public function setUrl($url)
	{
		$this->url = $url;
		$this->apiParas["url"] = $url;
	}

	public function getUrl()
	{
		return $this->url;
	}

	public function setxId($xId)
	{
		$this->xId = $xId;
		$this->apiParas["x_id"] = $xId;
	}

	public function getxId()
	{
		return $this->xId;
	}

	public function getApiMethodName()
	{
		return "taobao.tbk.coupon.convert";
	}
	
	public function getApiParas()
	{
		return $this->apiParas;
	}
	
	public function check()
	{
		
		RequestCheckUtil::checkNotNull($this->adzoneId,"adzoneId");
	}
	
	public function putOtherTextParam($key, $value) {
		$this->apiParas[$key] = $value;
		$this->$key = $value;
	}
}
