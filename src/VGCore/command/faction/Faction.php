<?php

namespace VGCore\command\faction;

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
        if (!(empty($args)) && $args[0] === "chat") {
            $check = FS::inFaction($sender);
            if ($check === true) {
                $name = $sender->getName();
                $lowername = strtolower($name);
                $checkstring = [];
                if (array_key_exists($lowername, FS::$fchat)) {
                    if (FS::$fchat[$lowername] === true) {
                        FS::$fchat[$lowername] = false;
                        $checkstring[0] = Chat::RED . "DISABLED";
                        $checkstring[1] = "enable";
                    } else {
                        FS::$fchat[$lowername] = true;
                        $checkstring[0] = Chat::GREEN . "ENABLED";
                        $checkstring[1] = "disable";
                    }
                } else {
                    FS::$fchat[$lowername] = true;
                    $checkstring[0] = Chat::GREEN . "ENABLED";
                    $checkstring[1] = "disable";
                }
                $sender->sendMessage(Chat::YELLOW . "Faction Chat has been " . Chat::BOLD . $checkstring[0] . Chat::RESET . Chat::YELLOW . "!" . Chat::EOL . 
                Chat::YELLOW . "To " . $checkstring[1] . " Faction Chat, please use the same command again.");
            }
            return;
        }
        if (!(empty($args)) && $args[0] === "claim") {
            $check = FS::inFaction($sender);
            if ($check === true) {
                $faction = FS::getPlayerFaction($sender);
                $query = FS::claimLand($faction, $sender);
                if ($query === 1) {
                    $sender->sendMessage(Chat::GREEN . "Land claimed succesfully.");
                    return;
                } else if ($query === 0) {
                    $sender->sendMessage(Chat::RED . "An unknown error occured with the API. Please notify support.");
                    return;
                }
            } else {
                $sender->sendMessage(Chat::RED . "Sorry, to use " . Chat::YELLOW . "/f claim" . Chat::RED . ", you need to be in a faction and be the leader." . Chat::EOL . Chat::YELLOW . "If you're leader has gone inactive, raise an issue at " . Chat::GREEN . Chat::BOLD . "support@vgpe.me" . 
                Chat::RESET . Chat::YELLOW . " to become the leader.");
            }
            return;
        }
        UIDriver::showUIbyID(self::$os, SystemOS::$uis['fManagerUI'], $sender);
    }
    
}