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

use VGCore\spawner\SpawnerAPI;

class MonsterSpawner extends MS {
    
    private $entityid = 0; // giving a predifined value so it fucking reaches something.. uhh.
    
    private static $drop;
    
    public function __construct() {
        // 
    }
    
    public function canBeActivated(): bool {
        return true;
    }
    
    public function onActivate(Item $item, Player $player = null): bool {
        if ($this->entityid === 0) {
            if ($item->getId() === Item::SPAWN_EGG) {
                $level = $this->getLevel();
                $tile = $level->getTile($this);
                $this->entityid = $item->getDamage();
                if (!($tile instanceof MobSpawner)) {
                    $nbt = Tile::createNBT($this);
                    Tile::createTile('MobSpawner', $level, $nbt);
                }
                $tile->setEID($this->entityid);
                return true;
            }
        }
        return false;
    }
    
    public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facepos, Player $player = null): bool {
        $level = $this->getLevel();
        $level->setBlock($block, $this, true, true);
        $nbt = Tile::createNBT($this);
        Tile::createTile('MobSpawner', $level, $nbt);
        return true;
    }
    
    public function getDrops(Item $item): array {
        return self::$drop;
    }
    
    public function getName(): string {
        if ($this->entityid === 0) {
            return "Monster Spawner";
        } else {
            var_dump($this->entityid);
            $type = SpawnerAPI::$mobtype;
            $eid = $type[$this->entityid];
            $name = ucfirst($eid ?? 'Monster') . 'Spawner';
            return $name;
        }
    }
    
}