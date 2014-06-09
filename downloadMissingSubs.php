#!/usr/bin/php
<?php

require_once(__DIR__ . "/inc/bootstrap.php");

$conf = Config::get("subs");
$script = Script\DownloadSubs::getInstance();
$script->setLang($conf->myLang);
$script->run();