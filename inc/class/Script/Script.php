<?php

namespace Script;

abstract class Script
{
    private $_lockCreated = false;
    
    /**
     * @return string
     */
    private function _getLockFile()
    {
        return "/tmp/" . str_replace("\\", "_", get_called_class()) . ".lock";
    }
    
    private function _lock()
    {
        $lockFile = $this->_getLockFile();
        if(file_exists($lockFile) || !touch($lockFile)) {
            exit(0);
        }
        $this->_lockCreated = true;
    }
    
    private function _unlock()
    {
        $lockFile = $this->_getLockFile();
        if($this->_lockCreated && file_exists($lockFile)) {
            unlink($lockFile);
        }
    }
    
    protected function __construct()
    {
        $this->_lock();
    }
    
    public function __destruct()
    {
        $this->_unlock();
    }    
    
    public function run()
    {
        try {
            $this->_run();
        }
        catch (\Exception $e) {
            $this->_unlock();
            throw $e;
        }
    }
    
    abstract protected function _run();
}