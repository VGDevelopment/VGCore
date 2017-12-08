<?php

namespace VGCore\managers\account;

use pocketmine\Player;
use VGCore\SystemOS;

class AccountManager{

	private $db;
	private $plugin;

	public $lobby = [
    "checkCoin"
    ];
	public $faction = [
    "sendCoin",
    "openShop"
    ];
	public $actions = [$lobby, $faction];

	public $rank;

	public function __construct(SystemOS $plugin){
		$this->plugin = $plugin;

		/////////////////////////// ACTIONS MANAGER ///////////////////////////

		public function allowAction(Player $player, String $action, String $server){
			if($server == "factions"){
				if(in_array($action, $actions[1])){
					return true;
				}else{
					return false;
				}
			}elseif($server == "lobby"){
				if(in_array($action, $actions[0])){
					return true;
				}else{
					return false;
				}
			}
		}

		/////////////////////////// DATABASE DETAILS ///////////////////////////

		$this->db = mysqli_connect(" ", " ", " ");

		if($this->db->connect_error){
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
			return;
		}
		if(!$this->db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			rank FLOAT,
			ranks FLOAT,
			kills FLOAT,
			deaths FLOAT
		);")){
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}
	}

	/////////////////////////// RANK DETAILS ///////////////////////////

	public function rankExists($rank){
		$result = $this->db->query("SELECT * FROM users WHERE ranks='".$this->db->real_escape_string($rank)."'");
		return $result->num_rows > 0 ? true:false;
	}

	public function createRank($rank){
	    if(!$this->rankExists($rank)){
			$this->db->query("INSERT INTO users (ranks) VALUES ('".$this->db->real_escape_string($rank)."'");
			return true;
		}
		return false;
	}

	public function removeRank($rank){
		if($this->db->query("DELETE FROM users WHERE ranks='".$this->db->real_escape_string($rank)."'") === true) return true;
		return false;
	}

	/////////////////////////// PLAYER ACCOUNT DETAILS ///////////////////////////

	public function accountExists(Player $player){
	    $playername = $player->getName();
		$playername2 = strtolower($playername);
		$result = $this->db->query("SELECT * FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		return $result->num_rows > 0 ? true:false;
	}

	public function createAccount(Player $player){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		if(!$this->accountExists($player)){
			$this->db->query("INSERT INTO users (username, rank) VALUES ('".$this->db->real_escape_string($playername2)."', default);");
			$this->db->query("INSERT INTO users (username, kills) VALUES ('".$this->db->real_escape_string($playername2)."', 0);");
			$this->db->query("INSERT INTO users (username, deaths) VALUES ('".$this->db->real_escape_string($playername2)."', 0);");
			return true;
		}
		return false;
	}

	public function removeAccount(Player $player){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		if($this->db->query("DELETE FROM users WHERE username='".$this->db->real_escape_string($playername2)."'") === true) return true;
		return false;
	}

	/////////////////////////// RANKS MANAGER ///////////////////////////

	public function getRank(Player $player){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$ran = $this->db->query("SELECT rank FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$rank = $ran->fetch_array()[0] ?? false; // if you want to use this in other functions - make it a public var
		$ran->free();
		return $rank;
	}

	public function setRank(Player $player, $rank){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		if($this->rankExists($rank)){
		    $rank = (float) $rank;
		    return $this->db->query("UPDATE users SET rank = $rank WHERE username='".$this->db->real_escape_string($playername2)."'");
		}
	}


  /////////////////////////// DEATHS MANAGER ///////////////////////////

	public function getDeaths(Player $player){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$deat = $this->db->query("SELECT deaths FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$death = $deat->fetch_array()[0] ?? false;
		$deat->free();
		return $death;
	}

	public function addDeath(Player $player){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		return $this->db->query("UPDATE users SET deaths = deaths + 1 WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

  public function setDeaths(Player $player, $deaths){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$deaths = (float) $deaths;
		return $this->db->query("UPDATE users SET deaths = $deaths WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

  /////////////////////////// KILLS MANAGER ///////////////////////////

  public function getKills(Player $player){
    $playername = $player->getName();
	$playername2 = strtolower($playername);
    $kill = $this->db->query("SELECT kills FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
    $kills = $kill->fetch_array()[0] ?? false;
    $kill->free();
    return $kills;
  }

  public function addKill(Player $player){
    $playername = $player->getName();
	$playername2 = strtolower($playername);
    return $this->db->query("UPDATE users SET kills = kills + 1 WHERE username='".$this->db->real_escape_string($playername2)."'");
  }

  public function setKills(Player $player, $kills){
    $playername = $player->getName();
	$playername2 = strtolower($playername);
    $kills = (float) $kills; // what is $kills ? the function doesn't tell me and it isn't a public var so what is it?
    return $this->db->query("UPDATE users SET kills = $kills WHERE username='".$this->db->real_escape_string($playername2)."'");
  }

	/////////////////////////// DATABASE CLOSE ///////////////////////////

	public function close(){
	 	$this->db->close();
	}
}
