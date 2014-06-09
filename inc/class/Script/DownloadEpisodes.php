<?php

namespace Script;

class DownloadEpisodes extends Script
{
    use \Singleton;
    
    const SUBLIMINAL_CMD = '/usr/local/subliminal/env/bin/subliminal -m -q -l %s "%s" > /dev/null 2>&1';
    
    private $_lang = null;
    
    public function setLang($lang)
    {
        $this->_lang = $lang;
    }
    
    protected function _run()
    {
        $syno = \Api\Synology::getInstance();
        $sickbeard = \Api\Sickbeard::getInstance();
        $kickass = \Api\KickassTorrent::getInstance();
        
        // Get currently downloading/downloaded
        $downs = $syno->getInDownload();

        // Get next episodes to download
        $episodes = $sickbeard->getToDownloadHdRegexp($downs);
        $episodesRegexp = array_keys($episodes);

        // Search torrents on KickAss torrent
        $torrents = $kickass->findTorrents($episodesRegexp, "tv");

        // Finally, if we got torrent, download them
        if (count($torrents)) {
            $syno->addDownloads($torrents);
        }
        if (count($episodesRegexp)) {
            $beautifullNames = [];
            echo "Following episodes has not been found in the last 24h dump:\n";
            foreach ($episodesRegexp as $r) {
                echo "\t" . $episodes[$r] . "\n";
            }
            echo "Think about download them manually (or use full dump, but use it carefully !).\n";
        }
    }
    
}