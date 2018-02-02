<?php

namespace VGCore\faction;

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\SystemOS;

use VGCore\network\{
	Database as DB,
	Slack
};

use VGCore\gui\lib\UIDriver;

use VGCore\faction\FactionWar;

class FactionSystem {
	
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
				$query = self::$db->query("SELECT " . $v . " FROM factions where faction='" . self::$db->real_escape_string($lowerfaction) . "'");
				if ($query !== null && $query !== false) {
					$stat[] = $query->fetch_array()[0] ?? false;
					$query->free();
				} else if ($query === false){
					$stat[] = "[/ERROR getting DATA/]";
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
				$query = self::$db->query("UPDATE users SET faction ='" . self::$db->real_escape_string($lowerfaction) . "' WHERE username='" . self::$db->real_escape_string($lowerleader) . "'");
				if (!$query) {
					return false;
				}
				$query = self::$db->query("UPDATE users SET factionrole ='" . self::$rank[0] . "' WHERE username='" . self::$db->real_escape_string($lowerleader) . "'");
				if (!$query) {
					return false;
				}
				return true;
			} else {
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
	
	public static function addToFaction(string $faction, string $name, int $type = 0): bool {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$lowername = strtolower($name);
			$query = self::$db->query("UPDATE users SET faction = '" . self::$db->real_escape_string($lowerfaction) . "' where username='" . self::$db->real_escape_string($lowername) . "'");
			if ($query) {
				$factiondata = self::factionStat($faction);
				$leadername = strtolower($factiondata[3]);
				$leader = self::$server->getPlayer($leadername);
				$player = self::$server->getPlayer($name);
				$message = [];
				switch ($type) {
					case 0:
						$message["leader"] = Chat::YELLOW . "You've " . Chat::GREEN . Chat::BOLD . "ACCEPTED" . Chat::RESET . Chat::YELLOW . " a player with the name of " . Chat::GREEN . $name . Chat::EOL . 
						Chat::YELLOW . " into your faction.";
						$message["player"] = Chat::YELLOW . "You've been " . Chat::GREEN . Chat::BOLD . "ACCEPTED" . Chat::RESET . Chat::YELLOW . " into " . Chat::GREEN . $lowerfaction . Chat::YELLOW . " by " . 
						Chat::GREEN . $leadername . Chat::YELLOW . "!";
						break;
					case 1:
						$message["leader"] = Chat::YELLOW . "A player with the name " . Chat::GREEN . $name . Chat::YELLOW . " has " . Chat::GREEN . Chat::BOLD . "ACCEPTED" . Chat::EOL . 
						Chat::RESET . Chat::YELLOW . "your invitation to join your faction.";
						$message["player"] = Chat::YELLOW . "You've succesfully " . Chat::GREEN . Chat::BOLD . "ACCEPTED" . Chat::RESET . Chat::YELLOW . " the faction invitation sent to you by " . Chat::EOL . 
						Chat::GREEN . $lowerfaction . Chat::YELLOW . "!";
						break;
				}
				if (array_key_exists($lowerfaction, self::$request)) {
					if (in_array($name, self::$request[$lowerfaction])) {
						$key = array_search($name, self::$request[$lowerfaction]);
						unset(self::$request[$lowerfaction][$key]);
					}
				}
				if (array_key_exists($lowername, self::$invite)) {
					if (in_array($lowerfaction, self::$invite[$lowername])) {
						$key = array_search($lowerfaction, self::$invite[$lowername]);
						unset(self::$invite[$lowername][$key]);
					}
				}
				$leader->sendMessage($message["leader"]);
				if ($player === null) {
					return true;
				}
				$player->sendMessage($message["player"]);
				return true;
			} else {
				return false;
			}
		}
	}
	
	public static function requestFaction(string $faction, string $name): int {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$factiondata = self::factionStat($faction);
			$leadername = strtolower($factiondata[3]);
			$leader = self::$server->getPlayer($leadername);
			if (array_key_exists($lowerfaction, self::$request)) {
				if (in_array($name, self::$request[$lowerfaction])) {
					return 2;
				}
			}
			self::$request[$lowerfaction][] = $name;
			if ($leader === null) {
				return 1;
			}
			$leader->sendMessage(Chat::YELLOW . "A player using the name " . Chat::GREEN . $name . Chat::YELLOW . " has decided to request your faction." . Chat::EOL . 
			"Do " . Chat::RED . "/f" . Chat::YELLOW . " to open up the request manager and " . Chat::GREEN . Chat::BOLD . "ACCEPT" . Chat::RESET . Chat::YELLOW . " or " . 
			Chat::RED . Chat::BOLD .  "DENY" . Chat::RESET . Chat::EOL . Chat::YELLOW . "the request.");
			return 1;
		} else {
			return 0;
		}
	}
	
	public static function invitePlayer(string $faction, string $name): int {
		$check = DB::checkUser($name);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$factiondata = self::factionStat($faction);
			$leadername = strtolower($factiondata[3]);
			$lowername = strtolower($name);
			$player = self::$server->getPlayer($name);
			if (array_key_exists($lowername, self::$invite)) {
				if (in_array($lowerfaction, self::$invite[$lowername])) {
					return 2;
				}
			}
			$check = self::ignInFaction($name);
			if ($check === true) {
				return 3;
			}
			self::$invite[$lowername][] = $lowerfaction;
			if ($player === null) {
				return 1;
			}
			$player->sendMessage(Chat::YELLOW . "A faction going by the name " . Chat::GREEN . $lowerfaction . Chat::YELLOW . " has decided to invite you into your faction." . Chat::EOL . 
			"Do " . Chat::RED . "/f" . Chat::YELLOW . " to open up the faction user invite manager and " . Chat::GREEN . Chat::BOLD . "ACCEPT" . Chat::RESET . cHAT::YELLOW . " or " . 
			Chat::RED  . Chat::BOLD . "DENY" . Chat::RESET . Chat::EOL . Chat::YELLOW . "the invitation.");
			return 1;
		} else {
			return 0;
		}
	}
	
	public static function getInvite(string $name): array {
		$check = DB::checkUser($name);
		if ($check === true) {
			$lowername = strtolower($name);
			$check = array_key_exists($lowername, self::$invite);
			if ($check === true) {
				if (empty(self::$invite[$lowername])) {
					return [];
				} else {
					return self::$invite[$lowername];
				}
			} else {
				return [];
			}
		} else {
			return [];
		}
	}
	
	public static function getRequest(string $faction): array {
		$check = self::validateFaction($faction);
		if ($check === true) {
			$lowerfaction = strtolower($faction);
			$check = array_key_exists($lowerfaction, self::$request);
			if ($check === true) {
				if (empty(self::$request[$lowerfaction])) {
					return [];
				} else {
					return self::$request[$lowerfaction];
				}
			} else {
				return [];
			}
		} else {
			return [];
		}
	}
	
	public static function claimLand(string $faction, Player $player): bool {
		$check = self::inFaction($player);
		if ($check === true) {
			$data = self::getLand();
			if (count($data) > 0) {
				
			}
			$pos = [];
			$size = 8 * self::CHUNK;
			$vectorcalc = $size / 2;
			$pos["x1"] = $player->x + $vectorcalc;
			$pos["x2"] = $player->x - $vectorcalc;
			$pos["z1"] = $player->z + $vectorcalc;
			$pos["z2"] = $player->z - $vectorcalc;
			$claim = [
				(string)$pos["x1"] . ":" . (string)$pos["z1"],
				(string)$pos["x2"] . ":" . (string)$pos["z2"]
			];
			$lowerfaction = strtolower($faction);
			return self::updateLand($lowerfaction, $claim);
		}
	}
	
	public static function updateLand(string $faction, array $claim): bool {
		$data = implode(", ", $claim);
		$query = self::$db->query("UPDATE factions SET ldata = '" . self::$db->real_escape_string($data) . "' WHERE faction='" . self::$db->real_escape_string($faction) . "'");
		if ($query === true) {
			return true;
		} else {
			return false;
		}
	}
	
	public static function getLand(string $faction = null): array {
		$check = false;
		if ($faction !== null) {
			$check = self::validateFaction($faction);
		}
		if ($check === true || $faction === null) {
			if ($faction !== null) {
				$lowerfaction = strtolower($faction);
				$query = self::$db->query("SELECT ldata FROM factions WHERE faction='" . self::$db->real_escape_string($lowerfaction) . "'");
			} else {
				$query = self::$db->query("SELECT ldata FROM factions");
			}
			$result = $query->fetchArray();
			$query->free();
			$rarray = [];
			foreach ($result as $i => $v) {
				$rarray[$i] = explode(", ", $v);
			}
			return $rarray;
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