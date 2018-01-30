<?php
namespace VGCore\listener;

use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\utils\{
  UUID,
  TextFormat as TF
};

use pocketmine\event\player\{
  PlayerJoinEvent,
  PlayerMoveEvent
};
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\math\Vector3;
use pocketmine\entity\{Entity, Skin};
use pocketmine\network\mcpe\protocol\{
  AddPlayerPacket,
  PlayerSkinPacket,
  InventoryTransactionPacket,
  MoveEntityPacket
};
use pocketmine\item\{
  ItemFactory,
  Item
};
// >>>
use VGCore\SystemOS;

class NPCListener implements Listener{
  private static $plugin;
  private static $spawned = [];

  const NPC = [
    'test' => ["command" => 'say works', "position" => '162:7:119', "world" => 'Sam2', "skin" => null]
  ];

  public function __construct(SystemOS $plugin) {
    self::$plugin = $plugin;
  }

  public function PlayerMoveEvent(PlayerMoveEvent $event){
    $player = $event->getPlayer();
    foreach(self::$spawned as $name => $data){
      $position = explode(":", self::NPC[$name]["position"]); 
      $position = new Vector3((int) $position[0], (int) $position[1], (int) $position[2]);
      $this->lookAtPlayer(self::$spawned[$name]["eid"], $position, $player);
    }
  }

  public function lookAtPlayer($eid, Vector3 $entity, Player $player){
    $x=$entity->x-$player->x;
    $y=$entity->y-$player->y;
    $z=$entity->z-$player->z;
    if(sqrt($x*$x+$z*$z)==0 || sqrt($x*$x+$z*$z+$y*$y)==0) return true;
    $yaw=asin($x/sqrt($x*$x+$z*$z))/3.14*180;
    $pitch=round(asin($y/sqrt($x*$x+$z*$z+$y*$y))/3.14*180);
    if($z>0) $yaw=-$yaw+180;
    $position = new Vector3($entity->x, $entity->y+1.62, $entity->z);
    $pk = new MoveEntityPacket();
		$pk->entityRuntimeId = $eid;
		$pk->position = $position;
		$pk->yaw = $yaw;
		$pk->pitch = $pitch;
		$pk->headYaw = $yaw;
		$player->level->addChunkPacket($player->chunk->getX(), $player->chunk->getZ(), $pk);
    return true;
  }

  public static function spawnNPC(Vector3 $position, string $name, string $command){
    if(isset(self::$spawned[$name])) return;
    self::$spawned[$name]["eid"] = Entity::$entityCount++;
    self::$spawned[$name]["command"] = $command;
    $pk = new AddPlayerPacket();
    $pk->entityRuntimeId = self::$spawned[$name]["eid"];
    $pk->uuid = UUID::fromRandom();
    $pk->username = $name;
    $pk->position = $position;
    $pk->item = ItemFactory::get(Item::AIR, 0, 0);
    $skinPk = new PlayerSkinPacket();
		$skinPk->uuid = $pk->uuid;
		//$skinPk->skin = set skin here;

    self::$plugin->getServer()->broadcastPacket(self::$plugin->getServer()->getOnlinePlayers(), $pk);
    //self::$plugin->getServer()->broadcastPacket(self::$plugin->getServer()->getOnlinePlayers(), $skinPk);
  }

  public function EntityLevelChangeEvent(EntityLevelChangeEvent $event){
    $player = $event->getEntity();
    if($player instanceof Player) {
      foreach(self::NPC as $name => $data){
        if($player->getLevel()->getName() == $data["world"]){
          $position = explode(":", $data["position"]); $position = new Vector3((int) $position[0], (int) $position[1], (int) $position[2]);
          $this->spawnNPC($position, $name, $data["command"]);
        }
      }
    }
  }

  public function onJoin(PlayerJoinEvent $event) {
    $player = $event->getPlayer();
    if($player instanceof Player) {
      foreach(self::NPC as $name => $data){
        if($player->getLevel()->getName() == $data["world"]){
          $position = explode(":", $data["position"]); $position = new Vector3((int) $position[0], (int) $position[1], (int) $position[2]);
          $this->spawnNPC($position, $name, $data["command"]);
        }
      }
    }
  }

  public function onPacketReceived(DataPacketReceiveEvent $event){
    $pk = $event->getPacket();
    $player = $event->getPlayer();
    if ($pk instanceof InventoryTransactionPacket) {
      if ($pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY && $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK) {
        $entity = $pk->trData->entityRuntimeId;
        foreach(self::$spawned as $name => $data){
          if($entity == $data["eid"]){
            self::$plugin->getServer()->getCommandMap()->dispatch($player, $data["command"]);
          }
        }
      }
    }
  }

}
