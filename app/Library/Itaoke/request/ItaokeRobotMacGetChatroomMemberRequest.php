<?php
/**
 * TOP API: taobao.tbk.sc.order.get request
 * 获取群好友
 * @author auto create
 * @since 1.0, 2018.07.25
 */
class ItaokeRobotMacGetChatroomMemberRequest
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

	public function setJson($data)
	{
		$this->data= $data;
		$this->apiParas["data"] = $data;
	}

	public function getJson()
	{
		return $this->data;
	}


	public function getApiMethodName()
	{
		return "itaoke.robot.macget.chatroommember";
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