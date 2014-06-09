<?php
namespace Api;

abstract class Api
{
    
    /**
     * Apis confs, should only modify this.
     * @var array
     */
    protected $_conf;

    protected function _connect() {}
    
    protected function _disconnect() {}

    protected function __construct()
    {
        $confName = $this->_getConfName();
        if(!$confName) {
            $this->_conf = new \stdClass();
        }
        else {
            $this->_conf = \Config::get($confName);
        }
        $this->_connect();
    }
    
    public function __destruct()
    {
        $this->_disconnect();
    }
    
    /**
     * @param string $url
     * @param string $type
     * @param array $params
     */
    protected function _apiCall($url, $type = "GET", $params = [])
    {
        $paramsCount = count(array_keys($params));
        $params = http_build_query($params);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if($type == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch,CURLOPT_POST, $paramsCount);
            curl_setopt($ch,CURLOPT_POSTFIELDS, $params);
        }
        else if(strlen($params)) {
            $url .= "?" . $params;
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }

    /**
     * @return string
     */
    abstract protected function _getConfName();
}