<?php

namespace VGCore\listener;

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

use VGCore\enchantment\CustomEnchantment;
use VGCore\enchantment\handler\Handler;

use VGCore\lobby\pet\BasicPet;

class CustomEnchantmentListener implements Listener {
    
    public $plugin;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->handler = new Handler($this->plugin);
    }
    
    public function onBB(BlockBreakEvent $event) {
        $player = $event->getPlayer();
        $item = $player->getInventory()->getItemInHand();
        $block = $event->getBlock();
        $enchantment = $this->plugin->getEnchantment($item, CustomEnchantment::TRUEAXE);
        if ($enchantment !== null) {
            $chance = mt_rand(0, 10); // no need of doing mt_rand(1, 100) as ratio is same and a decimal value isn't required.
            if ($chance > 6) { // should be > 6 as that would be 7, 8, 9, and 10. 4 different numbers.
                if ($block->getId() == Block::WOOD || $block->getId() == Block::WOOD2) {
                    if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                        $this->plugin->mined[$player->getLowerCaseName()] = 0;
                        $this->handler->trueAxe($player, $block);
                    }
                }
            }
            $event->setInstaBreak(true); 
        }
        $enchantment = $this->plugin->getEnchantment($item, CustomEnchantment::TRUEMINER);
        if ($enchantment !== null) {
            $chance = mt_rand(0, 100);
            if ($chance > 95) {
                $this->handler->trueMiner($event, $player);
            }
        }
    }
    
    public function onEDE(EntityDamageEvent $event) {
        if ($event instanceof EntityDamageByEntityEvent) {
            $damager = $event->getDamager();
            $entity = $event->getEntity();
            if ($damager instanceof Player && !($entity instanceof BasicPet)) {
                $damageritem = $damager->getInventory()->getItemInHand();
                $entityitem = $entity->getInventory()->getItemInHand();
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::WARAXE);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 100);
                    if ($chance > 95) {
                        $this->handler->warAxe($entity, $damager);
                    }
                }
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::DISABLE);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 10);
                    if ($chance > 9) {
                        $this->handler->disable($entity, $entityitem, $damager);
                    }
                }
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::VOLLEY);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 10);
                    if ($chance > 7) {
                        $level = $entity->getLevel();
                        $this->handler->volley($entity, $damager);
                    }
                }
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::ABSORB);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 10);
                    if ($chance > 8) {
                        $this->handler->absorb($entity, $damager);
                    }
                }
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::MECHANIC);
                if ($enchantment !== null) {
                    $this->handler->mechanic($damager, $damageritem);
                }
                foreach ($entity->getInventory()->getArmorContents() as $slot => $armor) {
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchantment::LASTCHANCE);
                    if ($enchantment !== null) {
                        $chance = mt_rand(0, 10);
                        if ($chance > 5) {
                            $this->handler->lastChance($entity, $event, $damager);
                        }
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchantment::MECHANIC);
                    if ($enchantment !== null) {
                        $this->handler->mechanic($entity, $armor);
                    }
                    $enchantment = $this->plugin->getEnchantment($armor, CustomEnchantment::MINIBLACKHOLE);
                    if ($enchantment !== null) {
                        if ($event->getDamage() >= $entity->getHealth()) {
                            $chance = mt_rand(0, 10);
                            if ($chance > 5) {
                                $this->handler->miniBlackHole($entity);
                            }
                        }
                    }
                    
                }
            }
        } else if ($event instanceof EntityDamageByChildEntityEvent) {
            $damager = $event->getDamager();
            $child = $event->getChild();
            $entity = $event->getEntity();
            if ($damager instanceof Player && $child instanceof Projectile) {
                $damageritem = $damager->getInventory()->getItemInHand();
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::ICEARROW);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 10);
                    if ($chance > 9) {
                        $this->handler->iceArrow($entity, $damager);
                    }
                }
                $enchantment = $this->plugin->getEnchantment($damageritem, CustomEnchantment::POISONARROW);
                if ($enchantment !== null) {
                    $chance = mt_rand(0, 10);
                    if ($chance > 9) {
                        $this->handler->poisonArrow($entity, $damager);
                    }
                }
            }
        }
    }
    
}