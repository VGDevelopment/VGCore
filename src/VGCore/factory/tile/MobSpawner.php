<?php

namespace VGCore\factory\tile;

use pocketmine\entity\Entity;

use pocketmine\item\Item;

use pocketmine\level\{
    Level,
    format\Chunk
};

use pocketmine\nbt\tag\{
    CompoundTag,
    IntTag
};
use pocketmine\Player;

use pocketmine\tile\Spawnable;
// >>>
use VGCore\factory\block\MonsterSpawner;

class MobSpawner extends Spawnable {
    
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
        if (!(isset($nbt->EntityId)) || !($nbt->EntityId instanceof IntTag)) {
            $nbt->EntityId = new IntTag("EntityId", 0);
        }
        if (!(isset($nbt->SpawnCount)) && !($nbt->SpawnCount instanceof IntTag)) {
            $nbt->SpawnCount = new IntTag("SpawnCount", 4);
        }
        if (!(isset($nbt->SpawnRange)) && !($nbt->SpawnRange instanceof IntTag)) {
            $nbt->SpawnRange = new IntTag("SpawnRange", 4);
        }
        if (!(isset($nbt->MinSpawnDelay)) && !($nbt->MinSpawnDelay instanceof IntTag)) {
            $nbt->MinSpawnDelay = new IntTag("MinSpawnDelay", 200);
        }
        if (!(isset($nbt->MaxSpawnDelay)) && !($nbt->MaxSpawnDelay instanceof IntTag)) {
            $nbt->MaxSpawnDelay = new IntTag("MaxSpawnDelay", 800);
        }
        if ($this->entityID() > 0) {
            $this->scheduleUpdate();
        }
    }
    
    public function entityID(): int {
        return $this->namedtag["EntityId"];
    }
    
    public function sc(): int {
        return $this->namedtag["SpawnCount"];
    }
    
    public function sr(): int {
        return $this->namedtag["SpawnRange"];
    }
    
    public function minsd(): int {
        return $this->namedtag["MinSpawnDelay"];
    }
    
    public function maxsd(): int {
        return $this->namedtag["MaxSpawnDelay"];
    }
    
    public function delay(): int {
        return $this->namedtag["Delay"];
    }
    
    public function setEntityID(int $id): bool {
        $this->namedtag->EntityId->setValue($id);
        $this->onChanged();
        $this->scheduleUpdate();
        if ($this->namedtag["EntityId"] === $id) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setSC(int $sc): bool {
        $this->namedtag->SpawnCount->setValue($sc);
        if ($this->namedtag["SpawnCount"] === $sc) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setSR(int $sr): bool {
        $this->namedtag->SpawnRange->setValue($sr);
        if ($this->namedtag["SpawnRange"] === $sr) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setMinSD(int $minsd): bool {
        $this->namedtag->MinSpawnDelay->setValue($minsd);
        if ($this->namedtag["MinSpawnDelay"] === $minsd) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setMaxSD(int $maxsd): bool {
        $this->namedtag->MaxSpawnDelay->setValue($maxsd);
        if ($this->namedtag["MaxSpawnDelay"] ===  $maxsd) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setDelay(int $delay): bool {
        $this->namedtag->Delay->setValue($delay);
        if ($this->namedtag["Delay"] === $delay) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getName(): string {
        $eid = $this->namedtag["EntityId"];
        if ($eid === 0) {
            return "Monster Spawner";
        } else {
            $ename = ucfirst(MonsterSpawner::ID_NAME[$eid] ?? 'Monster') . ' Spawner';
            return $ename;
        }
    }
    
    public function updateRequest(): bool {
        $eid = $this->namedtag["EntityId"];
        if ($eid === 0) {
            return false;
        }
        $player = false;
        $count = 0;
        $level = $this->getLevel();
        $alle = $level->getEntities();
        foreach ($alle as $e) {
            if ($e instanceof Player) {
                if ($e->distance($this->getBlock) <= 15) {
                    $player = true;
                }
            }
            if ($e::NETWORK_ID === $eid) {
                $count++;
            }
        }
        if ($player && $count < 15) {
            return true;
        } else {
            return false;
        }
    }
    
    public function onUpdate(): bool {
        if ($this->closed === true) {
            return false;
        }
        $this->timings->startTiming();
        if (!($this->chunk instanceof Chunk)) {
            return false;
        }
        if ($this->updateRequest() === true) {
            $delay = $this->namedtag["Delay"];
            if ($delay <= 0) {
                $success = 0;
                for ($i = 0; $i < $this->namedtag["SpawnCount"]; $i++) {
                    $r1 = mt_rand();
                    $r2 = mt_rand(-1, 1);
                    $rmax = mt_getrandmax();
                    $sr = $this->namedtag["SpawnRange"];
                    $pos = $this->add($r1 / $rmax * $sr, $r2, $r1 / $rmax * $sr);
                    $target = $this->getLevel()->getBlock($pos);
                    if ($target->getId() === Item::AIR) {
                        $success++;
                        $eid = $this->namedtag["EntityId"];
                        $level = $this->getLevel();
                        $nbtdata = [
                            $target->add(0.5, 0, 0.5),
                            null,
                            lcg_value() * 360,
                            0
                        ];
                        $nbt = Entity::createBaseNBT($nbtdata[0], $nbtdata[1], $nbtdata[2], $nbtdata[3]);
                        $e = Entity::createEntity($eid, $level, $nbt);
                        $e->spawnToAll();
                    }
                }
                if ($success > 0) {
                    $r1 = mt_rand($this->namedtag['MinSpawnDelay'], $this->namedtag['MaxSpawnDelay']);
                    $this->setDelay($r1);
                }
            } else {
                $this->setDelay($delay - 1);
            }
        }
        $this->timings->stopTiming();
        return true;
    }
    
    public function addAdditionalSpawnData(CompoundTag $nbt): void {
        $nbt->EntityId = $this->namedtag->EntityId;
        $nbt->Delay = $this->namedtag->Delay;
        $nbt->SpawnCount = $this->namedtag->SpawnCount;
        $nbt->SpawnRange = $this->namedtag->SpawnRange;
        $nbt->MinSpawnDelay = $this->namedtag->MinSpawnDelay;
        $nbt->MaxSpawnDelay = $this->namedtag->MaxSpawnDelay;
    }
    
}