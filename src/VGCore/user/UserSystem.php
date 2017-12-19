<?php

namespace VGCore\user;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

class UserSystem {
    
    private $db;
    
    public $plugin;
    
    // servers - each lobby or game server will have different port
    public $lobby = [19132];
    public $faction = [29838,];
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        // Database
        $this->db = mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1");
        if ($this->db->connect_error) {
            $this->plugin->getLogger()->critical("Could not connect to MySQL server: " . $this->db->connect_error);
			return;
        }
        if (!$this->db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			rank FLOAT,
			kill FLOAT,
			death FLOAT,
			ban INT(1)
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}
    }
    
    public function getPlugin() { // returns plugin instance for static methods
        return $this->plugin;
    }
    
    public static function isLobby() {
        $port = self::getPlugin()->getServer()->getPort();
        if (in_array($port, $lobby)) {
            return true;
        } else {
            return false;
        }
    }
    
    public static function isFaction() {
        $port = self::getPlugin()->getServer()->getPort();
        if (in_array($port, $faction)) {
            return true;
        } else {
            return false;
        }
    }
    
    public function checkRank(string $rank) {
        $result = $this->db->query("SELECT * FROM users WHERE rank='" . $this->db->real_escape_string($rank) . "'");
        return $result->num_rows > 0 ? true:false;
    }
    
    public function makeRank(string $rank) {
        if(!$this->checkExists($rank)) {
            $this->db->query("INSERT INTO users (rank) VALUES ('". $this->db->real_escape_string($rank) . "'");
            return true;
        }
        return false;
    }
    
    public function checkUser(string $user) {
        $lowuser = strtolower($user);
        $result = $this->db->query("SELECT * FROM users WHERE username='". $this->db->real_escape_string($lowuser) . "'");
        return $result->num_rows > 0 ? true:false;
    }
    
    public function makeUser(string $user) {
        $lowuser = strtolower($user);
        if(!$this->checkUser($user)) {
			$this->db->query("INSERT INTO users (username, rank) VALUES ('" . $this->db->real_escape_string($lowuser) . "', default);");
			$this->db->query("INSERT INTO users (username, kill) VALUES ('" . $this->db->real_escape_string($lowuser) . "', 0);");
			$this->db->query("INSERT INTO users (username, death) VALUES ('" . $this->db->real_escape_string($lowuser) . "', 0);");
			$this->db->query("INSERT INTO users (username, ban) VALUES ('" . $this->db->real_escape_string($lowuser) . "', 0);");
			return true;
		}
		return false;
    }
    
    public function addKill(string $user) {
        //
    }
    
}