<?php

/**
 * @author bing
 * Class ItaokeRobotCreateGetRequest
 */
class ItaokeRobotCreateGetRequest
{
    /**
     * 需返回的字段列表
     **/
    private $fields;

    private $apiParas = array();


    public function getApiMethodName()
    {
        return "itaoke.robot.create.get";
    }

    public function setApiParas($apiParas)
    {
        return $this->apiParas = $apiParas;
    }

    public function getApiParas()
    {
        return $this->apiParas;
    }

    public function check()
    {

    }

    public function putOtherTextParam($key, $value)
    {
        $this->apiParas[$key] = $value;
        $this->$key = $value;
    }
}
