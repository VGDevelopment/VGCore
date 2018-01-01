<?php

namespace VGCore\faction;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\network\Database as DB;

class FactionSystem {

	public $db;
	public $plugin;
	public $faction;
	public $rank = ["Leader", "Officer", "Member"];

	public function __construct(SystemOS $plugin) {
		$this->plugin = $plugin;
		$this->db = DB::getDatabase();
	}

	public function factionValidate(string $faction) {
		$result = $this->db->query("SELECT * FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
		return $result->num_rows > 0 ? true:false;
	}

	public function createFaction(string $faction, Player $leader) {
		$leadername = $leader->getName();
		$roleleader = $this->rank[0];
		if (!$this->factionValidate($faction)) {
			$this->db->query("INSERT INTO factions (faction, leader, kills, deaths, power, valid) VALUES ('" . $db->real_escape_string($faction) . 
		"', 
		$leadername,
		'0000', 
		'0000', 
		'0000', 
		'0'
		);");
		}
		return false;
	}

	public function disableFaction(string $faction) {
		if ($this->factionValidate($faction)) {
			return $this->db->query("UPDATE factions SET valid = 1 WHERE faction='" . $this->db->real_escape_string($faction) . "'");
		} else {
			return false;
		}
	}

	public function deleteFaction(string $faction) {
		if ($this->factionValidate($faction)) {
			return $this->db->query("DELETE FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
		} else {
			return false;
		}
	}

	public function invitePlayer(Player $player, string $faction){
		if($this->factionValidate($faction)){
			$playername = $player->getName();
			$this->db->query("INSERT INTO users (username, invites) VALUES ('" . $this->db->real_escape_string($playername) . $faction . ");");
		} else {
			return false;
		}
	}

	public function requestPlayer(Player $player, string $faction){
		if($this->factionValidate($faction)){
			$playername = $player->getName();
			$this->db->query("INSERT INTO users (username, requests) VALUES ('" . $this->db->real_escape_string($playername) . $faction . ");");
		} else {
			return false;
		}
	}

	public function joinFaction(Player $player, string $faction) {
		if ($this->factionValidate($faction)) {
			$playername = $player->getName();
			$defrank = $this->rank[2];
			$query = $this->db->query("UPDATE users SET faction = $faction WHERE player='" . $this->db->real_escape_string($playername) . ".");
			$query2 = $this->db->query("UPDATE users SET role = $defrank WHERE player='" . $this->db->real_escape_string($playername) . ".");
			if ($query === true && $query2 === true) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	public function getFactionStat(string $faction) {
		if ($this->factionValidate($faction)) {
			$stat = [];
			$dbquery = $this->db->query("SELECT power FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
			$stat[0] = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			$dbquery = $this->db->query("SELECT kills FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
			$stat[1] = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			$dbquery = $this->db->query("SELECT deaths FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
			$stat[2] = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			return $stat; // returns array of stats rather than just one at a time. One issue would be time - however, with fast server connections, there shouldn't be latancy.
		} else {
			return false;
		}
	}
	
	public function newLand($faction, $x1, $z1, $x2, $z2, string $level) {
		$this->db->query("INSERT OR REPLACE INTO factions (faction, x1, z1, x2, z2, world) VALUES ('" . $this->db->real_escape_string($faction) . $x1 . $z1 . $x2. $z2 . $level . ");");
    }
	
	public function claimLand(Player $player, $faction, $size = 15) {
		$x = floor($player->x);
		$y = floor($player->y);
		$z = floor($player->z);
		$level = $player->getLevel();
		$arm = ($size - 1) / 2;
		$block = new Snow();
		$level->setBlock(new Vector3($x + $arm, $y, $z + $arm), $block);
		$level->setBlock(new Vector3($x - $arm, $y, $z - $arm), $block);
		$this->newLand($faction, $x + $arm, $z + $arm, $x - $arm, $z - $arm, $level->getName());
        return true;
    }

}
