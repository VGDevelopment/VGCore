<?php

namespace VGCore\lobby\npc;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\ShortTag;
use pocketmine\nbt\tag\StringTag;

use pocketmine\utils\TextFormat as Chat;

use pocketmine\entity\Entity;
// >>>
use VGCore\SystemOS;

use VGCore\lobby\npc\entity\StandHumanNPC as SHNPC;

class NPCSystem {
    
    private $plugin;
    
    public $cratenpc = [136, 6, 106]; 
    
    public $entity = ["StandHumanNPC"];
    
    public function construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function start() {
        $classnpc = [
            SHNPC::class
        ];
        foreach ($classnpc as $class) {
            Entity::registerEntity($class, true);
        }
        $this->spawnCrateNPC();
    }
    
    public function spawnCrateNPC() {
        $cratenpc = $this->cratenpc;
        $dtag1 = new DoubleTag("", $cratenpc[0]);
        $dtag2 = new DoubleTag("", $cratenpc[1]);
        $dtag3 = new DoubleTag("", $cratenpc[2]);
        $dtagarray1 = [
            $dtag1,
            $dtag2,
            $dtag3
        ];
        $dtag4 = new DoubleTag("", 0);
        $dtag5 = new DoubleTag("", 0);
        $dtag6 = new DoubleTag("", 0);
        $dtagarray2 = [
            $dtag4,
            $dtag5,
            $dtag6
        ];
        $ftag1 = new FloatTag("", 0);
        $ftag2 = new FloatTag("", 0);
        $ftagarray = [
            $ftag1,
            $ftag2
        ];
        $skin = "SantaClaus";
        $skindata = $this->skinData($skin);
        $stag = new StringTag("Data", $skindata);
        $stagarray = [
            "Data" => $stag
        ];
        $ltag1 = new ListTag("Pos", $dtagarray1);
        $ltag2 = new ListTag("Motion", $dtagarray2);
        $ltag3 = new ListTag("Rotation", $ftagarray);
        $ctag1 = new CompoundTag("skin", $stagarray);
        $mixtagarray = [
            "Pos" => $ltag1,
            "Motion" => $ltag2,
            "Rotation" => $ltag3,
            "Skin" => $ctag1
        ];
        $ctag2 = new CompoundTag("", $mixtagarray);
        $level = $this->plugin->getServer()->getLevelByName("Sam2");
        $npc = new SHNPC($level, $ctag2);
        $npc->setNameTag("I am SantaClaus! :P");
        $npc->setNameTagVisible();
        $npc->setNameTagAlwaysVisible();
        $npc->spawnToAll();
    }
    
    public function skinData(string $skin) {
        $path = $this->plugin->getDataFolder() . "/skin/" . $skin;
        $image = imagecreatefrompng($path);
        $imagesize = getimagesize($path)[1];
        $int = (int)$imagesize;
        for ($y = 0; $y < $int; $y++) {
            for ($x = 0; $x < 64; $x++) {
                $icat = imagecolorat($image, $x, $y);
                $eq1 = $icat >> 24;
                $inteq1 = (int)$eq1;
                $eq2 = (~$inteq1) << 1;
				$a = $eq2 & 0xff;
				$eq3 = $icat >> 16;
				$r = $eq4 & 0xff;
				$eq4 = $icat >> 8;
				$g = $eq4 & 0xff;
				$b = $icat & 0xff;
				$skinbyte = chr($r) . chr($g) . chr($b) . chr($a);
            }
        }
        imagedestroy($image);
        return $skinbyte;
    }
    
}