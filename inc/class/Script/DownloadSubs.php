<?php

namespace Script;

class DownloadSubs extends Script
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
        if(!$this->_lang) {
            throw new \Exception("Lang not defined!");
        }
        
        $sickbeard = \Api\Sickbeard::getInstance();
        // Get all EN show
        $shows = $sickbeard->getForeignShows();
        $dirs = $sickbeard->getShowsDirectories($shows);

        $missing = [];

        // Get subs of videos that don't have their subs
        $videoExts = [".mkv", ".avi", ".mp4"];
        $srtExt = ".fr.srt";
        $findVideos = 'find "%s" -name *'.implode(' -o -name *', $videoExts);
        $findSrt = 'find "%s" -name *'.$srtExt;
        foreach ($dirs as $showId => $d) {
            exec(sprintf($findVideos, $d), $videos);
            exec(sprintf($findSrt, $d), $subs);

            $cleanSubs = [];
            foreach($videos as $v) {
                $srt = str_replace($videoExts, $srtExt, $v);
                if(!in_array($srt, $subs)) {
                    exec(sprintf($subliminalCmd, $this->_lang, $v));
                }
            }
        }
    }
    
}