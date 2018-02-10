<?php

namespace VGCore\factory\data;

use pocketmine\block\{
    Block,
    Planks,
    Prismarine,
    Stone,
    StoneSlab
};

use pocketmine\nbt\{
    BigEndianNBTStream,
    NBT,
    tag\ByteTag,
    tag\CompoundTag,
    tag\IntArrayTag,
    tag\IntTag,
    tag\ListTag,
    tag\ShortTag,
    tag\StringTag
};

use pocketmine\network\mcpe\protocol\ClientboundMapItemDataPacket;

use pocketmine\Server;

use pocketmine\utils\Config;
// >>>
use VGCore\factory\item\Map;

class MapCrayon {

    private static $cache = []; // so it doesn't regenerate again & again.
    
    public static $base = []; // base colors.
    public static $id; // id from JSON.

    // I'll do rest later.

}