<?php

namespace VGCore\command\admin;

use pocketmine\command\{
    CommandSender,
    ConsoleCommandSender,
    PluginCommand
};

use pocketmine\item\Item;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\spawner\SpawnerAPI;

class Spawn extends PluginCommand {
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Admin Spawn command");
        $this->setUsage("/spawn");
        $this->setPermission("vgcore.spawn");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        SpawnerAPI::giveSpawner($sender, 11);
    }
    
}