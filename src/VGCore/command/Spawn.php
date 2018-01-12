<?php

namespace VGCore\command;

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
        $player = $sender->getServer()->getPlayer($sender->getName());
        if ($player === null) {
            return false;
        }
        $type = "Chicken";
        $mobtype = SpawnerAPI::$mobtype;
        $newarray = array_flip($mobtype);
        $id = $newarray[$type];
        $block = Item::get(Item::MONSTER_SPAWNER, $id);
        $block->setCustomName(ucfirst($type) . ' Spawner');
        $player->getInventory()->addItem($block);
        return true;
    }
    
}