<?php

namespace VGCore\faction;

use pocketmine\Player;
use VGCore\SystemOS;

class FactionSystem {

	private $factions;
	private $plugin;
	public $faction;
	public $invite = [];


	public function __construct(SystemOS $plugin) {
		$this->plugin = $plugin;

		/////////////////////////// DATABASE DETAILS ///////////////////////////

		$this->db = mysqli_connect("184.95.55.26", "db_1", "048bda35cb", "db_1"); // database has host, user, pass, and db name :)

		if ($this->db->connect_error) {
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ". $this->db->connect_error);
			return;
		}
		if (!$this->db->query("CREATE TABLE IF NOT EXISTS factions(
			faction FLOAT,
			player FLOAT,
			rank FLOAT
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;

			$this->invite = $invite;
		}

		/////////////////////////// FACTION DETAILS ///////////////////////////

		public function factionExists(string $faction) {
			$this->factionValidate($faction);
		}

		public function factionValidate(string $name) {
			$result = $this->db->query("SELECT * FROM factions WHERE faction='".$this->db->real_escape_string($name)."'");
			return $result->num_rows > 0 ? true:false;
		}

		public function createFaction(string $faction, Player $leader) {
			$factionname = (float) $faction;
			$leadername = (float) $leader->getName();
			if (!$this->factionExists($faction)) {
				$this->db->query("INSERT INTO factions (player, faction, rank) VALUES ('".$this->db->real_escape_string($leadername), $factionname, 'Leader'););
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
				$this->invite = $playername;
				$inviter->sendMessage("You've invited "/ $playername. " to the faction!");
				$player->sendMessage("You've been invited by ".  $invitername. " to join " $this->getPlayerFaction(). "!");
			}
		}
	}
