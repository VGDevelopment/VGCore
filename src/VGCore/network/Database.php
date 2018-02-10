<?php

namespace VGCore\network;

use VGCore\SystemOS;
// >>>
use mysqli; // php object 

class Database {

    public static function createRecord(SystemOS $plugin) {
    	$db = self::getDatabase();
        if ($db->connect_error) {
			$plugin->getLogger()->critical("Could not connect to MySQL server: " . self::$db->connect_error);
			return;
		}
		if (!$db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			userid VARCHAR(16),
			dollars FLOAT,
			gems FLOAT,
			coins FLOAT,
			kills FLOAT,
			deaths FLOAT,
			faction VARCHAR(30),
			factionrole VARCHAR(12),
			ban FLOAT,
			rank VARCHAR(12),
			banid INT(5),
			date DATE,
			reason VARCHAR(50),
			admin VARCHAR(20),
			common_key int(5),
			rare_key int(5),
			legendary_key int(5)
			);")) {
			$plugin->getLogger()->critical("Error creating 'users' table: " . $db->error);
			return;
		}
		if (!$db->query("CREATE TABLE IF NOT EXISTS factions(
			faction VARCHAR(30) PRIMARY KEY,
			leader VARCHAR(30),
			valid INT(1),
			rank VARCHAR(12),
			kills INT(5),
			deaths INT(5),
			power INT(5),
			ldata VARCHAR(30)
			);")) {
			$plugin->getLogger()->critical("Error creating 'factions' table: " . $db->error);
			return;
		}
		/*
		To save the amount of players being sent to other server in a record of binary. 1 for true, 0 for false
		Format for all players = 
		t1 => 1:1:1
		t2 => 1:1:1
		as in true:true:true, true:true:true.
		*/
		if (!$db->query("CREATE TABLE IF NOT EXISTS wars(
			serverip VARCHAR(25) PRIMARY KEY,
			f1 VARCHAR(30),
			f2 VARCHAR(30),
			valid INT(1),
			result INT(1),
			t1 VARCHAR(5),
			t2 VARCHAR(5)
			);")) {
			$plugin->getLogger()->critical("Error creating 'wars' table: " . $db->error);
		}
    }

	/**
	 * Returns a database connection object.
	 *
	 * @return mysqli
	 */
    public static function getDatabase(): mysqli {
		return mysqli_connect("185.62.36.114", "db_1", "048bda35cb", "db_1"); // alias of mysql::__construct()
    }

    public static function checkUser(string $username): bool {
    	$db = self::getDatabase();
        $lowusername = strtolower($username);
		$query = $db->query("SELECT * FROM users WHERE username='" . $db->real_escape_string($lowusername) . "'");
		return $query->num_rows > 0 ? true:false;
    }

    public static function createUser(string $user, string $userid): bool {
		$check = self::checkUser($user);
        if ($check === false) {
        	$db = self::getDatabase();
            $lowuser = strtolower($user);
            $q = $db->query("INSERT INTO users (username, rank, kills, deaths, ban, coins, dollars, gems, userid) VALUES ('"
            . $db->real_escape_string($lowuser) .
            "',
            'Player',
            '0',
            '0',
            '0',
            '5000',
            '0',
            '10',
            '" . $db->real_escape_string($userid) . "'
            );");
			if ($q === true) {
				return true;
			} else {
			    return false;
			}
        } else {
            return false;
        }
    }

    public static function deleteUser(string $user): bool {
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
