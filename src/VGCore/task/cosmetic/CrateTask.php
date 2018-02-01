<?php
namespace VGCore\task\cosmetic;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use VGCore\SystemOS;

use VGCore\cosmetic\crate\Chest;

class CrateTask extends PluginTask {

  public function __construct(SystemOS $plugin, $pos) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
    $this->pos = $pos;
	}

  public function onRun(int $currentTick){
    print(SystemOS::$localdata[$this->pos]["Tick"]."\n");
    SystemOS::$localdata[$this->pos]["Tick"]++;
    Chest::spawnText(SystemOS::$localdata[$this->pos]["Block"]);
    if(SystemOS::$localdata[$this->pos]["Tick"] == 20) $this->lowerSpeed(4);
    if(SystemOS::$localdata[$this->pos]["Tick"] == 25) $this->lowerSpeed(6);
    if(SystemOS::$localdata[$this->pos]["Tick"] == 30) $this->lowerSpeed(11);
    if(SystemOS::$localdata[$this->pos]["Tick"] == 40) {};
    if(SystemOS::$localdata[$this->pos]["Tick"] == 47) {$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId()); Chest::resetCrate(SystemOS::$localdata[$this->pos]["Block"]);};
  }

  public function lowerSpeed(int $speed){
    $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
    $task = new CrateTask($this->plugin, $this->pos);
    $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($task, $speed);
  }

}
