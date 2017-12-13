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
// >>>
use VGCore\SystemOS;

use VGCore\enchantment\CustomEnchantment;
use VGCore\enchantment\handler\Handler;

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
            $chance = mt_rand(1, 10); // no need of doing mt_rand(1, 100) as ratio is same and a decimal value isn't required.
            var_dump($chance);
            if ($chance >= 6) {
                if ($block->getId() == Block::WOOD || $block->getId() == Block::WOOD2) {
                    if (!isset($this->plugin->using[$player->getLowerCaseName()]) || $this->plugin->using[$player->getLowerCaseName()] < time()) {
                        $this->plugin->mined[$player->getLowerCaseName()] = 0;
                        $this->handler->cutTree($player, $block);
                    }
                }
            }
            $event->setInstaBreak(true); 
        }
    }
    
}