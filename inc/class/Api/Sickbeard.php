<?php

namespace Api;

/**
 * @method Sickbeard getInstance() /!\ Static
 * @link http://sickbeard.com/api/ Sickbeard API details
 */
class Sickbeard extends Api
{
    use \Singleton;
    
    /**
     *
     * @var string
     */
    private $_apiUrl;
    
    protected function _getConfName()
    {
        return "sickbeard";
    }

    /**
     * Connect to Sickbeard.
     */
    protected function _connect()
    {
        $this->_apiUrl = $this->_conf->host . "/api/" . $this->_conf->apiKey . "/?cmd=";
        // Check if sb is reachable
        $this->_call("sb");
    }

    /**
     * @param string $apiMethod
     * @return stdClass
     * @throws Exception
     */
    private function _call($apiMethod)
    {
        $url = $this->_apiUrl . $apiMethod;
        $content = $this->_apiCall($url);
        if (!$content) {
            throw new \Exception("Sickbeard seams unreachable, aborting.");
        }
        $ret = json_decode($content);
        if (!$ret) {
            throw new \Exception("Wrong sickbeard API call\"$url\", aborting.");
        }
        return $ret->data;
    }

    /**
     * Get the list of missing episodes (in 720p) regexp.
     * @params array $inDownloadFiles List of currently downloading/downloaded files to prevent re-download.
     * @return array
     */
    public function getToDownloadHdRegexp($inDownloadFiles = [])
    {
        $episodesRegexp = [];
        $sickbeardInfos = $this->_call("future&sort=date&type=today|missed");
        
        foreach (array_merge($sickbeardInfos->missed, $sickbeardInfos->today) as $e) {
            $regexp = preg_replace("/\s/", '.*', $e->show_name) . '.*0*' . $e->season . 'E0*' . $e->episode . '.*720p.*(hdtv|publichd|eztv).*';
            $beautifullName = sprintf("%s S%02dE%02d", $e->show_name, $e->season, $e->episode);
            $found = false;
            // Download only if not already downloading
            foreach ($inDownloadFiles as $f) {
                if (preg_match('/' . $regexp . '/siU', $f)) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $episodesRegexp[$regexp] = $beautifullName;
            }
        }
        return $episodesRegexp;
    }

    /**
     * Get the shows in foreign languages.
     * @param string $localLanguage Local language (default: fr)
     * @return array
     */
    public function getForeignShows($localLanguage = "fr")
    {
        $foreignShows = [];
        $sickbeardShows = $this->_call("shows");
        foreach ($sickbeardShows as $showId => $showDetails) {
            if ($showDetails->language != $localLanguage) {
                $foreignShows[$showId] = $showDetails->show_name;
            }
        }
        return $foreignShows;
    }

    /**
     * Get the local directories of the given shows
     * @param array $shows
     * @return array
     */
    public function getShowsDirectories($shows)
    {
        $dirs = [];
        foreach ($shows as $showId => $name) {
            $sickbeardShowDetails = $this->_call("show&tvdbid=$showId");
            $dirs[$showId] = $sickbeardShowDetails->location;
        }
        return $dirs;
    }

}
