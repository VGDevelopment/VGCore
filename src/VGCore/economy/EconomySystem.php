<?php

namespace VGCore\economy;

use pocketmine\Player;
use VGCore\SystemOS;

class EconomySystem {

	private $db;
	private $plugin;
	
	public $amount;
	
	// conversion rates
	public $dollartogem = 50;
	public $gemtocoin = 100;
	public $dollartocoin = $dollartogem * $gemtocoin; // so you don't have to go to every function to change it. 
	
	public $gemtodollar = 0.02;
	public $cointogem = 0.01;
	public $cointodollar = $cointogem * $gemtodollar;

	public function __construct(SystemOS $plugin) {
		$this->plugin = $plugin;

		/////////////////////////// DATABASE DETAILS ///////////////////////////

		$this->db = mysqli_connect(" ", " ", " ");

		if ($this->db->connect_error) {
			$this->plugin->getLogger()->critical("Could not connect to MySQL server: ". $this->db->connect_error);
			return;
		}
		if (!$this->db->query("CREATE TABLE IF NOT EXISTS users(
			username VARCHAR(20) PRIMARY KEY,
			dollars FLOAT
			gems FLOAT
			coins FLOAT
			);")) {
			$this->plugin->getLogger()->critical("Error creating table: " . $this->db->error);
			return;
		}
	}

	/////////////////////////// PLAYER ACCOUNT DETAILS ///////////////////////////

	public function accountExists(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);

		$result = $this->db->query("SELECT * FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		return $result->num_rows > 0 ? true:false;
	}

	public function createAccount(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);

		if (!$this->accountExists($player)) {
			$this->db->query("INSERT INTO users (username, coins) VALUES ('".$this->db->real_escape_string($playername2)."', 5000);");
			$this->db->query("INSERT INTO users (username, dollars) VALUES ('".$this->db->real_escape_string($playername2)."', 0);");
			$this->db->query("INSERT INTO users (username, gems) VALUES ('".$this->db->real_escape_string($playername2)."', 10);");
			return true;
		}
		return false;
	}

	public function removeAccount(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);

		if ($this->db->query("DELETE FROM users WHERE username='".$this->db->real_escape_string($playername2)."'") === true) return true;
		return false;
	}

	/////////////////////////// DOLLAR CURRENCY ///////////////////////////

	public function getDollar(Player $player) { // shoudn't you be adding method typehints - like this should be int.
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$dbquery = $this->db->query("SELECT dollars FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$doll = $dbquery->fetch_array()[0] ?? false;
		$dbquery->free();
		return $doll;
	}

	public function setDollar(Player $player, $amount) { // shoudn't you be adding method typehints - like this should be int.
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function addDollar(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = dollars + $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function reduceDollar(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playermame);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET dollars = dollars - $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// GEMS CURRENCY ///////////////////////////

	public function getGem(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$dbquery = $this->db->query("SELECT gems FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$gem = $dol->fetch_array()[0] ?? false;
		$dbquery->free();
		return $gem;
	}

	public function setGem(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function addGem(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = gems + $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function reduceGem(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET gems = gems - $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// COINS CURRENCY ///////////////////////////

	public function getCoin(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$dbquery = $this->db->query("SELECT coins FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$coin = $dol->fetch_array()[0] ?? false;
		$dbquery->free();
		return $coin;
	}

	public function setCoin(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function addCoin(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = coins + $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function reduceCoin(Player $player, $amount) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		return $this->db->query("UPDATE users SET coins = coins - $amount WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// CURRENCY CONVERSION ///////////////////////////

	/////////////////////////// HIGHER TO LOWER CURRENCIES ///////////////////////////

	public function convertDollarToGem(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $dollartogem;
		return $this->db->query("UPDATE users SET gems = gems + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertDollarToCoin(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $dollartocoin;
		return $this->db->query("UPDATE users SET coins = coins + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertGemToCoin(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $gemtocoin;
		return $this->db->query("UPDATE users SET coins = coins + $conv * 100 WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// LOWER TO HIGHER CURRENCIES ///////////////////////////

	public function convertGemToDollar(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $gemtodollar;
		return $this->db->query("UPDATE users SET dollars = dollars + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertCoinToDollar(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $cointodollar;
		return $this->db->query("UPDATE users SET dollars = dollars + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertCoinToGem(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $cointogem;
		return $this->db->query("UPDATE users SET gems = gems + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// DATABASE CLOSE ///////////////////////////

	public function close(){
	 	$this->db->close();
	}
}
