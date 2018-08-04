<?php
header("Cache-Control: max-age=3600, public");
header("Pragma: cache");
header("Vary: Accept-Encoding"); // Handle proxies
header('Content-Type: text/javascript; charset: UTF-8');

include(dirname(__FILE__) . '/comment.js');
