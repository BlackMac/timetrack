<?php
require_once "TimeTrackAPI.class.php";
require_once "jsonRPCServer.php";

$api = new TimeTrackAPI();
jsonRPCServer::handle($api);

