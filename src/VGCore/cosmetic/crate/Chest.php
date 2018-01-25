<?php

namespace VGCore\cosmetic\crate;

use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

use pocketmine\network\mcpe\protocol\{AddItemEntityPacket, BlockEventPacket, RemoveEntityPacket};
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\entity\Entity;
use pocketmine\level\sound\ClickSound;
// >>>
use VGCore\SystemOS;

class Chest implements Listener {
    const CRATES = [
        '260:71:260' => ["crate1", 'common'],
        '0:0:0' => ["crate2", 'rare'],
        '0:0:0' => ["crate3", 'legendary']
    ];

    public static function start(){
    }

    public static function resetCrate(Block $block): void {
      unset(SystemOS::$localdata[$block->getPosition()]);
      $pk = new BlockEventPacket();
      $pk->x = $chest->getX();
      $pk->y = $chest->getY();
      $pk->z = $chest->getZ();
      $pk->case1 = 1;
      $pk->case2 = 0;
      foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }
    }

    public static function openCrate(Player $player, Block $block): bool {
        $blockdata = SystemOS::$localdata;
        $pos = $block->getPosition();
        if (isset($blockdata[$pos]['InUse'])) {
            $player->sendMessage(TF::RED . "Crate currently being used, please kindly wait.");
            return false;
        } else {
            $player->sendMessage(TF::GREEN . "Opening crate...");
            return true;
        }
    }

    public function spawnItem(Block $block, Item $item = null){
      if($item == null) // random item
      $block->getLevel()->addSound(new ClickSound($block));

      $pk = new AddItemEntityPacket();
      $pk->entityRuntimeId = Entity::$entityCount++;
      $id = $pk->entityRuntimeId;
      $pk->type = ItemEntity::NETWORK_ID;
      $pk->position = new Vector3($block->x+.5,$block->y+1,$block->z+.5);
      $pk->item = $item;
      $pk->speedX = 0; $pk->speedY = 0; $pk->speedZ = 0; $pk->yaw = 0.0; $pk->pitch = 0.0;
      $flags = 0;
      $flags |= 1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG;
      $flags |= 1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG;
      $flags |= 1 << Entity::DATA_FLAG_IMMOBILE;
      $pk->metadata = [
          Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
          Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, "set custom name on nbt"]
      ];
      foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }

      $pk->entityRuntimeId = Entity::$entityCount++;
      $pk->position = new Vector3($block->x+.5,$block->y+1,$block->z+.5);
      $pk->item = Item::get(0,0,0);
      $pk->metadata = [
          Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
          Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, "name"]
      ];
      foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }
    }

    public function despawnItem(Block $block){
      for ($i=0; $i < 2; $i++) {
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = 1;
        foreach($this->plugin->getServer()->getOnlinePlayers() as $player){
          $player->dataPacket($pk);
        }
      }
    }

}
