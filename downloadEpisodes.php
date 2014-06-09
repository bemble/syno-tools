#!/usr/bin/php
<?php

require_once(__DIR__ . "/inc/bootstrap.php");

$script = Script\DownloadEpisodes::getInstance();
$script->run();