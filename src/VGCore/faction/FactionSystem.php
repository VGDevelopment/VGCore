<?php

namespace VGCore\faction;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\network\Database as DB;

use VGCore\gui\lib\UIDriver;

use VGCore\faction\FactionWar;

class FactionSystem {
	
	const CHUNK_X = 8;
	const CHUNK_Y = 8;
	const CHUNK = 2;
	
	private static $namesuggestion1 = [
		"Alpha",
		"Anti",
		"Atomic",
		"Ant",
		"Blizzard",
		"Bat",
		"Boxing",
		"Big",
		"Big",
		"Cat",
		"Cool",
		"Catastrophic",
		"Dangerous",
		"Diatomic",
		"Dinosize",
		"Ender",
		"Excalibur",
		"Extreme",
		"Easter",
		"Eager",
		"Flippin",
		"Floating",
		"Fastest",
		"Fun",
		"Fighting",
		"Furious",
		"Giga",
	];
	
	private static $namesuggestion2 = [
		"Giant",
		"Hacker",
		"Boys",
		"Girls",
		"Bunny",
		"Lion",
		"Dinosaur",
		"DinoKing"
	];
	
	private static $db;
	
	private static $rank = [
		"Leader",
		"Officer",
		"Member"
	];
	private static $sessionfaction = [];
	
	private static $os;
	private static $server;
	
	public static $request = [];
	public static $invite = [];
	
	// >>> onEnable()
	
	public static function start(SystemOS $os): void {
		self::$db = DB::getDatabase();
		self::$os = $os;
		self::$server = $os->getServer();
	}
	
	// >>> Get Player's Faction
	
	public static function getPlayerFaction(Player $player): string {
		$playername = $player->getName();
		return self::getIGNFaction($playername);
	}
	
	// >>> Subset of getPlayerFaction()
	
	public static function getIGNFaction(string $name): string {
		$lowname = strtolower($name);
		$query = self::$db->query("SELECT faction FROM users WHERE username='" . self::$db->real_escape_string($lowname) . "'");
		if ($query !== null) {
			$faction = $query->fetch_array()[0] ?? false;
			if ($faction) {
				$query->free();
				if ($faction !== "") {
					return $faction;
				} else {
					return "[/No Faction Found/]";
				}
			} else {
				return "[/Internal System Error/]";
			}
		} else {
			return "[/Internal System Error/]";
		}
	}
	
	// >>> Check if Player is in Faction
	
	public static function inFaction(Player $player): bool {
		$playername = $player->getName();
		$check = self::ignInFaction($playername);
		if ($check === false) {
			return false;
		} else {
			return true;
		}
	}
	
	// >>> Subset of inFaction()
	
	public static function ignInFaction(string $name): bool {
		$query = self::getIGNFaction($name);
		if ($query === "[/No Faction Found/]" || $query === "[/Internal System Error/]") {
			return false;
		} else {
			return true;
		}
	}
	
	// >>> Checks if Faction exists
	
	public static function validateFaction(string $faction): bool {
		$lowerfaction = strtolower($faction);
		if (in_array($lowerfaction, self::$sessionfaction)) {
			return true;
		} else {
			$query = self::$db->query("SELECT * FROM factions WHERE faction='" . self::$db->real_escape_string($lowerfaction) . "'");
			if ($query->num_rows > 0) {
				self::$sessionfaction[] = $lowerfaction;
				$query->free();
				return true;
			} else {
				$query->free();
				return false;
			}
		}
	}
	
	// >>> Gets Data about the faction.
	
	public static function factionStat(string $faction): array {
		$stat = [];
		$check = self::validateFaction($faction);
		if ($check === true) {
			$reqstat =[
				"power",
				"kills",
				"deaths",
				"leader"
			];
			$lowerfaction = strtolower($faction);
			foreach ($reqstat as $i => $v) {
				$query = self::$db->query("SELECT" . $i . "FROM factions where factions='" . self::$db->real_escape_string($lowerfaction) . "'");
				if ($query !== null) {
					$stat[] = $query->fetch_array()[0] ?? false;
					$query->free();
				} else {
					$stat[] = "[/ERROR getting DATA/]";
					$query->free();
				}
			}
			return $stat;
		} else {
			$i = 1;
			while ($i < 4) {
				$stat[] = "[/ERROR getting FACTION/]";
				$i++;
			}
			return $stat;
		}
	}
	
	public static function createFaction(string $faction, string $leader): bool {
		$check = self::validateFaction($faction);
		if (!$check) {
			$lowerfaction = strtolower($faction);
			$lowerleader = strtolower($leader);
			$query = self::$db->query("INSERT INTO factions (faction, leader, power, kills, deaths, valid) VALUES ('" . self::$db->real_escape_string($lowerfaction) . "',
				'" . self::$db->real_escape_string($lowerleader) . "',
				'0000',
				'0000',
				'0000',
				'0'
			);");
			if ($query) {
				// $query->free();
				$query = self::$db->query("UPDATE users SET faction ='" . self::$db->real_escape_string($lowerfaction) . "' WHERE username='" . self::$db->real_escape_string($lowerleader) . "'");
				if (!$query) {
					$query->free();
					return false;
				}
				// $query->free();
				return true;
			} else {
				// $query->free();
				return false;
			}
		} else {
			return false;
		}
	}
	
	public static function createPlayerFaction(string $faction, Player $leader): bool {
		$leadername = $leader->getName();
		return self::createFaction($faction, $leadername);
	}
	
	public static function requestFaction(string $faction, string $name, Player $player = null): bool {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$factiondata = self::factionStat($faction);
			$leadername = strtolower($factiondata[3]);
			if ($player === null) {
				$player = self::$server->getPlayer($name);
			}
			$leader = self::$server->getPlayer($leadername);
			self::$request[$lowerfaction][] = $name;
			UIDriver::showUIbyID(self::$os, SystemOS::$uis['---'], $leader);
			return true;
		}
	}
	
	public static function invitePlayer(string $faction, string $name, Player $player = null): bool {
		$check = DB::checkUser($name);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$factiondata = self::$factionStat($faction);
			$leadername = strtolower($factiondata[3]);
			if ($player === null) {
				$player = self::$server->getPlayer($name);
			}
			$leader = self::$server->getPlayer($leadername);
			self::$invite[$lowerfaction][] = $name;
			UIDriver::showUIbyID(self::$os, SystemOS::$uis['---'], $player);
			return true;
		}
	}
	
	public static function getInvite(string $faction): array {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$check = array_key_exists($lowerfaction, self::$invite);
			if ($check === true) {
				if (empty(self::$invite[$lowerfaction])) {
					return [182];
				} else {
					return self::$invite[$lowerfaction];
				}
			} else {
				return [182];
			}
		}
	}
	
	public static function getRequest(string $faction): array {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$check = array_key_exists($lowerfaction, self::$invite);
			if ($check === true) {
				if (empty(self::$invite[$lowerfaction])) {
					return [182];
				} else {
					return self::$invite[$lowerfaction];
				}
			} else {
				return [182];
			}
		}
	}
	
	public static function claimLand(string $faction, Player $player): void {
		$check = self::inFaction($player);
		if ($check === true) {
			$pos = [];
			$claim = [];
			$pos["x"] = (int)$player->getX();
			$pos["z"] = (int)$player->getZ();
			$claim["x1"] = $pos["x"] - (self::CHUNK * self::CHUNK_X / 2);
			$claim["x2"] = $pos["x"] + (self::CHUNK * self::CHUNK_X / 2);
			$claim["z1"] = $pos["z"] - (self::CHUNK * self::CHUNK_Z / 2);
			$claim["z2"] = $pos["z"] + (self::CHUNK * self::CHUNK_Z / 2);
			$claim["pos1"] = [
				$claim["x1"],
				$claim["z1"],
				1
			];
			$claim["pos2"] = [
				$claim["x2"],
				$claim["z2"],
				256
			];
			$claim["loc"] = [
				$claim["pos1"],
				$claim["pos2"]
			];
			$lowerfaction = strtolower($faction);
			self::updateLand($lowerfaction, $claim);
		}
	}
	
	public static function updateLand(string $faction, array $claim): void {
		$loc = $claim["loc"];
		$pos1 = $loc["pos1"];
		$pos2 = $loc["pos2"];
		$x1 = $pos1[0];
		$x2 = $pos2[0];
		$z1 = $pos1[1];
		$z2 = $pos2[1];
		$x = [
			1 => $x1,
			2 => $x2
		];
		$z = [
			1 => $z1,
			2 => $z2
		];
		foreach ($x as $i => $v) {
			$query = self::$db->query("UPDATE factions SET landx" . $i . " = " . $v . " WHERE faction='" . self::$db->real_escape_string($faction) . "'");
			$query->free();
		}
		foreach ($z as $i => $v) {
			$query = self::$db->query("UPDATE factions SET landz" . $i . " = " . $v . " WHERE faction='" . self::$db->real_escape_string($faction) . "'");
			$query->free();
		}
	}
	
	public static function getAllFactionMember(string $faction): array {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$query = self::$db->query("SELECT username FROM users WHERE faction='" . self::$db->real_escape_string($lowerfaction) . "'");
			$queryarray = $query->fetchArray();
			foreach ($queryarray as $qa) {
				$playerlist = [];
				$playerlist[] = self::$server->getPlayer($qa);
				$query->free();
			}
			return $playerlist;
		}
	}
	
	public static function addKill(string $faction, int $kill): void {
		$check = self::validateFaction($faction);
		if ($check === true) {
			
		}
	}
	
	public static function getNameSuggestion(string $name): array {
		$i = 0;
		$namelist = [];
		while ($i > 3) {
			$string = self::$namesuggestion1[array_rand($namesuggestion1, 1)] . self::$namesuggestion2[array_rand($namesuggestion2, 1)];
			if (!(self::validateFaction($string))) {
				$namelist[] = $string;
				$i++;
			}
		}
		return $namelist;
	}
	
}