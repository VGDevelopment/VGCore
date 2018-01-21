<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\SystemOS;

class Ping extends PluginCommand {
    
    private static $os = null;
    
    public function __construct($name, SystemOS $plugin) {
        parent::__construct($name, $plugin);
        self::$os = $plugin;
        $this->setDescription("Check your Ping");
        $this->setUsage("/p /ping");
        $this->setPermission("vgcore.pingcheck");
        $this->setAliases([
            "p"    
        ]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args) {
        if ($sender instanceof Player) {
            $ping = $sender->getPing();
            if ($ping > 200) {
                $ping = Chat::RED . (string)$ping;
            } else if ($ping > 100 && $ping < 200) {
                $ping = Chat::YELLOW . (string)$ping;
            } else {
                $ping = Chat::GREEN . (string)$ping;
            }
            $sender->sendMessage(Chat::AQUA . "The server reported your ping to be:" . Chat::EOL . $ping . Chat::AQUA . "ms");
        }
    }
    
}