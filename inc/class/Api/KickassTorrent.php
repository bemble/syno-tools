<?php

namespace Api;

/**
 * @method KickAss getInstance() /!\ Static
 * @link http://kickass.to/api/ KickAss Torrent API details
 */
class KickassTorrent extends Api
{
    use \Singleton;

    /**
     * @var array
     */
    private $_24hDump;

    /**
     *
     * @var array
     */
    private $_fullDump;
    
    protected function _getConfName()
    {
        return "kickassTorrent";
    }

    /**
     * Load a dump file from KickAss torrent
     * @param string $url
     * @return array
     */
    private function _loadDump($url)
    {
        return gzfile($url);
    }

    /**
     * Load the KickAss torrent 24h dump
     */
    private function _load24hDump()
    {
        if (!$this->_24hDump) {
            $this->_24hDump = $this->_loadDump($this->_conf->dayDumpUrl);
        }
    }

    /**
     * Load the KickAss torrent full dump
     * /!\ Very big file, can kill your computer!
     */
    private function _loadFullDump()
    {
        if (!$this->_24hDump) {
            $this->_24hDump = $this->_loadDump($this->_conf->fullDumpUrl);
        }
    }

    /**
     * Decompose a raw dump line into a human readable array.
     * @param string $rawLine
     * @param string $separator
     * @return array
     */
    private function _decomposeLine($rawLine, $separator = "|")
    {
        $rawInfos = str_getcsv($rawLine, $separator);
        return [
            "id" => $rawInfos[0],
            "name" => $rawInfos[1],
            "type" => $rawInfos[2],
            "url" => $rawInfos[3],
            "torrentUrl" => $rawInfos[4],
        ];
    }

    /**
     * Does the given file match to any of the searched regexp?
     * If so, corresponding regexp is deleted from the array.
     *
     * @param array $regexps
     * @param array $file
     * @return boolean
     */
    private function _match(&$regexps, $file)
    {
        foreach ($regexps as $i => $r) {
            if (preg_match('/' . $r . '/siU', $file["name"])) {
                unset($regexps[$i]);
                return true;
            }
        }
        return false;
    }

    /**
     * Find torrent according to given regexps.
     * @param array $regexps Will be updated. At the end of the process, only not found corresponding torrents remain
     * @param string $type Restrict search to given type (eg. "tv" for tv shows)
     * @param bool $fullDump Search into the full dump instead of last 24h
     *  /!\ Caution, huge amount of data, can kill you computer!!
     * @return array List of torrent found
     */
    public function findTorrents(&$regexps, $type = false, $fullDump = false)
    {
        $torrents = [];
        
        if(count($regexps)) {
            $dump = [];
            if ($fullDump) {
                $this->_loadFullDump();
                $dump = $this->_fullDump;
            } else {
                $this->_load24hDump();
                $dump = $this->_24hDump;
            }

            for ($i = 0; count($regexps) > 0 && $i < count($this->_24hDump); $i++) {
                $file = $this->_decomposeLine($this->_24hDump[$i]);
                if ($type && strtolower($file["type"]) != $type) {
                    continue;
                }
                if ($this->_match($regexps, $file)) {
                    $torrents[] = $file["torrentUrl"];
                }
            }
        }
        return $torrents;
    }

}