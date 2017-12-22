<?php

namespace VGCore\ban;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\user\UserSystem as US;

use VGCore\network\Database as DB;

class BanSystem {
    
    private $db;
    
    public $plugin;
    public $us;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->us = new US($this->plugin);
        $this->db = DB::getDatabase();
    }
    
    public function banUser(string $user, string $reason, string $admin, int $banid) {
        $lowuser = strtolower($user);
        $check = DB::checkUser($user);
        if ($check === true) {
            $this->db->query("UPDATE users SET ban = 1 WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            $this->db->query("UPDATE users SET banid = $banid WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            $this->db->query("UPDATE users SET reason = $reason WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            $this->db->query("UPDATE users SET admin = $admin WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            return true;
        } else {
            return false;
        }
    }
    
    public function isBan(string $user) {
        $lowuser = strtolower($user);
        $check = DB::checkUser($user);
        if ($check === true) {
            $query = $this->db->query("SELECT ban FROM users WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            if ($query === 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    public function getBanID(string $user) {
        $lowuser = strtolower($user);
        $check = DB::checkUser($user);
        if ($check === true) {
            $bancheck = $this->isBan($user);
            if ($bancheck === true) {
                $query = $this->db->query("SELECT banid FROM users WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
                return $query->fetch_array()[0] ?? false;
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }
    }
    
    public function getBanReason(string $user) {
        $lowuser = strtolower($user);
        $check = DB::checkUser($user);
        if ($check === true) {
            $bancheck = $this->isBan($user);
            if ($bancheck === true) {
                $query = $this->db->query("SELECT reason FROM users WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
                return $query->fetch_array()[0] ?? false;
            } else {
                return "ERROR";
            }
        } else {
            return "ERROR";
        }
    }
    
    public function generateBanID() {
        $query = $this->db->query("SELECT banid FROM users");
        $allbanid = $query->fetch_array();
        $max = max($allbanid);
        $randomid = $max + 1;
        $formatdigit = sprintf("%05d", $randomid);
        return $formatdigit;
    }
    
}