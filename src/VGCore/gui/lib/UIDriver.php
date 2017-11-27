<?php 

namespace VGCore\gui\lib;

use pocketmine\OfflinePlayer;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\utils\Utils;
// >>>
use VGCore\network\ModalFormRequestPacket;

class UIDriver {
    
    private static $UIs = []; // array of all uis so making an ID for each isn't always necessary and can be changed without changing the actual command or event of UI.
    
    public static function addUI(Plugin $plugin, LibraryInt &$ui) {
        $ui->setID(count(self::$UIs[$plugin->getName()]??[]));
		$id = $ui->getID();
		self::$UIs[$plugin->getName()][$id] = $ui;
		return $id;
    }
    
    public static function resetUIs(Plugin $plugin) {
        self::$UIs[$plugin->getName()] = [];
    }
    
    public static function getAllUIs(): array {
		return self::$UIs;
	}
	
	public static function getPluginUIs(Plugin $plugin): array {
		return self::$UIs[$plugin->getName()];
	}
	
	public static function getPluginUI(Plugin $plugin, int $id): LibraryInt {
		return self::$UIs[$plugin->getName()][$id];
	}
	
	public static function handle(Plugin $plugin, int $id, $response, Player $player) {
		$ui = self::getPluginUIs($plugin)[$id];
		// var_dump($ui); DEVELOPER ONLY FUNCTION - use if wanting to check how you're getting the response. Not sending, but response. 
		return $ui->handle($response, $player)??"";
	}
	
	public static function showUI(LibraryInt $ui, Player $player) {
		$pk = new ModalFormRequestPacket();
		$pk->formData = json_encode($ui);
		$pk->formId = Utils::javaStringHash($ui->getTitle());
		$player->dataPacket($pk);
	}
	
	public static function showUIbyID(Plugin $plugin, int $id, Player $player) {
		$ui = self::getPluginUIs($plugin)[$id];
		$pk = new ModalFormRequestPacket();
		$pk->formData = json_encode($ui);
		$pk->formId = $id;
		$player->dataPacket($pk);
	}
    
}