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
		$accountcheck = $economy->createAccount($player);
		$coin = $economy->getCoin($player);
		// Run-time UI Form (checkCoinWindowUI) @var SystemOS::$uis [] int array for ID
		$ui = new CustomForm('§2Your §eCoins');
        $main = new Label('§aYour total §ecoins §aare §e[C]' . $coin);
        $option = new Dropdown('§2Please pick an option to go to next or close this window.', ['§cBack to Menu', '§aGo to §lSHOP']);
        $ui->addElement($main);
    	$ui->addElement($option);
        SystemOS::$uis['checkCoinWindowUI'] = UIDriver::addUI($this->os, $ui);
        // >>> RUNTIME UI END <<<
		switch ($id = $event->getID()) {
			case SystemOS::$uis['serverSettingsUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				break;
			}
			case SystemOS::$uis['tutorialUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§2Account Settings': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['serverSettingTutorialUI'], $event->getPlayer());
						break;
					}
				}
				break;
			}
			case SystemOS::$uis['economyUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response) {
					case '§2Check §eCoins': {
						UIDriver::showUIbyID($p, SystemOS::$uis['checkCoinWindowUI'], $player);
						break;
					}
					case '§2Send §eCoins': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['sendCoinUI'], $event->getPlayer());
					}
				}
				break;
			}
			case SystemOS::$uis['sendCoinUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				var_dump($response);
				$string = $response[1];
				$amount = (int)$string;
				$sendto = $response[2];
				$economy = new EconomySystem($event->getPlugin());
				$player = $event->getPlayer();
				$sender = $player->getName();
				$send = $economy->sendCoin($sender, $sendto, $amount);
				if ($send === true) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['successUI'], $event->getPlayer());
				} else if ($send === false) {
					UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['errorUI'], $event->getPlayer());
				}
			}
			case SystemOS::$uis['checkCoinWindowUI']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->os, $id);
				$response = $ui->handle($data, $event->getPlayer());
				switch ($response[1]) {
					case '§cBack to Menu': {
						UIDriver::showUIbyID($event->getPlugin(), SystemOS::$uis['economyUI'], $event->getPlayer());
						break;
					}
					case '§aGo to §lSHOP': {
						$player = $event->getPlayer();
						$player->sendMessage(Chat::YELLOW . "This is not available yet. Stay Tuned!");
						break;
					}
				}
				break;
			}
		}
	}

    
}