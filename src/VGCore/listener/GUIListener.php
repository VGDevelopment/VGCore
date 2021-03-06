<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\economy\EconomySystem;

use VGCore\faction\FactionSystem as FS;

use VGCore\SystemOS;

use VGCore\gui\lib\UIDriver;
use VGCore\gui\lib\window\CustomForm;
use VGCore\gui\lib\element\Label;
use VGCore\gui\lib\element\Dropdown;

use VGCore\listener\event\UICloseEvent;
use VGCore\listener\event\UIDataReceiveEvent;

use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;
use VGCore\network\Database as DB;

use VGCore\store\Store;
use VGCore\store\ItemList as IL;

use VGCore\lobby\music\MusicPlayer;

use VGCore\form\{
	EconomyUI,
	FactionUI,
	StoreUI
};

use VGCore\spawner\SpawnerAPI;

class GUIListener implements Listener {

    public $plugin;

	public static $coindata;
	
	private static $cachedresponse = [];

    public function __construct(SystemOS $plugin) {
		$this->os = $plugin;
	}

	public function onPacket(DataPacketReceiveEvent $event) {
		$packet = $event->getPacket();
		$player = $event->getPlayer();
		switch ($packet::NETWORK_ID) {
			case ModalFormResponsePacket::NETWORK_ID: {
				$this->handleModalFormResponse($packet, $player);
				$packet->reset();
				$event->setCancelled(true);
				break;
			}
			case ServerSettingsRequestPacket::NETWORK_ID: {
				$this->handleServerSettingsRequestPacket($packet, $player);
				$packet->reset();
				$event->setCancelled(true);
				break;
			}
			case ServerSettingsResponsePacket::NETWORK_ID: {
				$this->handleServerSettingsResponsePacket($packet, $player);
				$packet->reset();
				$event->setCancelled(true);
				break;
			}
		}
	}

	public function handleModalFormResponse(ModalFormResponsePacket $packet, Player $player): bool {
		$event = new UIDataReceiveEvent($this->os, $packet, $player);
		if (is_null($event->getData())) $event = new UICloseEvent($this->os, $packet, $player);
		Server::getInstance()->getPluginManager()->callEvent($event);
		return true;
	}

	public function handleServerSettingsResponsePacket(ServerSettingsResponsePacket $packet, Player $player): bool {
		$event = new UIDataReceiveEvent($this->os, $packet, $player);
		if (is_null($event->getData())) $event = new UICloseEvent($this->os, $packet, $player);
		Server::getInstance()->getPluginManager()->callEvent($event);
		return true;
	}

	public function handleServerSettingsRequestPacket(ServerSettingsRequestPacket $packet, Player $player): bool {
		$ui = UIDriver::getPluginUI($this->os, SystemOS::$uis['serverSettingsUI']);
		$pk = new ServerSettingsResponsePacket();
		$pk->formId = SystemOS::$uis['serverSettingsUI'];
		$pk->formData = json_encode($ui);
		$player->dataPacket($pk);
		return true;
	}

	public function onUIDataReceiveEvent(UIDataReceiveEvent $event) {
		$player = $event->getPlayer();
		$p = $event->getPlugin();
		$economy = new EconomySystem($p);
		switch ($id = $event->getID()) {
			case SystemOS::$uis['serverSettingsUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				break;
			}
			case SystemOS::$uis['settingsUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§cPets': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['petUI'], $event->getPlayer());
						break;
					}
					/*
					case '§cMusic': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['musicUI'], $event->getPlayer());
						break;
					}
					*/
				}
				break;
			}
			case SystemOS::$uis['petUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$pet = $response[0];
				$ppet = $this->os->getPlayerPet($player);
				$petcount = count($ppet);
				if ($pet === "OFF") {
					foreach ($ppet as $pet) {
						$this->os->destroyPet($pet->getName(), $player);
					}
				}
				if ($pet === "EnderDragon" || $pet === "Baby Ghast") {
					if ($pet === "Baby Ghast") {
						$pet = "Ghast";
					}
					$this->os->makePet($pet, $player, $player->getName() . "'s " . $pet . " Pet", 0.3);
				} else {
					$this->os->makePet($pet, $player, $player->getName() . "'s " . $pet . " Pet", 3.25);
				}
				break;
			}
			/*
			case SystemOS::$uis['musicUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$music = $response[0];
				if ($music === "OFF") {
					return;
				}
				$mp = new MusicPlayer($p);
				$mp->play($event->getPlayer());
				break;
			}
			*/
			case SystemOS::$uis['economyUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§2Check §6Coins': {
						EconomyUI::createShowCoinUI($player);
						UIDriver::showUIbyID($p, SystemOS::$uis['checkCoinWindowUI'], $player);
						break;
					}
					case '§2Send §6Coins': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['sendCoinUI'], $event->getPlayer());
						break;
					}
					case '§6§lSHOP': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopMainMenuUI'], $event->getPlayer());
						break;
					}
				}
				break;
			}
			case SystemOS::$uis['sendCoinUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$string = $response[1];
				$amount = (int)$string;
				$sendto = $response[2];
				$economy = new EconomySystem($event->getPlugin());
				$player = $event->getPlayer();
				$sender = $player->getName();
				$valid = DB::checkUser($sendto);
				if ($valid === true) {
					$send = $economy->sendCoin($sender, $sendto, $amount);
					if ($send === true) {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
					} else if ($send === false) {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
					}
				} else if ($valid === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['customEnchantUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$string = $response[0];
				$plugin = $event->getPlugin();
				$player = $event->getPlayer();
				$playerinv = $player->getInventory();
				$playeritemhand = $playerinv->getItemInHand();
				$enchantment = $plugin->setEnchantment($playeritemhand, $string, 1, true, $player);
				$playerinv->setItemInHand($enchantment);
			}
			case SystemOS::$uis['fManagerUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§aJoin a §cFACTION': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['fJoinUI'], $event->getPlayer());
						break;
					}
					case '§aCreate a §cFACTION': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['fCreateUI'], $event->getPlayer());
						break;
					}
					case '§aManage your §cFACTION': {
						if (FS::inFaction($player)) {
							UIDriver::showUIbyID($p, SystemOS::$uis['fSettingsUI'], $player);
						} else {
							$player->sendMessage(Chat::RED . "Sorry, to access that, you need to be in a faction.");
						}
						break;
					}
					case '§aAccept Invites': {
						if (FS::inFaction($player)) {
							$player->sendMessage(Chat::RED . "Sorry, invites are disabled if you're in a faction.");
							return;
						}
						$name = $player->getName();
						FactionUI::createUserInviteManagerUI($name);
						UIDriver::showUIbyID($p, SystemOS::$uis['fInviteManagerUI'], $player);
					}
				}
				break;
			}
			case SystemOS::$uis['fCreateUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$string = $response[1];
				if (preg_match("~[0-9]~", $string)) {
					$player->sendMessage(Chat::RED . "Your faction name must not include numbers.");
					return;
				}
				if (strlen($string) > 30 || strlen($string) < 5) {
					$player->sendMessage(Chat::RED . "Sorry, that is too long/short of a name. Please stay below 30 characters and above 5.");
					return;
				}
				if (FS::inFaction($player)) {
					$player->sendMessage(Chat::RED . "You're already in a faction. You need to leave this one to create your own.");
					return;
				}
				if (FS::validateFaction($string)) {
					$player->sendMessage(Chat::RED . "Sorry, a faction with that name already exists.");
					return;
				}
				$query = FS::createPlayerFaction($string, $player);
				if ($query === true) {
					$player->sendMessage(Chat::GREEN . "Your faction has been created succesfully!");
					return;
				} else {
					$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
					return;
				}
				break;
			}
			case SystemOS::$uis['fJoinUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$string = $response[0];
				if (preg_match("~[0-9]~", $string)) {
					$player->sendMessage(Chat::RED . "Sorry, no faction name can include numbers.");
					return;
				}
				if (strlen($string) > 30) {
					$player->sendMessage(Chat::RED . "Sorry, no faction name can be longer than 30 characters");
					return;
				}
				if (FS::inFaction($player)) {
					$player->sendMessage(Chat::RED . "Sorry, but you need to leave your previous faction to join a new one.");
					return;
				}
				if (FS::validateFaction($string)) {
					$name = $player->getName();
					$query = FS::requestFaction($string, $name);
					if ($query === 1) {
						$player->sendMessage(Chat::GREEN . "You've requested " . Chat::YELLOW . $string . Chat::GREEN . " succesfully!");
						return;
					} else if ($query === 2) {
						$player->sendMessage(Chat::RED . "Sorry, we can't allow you to spam a specific faction's inbox. Please understand.");
						return;
					} else {
						$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
						return;
					}
				}
				break;
			}
			case SystemOS::$uis['fSettingsUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§cClick to accept some' . Chat::EOL . 'join requests.': {
						$faction = FS::getPlayerFaction($player);
						FactionUI::createRequestManagerUI($faction);
						UIDriver::showUIbyID($p, SystemOS::$uis['fRequestManagerUI'], $player);
						break;
					}
					case '§cClick to invite a player' . Chat::EOL . 'into your faction.': {
						UIDriver::showUIbyID($p, SystemOS::$uis['fInviterUI'], $player);
						break;
					}
					case '§cClick to view info about' . Chat::EOL . 'your faction.': {
						if (FS::inFaction($player) === false) {
							$player->sendMessage(Chat::RED . "Sadly, we can't get data about your faction if you aren't in a faction. Join or create a faction first!");
							return;
						}
						$faction = FS::getPlayerFaction($player);
						FactionUI::createFactionDataUI($faction, $player);
						UIDriver::showUIbyID($p, SystemOS::$uis['fInfoUI'], $player);
						break;
					}
					case '§c[DELETE]' . Chat::EOL . 'Faction': {
						$faction = FS::getPlayerFaction($player);
						$query = FS::factionStat($faction);
						$name = $player->getName();
						$lowername = strtolower($name);
						if ($query[3] !== $lowername) {
							return;
						}
						$query = FS::disbandFaction($faction);
						if ($query === true) {
							$player->sendMessage(Chat::YELLOW . "We've deleted your faction - so sad to see you go!");
							return;
						} else if ($query === false) {
							$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
							return;
						}
						break;
					}
				}
				break;
			}
			case SystemOS::$uis['fRequestManagerUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$name = $response[0];
				if ($name === "§ePick a name") {
					return;
				}
				if (FS::ignInFaction($name)) {
					$player->sendMessage(Chat::RED . "It looks like that player joined a faction before you could accept the request. Bad luck.");
					return;
				}
				$faction = FS::getPlayerFaction($player);
				$query = FS::addToFaction($faction, $name);
				if ($query === false) {
					$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
					return;
				}
				break;
			}
			case SystemOS::$uis['fInviterUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$name = $response[0];
				if (strlen($name) > 20) {
					$player->sendMessage(Chat::RED . "Sorry, no username name can be longer than 20 characters");
					return;
				}
				$check = DB::checkUser($name);
				if ($check === false) {
					$player->sendMessage(Chat::RED . "Sorry, no user with that username exists in our database.");
					return;
				}
				$faction = FS::getPlayerFaction($player);
				$query = FS::invitePlayer($faction, $name);
				if ($query === 1) {
					$player->sendMessage(Chat::GREEN . "You've invited " . Chat::YELLOW . $name . Chat::GREEN . " succesfully!");
					return;
				} else if ($query === 2) {
					$player->sendMessage(Chat::RED . "Sorry, we can't allow you to spam a specific user's inbox. Please understand.");
					return;
				} else if ($query === 3) {
					$player->sendMessage(Chat::RED . "Looks like that player already belongs to a faction. Invites have been disabled for him.");
					return;
				} else {
					$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
					return;
				}
			}
			case SystemOS::$uis['fInviteManagerUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$username = $player->getName();
				$faction = $response[0];
				if ($faction === "§ePick a name") {
					return;
				}
				if (FS::ignInFaction($username)) {
					$player->sendMessage(Chat::RED . "It looks like you got to this page by a mistake. Please notify support.");
					return;
				}
				$query = FS::addToFaction($faction, $username, 1);
				if ($query === false) {
					$player->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
					return;
				}
				break;
			}
			case SystemOS::$uis['shopMainMenuUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§c§lITEMS': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopItemMenuUI'], $event->getPlayer());
						break;
					}
					case '§c§lBLOCKS': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopBlockMenuUI'], $event->getPlayer());
						break;
					}
					case '§c§lSPAWNERS': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSpawnerMenuUI'], $event->getPlayer());
						break;
					}
				}
				break;
			}
			case SystemOS::$uis['shopBlockMenuUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§c§lDirt': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDirtUI'], $event->getPlayer());
						break;
					}
					case '§c§lCobblestone': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopCobblestoneUI'], $event->getPlayer());
						break;
					}
					case '§c§lOak Wood': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopNormalWoodUI'], $event->getPlayer());
						break;
					}
					case '§c§lIron Ore': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopIOreUI'], $event->getPlayer());
						break;
					}
					case '§c§lGold Ore': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGOreUI'], $event->getPlayer());
						break;
					}
					case '§c§lDiamond Ore': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDOreUI'], $event->getPlayer());
						break;
					}
					case '§c§lCoal Ore': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopCOreUI'], $event->getPlayer());
						break;
					}
					case '§c§lGlass': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGlassUI'], $event->getPlayer());
						break;
					}
					case '§c§lChest': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopChestUI'], $event->getPlayer());
						break;
					}
					case '§c§lCrafting Table': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopCraftingTableUI'], $event->getPlayer());
						break;
					}
					case '§c§lFurnace': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopFurnaceUI'], $event->getPlayer());
						break;
					}
				}
			}
			case SystemOS::$uis['shopItemMenuUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§c§lWooden Sword': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopWSwordUI'], $event->getPlayer());
						break;
					}
					case '§c§lWooden Axe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopWAxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lWooden Pickaxe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopWPickaxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lWooden Shovel': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopWShovelUI'], $event->getPlayer());
						break;
					}
					case '§c§lStone Sword': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSSwordUI'], $event->getPlayer());
						break;
					}
					case '§c§lStone Axe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSAxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lStone Pickaxe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSPickaxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lStone Shovel': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSShovelUI'], $event->getPlayer());
						break;
					}
					case '§c§lIron Sword': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopISwordUI'], $event->getPlayer());
						break;
					}
					case '§c§lIron Axe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopIAxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lIron Pickaxe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopIPickaxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lIron Shovel': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopIShovelUI'], $event->getPlayer());
						break;
					}
        			case '§c§lGold Sword': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGSwordUI'], $event->getPlayer());
						break;
					}
					case '§c§lGold Axe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGAxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lGold Pickaxe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGPickaxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lGold Shovel': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopGShovelUI'], $event->getPlayer());
						break;
					}
        			case '§c§lDiamond Sword': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDSwordUI'], $event->getPlayer());
						break;
					}
					case '§c§lDiamond Axe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDAxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lDiamond Pickaxe': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDPickaxeUI'], $event->getPlayer());
						break;
					}
					case '§c§lDiamond Shovel': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopDShovelUI'], $event->getPlayer());
						break;
					}
				}
				break;
			}
			case SystemOS::$uis['shopSpawnerMenuUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$r = substr($response, 6);
				$check = array_key_exists($r, IL::SPAWNER);
				if ($check === true) {
					$name = $player->getName();
					$lowername = strtolower($name);
					self::$cachedresponse[$lowername] = $r;
					StoreUI::createSpawnerBuyOffer($r);
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['shopSpawnerBuyOffer'], $event->getPlayer());
					return;
				} else {
					$player->sendMessage(Chat::RED . "An unrecoverable error occured with the Store System. Please contact support.");
					return;
				}
				break;
			}
			case SystemOS::$uis['shopSpawnerBuyOffer']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$name = $player->getName();
				$lowername = strtolower($name);
				$check = array_key_exists($lowername, self::$cachedresponse);
				if ($check === true) {
					$stype = self::$cachedresponse[$lowername];
					$amount = (int)$response[0];
					$list = IL::SPAWNER;
					$totalprice = $amount * $list[$stype][2];
					$check = DB::checkUser($name);
					if ($check === false) {
						$player->sendMessage(Chat::RED . "Please rejoin the server, we were unable to get the details of your account sadly.");
						return;
					}
					$coin = $economy->getCoin($player);
					if ($coin >= $totalprice) {
						$give = SpawnerAPI::giveSpawner($player, $list[$stype][0], $amount);
						if ($give === true) {
							$economy->reduceCoin($player, $totalprice);
							UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
							return;
						} else if ($give !== true && $give !== false) {
							UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
							return;
						}
						return;
					}
					return;
				}
				break;
			}
    		case SystemOS::$uis['shopDirtUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$dirt;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopCobblestoneUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$cobblestone;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopNormalWoodUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$normalwood;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopIOreUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$ironore;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopGOreUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$goldore;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopDOreUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$diamondore;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopCOreUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$coalore;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopGlassUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$glass;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopChestUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$chest;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopCraftingTableUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$craftingtable;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopFurnaceUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$furnace;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopWSwordUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$woodsword;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopWAxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$woodaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopWPickaxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$woodpickaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopWShovelUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$woodshovel;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopSSwordUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$stonesword;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopSAxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$stoneaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopSPickaxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$stonepickaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopSShovelUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$stoneshovel;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopISwordUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$ironsword;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopIAxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$ironaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopIPickaxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$ironpickaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopIShovelUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$ironshovel;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
        	case SystemOS::$uis['shopGSwordUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$goldsword;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopGAxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$goldaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopGPickaxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$goldpickaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopGShovelUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$goldshovel;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
        	case SystemOS::$uis['shopDSwordUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$diamondsword;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopDAxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$diamondaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopDPickaxeUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$diamondpickaxe;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
			case SystemOS::$uis['shopDShovelUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$amount = (int)$response[0];
				$store = new Store($event->getPlugin(), $economy);
				$product = IL::$diamondshovel;
				$buy = $store->buyItem($event->getPlayer(), $amount, $product);
				if ($buy === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($buy === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
				break;
			}
		}
	}

}
