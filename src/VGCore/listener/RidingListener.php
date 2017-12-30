<?php

namespace VGCore\listener;

use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\InteractPacket;
use pocketmine\network\mcpe\protocol\PlayerActionPacket;
use pocketmine\network\mcpe\protocol\PlayerInputPacket;
use pocketmine\Player;
// >>>
use VGCore\SystemOS;

class RidingListener implements Listener {
    
    private $os;
    
    public function __construct(SystemOS $os) {
        $this->os = $os;
    }
    
    public function duringPetRide(DataPacketReceiveEvent $event): void {
        $packet = $event->getPacket();
		if ($packet instanceof PlayerInputPacket) {
			if ($this->os->playerRidding($event->getPlayer())) {
				if ($packet->motionX === 0 && $packet->motionY === 0) {
					return;
				}
				$pet = $this->os->getRiddenPet($event->getPlayer());
				$pet->ridingMovement($packet->motionX, $packet->motionY);
			}
		} elseif ($packet instanceof InteractPacket) {
			if ($packet->action === $packet::ACTION_LEAVE_VEHICLE) {
				if ($this->os->playerRidding($event->getPlayer())) {
					$this->os->getRiddenPet($event->getPlayer())->throwRiderOff();
				}
			}
		} elseif ($packet instanceof PlayerActionPacket) {
			if ($packet->action === $packet::ACTION_JUMP) {
				foreach ($this->getLoader()->getPlayerPet($event->getPlayer()) as $pet) {
					if ($pet->isRiding()) {
						$pet->dismountOwner();
					}
				}
			}
		}
    }
    
    public function onTP(EntityTeleportEvent $event) {
        $player = $event->getEntity();
		if ($player instanceof Player) {
			if ($this->os->playerRidding($player)) {
				$this->os->getRiddenPet($player)->throwRiderOff();
				foreach ($this->os->getPlayerPet($player) as $pet) {
					$pet->dismountOwner();
				}
			}
		}
    }
    
    public function stopPMAntiHack(PlayerIllegalMoveEvent $event): void {
		if ($this->getLoader()->playerRidding($event->getPlayer())) {
			$event->setCancelled();
		}
	}
	
	public function onQuit(PlayerQuitEvent $event): void {
		foreach ($this->os->getPlayerPet($event->getPlayer()) as $pet) {
			if ($pet->ridden()) {
				$pet->throwRiderOff();
			}
		}
	}
    
}