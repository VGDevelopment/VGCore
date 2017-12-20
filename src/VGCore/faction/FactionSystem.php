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
		$this->db = DB::$db;
	}
	
	public function factionValidate(string $faction) {
		$result = $this->db->query("SELECT * FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
		return $result->num_rows > 0 ? true:false;
	}
	
	public function createFaction(string $faction, Player $leader) {
		$leadername = $leader->getName();
		$roleleader = $this->role[0];
		if (!$this->factionValidate($faction)) {
			$this->db->query("INSERT INTO factions (player, faction) VALUES ('" . $this->db->real_escape_string($leadername) . $faction . ");");
			$this->db->query("INSERT INTO factions (player, rank) VALUES ('" . $this->db->real_escape_string($leadername) . $roleleader . ");");
			$this->db->query("INSERT INTO factions (faction, kill) VALUES ('" . $this->db->real_escape_string($faction) . "', 00000);");
			$this->db->query("INSERT INTO factions (faction, death) VALUES ('" . $this->db->real_escape_string($faction) . "', 00000);");
			$this->db->query("INSERT INTO factions (faction, power) VALUES ('" . $this->db->real_escape_string($faction) . "', 00000);");
			$this->db->query("INSERT INTO factions (faction, valid) VALUES ('" . $this->db->real_escape_string($faction) . "', 0);");
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
	
	public function joinFaction(Player $player, string $faction) {
		if ($this->factionValidate($faction)) {
			$playername = $player->getName();
			$defrank = $this->rank[2];
			$query = $this->db->query("UPDATE factions SET faction = $faction WHERE player='" . $this->db->real_escape_string($playername) . ".");
			$query2 = $this->db->query("UPDATE factions SET rank = $defrank WHERE player='" . $this->db->real_escape_string($playername) . ".");
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
			$dbquery = $this->db->query("SELECT kill FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
			$stat[1] = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			$dbquery = $this->db->query("SELECT death FROM factions WHERE faction='" . $this->db->real_escape_string($faction) . "'");
			$stat[2] = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			return $stat; // returns array of stats rather than just one at a time. One issue would be time - however, with fast server connections, there shouldn't be latancy.
		} else {
			return false;
		}
	}
	
}