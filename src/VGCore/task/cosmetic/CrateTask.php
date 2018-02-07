<?php
namespace VGCore\task\cosmetic;

use pocketmine\Server;
use pocketmine\Player;
use pocketmine\scheduler\PluginTask;

use VGCore\SystemOS;

use VGCore\cosmetic\crate\Chest;
use pocketmine\utils\TextFormat as TF;

class CrateTask extends PluginTask {

  public function __construct(SystemOS $plugin, $pos) {
		parent::__construct($plugin);
		$this->plugin = $plugin;
    $this->pos = $pos;
	}

  public function onRun(int $currentTick){
    SystemOS::$localdata[$this->pos]["Tick"]++;
    if(SystemOS::$localdata[$this->pos]["Tick"] == 20) {$this->lowerSpeed(4); return;}
    if(SystemOS::$localdata[$this->pos]["Tick"] == 25) {$this->lowerSpeed(6); return;}
    if(SystemOS::$localdata[$this->pos]["Tick"] == 30) {$this->lowerSpeed(11); return;}
    if(SystemOS::$localdata[$this->pos]["Tick"] == 39) {
      $this->plugin->getServer()->getPlayer(SystemOS::$localdata[$this->pos]["User"])->sendMessage(TF::GREEN."You have won ".SystemOS::$localdata[$this->pos]["Text"]);
      Chest::spawnText(SystemOS::$localdata[$this->pos]["Block"], TF::GREEN."> ".SystemOS::$localdata[$this->pos]["Text"].TF::GREEN." <");
      return;
    }
    if(SystemOS::$localdata[$this->pos]["Tick"] >= 40 && SystemOS::$localdata[$this->pos]["Tick"] < 45) {Chest::firework(SystemOS::$localdata[$this->pos]["Block"]); return;}
    if(SystemOS::$localdata[$this->pos]["Tick"] == 45) {$this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId()); Chest::resetCrate(SystemOS::$localdata[$this->pos]["Block"]); return;}
    Chest::spawnText(SystemOS::$localdata[$this->pos]["Block"]);
  }

  public function lowerSpeed(int $speed){
    $this->plugin->getServer()->getScheduler()->cancelTask($this->getTaskId());
    $task = new CrateTask($this->plugin, $this->pos);
    $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($task, $speed);
  }

}
