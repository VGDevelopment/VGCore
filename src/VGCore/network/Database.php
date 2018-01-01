<?php

namespace VGCore\network;

use VGCore\SystemOS;

class Database {

    public static function createRecord(SystemOS $plugin) {
    	$db = self::getDatabase();
        if ($db->connect_error) {
			$plugin->getLogger()->critical("Could not connect to MySQL server: " . self::$db->connect_error);
			return;
		}
		if (!$db->query("CREATE TABLE IF NOT EXISTS users(
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
			$plugin->getLogger()->critical("Error creating 'users' table: " . $db->error);
			return;
		}
		if (!$db->query("CREATE TABLE IF NOT EXISTS factions(
			player TEXT,
    		invites TEXT,
    		requests TEXT,
			faction TEXT,
			valid INT(1),
			rank TEXT,
			kills INT(5),
			deaths INT(5),
			power INT(5)
			);")) {
			$plugin->getLogger()->critical("Error creating 'factions' table: " . $db->error);
			return;
		}
    }

    public static function getDatabase() {
		return mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1");
    }

    public static function checkUser(string $username) {
    	$db = self::getDatabase();
        $lowusername = strtolower($username);
		$query = $db->query("SELECT * FROM users WHERE username='" . $db->real_escape_string($lowusername) . "'");
		return $query->num_rows > 0 ? true:false;
    }

    public static function createUser(string $user) {
		$check = self::checkUser($user);
        if ($check === false) {
        	$db = self::getDatabase();
            $lowuser = strtolower($user);
            $q = $db->query("INSERT INTO users (username, rank) VALUES ('" . $db->real_escape_string($lowuser) . "', 'Player');");
			$q2 = $db->query("INSERT INTO users (username, kills) VALUES ('" . $db->real_escape_string($lowuser) . "', '0');");
			$q3 = $db->query("INSERT INTO users (username, deaths) VALUES ('" . $db->real_escape_string($lowuser) . "', '0');");
			$q4 = $db->query("INSERT INTO users (username, ban) VALUES ('" . $db->real_escape_string($lowuser) . "', '0');");
			$q5 = $db->query("INSERT INTO users (username, coins) VALUES ('" . $db->real_escape_string($lowuser) . "', '5000');");
			$q6 = $db->query("INSERT INTO users (username, dollars) VALUES ('" . $db->real_escape_string($lowuser) . "', '0');");
			$q7 = $db->query("INSERT INTO users (username, gems) VALUES ('" . $db->real_escape_string($lowuser) . "', '10');");
			if ($q === true && $q2 === true && $q3 === true && $q4 === true && $q5 === true && $q6 === true && $q7 === true) {
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
        	$db = self::getDatabase();
            $lowuser = strtolower($user);
            $query = $db->query("DELETE FROM users WHERE username='" . $db->real_escape_string($lowuser) . "'");
            if ($query === true) {
                return true;
            } else {
                return false;
            }
        }

    }

}
