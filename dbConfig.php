<?php

/*
Table schema : 
CREATE TABLE `nginx_cache_cleaner_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `entity_type` varchar(20) NOT NULL,
  `entity_id` int(11) NOT NULL,
  `cache_key_identifier` varchar(1000) NOT NULL,
  `status` varchar(50) DEFAULT 'pending',
  `added_time` timestamp NULL DEFAULT NULL,
  `updated_time` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `entity_type_index` (`entity_type`),
  KEY `entity_id_index` (`entity_id`),
  KEY `idx_added_time` (`added_time`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
*/
$dbConfig = array(
        'write' => array(
                'host' => '<hostname>',
                'user' => '<user>',
                'password' => '<password>',
                'database' => '<db name>',
                'port' => 3306,
                'socket' => '<socket>'
        ),
        'read' => array(
                'host' => '<hostname>',
                'user' => '<user>',
                'password' => '<password>',
                'database' => '<db name>',
                'port' => 3306,
                'socket' => '<socket>'
        )
);
?>
