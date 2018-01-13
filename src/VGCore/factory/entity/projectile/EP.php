<?php

namespace VGCore\factory\entity\projectile;

use pocketmine\entity\{
    Human,
    projectile\Throwable
};
use pocketmine\event\entity\EntityDamageEvent;
// >>>
use VGCore\sound\Sound as S;

class EP extends Throwable {
    
    const NETWORK_ID = self::ENDER_PEARL;
    
    public function onUpdate(int $currentTick): bool {
        if ($this->isCollided || $this->age > 1200) {
            $owner = $this->getOwningEntity();
            if ($owner instanceof Human && $this->y > 0) {
                S::playSound([$owner], "EnderTP");
                $landingpoint = $this->getPosition();
                $owner->teleport($landingpoint);
                $ede = new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5);
                $owner->attack($ede);
                $this->kill();
            }
        }
        return parent::onUpdate($currentTick);
    }
    
}