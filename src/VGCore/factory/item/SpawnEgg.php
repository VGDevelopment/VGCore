<?php

namespace VGCore\block\item;

use pocketmine\block\Block;

use pocketmine\entity\Entity;

use pocketmine\item\SpawnEgg as SE;

use pocketmine\level\Level;

use pocketmine\math\Vector3;

use pocketmine\Player;
// >>>
use VGCore\factory\block\MonsterSpawner;

class SpawnEgg extends SE {
    
    public function onActivate(Level $level, Player $player, Block $br, Block $bc, int $f, Vector3 $cv): bool {
        if (!($bc instanceof MonsterSpawner)) {
            $bradd = $br->add(0.5, 0, 0.5);
            $lcgvalue =  lcg_value() * 360;
            $nbt = Entity::createBaseNBT($bradd, null, $lcgvalue, 0);
            if ($this->hasCustomName()) {
                $nbt->setString("CustomName", $this->getCustomName());
            }
            $e = Entity::createEntity($this->meta, $level, $nbt);
            if ($e instanceof Entity) {
                if ($player->isSurvival()) {
                    --$this->count;
                }
                $entity->spawnToAll();
                return true;
            }
            return false;
        }
        return false;
    }
    
}