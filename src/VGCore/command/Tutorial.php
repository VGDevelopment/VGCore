<?php 

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;

use pocketmine\utils\TextFormat;
// >>>
use VGCore\SystemOS;

use VGCore\gui\lib\UIDriver;

class Tutorial extends PluginCommand {
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Command to open up tutorial.");
        $this->setUsage("/tutorial");
        $this->setPermission("vgcore.tutorial");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID($this->getPlugin(), SystemOS::$uis['tutorialUI'], $sender);
    }
    
}