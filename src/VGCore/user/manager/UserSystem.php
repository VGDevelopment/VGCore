<?php

namespace VGCore\user\manager;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\network\{
    Database as DB,
    NetworkManager
};

use VGCore\user\{
    UserOS,
    Staff,
    manager\ban\BanSystem
};

class UserSystem extends UserOS implements Staff {

    /**
     * Player
     */
    const R1 = "Player";
    /**
     * Lunar
     */
    const R2 = "Lunar";
    /**
     * Warrior
     */
    const R3 = "Warrior";
    /**
     * Giant
     */
    const R4 = "Giant";
    /**
     * Dwarf
     */
    const R5 = "Dwarf";
    /**
     * Admin
     */
    const R6 = "ADMIN";
    const POS_SHOW = "showcoordinates";
    
    private static $db;
    private static $os;

    private static $ranklist = [];

    private static $ckill = [];
    private static $cdeath = [];

    /**
     * Starts up the user management system.
     *
     * @param SystemOS $os
     * @param mixed $db
     * @return void
     */
    protected static function start(SystemOS $os, mixed $db): void {
        self::$os = $os;
        self::$db = $db;
        self::$ranklist = [
            self::R1,
            self::R2,
            self::R3,
            self::R4,
            self::R5,
            self::R6
        ];
        child::start();
    }

    /**
     * Creates a user profile when the user joins.
     *
     * @param string $user
     * @param string $userid
     * @param string $rank
     * @return boolean
     */
    public static function createUser(string $user, string $userid, string $rank = self::R1): bool {
		$check = self::checkUser($user);
        if ($check === false) {
        	$db = self::getDatabase();
            $lowuser = strtolower($user);
            $check = in_array($user, self::STAFF);
            if ($check === true) {
                $rank = self::R6;
            }
            $q = $db->query("INSERT INTO users (username, rank, kills, deaths, ban, coins, dollars, gems, userid) VALUES ('"
            . $db->real_escape_string($lowuser) .
            "',
            '" . $rank . "',
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

    /**
     * Deletes a user profile permanently.
     *
     * @param string $user
     * @return boolean
     */
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

    /**
     * Sets the rank for a specific username.
     *
     * @param string $name
     * @param string $rank
     * @return boolean
     */
    public static function setRank(string $name, string $rank = self::R1): bool {
        $check = in_array($rank, self::$ranklist);
        if ($check !== true) {
            return false;
        }
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            $query = self::$db->query("UPDATE users SET rank = '" . $rank . "' WHERE username='" . self::$db->real_escape_string($lowername) . "'");
            if ($query === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the rank for a specific username.
     *
     * @param string $name
     * @return string
     */
    public static function getRank(string $name): string {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            $query = self::$db->query("SELECT rank FROM users WHERE username='" . self::$db->real_escape_string($name) . "'");
            $result = $query->fetch_array();
            $query->free();
            $check = in_array($result[0], self::$ranklist);
            if ($check !== true) {
                return "ERROR";
            }
            return $result[0];
        }
        return "ERROR";
    }

    /**
     * Checks whether a user exists with the given UserID.
     *
     * @param string $userid
     * @return boolean
     */
    public static function checkIfUserIDExist(string $userid): bool {
        $query = self::$db->query("SELECT * FROM users WHERE userid='" . self::$db->real_escape_string($userid) . "'");
        $check = $query->num_rows > 0 ? true : false;
        $query->free();
        if ($check === true) {
            return true;
        }
        return false;
    }

    /**
     * Updates a user's username per given UserID. Used in-case of a namechange per xBox XUID.
     *
     * @param string $userid
     * @param string $name
     * @return boolean
     */
    public static function updateUserPerUserID(string $userid, string $name): bool {
        $lowername = strtolower($name);
        $query = self::$db->query("UPDATE users SET username='" . self::$db->real_escape_string($lowername) . "' WHERE userid='" . self::$db->real_escape_string($userid) . "'");
        if ($query === true) {
            return true;
        }
        return false; 
    }

    /**
     * Get the UserID for a specific username.
     *
     * @param string $name
     * @return string
     */
    public static function getUserID(string $name): string {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            $query = self::$db->query("SELECT userid FROM users WHERE username='" . self::$db->real_escape_string($lowername) . "'");
            $result = $query->fetchArray();
            $query->free();
            $l = strlen($result[0]);
            if ($l === 16) {
                return $result[0];
            } else {
                return "ERROR";
            }
        }
        return "ERROR";
    }

    /**
     * Adds kills to the server RANDOM ACCESS MEMORY to store.
     *
     * @param string $name
     * @return boolean
     */
    public static function addKill(string $name): bool {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            if (array_key_exists($lowername, self::$ckill)) {
                self::$ckill[$lowername] = self::$ckill[$lowername] + 1;
                return true;
            }
            self::$ckill[$lowername] = 1;
            return true;
        }
        return false;
    }

    /**
     * Adds deaths to the server RANDOM ACCESS MEMORY to store.
     *
     * @param string $name
     * @return boolean
     */
    public static function addDeath(string $name): bool {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            if (array_key_exists($lowername, self::$cdeath)) {
                self::$cdeath[$lowername] = self::$cdeath[$lowername] + 1;
                return true;
            }
            self::$cdeath[$lowername] = 1;
            return true;
        }
        return false;
    }

    /**
     * Updates the database per the server stored details in RANDOM ACCESS MEMORY.
     * 
     * Use methods such as addKill() or addDeath() on runtime. 
     *
     * @return boolean
     */
    private static function updateStat(): bool {
        foreach (self::$ckill as $i => $v) {
            $query = self::$db->query("UPDATE users SET kills = kills + " . $v . " WHERE username='" . self::$db->real_escape_string($i) . "'");
            if ($query !== true) {
                return false;
            }
        }
        foreach (self::$ckill as $i => $v) {
            $query = self::$db->query("UPDATE users SET kills = kills + " . $v . " WHERE username='" . self::$db->real_escape_string($i) . "'");
            if ($query !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * Get the kill stats associated with a username.
     *
     * @param string $name
     * @return integer
     */
    public static function getKill(string $name): int {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            $query = self::$db->query("SELECT kills FROM users WHERE username='" . self::$db->real_escape_string($lowername) . "'");
            $result = $query->fetchArray();
            $query->free();
            $kill = (int)$result[0];
            if (array_key_exists($lowername, self::$ckill)) {
                $kill = $kill + self::$ckill[$lowername];
            }
            return $kill;
        }
        return 0x01;
    }

    /**
     * Get the death stats associated with a username.
     *
     * @param string $name
     * @return integer
     */
    public static function getDeath(string $name): int {
        $check = DB::checkUser($name);
        if ($check === true) {
            $lowername = strtolower($name);
            $query = self::$db->query("SELECT deaths FROM users WHERE username='" . self::$db->real_escape_string($lowername) . "'");
            $result = $query->fetchArray();
            $query->free();
            $death = (int)$result[0];
            if (array_key_exists($lowername, self::$cdeath)) {
                $death = $death + self::$ckill[$lowername];
            }
            return $death;
        }
        return 0x01;
    }

    /**
     * Gets the stat of users.
     * 
     * Returns an array if @param string $index = null and an integer if it isn't.
     * Used when you want collective stats.
     *
     * @param string $name
     * @param string $index
     * @return mixed
     */
    public static function getStat(string $name, string $index = null): mixed {
        $check = DB::checkUser($name);
        if ($check === true) {
            $kill = self::getKill($name);
            $death = self::getDeath($name);
            $rank = self::getRank($name);
            $stat = [
                "kill" => $kill,
                "death" => $death,
                "rank" => $rank
            ];
            if ($index !== null) {
                return $stat[$index];
            }
            return $stat;
        }
    }

    /**
     * Allows players to see their position on the map.
     *
     * @param Player $player
     * @param string $type
     * @param integer $byte
     * @param boolean $bool
     * @return boolean
     */
    public static function allowPositionView(Player $player, string $type = self::POS_SHOW, int $byte = 1, bool $bool = true): bool {
        $pk = NetworkManager::handleGameRulePacket($player, $type);
        $gr = $pk->gamerules[$type];
        if ($gr === [$byte, $bool]) {
            return true;
        }
        return false;
    }
    
}
