<?php

namespace VGCore\managers\account; // lol really? you messed up the namespace? ...

use pocketmine\Player;
use VGCore\SystemOS;

class AccountManager{

	private $db;
	private $plugin;

	public function __construct(SystemOS $plugin){
		$this->plugin = $plugin;

		/////////////////////////// DATABASE DETAILS ///////////////////////////

		$this->db = mysqli_connect(" ", " ", " ");

		if($this->db->connect_error){
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ".$this->db->connect_error);
			return;
		}
		if(!$this->db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			rank FLOAT
			ranks FLOAT
			kills FLOAT
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

	public function accountExists($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$result = $this->db->query("SELECT * FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		return $result->num_rows > 0 ? true:false;
	}

	public function createAccount($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(!$this->accountExists($player)){
			$this->db->query("INSERT INTO users (username, rank) VALUES ('".$this->db->real_escape_string($player)."', default);");
			$this->db->query("INSERT INTO users (username, kills) VALUES ('".$this->db->real_escape_string($player)."', 0);");
			$this->db->query("INSERT INTO users (username, deaths) VALUES ('".$this->db->real_escape_string($player)."', 0);");
			return true;
		}
		return false;
	}

	public function removeAccount($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if($this->db->query("DELETE FROM users WHERE username='".$this->db->real_escape_string($player)."'") === true) return true;
		return false;
	}

	/////////////////////////// RANKS MANAGER ///////////////////////////

	public function getRank($player){
		if($player instanceof Player){ // why check instance of? Just define it as Player in the method.
			$player = $player->getName();
		}
		$player = strtolower($player);
		$ran = $this->db->query("SELECT rank FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		$rank = $ran->fetch_array()[0] ?? false; // if you want to use this in other functions - make it a public var
		$ran->free();
		return $rank;
	}

	public function setRank($player, $rank){ 
		if($player instanceof Player){
			$player = $player->getName();
		}
		if($this->rankExists($rank)){
		    $player = strtolower($player);
		    $rank = (float) $rank; // rank is set float here; but in the method, rank is undefined.
		    return $this->db->query("UPDATE users SET rank = $rank WHERE username='".$this->db->real_escape_string($player)."'");
		}
	}


  /////////////////////////// DEATHS MANAGER ///////////////////////////

	public function getDeaths($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$deat = $this->db->query("SELECT deaths FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		$death = $deat->fetch_array()[0] ?? false;
		$deat->free();
		return $death;
	}

	public function addDeath($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		return $this->db->query("UPDATE users SET deaths = deaths + 1 WHERE username='".$this->db->real_escape_string($player)."'");
	}

  public function setDeaths($player, $deaths){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$deaths = (float) $deaths;
		return $this->db->query("UPDATE users SET deaths = $deaths WHERE username='".$this->db->real_escape_string($player)."'");
	}

  /////////////////////////// KILLS MANAGER ///////////////////////////

  public function getKills($player){
    if($player instanceof Player){
      $player = $player->getName();
    }
    $player = strtolower($player);
    $kill = $this->db->query("SELECT kills FROM users WHERE username='".$this->db->real_escape_string($player)."'");
    $kills = $kill->fetch_array()[0] ?? false;
    $kill->free();
    return $kills;
  }

  public function addKill($player){
    if($player instanceof Player){
      $player = $player->getName();
    }
    $player = strtolower($player);
    return $this->db->query("UPDATE users SET kills = kills + 1 WHERE username='".$this->db->real_escape_string($player)."'");
  }

  public function setKills($player, $kills){
    if($player instanceof Player){
      $player = $player->getName();
    }
    $player = strtolower($player);
    $kills = (float) $kills; // what is $kills ? the function doesn't tell me and it isn't a public var so what is it?
    return $this->db->query("UPDATE users SET kills = $kills WHERE username='".$this->db->real_escape_string($player)."'");
  }

	/////////////////////////// DATABASE CLOSE ///////////////////////////

	public function close(){
	 	$this->db->close();
	}
}
