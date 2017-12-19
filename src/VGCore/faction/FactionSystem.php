<?php

namespace VGCore\faction;

use pocketmine\Player;
use VGCore\SystemOS;

class FactionSystem {

	private $factions;
	private $plugin;

	public $faction;
	public $invite = [];

	public $request = [];


	public function __construct(SystemOS $plugin) {
		$this->plugin = $plugin;

		/////////////////////////// DATABASE DETAILS ///////////////////////////

		$this->db = mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1"); // database has host, user, pass, and db name :)

		if ($this->db->connect_error) {
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ". $this->db->connect_error);
			return;
		}
		if (!$this->db->query("CREATE TABLE IF NOT EXISTS factions(
			faction TEXT,
			player TEXT,
			rank TEXT,
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}

		//FLOAT IS FOR NUMERICAL DATA TYPES :stfacepalm:

		if (!$this->db->query("CREATE TABLE IF NOT EXISTS facstats(
			faction TEXT,
			kills TEXT,
			power TEXT,
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}

		$this->request = $request
		$this->invite = $invite;

		/////////////////////////// FACTION DETAILS ///////////////////////////

		public function factionExists(string $faction) {
			$this->factionValidate($faction);
		}

		public function factionValidate(string $name) {
			$result = $this->db->query("SELECT * FROM factions WHERE faction='".$this->db->real_escape_string($name)."'");
			return $result->num_rows > 0 ? true:false;
		}

		public function createFaction(string $faction, Player $leader) {
			$leadername = $leader->getName();
			if (!$this->factionExists($faction)) {
				$this->db->query("INSERT INTO factions (player, faction, rank) VALUES ('".$this->db->real_escape_string($leadername), $faction., 'Leader'););
				$this->db->query("INSERT INTO users (faction, kills) VALUES ('".$this->db->real_escape_string($faction)."', 0);");
				$this->db->query("INSERT INTO users (faction, power) VALUES ('".$this->db->real_escape_string($faction)."', 50);");
				return true;
			}
			return false;
		}

		public function removeFaction(string $faction){
			if ($this->db->query("DELETE FROM factions WHERE faction='".$this->db->real_escape_string($faction)."'") === true) return true;
			return false;
		}

		public function getPlayerFaction(Player $player){
			$playername = $player->getName();
			$dbquery = $this->db->query("SELECT faction FROM factions WHERE player='".$this->db->real_escape_string($playername)."'");
			$fac = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			return $fac;
		}

		public function getFactionKills(string $faction) {
			if($this->factionExists($faction)){
				$dbquery = $this->db->query("SELECT kills FROM facstats WHERE faction='".$this->db->real_escape_string($faction)."'");
				$kills = $dbquery->fetch_array()[0] ?? false;
				$dbquery->free();
				return $kills;
			}
		}

		public function getFactionPower(string $faction) {
			if($this->factionExists($faction)){
				$dbquery = $this->db->query("SELECT power FROM facstats WHERE faction='".$this->db->real_escape_string($faction)."'");
				$power = $dbquery->fetch_array()[0] ?? false;
				$dbquery->free();
				return $power;
			}
		}

		public function isLeader(Player $player){
			$playername = $player->getName();
			$dbquery = $this->db->query("SELECT rank FROM factions WHERE player='".$this->db->real_escape_string($playername)."'");
			$pla = $dbquery->fetch_array()[0] ?? false;
			$dbquery->free();
			return $dbquery["rank"] == "Leader"; // idk if this worrks
		}

		/////////////////////////// INVITATIONS ///////////////////////////

		public function invitePlayer(Player $player, Player $inviter){
			$playername = $player->getName();
			$invitername = $inviter->getName();
			if(!isset($playername, $this->invite)){
				$this->invite[$playername] = $this->getPlayerFaction($inviter);
			}
		}

		/////////////////////////// REQUESTS ///////////////////////////

		public function requestToJoin(Player $player, string $faction){
			$playername = $player->getName();
			if(!isset($playername, $this->request) && $this->factionExists($faction)){
				$this->request[$playername] = $faction;
			}
		}
	}
