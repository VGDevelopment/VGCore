<?php

namespace VGCore\factory\block;

use pocketmine\block\{
    Block, 
    MonsterSpawner as MS    
};

use pocketmine\item\{
    Item,
    enchantment\Enchantment
};

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\{
    CompoundTag,
    IntTag,
    StringTag
};

use pocketmine\Player;

use pocketmine\tile\Tile;
// >>>
use VGCore\factory\tile\MobSpawner;

class MonsterSpawner extends MS {
    
    const ID_NAME = [
        10 => "Chicken",
        11 => "Cow",
        12 => "Pig",
        13 => "Sheep",
        16 => "Mooshroom",
        20 => "Iron Golem",
        22 => "Ocelot",
        32 => "Zombie",
        34 => "Skeleton",
        35 => "Spider",
        36 => "Zombie Pigman",
        43 => "Blaze"
    ];
    
    private $eid = 0;
    
    public function __construct() {
        //
    }
    
    public function canBeActivated(): bool {
        return true;
    }
    
    public function onActivate(Item $item, Player $player = null): bool {
        if ($this->eid !== 0 || $item->getId() != ITEM::SPAWN_EGG) {
            return false;
        }
        $level = $this->getLevel();
        $tile = $level->getTile($this);
        $this->eid = $item->getDamage();
        if (!($tile instanceof MobSpawner)) {
            $pos = [
                $this->x,
                $this->y,
                $this->z
            ];
            $tilepos = [
                Tile::TAG_X,
                Tile::TAG_Y,
                Tile::TAG_Z
            ];
            $itag = [];
            foreach ($pos as $index => $value) {
                $itag[$index] = new IntTag($tilepos[$index], (int)$pos[$index]);
            }
            $stag = new StringTag(Tile::TAG_ID, Tile::MOB_SPAWNER);
            $mixtagarray = [
                $stag,
                $itag[0],
                $itag[1],
                $itag[2]
            ];
            $nbt = new CompoundTag("", $mixtagarray);
            $mstile = Tile::createTile(Tile::MOB_SPAWNER, $level, $nbt);
            $tile->setEntityID($this->eid);
            return true;
        }
        return true;
    }
    
    public function getDrops(Item $item): array {
        $tile = $this->getLevel()->getTile($this);
        $drop = [];
        if (!($tile instanceof MobSpawner)) {
            return $drop;
        }
        if ($item->hasEnchantment(Enchantment::SILK_TOUCH)) {
            $itemid = $this->getItemId();
            $eid = (int)$tile->entityID();
            $nametag = $tile->namedtag;
            $drop = [
                Item::get($itemid, $eid, 1, $nametag)
            ];
            return $drop;
        }
        return $drop;
    }
    
    public function getName(): string {
        if ($this->eid === 0) {
            return "Monster Spawner";
        } else {
            $name = ucfirst(self::ID_NAME[$this->eid] ?? 'Monster') . ' Spawner';
            return $name;
        }
    }
    
}