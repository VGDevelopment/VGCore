<?php

namespace VGCore\spawner;

use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\nbt\tag\{CompoundTag, IntTag};
use pocketmine\item\Item;
use pocketmine\utils\TextFormat as TF;
// >>>
use VGCore\SystemOS;

class SpawnerAPI {

    public static $mobtype = [
        10 => "Chicken",
        11 => "Cow",
        12 => "Pig",
        13 => "Sheep",
        16 => "Mooshroom",
        20 => "Iron_Golem",
        22 => "Ocelot",
        32 => "Zombie",
        34 => "Skeleton",
        35 => "Spider",
        36 => "Zombie_PigMan",
        43 => "Blaze",
    ];

    public static function start(): void {
        //DO YOU KNOW DA WAE IN DIS FUCTION
    }

    public static function giveSpawner(Player $player, int $id, int $amount): bool {
        $item = Item::get(52, 0, $amount);
        $item->setCustomName(TF::RESET . self::$mobtype[$id] . " Spawner");
        $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
        $nbt->entityid = new IntTag("entityid", $id);
        $item->setNamedTag($nbt);
        $check = $player->getInventory()->canAddItem($item);
        if ($check !== true) {
            $player->sendMessage(TF::RED . "Sorry, your inventory doesn't have space to add this item.");
            return false;
        }
        $player->getInventory()->addItem($item);
        return true;
    }

}
