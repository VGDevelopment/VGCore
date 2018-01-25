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

    public static function giveSpawner(Player $player, int $id): void {
        $item = Item::get(52, 0, 1);
        $item->setCustomName(TF::RESET . self::$mobtype[$id] . " Spawner");
        $nbt = $item->getNamedTag() ?? new CompoundTag("", []);
        $nbt->entityid = new IntTag("entityid", $id);
        $item->setNamedTag($nbt);
        $player->getInventory()->addItem($item);
    }

}
