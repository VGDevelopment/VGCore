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
use VGCore\faction\FactionSystem;

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

class GUIListener implements Listener {

    public $plugin;

    public static $coindata;

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
		if ($event->getPlugin() !== $this->os) return; // events handled for UI only
		$economy = new EconomySystem($event->getPlugin());
		$player = $event->getPlayer();
		$p = $event->getPlugin();
		$coin = $economy->getCoin($player);
		// Run-time UI Form (checkCoinWindowUI) @var SystemOS::$uis [] int array for ID
		$ui = new CustomForm('§2Your §6Coins');
        $main = new Label('§aYour total §ecoins §aare §e[C]' . $coin);
        $ui->addElement($main);
        SystemOS::$uis['checkCoinWindowUI'] = UIDriver::addUI($this->os, $ui);
        // >>> RUNTIME UI END <<<
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
					case '§cMusic': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['musicUI'], $event->getPlayer());
						break;
					}
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
			case SystemOS::$uis['musicUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$music = $response[0];
				if ($music === "OFF") {
					$p->getServer()->getScheduler()->cancelTasks($p);
					return;
				}
				$filename = $music;
				$mp = new MusicPlayer($p);
				$p->getServer()->getScheduler()->cancelTasks($p);
				$mp->play();
				break;
			}
			case SystemOS::$uis['economyUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§2Check §6Coins': {
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

      case SystemOS::$uis['factionUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case 'Create Faction': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['createFactionUI'], $event->getPlayer());
						break;
					}
					case 'Join Faction': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['joinFactionUI'], $event->getPlayer());
						break;
					}
				}
				break;
			}

      case SystemOS::$uis['createFactionUI']: {
        $faction = new FactionSystem($event->getPlugin());
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				$string = $response[0];
				$plugin = $event->getPlugin();
				$player = $event->getPlayer();
				if(!$this->alphanum($string)){
          $player->sendMessage("§cYou may only use letters and numbers.");
          return true;
        }
        if($faction->factionValidate($string)){
          $player->sendMessage("§cThat faction already exists!");
          return true;
        }
        if(strlen($string) > 30){
          $player->sendMessage("§cThat name is too long, the limit is 30 characters.");
          return true;
        }
        if($faction->isinFaction($player)){
          $player->sendMesssage("§cYou're already in a faction!");
          return true;
        }else{
          $faction->createFaction($string, $player);
        }
        break;
      }

      case SystemOS::$uis['joinFactionUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case 'Check Invites': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['checkInviteUI'], $event->getPlayer());
						break;
					}
					case 'Check Requests': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['checkRequestUI'], $event->getPlayer());
						break;
					}
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
