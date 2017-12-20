<?php

namespace VGCore\network;

use VGCore\SystemOS;

class Database {
    
    public static $db;
    
    public static function createRecord(SystemOS $plugin) {
        self::$db = mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1");
        if (self::$db->connect_error) {
			$plugin->getLogger()->critical("Could not connect to MySQL server: " . self::$db->connect_error);
			return;
		}
		if (!self::$db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			dollars FLOAT,
			gems FLOAT,
			coins FLOAT,
			kills FLOAT,
			deaths FLOAT,
			ban FLOAT,
			rank TEXT,
			banid INT(5),
			date DATE,
			reason VARCHAR(50),
			admin VARCHAR(20)
			);")) {
			$plugin->getLogger()->critical("Error creating 'users' table: " . self::$db->error);
			return;
		}
		if (!self::$db->query("CREATE TABLE IF NOT EXISTS factions(
			player TEXT,
			faction TEXT,
			valid INT(1),
			rank TEXT,
			kill INT(5),
			death INT(5),
			power INT(5)
			);")) {
			$plugin->getLogger()->critical("Error creating 'factions' table: " . self::$db->error);
			return;
		}
    }
    
}