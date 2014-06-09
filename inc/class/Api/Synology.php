<?php

namespace Api;

/**
 * @method Synology getInstance() /!\ Static
 * @link http://ukdl.synology.com/ftp/other/Synology_Download_Station_Official_API_V3.pdf Synology DownloadStation API details
 */
class Synology extends Api
{
    use \Singleton;
    
    const SESSION_NAME = "SynoApi";
    
    /**
     * Synology API conf
     * @type array
     */
    private static $_apis = [
        "login" => [
            "type" => "GET",
            "url" => "auth.cgi",
            "api" => "SYNO.API.Auth",
            "version" => 3,
            "method" => "login",
            "parameters" => ["session" => self::SESSION_NAME, "format" => "sid"]
        ],
        "logout" => [
            "type" => "GET",
            "url" => "auth.cgi",
            "api" => "SYNO.API.Auth",
            "version" => 3,
            "method" => "logout",
            "parameters" => ["session" => self::SESSION_NAME]
        ],
        "getDownloads" => [
            "type" => "GET",
            "url" => "DownloadStation/task.cgi",
            "api" => "SYNO.DownloadStation.Task",
            "version" => 3,
            "method" => "list"
        ],
        "addDownload" => [
            "type" => "POST",
            "url" => "DownloadStation/task.cgi",
            "api" => "SYNO.DownloadStation.Task",
            "version" => 3,
            "method" => "create"
        ]
    ];
    
    /**
     * Session ID
     * @type string
     */
    private $_sid = null;
    
    protected function _getConfName()
    {
        return "synology";
    }

    protected function _connect()
    {
        $this->_sid = $this->_call("login", [
            "account" => $this->_conf->user,
            "passwd" => $this->_conf->password
        ])->sid;
    }
    
    protected function _disconnect()
    {
        $this->_sid = null;
        $this->_call("logout");
    }
    
    /**
     * @param string $apiName
     * @param array $parameters
     * @return array
     */
    private function _prepareQuery($apiName, $parameters)
    {
        $apiDetails = self::$_apis[$apiName];
        $url = $this->_conf->host . '/webapi/' . $apiDetails["url"];
        if($apiDetails["parameters"]) {
            $parameters = array_merge($apiDetails["parameters"], $parameters);
        }
        $parameters["method"] = $apiDetails["method"];
        $parameters["api"] = $apiDetails["api"];
        $parameters["version"] = $apiDetails["version"];
        if(!$parameters["_sid"] && $this->_sid) {
            $parameters["_sid"] = $this->_sid;
        }
        
        return [$url, $apiDetails["type"], $parameters];
    }

    /**
     * @param string $apiName
     * @param array $parameters
     * @return stdClass
     * @throws Exception
     */
    private function _call($apiName, $parameters = [])
    {
        list($url, $type, $params) = $this->_prepareQuery($apiName, $parameters);
        $content = $this->_apiCall($url, $type, $params);
        if (!$content) {
            throw new \Exception("Synology API call failed, aborting.");
        }
        
        $ret = json_decode($content);
        if (!$ret) {
            throw new \Exception("Wrong Synology API call \"$url\", aborting.");
        }
        if(!$ret->success) {
            throw new \Exception("Synology API call \"$url\" failed, error code ".$ret->error->code.", aborting.");
        }
        return $ret->data;
    }
    
    /**
     * Get the list of downloads
     * @return array
     */
    public function getInDownload()
    {
        $tasks = $this->_call("getDownloads")->tasks;
        $downs = [];
        foreach($tasks as $t) {
            $downs[] = $t->title;
        }
        return $downs;
    }
    
    /**
     * Add a download to DownloadStation
     * @param string $uri
     */
    public function addDownload($uri)
    {
        $this->_call("addDownload", [
            "uri" => $uri
        ]);
    }
    
    /**
     * Add downloads to DownloadStation
     * @param string[] $uris
     */
    public function addDownloads($uris)
    {
        foreach($uris as $uri) {
            $this->addDownload($uri);
        }
    }

}