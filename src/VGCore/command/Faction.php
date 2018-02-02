<?php

namespace VGCore\command;

use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\SystemOS;
use VGCore\faction\FactionSystem as FS;

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
        if (!(empty($args)) && $args[0] === "claim") {
            $check = FS::inFaction($sender);
            if ($check === true) {
                $faction = FS::getPlayerFaction($sender);
                $query = FS::claimLand($faction, $sender);
                if ($query === 1) {
                    $sender->sendMessage(Chat::GREEN . "Land claimed succesfully.");
                } else if ($query === 0) {
                    $sender->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
                }
            } else {
                $sender->sendMessage(Chat::RED . "Sorry, to use " . Chat::YELLOW . "/f claim" . Chat::RED . ", you need to be in a faction and a leader");
            }
            return;
        }
        UIDriver::showUIbyID(self::$os, SystemOS::$uis['fManagerUI'], $sender);
    }
    
}