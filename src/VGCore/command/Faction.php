<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;
use VGCore\faction\FactionSystem;

use VGCore\gui\lib\UIDriver;

class Faction extends PluginCommand {

  public $faction;

  public function __construct($name, SystemOS $plugin) {
    parent::__construct($name, $plugin);
    $this->setDescription("Access Faction Features");
    $this->setUsage("/faction");
    $this->setPermission("vgcore.faction");
    $this->faction = new FactionSystem($plugin);
  }

  public function execute(CommandSender $sender, string $commandLabel, array $args) {
    if(!$this->faction->isInFaction($sender)){
      UIDriver::showUIbyID($this->getPlugin(), SystemOS::$uis['factionUI'], $sender); // returns a Menu for thos who's not in a fac yt.
    }
  }

}
