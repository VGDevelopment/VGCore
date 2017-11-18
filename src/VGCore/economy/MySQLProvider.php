<?php

namespace VGCore\economy;

use pocketmine\Player;
use VGCore\SystemOS;

class MySQLProvider{

	private $db;
	private $plugin;

	public function __construct(SystemOS $plugin){
		$this->plugin = $plugin;

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
  
	public function accountExists($player){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		$result = $this->db->query("SELECT * FROM users WHERE username='".$this->db->real_escape_string($player)."'");
		return $result->num_rows > 0 ? true:false;
	}
  
	public function createAccount($player, $defaultMoney = 1000.0){
		if($player instanceof Player){
			$player = $player->getName();
		}
		$player = strtolower($player);

		if(!$this->accountExists($player)){
			$this->db->query("INSERT INTO users (username, coins) VALUES ('".$this->db->real_escape_string($player)."', $defaultMoney);");
			$this->db->query("INSERT INTO users (username, dollars) VALUES ('".$this->db->real_escape_string($player)."', 0);");
			$this->db->query("INSERT INTO users (username, gems) VALUES ('".$this->db->real_escape_string($player)."', 0);");
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
  
	public function close(){
	 	$this->db->close();
	}
}
