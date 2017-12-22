<?php

namespace VGCore\lobby\npc\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;

use pocketmine\level\Level;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\IntTag;

class StandHumanNPC extends Human {
    
    private $ntv
    private $nts
    
    public function __construct(Level $level, CompoundTag $nbt) {
        parent::__construct($level, $nbt);
        $this->ntv = $this->namedtag->NameVisibility;
        if (!isset($ntvisibility)) {
            $ntvisibility = new IntTag("NameVisibility", 2);
        }
        $ntnvalue = $this->ntv->getValue();
        switch ($ntvalue) {
            case 0:
                $this->setNameTagVisible(false);
				$this->setNameTagAlwaysVisible(false);
				break;
			case 1:
                $this->setNameTagVisible(true);
				$this->setNameTagAlwaysVisible(false);
				break;
			case 2:
                $this->setNameTagVisible(true);
				$this->setNameTagAlwaysVisible(true);
				break;
			default:
                $this->setNameTagVisible(true);
				$this->setNameTagAlwaysVisible(true);
				break;
        }
        $this->nts = $this->namedtag->Scale;
        if (!isset($ntscale)) {
            $ntscale = new FloatTag("Scale", 1.0);
        }
        $ntsvalue = $this->nts->getValue();
        $this->setDataProperty(self::DATA_SCALE, self::DATA_TYPE_FLOAT, $ntsvalue);
    }
    
    public function saveNBT() {
        parent::saveNBT();
        $visibility = 0;
        $isntv = $this->isNameTagVisible();
        if ($isntv === true) {
            $visibility = 1;
            $isntav = $this->isNameTagAlwaysVisible();
            if ($isntav === true) {
                $visibility = 2;
            }
        }
        $scale = $this->getDataProperty(Entity::DATA_SCALE);
        $this->ntv = new IntTag("NameVisibility", $visibility);
        $this->nts = new FloatTag("Scale", $scale);
    }
    
    protected function sendSpawnPacket(Player $player) : void {
        parent::sendSpawnPacket($player);
        $playername = $this->getDisplayName($player);
        $datatsarray = [self::DATA_TYPE_STRING, $playername];
        $datantarray = [self::DATA_NAMETAG => $datatsarray];
        $this->sendData($player, $datantarray);
        if (isset($this->namedtag["MenuName"]) || $this->namedtag["MenuName"] !== "") {
            $uniqueid = $this->getUniqueId();
            $id = $this->getId();
            $nt = $this->namedtag["MenuName"];
            $skin =  $this->skin;
            $earray = [$player];
            $server = $player->getServer();
            $server->updatePlayerListData($uniqueid, $id, $nt, $skin, $earray);
        }
    }
    
}