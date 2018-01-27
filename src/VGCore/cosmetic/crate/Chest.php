<?php

namespace VGCore\cosmetic\crate;

use pocketmine\event\Listener;
use pocketmine\block\Block;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

use pocketmine\item\Item;
use pocketmine\network\mcpe\protocol\{BlockEventPacket, RemoveEntityPacket};
use pocketmine\utils\UUID;
use pocketmine\network\mcpe\protocol\AddPlayerPacket;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\level\sound\ClickSound;
// >>>
use VGCore\SystemOS;
use VGCore\task\cosmetic\CrateTask;

class Chest {
    private static $plugin;

    public static function start($plugin){
      self::$plugin = $plugin;
    }

    public static function resetCrate(Block $block): void {
      unset(SystemOS::$localdata[json_encode($block->asVector3())]);
      $pk = new BlockEventPacket();
      $pk->x = $block->getX();
      $pk->y = $block->getY();
      $pk->z = $block->getZ();
      $pk->case1 = 1;
      $pk->case2 = 0;
      foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }
    }

    public static function openCrate(Player $player, Block $block, string $key): bool {
        $blockdata = SystemOS::$localdata;
        $pos = $block->asVector3();
        if (isset($blockdata[json_encode($pos)]['InUse'])) {
            $player->sendMessage(TF::RED . "Crate currently being used, please kindly wait.");
            return false;
        } else {
            $player->sendMessage(TF::GREEN . "Opening crate...");
            $blockdata[json_encode($pos)]["InUse"] = true;
            $blockdata[json_encode($pos)]["User"] = $player->getName();
            $blockdata[json_encode($pos)]["KeyType"] = $key;
            $blockdata[json_encode($pos)]["Tick"] = 0;
            $blockdata[json_encode($pos)]["Block"] = $block;
            SystemOS::$localdata = $blockdata;//to resave...
            $task = new CrateTask(self::$plugin, json_encode($pos));
            self::$plugin->getServer()->getScheduler()->scheduleRepeatingTask($task, 1);
            return true;
        }
    }

    public static function spawnText(Block $block){
      self::despawnText($block);
      $pk = new AddPlayerPacket();
      $uuid = UUID::fromRandom();
      $pk->uuid = $uuid;
      SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"] = $uuid;
      $pk->username = "Crate";
      $pk->entityRuntimeId = Entity::$entityCount++;
      $pk->position = new Vector3($block->x+.5,$block->y+1.2,$block->z+.5);
      $pk->item = Item::get(0,0,0);
      $flags = (
        (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
        (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
        (1 << Entity::DATA_FLAG_IMMOBILE)
      );
      $pk->metadata = [
        Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
        Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, "Testing"],
        Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
      ];
      foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }
    }

    public static function despawnText(Block $block){
      if(isset(SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"])){
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"];
        foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
          $player->dataPacket($pk);
        }
      }
    }

}
