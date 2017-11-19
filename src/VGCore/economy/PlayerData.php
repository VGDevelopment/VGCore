<?php

namespace VGCore\economy;

use pocketmine\Player;
use VGCore\SystemOS;

class PlayerData{

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
			dollars FLOAT
			gems FLOAT
			coins FLOAT
		);")){
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}
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
			$this->db->query("INSERT INTO users (username, coins) VALUES ('".$this->db->real_escape_string($player)."', 5000);");
			$this->db->query("INSERT INTO users (username, dollars) VALUES ('".$this->db->real_escape_string($player)."', 0);");
			$this->db->query("INSERT INTO users (username, gems) VALUES ('".$this->db->real_escape_string($player)."', 10);");
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

	/////////////////////////// DOLLAR CURRENCY ///////////////////////////

	public function getDollars($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$dol = $this->db->query("SELECT dollars FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		$doll = $dol->fetch_array()[0] ?? false;
		$dol->free();
		return $doll;
	}

	public function setDollars($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function addDollars($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = dollars + $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function reduceDollars($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = dollars - $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	/////////////////////////// GEMS CURRENCY ///////////////////////////

	public function getGems($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$dol = $this->db->query("SELECT gems FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		$doll = $dol->fetch_array()[0] ?? false;
		$dol->free();
		return $doll;
	}

	public function setGems($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function addGems($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = gems + $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function reduceGems($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = gems - $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	/////////////////////////// COINS CURRENCY ///////////////////////////

	public function getCoins($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$dol = $this->db->query("SELECT coins FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		$doll = $dol->fetch_array()[0] ?? false;
		$dol->free();
		return $doll;
	}

	public function setCoins($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function addCoins($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = coins + $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function reduceCoins($player, $amount){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = coins - $amount WHERE username='".$this->db->real_escape_string($player)."'");
	}

	/////////////////////////// CURRENCY CONVERSION ///////////////////////////

	// $1 = 50 Gems = 5000 coins
	// 1 Gem = 100 coins
	// $1 = 5000 coins

	/////////////////////////// HIGHER TO LOWER CURRENCIES ///////////////////////////

	public function convertDollarsToGems($player, $dollars){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$dollars = (float) $dollars;
		return $this->db->query("UPDATE users SET gems = gems + $dollars * 50 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function convertDollarsToCoins($player, $dollars){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$dollars = (float) $dollars;
		return $this->db->query("UPDATE users SET coins = coins + $dollars * 5000 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function convertGemsToCoins($player, $gems){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$gems = (float) $gems;
		return $this->db->query("UPDATE users SET coins = coins + $gems * 100 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	/////////////////////////// LOWER TO HIGHER CURRENCIES ///////////////////////////

	// $1 = 50 Gems = 5000 coins
	// 1 Gem = 100 coins
	// $1 = 5000 coins

	public function convertGemsToDollars($player, $gems){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$gems = (float) $gems;
		return $this->db->query("UPDATE users SET dollars = dollars + $gems / 50 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function convertCoinsToDollars($player, $coins){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$coins = (float) $coins;
		return $this->db->query("UPDATE users SET dollars = dollars + $coins / 5000 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	public function convertCoinsToGems($player, $coins){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);
		$coins = (float) $coins;
		return $this->db->query("UPDATE users SET gems = gems + $coins / 100 WHERE username='".$this->db->real_escape_string($player)."'");
	}

	/////////////////////////// DATABASE CLOSE ///////////////////////////

	public function close(){
	 	$this->db->close();
	}
}
