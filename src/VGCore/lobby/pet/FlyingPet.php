<?php

namespace VGCore\lobby\pet;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
// >>>
use VGCore\lobby\pet\BasicPet;

abstract class FlyingPet extends BasicPet {
    
    public $gravity = 0;
    
    protected $flyheight = 35.0;
    
    public function onUpdate($currentTick): bool {
        if (!$this->updateRequirement()) {
			return true;
		}
		$owner = $this->getOwner();
		if ($this->isRiding()) {
			$this->yaw = $this->getOwner()->yaw;
			$this->pitch = $this->getOwner()->pitch;
			$this->updateMovement();
			return parent::onUpdate($currentTick);
		}
		if (!(parent::onUpdate($currentTick))) {
			return false;
		}
		if (!($this->isAlive())) {
			return false;
		}
		$x = $owner->x + $this->xoff - $this->x;
		$y = $owner->y + abs($this->yoff) + 1.5 - $this->y;
		$z = $owner->z + $this->zoff - $this->z;
		$eq1 = $x * $x + $z * $z;
		$eq2 = 8 + $this->scale;
		if ($eq1 < $eq2) {
			$this->motionX = 0;
			$this->motionZ = 0;
		} else {
		    $eq1 = abs($x) + abs($z);
		    $eq2 = $x / $eq1;
			$this->motionX = $this->speed * 0.25 * $eq2;
			$eq1 = abs($x) + abs($z);
			$eq2 = $z / $eq1;
			$this->motionZ = $this->speed * 0.25 * $eq2;
		}
		if ((float)$y !== 0.0) {
			$this->motionY = $this->speed * 0.25 * $y;
		}
		$atan2 = atan2(-$x, $z);
		$this->yaw = rad2deg($atan2);
		if ($this->getNetworkID() === 53) {
			$this->yaw += 180;
		}
		$sqrt = sqrt($x * $x + $z * $z);
		$negatan2 = -atan2($y, $sqrt);
		$this->pitch = rad2deg($negatan2);
		$this->move($this->motionX, $this->motionY, $this->motionZ);
		$this->updateMovement();
		return $this->isAlive();
    }
    
    public function ridingMovement(float $motionx, float $motionz): bool {
		$rider = $this->getOwner();
		$this->pitch = $rider->pitch;
		$this->yaw = $this instanceof EnderDragonPet ? $rider->yaw + 180 : $rider->yaw;
		$x = $rider->getDirectionVector()->x / 2 * $this->getSpeed();
		$z = $rider->getDirectionVector()->z / 2 * $this->getSpeed();
		$y = $rider->getDirectionVector()->y / 2 * $this->getSpeed();
		$finalmotion = [0, 0];
		switch ($motionz) {
			case 1:
				$finalmotion = [$x, $z];
				break;
			case 0:
				break;
			case -1:
				$finalmotion = [-$x, -$z];
				break;
			default:
				$average = $x + $z / 2;
				$finalmotion = [$average / 1.414 * $motionz, $average / 1.414 * $motionx];
				break;
		}
		switch ($motionx) {
			case 1:
				$finalmotion = [$z, -$x];
				break;
			case 0:
				break;
			case -1:
				$finalmotion = [-$z, $x];
				break;
		}
		$yfloat = (float)$y;
		if ($yfloat !== 0.0) {
			if ($y < 0) {
				$this->motionY = $this->getSpeed() * 0.3 * $y;
			} elseif ($this->y - $this->getLevel()->getHighestBlockAt((int) $this->x, (int)$this->z) < $this->flyheight) {
				$this->motionY = $this->getSpeed() * 0.3 * $y;
			}
		}
		if (abs($y) < 0.2) {
			$this->motionY = 0;
		}
		$this->move($finalmotion[0], $this->motionY, $finalMotion[1]);
		$this->updateMovement();
		return $this->isAlive();
	}
	
	public function attack(EntityDamageEvent $source): void {
	    if ($source->getCause() === $source::CAUSE_FALL) {
	        $source->setCancelled();
	    }
	    parent::attack($source);
	}
	
	public function makeProperty(string $entitytype): void {
	    parent::makeProperty($entitytype);
	    $this->flyheight = 35.0;
	}
    
}