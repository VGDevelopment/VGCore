<?php

namespace VGCore\enchantment\handler;

use pocketmine\entity\Effect;
use pocketmine\entity\Entity;
use pocketmine\entity\projectile\Arrow;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\entity\EntityArmorChangeEvent;
use pocketmine\event\entity\EntityDamageByChildEntityEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityEffectAddEvent;
use pocketmine\event\entity\EntityEvent;
use pocketmine\event\entity\EntityShootBowEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\cheat\PlayerIllegalMoveEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerKickEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerToggleSneakEvent;
use pocketmine\event\server\DataPacketReceiveEvent;

use pocketmine\item\Armor;
use pocketmine\item\Item;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\level\sound\GenericSound;

use pocketmine\math\Vector3;

use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;

use pocketmine\network\mcpe\protocol\PlayerActionPacket;

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
use pocketmine\utils\Random;

use pocketmine\block\Block;
use pocketmine\block\Wood;
use pocketmine\block\Wood2;
// >>>
use VGCore\SystemOS;

use VGCore\listener\CustomEnchantmentListener;

use VGCore\store\ItemList as IL;

use VGCore\sound\Sound as S;

class Handler {
    
    public $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function trueAxe(Entity $player, $block, Block $oldblock = null) {
        $sound = "AnvilUse";
        $allentity = [$player];
        S::playSound($allentity, $sound);
        if ($block instanceof Wood) {
            $item = $player->getInventory()->getItemInHand();
            for ($i = 0; $i <= 5; $i++) {
                if ($this->plugin->mined[$player->getLowerCaseName()] > 800) {
                    break;
                }
                $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
                $side = $block->getSide($i);
                if ($oldblock !== null) {
                    if ($side->equals($oldblock)) {
                        continue;
                    }
                }
                if ($side->getId() !== Block::WOOD && $side->getId() !== Block::WOOD2) {
                    continue;
                }
                $player->getLevel()->useBreakOn($side, $item, $player);
                $this->plugin->mined[$player->getLowerCaseName()]++;
                $this->trueAxe($player, $side, $block);
            }
        } else if ($block instanceof Wood2) {
            $item = $player->getInventory()->getItemInHand();
            for ($i = 0; $i <= 5; $i++) {
                if ($this->plugin->mined[$player->getLowerCaseName()] > 800) {
                    break;
                }
                $this->plugin->using[$player->getLowerCaseName()] = time() + 1;
                $side = $block->getSide($i);
                if ($oldblock !== null) {
                    if ($side->equals($oldblock)) {
                        continue;
                    }
                }
                if ($side->getId() !== Block::WOOD && $side->getId() !== Block::WOOD2) {
                    continue;
                }
                $player->getLevel()->useBreakOn($side, $item, $player);
                $this->plugin->mined[$player->getLowerCaseName()]++;
                $this->trueAxe($player, $side, $block);
            }
        }
        
    }
    
    public function warAxe(Entity $entity, Entity $damager) {
        $e = [$entity, $damager];
        $sound = "Thunder";
        S::playSound($e, $sound);
        $entityhealth = $entity->getHealth();
        $healthcalc = $entityhealth - 10;
        $entity->setHealth($healthcalc);
    }
    
    public function disable(Entity $entity, $item, Entity $damager) {
        $e = [$entity, $damager];
        $sound = "AnvilFall";
        S::playSound($e, $sound);
        $entity->getInventory()->removeItem($item);
        $motion = $entity->getDirectionVector()->multiply(0.4);
        $entity->getLevel()->dropItem($entity->add(0, 1.3, 0), $item, $motion, 40);
    }
    
    public function volley(Entity $entity, Entity $damager) {
        $e = [$entity, $damager];
        $sound = "Pop";
        S::playSound($e, $sound);
        $entitymotionx = $entity->getMotion()->x;
        $levelproduct = 3 * 0.05;
        $entitymotiony = $levelproduct + 0.75;
        $entitymotionz = $entity->getMotion()->z;
        $newmotion = new Vector3($entitymotionx, $entitymotiony, $entitymotionz);
        $entity->setMotion($newmotion);
    }
    
    public function lastChance(Entity $entity, $event, Entity $damager) {
        $e = [$entity, $damager];
        $sound = "EnderDragon";
        S::playSound($e, $sound);
        $event->setCancelled(true);
        $entityhealth = $entity->getHealth(true);
        if ($entity >= 16) {
            $entitymaxhealth = $entity->getMaxHealth();
            $entity->setHealth($entitymaxhealth);
        } else if ($entity < 16) {
            $healthcalc = $entityhealth + 4;
            $entity->setMaxHealth($healthcalc);
        }
    }
    
    public function trueMiner($event, Entity $player) {
        $e = [$player];
        $sound = "Orb";
        S::playSound($e, $sound);
        $diamond = Item::get(IL::$diamondore[0], IL::$diamondore[1], 1);
        $newdrop = [$diamond];
        $event->setDrops($newdrop);
    }
    
    public function iceArrow(Entity $entity, Entity $damager) {
        $e = [$entity];
        $sound = "Portal";
        S::playSound($e, $sound);
        $effect = Effect::getEffect(Effect::SLOWNESS);
        $effect->setAmplifier(3);
        $effect->setDuration(15);
        $effect->setVisible(false);
        $entity->addEffect($effect);
    }
    
    public function poisonArrow(Entity $entity, Entity $damager) {
        $e = [$entity];
        $sound = "Portal";
        S::playSound($e, $sound);
        $effect = Effect::getEffect(Effect::POISON);
        $effect->setAmplifier(3);
        $effect->setDuration(15);
        $effect->setVisible(false);
        $entity->addEffect($effect);
    }
    
    public function absorb(Entity $entity, Entity $damager) {
        $e = [$entity, $damager];
        $sound = "Blaze";
        S::playSound($e, $sound);
        $entityhealth = $entity->getHealth();
        $damagerhealth = $damager->getHealth();
        $newehealth = $entityhealth - 4;
        if ($damagerhealth >= 16) {
            $newdhealth = $damager->getMaxHealth();
            $damager->setHealth($newdhealth);
        } else {
            $newdhealth = $damagerhealth + 4;
            $damager->setHealth($newdhealth);
        }
    }
    
    public function mechanic(Entity $entity, Item $tool) {
        $e = [$entity];
        $sound = "AnvilUse";
        S::playSound($e, $sound);
        $newdamage = $tool->getDamage() - 1;
        if ($newdamage < 0) {
            $item->setDamage(0);
        } else {
            $item->setDamage($newdamage);
        }
    }
    
    public function miniBlackHole(Entity $entity) {
        $e = [$entity];
        $sound = "EnderDragon";
        S::playSound($e, $sound);
        $random = new Random();
        $level = $entity->getLevel();
        $explosionentity = "PrimedTNT";
        $entx = $entity->x;
        $enty = $entity->y;
        $entz = $entity->z;
        $r = $random->nextFloat() * 1.5 - 1;
        $r2 = $random->nextFloat() * 1.5;
        $dtagarray = [
            new DoubleTag("", $entx), 
            new DoubleTag("", $enty), 
            new DoubleTag("", $entz)
        ];
        $dtagarray2 = [
            new DoubleTag("", $r), 
            new DoubleTag("", $r2), 
            new DoubleTag("", $r)
        ];
        $ftagarray = [
            new FloatTag("", 0), 
            new FloatTag("", 0)
        ];
        $ltag = new ListTag("Pos", $dtagarray);
        $ltag2 = new ListTag("Motion", $dtagarray2);
        $ltag3 = new ListTag("Rotation", $ftagarray);
        $btag = new ByteTag("Fuse", 40);
        $ltagarray = [
            "Pos" => $ltag, 
            "Motion" => $ltag2, 
            "Rotation" => $ltag3, 
            "Fuse" => $btag
        ];
        $ctag = new CompoundTag("", $ltagarray);
        $tnt = Entity::createEntity($explosionentity, $level, $ctag);
        $tnt->spawnToAll();
    }
    
}