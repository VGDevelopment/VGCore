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
    
    public static function checkUser(string $username) {
        $lowusername = strtolower($username);
		$query = self::$db->query("SELECT * FROM users WHERE username='" . self::$db->real_escape_string($lowusername) . "'");
		return $query->num_rows > 0 ? true:false;
    }
    
    public static function createUser(string $user) {
        $check = self::checkAccount($user);
        if ($check === false) {
            $lowuser = strtolower($user);
            $q = self::$db->query("INSERT INTO users (username, rank) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 'Player');");
			$q2 = self::$db->query("INSERT INTO users (username, kills) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 0);");
			$q3 = self::$db->query("INSERT INTO users (username, deaths) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 0);");
			$q4 = self::$db->query("INSERT INTO users (username, ban) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 0);");
			$q5 = self::$db->query("INSERT INTO users (username, coins) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 5000);");
			$q6 = self::$db->query("INSERT INTO users (username, dollars) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 0);");
			$q7 = self::$db->query("INSERT INTO users (username, gems) VALUES ('" . self::$db->real_escape_string($lowuser) . "', 10);");
			if ($q === true || $q2 === true || $q3 === true || $q4 === true || $q5 === true || $q6 === true || $q7 === true) {
			    return true;
			} else {
			    return false;
			}
        } else {
            return false;
        }
    }
    
    public static function deleteUser(string $user) {
        if ($check === true) {
            $lowuser = strtolower($user);
            $query = self::$db->query("DELETE FROM users WHERE username='" . self::$db->real_escape_string($lowuser) . "'");
            if ($query === true) {
                return true;
            } else {
                return false;
            }
        }
        
    }
    
}