<?php

namespace VGCore\factory\block;

use pocketmine\block\Block;
use pocketmine\item\Item;
use pocketmine\nbt\tag\{CompoundTag, StringTag, IntTag};
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\math\Vector3;

use VGCore\spawner\SpawnerAPI;

class MonsterSpawner extends \pocketmine\block\MonsterSpawner{

	public function __construct($meta = 0){
		$this->meta = $meta;
	}

	public function canBeActivated(): bool{
		return true;
	}

  public function onActivate(Item $item, Player $player = null): bool {
    return false;//needs work
      if ($this->entityid === 0) {
          if ($item->getId() === Item::SPAWN_EGG) {
              $level = $this->getLevel();
              $tile = $level->getTile($this);
              $this->entityid = $item->getDamage();
              if (!($tile instanceof MobSpawner)) {
                  $nbt = Tile::createNBT($this);
                  Tile::createTile('MobSpawner', $level, $nbt);
              }
              $tile->setEID($this->entityid);
              return true;
          }
      }
      return false;
  }

	public function place(Item $item, Block $block, Block $target, int $face, Vector3 $facePos, Player $player = NULL) : bool{
		$this->getLevel()->setBlock($block, $this, true, true);
		$nbt = new CompoundTag("", [
			new StringTag("id", 'MobSpawner'),
			new IntTag("x", $block->x),
			new IntTag("y", $block->y),
			new IntTag("z", $block->z),
			new IntTag("EntityId", $item->getNamedTag()->entityid->getValue()),//set the mob id in nbt because cant save anywhere else lmao
		]);
		Tile::createTile('MobSpawner', $this->getLevel(), $nbt);
		return true;
	}

	public function getDrops(Item $item) : array{
		return [Item::get(52,0,1)];//TODO maybe do silk touch thing?
	}

  public function getName(): string {
    if(!isset($this->entityid)) return "";
      if ($this->entityid === 0) {
          return "Monster Spawner";
      } else {
          var_dump($this->entityid);
          $type = SpawnerAPI::$mobtype;
          $eid = $type[$this->entityid];
          $name = ucfirst($eid ?? 'Monster') . 'Spawner';
          return $name;
      }
  }
}
