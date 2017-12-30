<?php

namespace VGCore\lobby\pet;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
// >>>
use VGCore\lobby\pet\BasicPet;

abstract class WalkingPet extends BasicPet {
    
    protected $jumptick = 0;
    
    public function onUpdate(int $currentTick): bool {
        if (!$this->updateRequirement()) {
            return true;
        }
        if ($this->isRiding()) {
            $this->yaw = $this->getOwner()->yaw;
            $this->pitch = $this->getOwner()->pitch;
            $this->updateMovement();
            return parent::onUpdate($currentTick);
        }
        if (!(parent::onUpdate($currentTick)) || !($this->isAlive())) {
            return false;
        }
        $owner = $this->getOwner();
        if ($this->jumptick > 0) {
            $this->jumptick--;
        }
        if (!($this->isOnGround())) {
            if ($this->motionY > -($this->gravity) * 4) {
                $this->motionY = -($this->gravity) * 4;
            } else {
                $this->motionY += $this->isInsideOfWater() ? $this->gravity : -($this->gravity);
            }
        } else {
            $this->motionY -= $this->gravity;
        }
        if ($this->isCollidedHorizontally && $this->jumptick === 0) {
            $this->doJump();
        }
        $this->move($this->motionX, $this->motionY, $this->motionZ);
        $x = $owner->x + $this->xoff - $this->x;
        $z = $owner->z + $this->zoff - $this->z;
        $y = $owner->y - $this->y;
        $eq1 = $x * $x + $z * $z;
        if ($eq1 < 4 + $this->scale) {
            $this->motionX = 0;
			$this->motionZ = 0;
        } else {
            $absadd = abs($x) + abs($z);
            $eq1 = $x / $absadd;
            $this->motionX = $this->speed * 0.15 * $eq1;
            $eq1 = $y / $absadd;
			$this->motionZ = $this->speed * 0.15 * $eq1;
        }
        $atan2 = atan2(-($x), $z);
        $this->yaw = rad2deg($atan2);
        $sqrt = sqrt($x * $x + $z * $z);
        $negatan2 = -atan2($y, $sqrt);
        $this->pitch = rad2deg($negatan2);
        $this->move($this->motionX, $this->motionY, $this->motionZ);
        $this->updateMovement();
		return $this->isAlive();
    }
    
    public function doJump(): void {
        $this->motionY = $this->gravity * 8;
        $this->move($this->motionX, $this->motionY, $this->motionZ);
        $this->jumptick = 5;
    }
    
    public function ridingMovement(float $motionX, float $motionZ): bool {
        $rider = $this->getOwner();
        $this->pitch = $rider->pitch;
        $this->yaw = $rider->yaw;
        $x = $this->getDirectionVector()->x / 2.5 * $this->speed;
        $z = $this->getDirectionVector()->z / 2.5 * $this->speed;
        if ($this->jumptick > 0) {
            $this->jumptick--;
        }
        if (!($this->isOnGround())) {
            if ($this->motionY > -($this->gravity) * 4) {
                $this->motionY = -($this->gravity) * 4;
            } else {
                $this->motionY -= $this->gravity;
            }
        } else {
            $this->motionY -= $this->gravity;
        }
        if ($this->isCollidedHorizontally && $this->jumptick === 0) {
            $this->doJump();
        }
        $fm = [
            
        ];
        switch ($motionZ) {
            case 1:
                $fm = [
                    $x,
                    $z
                ];
            case 0:
				break;
			case -1:
				$fm = [
                    -($x),
                    -($z)
				];
				break;
			default:
				$average = $x + $z / 2;
				$fm = [
                    $average / 1.414 * $motionZ,
                    $average / 1.414 * $motionX
				];
				break;
        }
        switch ($motionX) {
            case 1:
				$fm = [
                    $z,
                    -($x)
				];
				break;
			case 0:
				break;
			case -1:
				$fm = [
                    -($z),
                    $x
				];
				break;
        }
        $this->move($fm[0], $this->motionY, $fm[1]);
        $this->updateMovement();
        return $this->isAlive();
    }
    
}