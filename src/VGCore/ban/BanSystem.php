<?php

namespace VGCore\ban;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;
use VGCore\user\UserSystem;

class BanSystem {
    
    private $db;
    
    public $plugin;
    public $us;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->us = new US($this->plugin);
        // Database
        $this->db = mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1");
        if ($this->db->connect_error) {
            $this->plugin->getLogger()->critical("Could not connect to MySQL server: " . $this->db->connect_error);
			return;
        }
        if (!$this->db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			ban INT(1),
			banid INT(5),
			date DATE,
			reason VARCHAR(50),
			admin VARCCHAR(20)
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}
    }
    
    public function banUser(string $user, string $reason, string $admin, int $banid) {
        $lowuser = strtolower($user);
        $check = $this->us->checkUser($user);
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
        $check = $this->checkUser($user);
        if ($check === true) {
            $query = $this->db->query("SELECT ban FROM users WHERE username='" . $this->db->real_escape_string($lowuser) . "'");
            $bancheck = $query->fetch_array()[0] ?? false;
            if ($bancheck === 1) {
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
        $check = $this->checkUser($user);
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
        $check = $this->checkUser($user);
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