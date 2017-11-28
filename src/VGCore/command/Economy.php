<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\gui\lib\UIDriver;

class Economy extends PluginCommand {
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Access Economy Features");
        $this->setUsage("/economy");
        $this->setPermission("vgcore.economy");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID($this->getPlugin(), SystemOS::$uis['economyUI'], $sender);
    }
    
}