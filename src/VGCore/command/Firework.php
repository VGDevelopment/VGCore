<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\SystemOS;

use VGCore\factory\{
    data\FireworkData,
    entity\projectile\FWR,
    item\Firework as FItem,
    particle\explosion\FireworkExplosion as FE
};

class Firework extends PluginCommand {
    
    private static $os = null;
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        self::$os = $plugin;
        $this->setDescription("Get a custom firework.");
        $this->setUsage("/firework");
        $this->setPermission("vgcore.firework");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($sender instanceof Player) {
            $color = [1, 2, 3];
            $fade = [3, 2, 1];
            $explosion = new FE($color, $fade);
            $data = new FireworkData(1, [$explosion]);
            $firework = new FItem();
            $nbt = $firework::sendToNBT($data);
            $firework->setNamedTag($nbt);
            $sender->getInventory()->addItem($firework);
        }
    }
    
}