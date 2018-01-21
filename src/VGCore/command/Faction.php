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
    
    private static $os = null;
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        self::$os = $plugin;
        $this->setDescription("Access Faction Features");
        $this->setUsage("/f or /faction");
        $this->setPermission("vgcore.faction");
        $this->setAliases([
            "f"    
        ]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID(self::$os, SystemOS::$uis['fManagerUI'], $sender);
    }
    
}