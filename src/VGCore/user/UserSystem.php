<?php

namespace VGCore\user;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\network\Database as DB;

class UserSystem {
    
    private $db;
    
    public $plugin;
    
    public static $rank = ["Lunar", "Warrior", "Giant", "Dwarf", "Discord", "Player"];
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->db = DB::$db;
    }
    
    public function getRank(string $username) {
        if ($this->checkUser($username)) {
            $lowuser = strtolower($username);
            $dbquery = $this->db->query("SELECT rank FROM users WHERE username='" . $this->db->real_escape_string($username) . "'");
            return $dbquery->fetch_array()[0] ?? false;;
        } else {
            return false;
        }
    }
    
    public function addKill(string $user) {
        //
    }
    
    public function setRank(string $user, string $rank) {
        $lowuser = strtolower($user);
        $check = $this->checkUser($user);
        if ($check === true && in_array($rank, self::rank)) {
            return $this->db->query("UPDATE users SET rank = $rank WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
        } else {
            return false;
        }
    }
    
}