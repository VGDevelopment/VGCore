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
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\entity\Item as ItemEntity;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\{
    NBT,
    tag\ByteTag,
    tag\CompoundTag,
    tag\ListTag
};
use pocketmine\utils\Random;
use VGCore\factory\{
    data\FireworkData,
    entity\projectile\FWR,
    item\Firework as FItem,
    particle\explosion\FireworkExplosion as FE
};
// >>>
use VGCore\SystemOS;
use VGCore\task\cosmetic\CrateTask;

class Chest {
    private static $plugin;
    const CRATES = [
        '259:71:259' => ["crate1", 'common'],
        '0:0:0' => ["crate2", 'rare'],
        '0:0:0' => ["crate3", 'legendary']
    ];

    public static function start($plugin){
      self::$plugin = $plugin;
    }

    public function onJoin(PlayerJoinEvent $event) {
      $player = $event->getPlayer();
      if($player instanceof Player) {
        foreach(self::CRATES as $pos => $data){
          $pos = explode(":", $pos);
          self::spawnText($player->getLevel()->getBlock(new Vector3((int) $pos[0], (int) $pos[1], (int) $pos[2])), TF::GOLD.$data[0]."\n".TF::AQUA.$data[1]);
        }
      }
    }

    public function EntityLevelChangeEvent(EntityLevelChangeEvent $event){
      $player = $event->getEntity();
      if($player instanceof Player) {
        foreach(self::CRATES as $pos => $data){
          $pos = explode(":", $pos);
          self::spawnText($player->getLevel()->getBlock(new Vector3((int) $pos[0], (int) $pos[1], (int) $pos[2])), TF::GOLD.$data[0]."\n".TF::AQUA.$data[1]);
        }
      }
    }

    public static function resetCrate(Block $block): void {
      $pk = new BlockEventPacket();
  		$pk->x = $block->x;
  		$pk->y = $block->y;
  		$pk->z = $block->z;
  		$pk->eventType = 1;
  		$pk->eventData = 0;
      foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }

      $random = new Random();
      $yaw = $random->nextBoundedInt(360);
      $f = $random->nextFloat();
      $d = 5.0;
      $vectorcalc = 90 + ($f * $d - $d / 2);
      $pitch = -1 * (float)$vectorcalc;
      $nbt = Entity::createBaseNBT($block, null, $yaw, $pitch);
      $color = [4, 4, 4];
      $fade = [5, 5, 5];
      $ex = new FE($color, $fade, true, false, 4);
      $data = new FireworkData(1, [$ex]);
      $firework = new FItem();
      $customnbt = $firework::sendToNBT($data);
      $nbt->setNamedTag($customnbt);
      $rocket = new FWR($block->level, $nbt, null, $firework, null);
      $block->level->addEntity($rocket);
      $rocket->spawnToAll();
      self::despawnText($block);
      unset(SystemOS::$localdata[json_encode($block->asVector3())]);
      foreach(self::CRATES as $pos => $data){
        $pos = explode(":", $pos);
        self::spawnText($player->getLevel()->getBlock(new Vector3((int) $pos[0], (int) $pos[1], (int) $pos[2])), TF::GOLD.$data[0]."\n".TF::AQUA.$data[1]);
      }
    }

    public static function openChest(Block $block): void {
      $pk = new BlockEventPacket();
  		$pk->x = $block->x;
  		$pk->y = $block->y;
  		$pk->z = $block->z;
  		$pk->eventType = 1;
  		$pk->eventData = 1;
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
            self::openChest($block);
            return true;
        }
    }

    public static function spawnText(Block $block, string $text = null): void{
      if($text == null) $text = (new Prize())->prizes();
      self::despawnText($block);
      $pk = new AddPlayerPacket();
      $pk->uuid = UUID::fromRandom();
      $pk->username = "Crate";
      $eid = Entity::$entityCount++;
      $pk->entityRuntimeId = $eid;
      SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"] = $eid;
      $pk->position = new Vector3($block->x+.5,$block->y+1.2,$block->z+.5);
      $pk->item = Item::get(0,0,0);
      $flags = (
        (1 << Entity::DATA_FLAG_CAN_SHOW_NAMETAG) |
        (1 << Entity::DATA_FLAG_ALWAYS_SHOW_NAMETAG) |
        (1 << Entity::DATA_FLAG_IMMOBILE)
      );
      $pk->metadata = [
        Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, $flags],
        Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $text],
        Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
      ];
      foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
        $player->dataPacket($pk);
      }
    }

    public static function despawnText(Block $block): void{
      if(isset(SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"])){
        $pk = new RemoveEntityPacket();
        $pk->entityUniqueId = SystemOS::$localdata[json_encode($block->asVector3())]["floating_tag"];
        foreach(self::$plugin->getServer()->getOnlinePlayers() as $player){
          $player->dataPacket($pk);
        }
      }
    }

}
