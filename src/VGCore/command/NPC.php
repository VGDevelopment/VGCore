<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;
// >>>
use VGCore\SystemOS;

use VGCore\lobby\npc\NPCSystem;

class NPC extends PluginCommand {
    
    private $plugin;
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        $this->setDescription("Access npc Features");
        $this->setUsage("/npc");
        $this->setPermission("vgcore.npc");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        $npcsys = new NPCSystem($this->plugin);
        $sender->getSkin();
        $npcsys->spawnCrateNPC($skin);
    }
    
}