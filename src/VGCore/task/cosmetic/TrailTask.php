<?php
namespace VGCore\task\cosmetic;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use VGCore\SystemOS;

class TrailTask extends PluginTask {

  public function __construct(SystemOS $plugin) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
	}

  public function onRun(int $currentTick){
  }

}
