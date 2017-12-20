<?php

namespace VGCore\economy;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\network\Database as DB;

class EconomySystem {

	private $db;
	private $plugin;
	
	public $amount;
	
	// conversion rates
	public $dollartogem = 50;
	public $gemtocoin = 100;
	public $dollartocoin = 5000; 
	
	public $gemtodollar = 0.02;
	public $cointogem = 0.01;
	public $cointodollar = 0.0002; // can't do mathematic equations because logic is done on the right and public / private are on the left.

	public function __construct(SystemOS $plugin) {
		$this->plugin = $plugin;
		$this->db = DB::$db;
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
		$gem = $dbquery->fetch_array()[0] ?? false;
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
	
	public function sendGem(string $from, string $to, int $amount){
	    $sender = strtolower($from);
	    $receiver = strtolower($to);
	    $amount = (float) $amount;
		$query = $this->db->query("SELECT gems FROM users WHERE username='".$this->db->real_escape_string($sender)."'");
		$check = $query->fetch_array()[0] ?? false;
		$query->free();
		if ($check > $amount) {
			$this->db->query("UPDATE users SET gems = gems - $amount WHERE username='".$this->db->real_escape_string($sender)."'");
			$this->db->query("UPDATE users SET gems = gems + $amount WHERE username='".$this->db->real_escape_string($receiver)."'");
			return true;
		} else {
			return false;
		}
	}

	/////////////////////////// COINS CURRENCY ///////////////////////////

	public function getCoin(Player $player) {
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$dbquery = $this->db->query("SELECT coins FROM users WHERE username='".$this->db->real_escape_string($playername2)."'");
		$coin = $dbquery->fetch_array()[0] ?? false;
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
	
	public function sendCoin(string $from, string $to, int $amount) {
	    $sender = strtolower($from);
	    $receiver = strtolower($to);
	    $amount = (float) $amount;
		$query = $this->db->query("SELECT coins FROM users WHERE username='".$this->db->real_escape_string($sender)."'");
		$check = $query->fetch_array()[0] ?? false;
		$query->free();
		if ($check > $amount) {
			$this->db->query("UPDATE users SET coins = coins - $amount WHERE username='".$this->db->real_escape_string($sender)."'");
			$this->db->query("UPDATE users SET coins = coins + $amount WHERE username='".$this->db->real_escape_string($receiver)."'");
			return true;
		} else {
			return false;
		}
	}

	/////////////////////////// CURRENCY CONVERSION ///////////////////////////

	/////////////////////////// HIGHER TO LOWER CURRENCIES ///////////////////////////

	public function convertDollarToGem(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->dollartogem;
		return $this->db->query("UPDATE users SET gems = gems + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertDollarToCoin(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->dollartocoin;
		return $this->db->query("UPDATE users SET coins = coins + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertGemToCoin(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->gemtocoin;
		return $this->db->query("UPDATE users SET coins = coins + $conv * 100 WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// LOWER TO HIGHER CURRENCIES ///////////////////////////

	public function convertGemToDollar(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->gemtodollar;
		return $this->db->query("UPDATE users SET dollars = dollars + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertCoinToDollar(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->cointodollar;
		return $this->db->query("UPDATE users SET dollars = dollars + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	public function convertCoinToGem(Player $player, $amount){
		$playername = $player->getName();
		$playername2 = strtolower($playername);
		$amount = (float) $amount;
		$conv = $amount * $this->cointogem;
		return $this->db->query("UPDATE users SET gems = gems + $conv WHERE username='".$this->db->real_escape_string($playername2)."'");
	}

	/////////////////////////// DATABASE CLOSE ///////////////////////////

	public function close(){
	 	$this->db->close();
	}
}
