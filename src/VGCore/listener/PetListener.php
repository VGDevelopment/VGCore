<?php 

namespace VGCore\listener;

use pocketmine\entity\Living;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDeathEvent;
use pocketmine\event\entity\EntitySpawnEvent;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerJoinEvent;

use pocketmine\Player;
use pocketmine\utils\TextFormat;
// >>>
use VGCore\listener\event\RemakePetEvent;

use VGCore\SystemOS;

use VGCore\lobby\pet\BasicPet;

use VGCore\task\pet\PetRespawnTask;

class PetListener implements Listener {
    
    private $os;
    
    public function __construct(SystemOS $os) {
        $this->os = $os;
    }
    
    public function onDamage(EntityDamageEvent $event): void {
        $player = $event->getEntity();
        if ($player instanceof Player) {
            $eventcause = $event->getCause();
            if ($eventcause === $event::CAUSE_FALL && $this->os->playerRidding($player)) {
                $event->setCancelled();
                return;
            }
        }
    }
    
    public function onDeathOfPet(EntityDeathEvent $event): void {
        $pet = $event->getEntity();
        $delay = 2;
        if ($pet instanceof BasicPet) {
            if($pet->checkEventIgnorant) {
				return;
			}
			$owner = $this->os->getServer()->getPlayer($pet->getOwnerName());
			$this->os->removePet($pet->getName(), $pet->getOwner());
			$newpet = $this->os->createPet($pet->getEntityType(), $owner, $pet->getName());
			$event = new RemakePetEvent($this->os, $newpet, $delay);
			$this->os->getServer()->getPluginManager()->callEvent($event);
			if($event->isCancelled()) {
				return;
			}
			$delay = $event->getDelay() * 20;
			$task = new PetRespawnTask($this->os(), $newpet);
			$this->os->getServer()->getScheduler()->scheduleDelayedTask($task, $delay);
			$newpet->despawnFromAll();
			$newpet->setDormant();
        }
    }
    
}