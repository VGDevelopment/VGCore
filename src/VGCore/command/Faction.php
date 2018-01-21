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

class Factions extends PluginCommand {
    
    private static $os = null;
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        self::$os = $plugin;
        $this->setDescription("Access Economy Features");
        $this->setUsage("/economy");
        $this->setPermission("vgcore.economy");
        $this->setAliases([
            "f"    
        ]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID(self::$os, SystemOS::$uis['fManagerUI'], $sender);
    }
    
}