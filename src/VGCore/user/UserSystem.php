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
        $this->db = DB::getDatabase();
    }
    
    public function getRank(string $username): string {
        if (DB::checkUser($username)) {
            $lowuser = strtolower($username);
            $dbquery = $this->db->query("SELECT rank FROM users WHERE username='" . $this->db->real_escape_string($username) . "'");
            return $dbquery->fetch_array()[0] ?? false;
        } else {
            return false;
        }
    }
    
    public function addKill(string $user) {
        
    }
    
    public function setRank(string $user, string $rank): bool {
        $lowuser = strtolower($user);
        $check = DB::checkUser($user);
        if ($check === true && in_array($rank, self::rank)) {
            return $this->db->query("UPDATE users SET rank = $rank WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
        } else {
            return false;
        }
    }
    
    public function userStat(string $name): array {
        $check = DB::checkUser($user);
        if ($check === true) {
            $reqstat = [
                "kills",
                "deaths"
            ];
            $lowername = strtolower($name);
            foreach ($reqstat as $r) {
                $stat = [];
                $query = $this->db->query("SELECT " . $r . " FROM users WHERE username='" . $this->db->real_escape_string($lowername) . "'");
                $stat[] = $query;
            }
            return $stat;
        }
    }
    
}
