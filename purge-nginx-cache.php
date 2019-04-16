<?php

// load database config
require __DIR__.'/dbConfig.php';

// initialize variables
global $dbConfig;
global $server;
global $serverType;
$server = getHostByName(getHostName());

// Method to get items to be removed from cache
function getCacheForInvalidation(){
	global $dbConfig;
	$mysqli = new mysqli($dbConfig['read']['host'], $dbConfig['read']['user'], $dbConfig['read']['password'],
                  $dbConfig['read']['database'], $dbConfig['read']['port'], $dbConfig['read']['socket']);
	if ($mysqli->connect_errno) {
        	echo "Errno: " . $mysqli->connect_errno . "\n";
	        echo "Error: " . $mysqli->connect_error . "\n";
        	exit;
	}

	global $server;
	$sql = "select id, entity_id, entity_type,cache_key_identifier from nginx_cache_cleaner_log where status='pending'";

	// add this safe-check in case you want to avoid picking any old unprocessed log. You can change the hours as per your requirement
	//$sql .= " and  added_time > NOW() - INTERVAL 24 HOUR";

	if(!$result = $mysqli->query($sql)) {
        	echo "Errno: " . $mysqli->errno . "\n";
	        echo "Error: " . $mysqli->error . "\n";
	}

	$itemsToBeRemoved = array();
	while($row = $result->fetch_assoc()) {
		$itemsToBeRemoved[$row['entity_type']][$row['id']] = $row;
	}
	$mysqli->close();
	return $itemsToBeRemoved;

}

// Menthod to get all possible items to be removed from cache and call the necessary sub-methods to clean/delete the cache of same
function cleanCache(){

	$itemsToBeRemoved = getCacheForInvalidation();
	global $dbConfig;
	$mysqli = new mysqli($dbConfig['read']['host'], $dbConfig['read']['user'], $dbConfig['read']['password'],
                  $dbConfig['read']['database'], $dbConfig['read']['port'], $dbConfig['read']['socket']);
        if ($mysqli->connect_errno) {
                echo "Errno: " . $mysqli->connect_errno . "\n";
                echo "Error: " . $mysqli->connect_error . "\n";
                exit;
        }

	foreach($itemsToBeRemoved as $entityType=>$rows){
        
		foreach($rows as $itemId=>$rowdata){
			cleanElementCache($itemId, $rowdata['entity_id'],$entityType, $rowdata['cache_key_identifier']);
		}
	}

	$mysqli->close();
}

// Method to clean/remove all possible keys corresponding to the entity present in the log table
function cleanElementCache($itemId, $entityId, $entityType, $cacheKeyIdentifier){

	global $serverType;
	$cacheDirectory='';

	$cacheDirectoryMapping = array("entity1" => "cacheDir1"); // add more entity to directory corresponding to zones here

	$cacheKeyIdentifierList = array($cacheKeyIdentifier); // change this in case you have multiple caches corresponding to single entity

	if(empty($cacheKeyIdentifierList)){
		echo "Empty Cache Key found for ".$entityId." ".$entityType;
		#continue;
	}
	else{

		$cacheDirectory = $cacheDirectoryMapping[$entityType];
		$cacheParentDirectory = '/data/nginxcache/'; // change this to your cache dir

		foreach($cacheKeyIdentifierList as $cacheKeyIdentifier){

			$shellCommandForPurging = "nginx-cache-cleaner '".$cacheKeyIdentifier."' ".$cacheParentDirectory.$cacheDirectory;

			var_dump("Command : ".$shellCommandForPurging);
			$output = shell_exec($shellCommandForPurging);

			$deletionStatus = 'done';
			$output = str_replace(PHP_EOL, '', $output);
			if($output == 'not cached'){
				$deletionStatus = 'notfound';
			}
		}
		global $server;

		$sql = "update nginx_cache_cleaner_log set status = CONCAT(status, ' - ".$server.":".$deletionStatus."') where id=".$itemId;

		global $dbConfig;
		$mysqli = new mysqli($dbConfig['write']['host'], $dbConfig['write']['user'], $dbConfig['write']['password'],
    		          $dbConfig['write']['database'], $dbConfig['write']['port'], $dbConfig['write']['socket']);
		if(!$result = $mysqli->query($sql)) {
        		echo "Errno: " . $mysqli->errno . "\n";
        		echo "Error: " . $mysqli->error . "\n";
		}

	}

}

// start cleaning the cache
cleanCache();
?>
