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
        if (!(isset($nbt->eid)) || !($nbt->eid instanceof IntTag)) {
            $nbt->eid = new IntTag("eid", 0);
        }
        if (!(isset($nbt->sc)) && !($nbt->sc instanceof IntTag)) {
            $nbt->sc = new IntTag("sc", 4);
        }
        if (!(isset($nbt->sr)) && !($nbt->sr instanceof IntTag)) {
            $nbt->sr = new IntTag("sr", 4);
        }
        if (!(isset($nbt->minsd)) && !($nbt->minsd instanceof IntTag)) {
            $nbt->minsd = new IntTag("minsd", 200);
        }
        if (!(isset($nbt->maxsd)) && !($nbt->maxsd instanceof IntTag)) {
            $nbt->maxsd = new IntTag("maxsd", 800);
        }
        if ($this->entityID() > 0) {
            $this->scheduleUpdate();
        }
    }
    
    public function entityID(): int {
        return $this->namedtag["eid"];
    }
    
    public function sc(): int {
        return $this->namedtag["sc"];
    }
    
    public function sr(): int {
        return $this->namedtag["sr"];
    }
    
    public function minsd(): int {
        return $this->namedtag["minsd"];
    }
    
    public function maxsd(): int {
        return $this->namedtag["maxsd"];
    }
    
    public function delay(): int {
        return $this->namedtag["Delay"];
    }
    
    public function setEntityID(int $id): bool {
        $this->namedtag->eid->setValue($id);
        $this->onChanged();
        $this->scheduleUpdate();
        if ($this->namedtag["eid"] === $id) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setSC(int $sc): bool {
        $this->namedtag->sc->setValue($sc);
        if ($this->namedtag["sc"] === $sc) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setSR(int $sr): bool {
        $this->namedtag->sr->setValue($sr);
        if ($this->namedtag["sr"] === $sr) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setMinSD(int $minsd): bool {
        $this->namedtag->minsd->setValue($minsd);
        if ($this->namedtag["minsd"] === $minsd) {
            return true;
        } else {
            return false;
        }
    }
    
    public function setMaxSD(int $maxsd): bool {
        $this->namedtag->maxsd->setValue($maxsd);
        if ($this->namedtag["maxsd"] ===  $maxsd) {
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
        $eid = $this->namedtag["eid"];
        if ($eid === 0) {
            return "Monster Spawner";
        } else {
            $ename = ucfirst(MonsterSpawner::ID_NAME[$eid] ?? 'Monster') . ' Spawner';
            return $ename;
        }
    }
    
    public function updateRequest(): bool {
        $eid = $this->namedtag["eid"];
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
                for ($i = 0; $i < $this->namedtag["sc"]; $i++) {
                    $r1 = mt_rand();
                    $r2 = mt_rand(-1, 1);
                    $rmax = mt_getrandmax();
                    $sr = $this->namedtag["sr"];
                    $pos = $this->add($r1 / $rmax * $sr, $r2, $r1 / $rmax * $sr);
                    $target = $this->getLevel()->getBlock($pos);
                    if ($target->getId() === Item::AIR) {
                        $success++;
                        $eid = $this->namedtag["eid"];
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
                    $r1 = mt_rand($this->namedtag['minsd'], $this->namedtag['maxsd']);
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
        $nbt->eid = $this->namedtag->eid;
        $nbt->Delay = $this->namedtag->Delay;
        $nbt->sc = $this->namedtag->sc;
        $nbt->sr = $this->namedtag->sr;
        $nbt->minsd = $this->namedtag->minsd;
        $nbt->maxsd = $this->namedtag->maxsd;
    }
    
}