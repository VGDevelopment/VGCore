<?php

namespace VGCore\command\lobby;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\gui\lib\UIDriver;

class PlayerSetting extends PluginCommand {
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Access VG Player Settings");
        $this->setUsage("/settings");
        $this->setPermission("vgcore.playersetting");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID($this->getPlugin(), SystemOS::$uis['settingsUI'], $sender);
    }
    
}