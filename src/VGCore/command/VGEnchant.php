<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\gui\lib\UIDriver;

class VGEnchant extends PluginCommand {
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Access VG Custom Enchants");
        $this->setUsage("/vgenchant");
        $this->setPermission("vgcore.vgenchant");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        UIDriver::showUIbyID($this->getPlugin(), SystemOS::$uis['customEnchantUI'], $sender);
    }
    
}