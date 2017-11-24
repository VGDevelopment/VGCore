<?php

namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\Server;
// >>>
use VGCore\SystemOS;

use VGCore\gui\UILoader;
use VGCore\gui\lib\UIDriver;
use VGCore\gui\lib\window\CustomForm;

use VGCore\listener\event\UICloseEvent;
use VGCore\listener\event\UIDataReceiveEvent;

use VGCore\network\ModalFormResponsePacket;
use VGCore\network\ServerSettingsRequestPacket;
use VGCore\network\ServerSettingsResponsePacket;

class GUIListener implements Listener {
    
    public $plugin;
    
    public function __construct() {
		$this->plugin = UILoader::getInstance();
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
		$event = new UIDataReceiveEvent($this->plugin, $packet, $player);
		if (is_null($event->getData())) $event = new UICloseEvent($this->plugin, $packet, $player);
		Server::getInstance()->getPluginManager()->callEvent($event);
		return true;
	}
	
	public function handleServerSettingsResponsePacket(ServerSettingsResponsePacket $packet, Player $player): bool {
		$event = new UIDataReceiveEvent($this->plugin, $packet, $player);
		if (is_null($event->getData())) $event = new UICloseEvent($this->plugin, $packet, $player);
		Server::getInstance()->getPluginManager()->callEvent($event);
		return true;
	}
	
	public function handleServerSettingsRequestPacket(ServerSettingsRequestPacket $packet, Player $player): bool {
		$ui = UIDriver::getPluginUI($this->plugin, Loader::$uis['serverSettings']);
		$pk = new ServerSettingsResponsePacket();
		$pk->formId = UILoader::$uis['serverSettings'];
		$pk->formData = json_encode($ui);
		$player->dataPacket($pk);
		return true;
	}
	
	public function onUIDataReceiveEvent(UIDataReceiveEvent $event) {
		if ($event->getPlugin() !== $this->plugin) return; // events handled for UI only
		switch ($id = $event->getID()) {
			case UILoader::$uis['serverSettings']: {
				$data = $event->getData();
				$ui = UIDriver::getPluginUI($this->plugin, $id);
				$response = $ui->handle($data, $event->getPlayer());
				break;
			}
			default: {
				print 'Any other formId' . PHP_EOL;
				var_dump(UIDriver::handle($event->getPlugin(), $event->getID(), $event->getData(), $event->getPlayer()));
				break;
			}
		}
	}

    
}