<?php

namespace VGCore\lobby\music;

use pocketmine\Level;
use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\math\Math;
use pocketmine\level\format\Chunk;
use pocketmine\level\format\FullChunk;
use pocketmine\utils\BinaryStream;
use pocketmine\utils\Binary;
//
use VGCore\SystemOS;
use VGCore\sound\nbs\Song;
use VGCore\task\music\PlayMusicTask;

class MusicPlayer {
    
    public $song;
    public $task = [];
    public $name;
    
    private $plugin;
    private $dir;
    private $dircount;
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
        $this->dir = $plugin->getServer()->getDataPath() . "plugins/VGCore-master/songlist/";
        $this->dircount = $this->dirCount($this->dir);
    }
    
    public function dirCount() {
        $scan = scandir($filepath);
        $size = sizeof($scan);
        $count = ($size > 2) ? $size - 2 : 0;
        return $count;
    }
    
    public function randomFile($filefolder = "", $ext = ".*") {
        $newfolder = trim($filefolder);
        $folder = ($newfolder === '') ? './' : $newfolder;
        if (!(is_dir($folder))) {
            return false;
        }
        $allfile = [];
        $dir = opendir($folder);
        if ($dir) {
            $file = readdir($dir);
            while ($file) {
                $preg1 = preg_match('/^\.+$/', $file);
                $preg2 = preg_match('/\.(' . $ext . ')$/', $file);
                if ($preg1 && $preg2) {
                    $allfile[] = $file;
                }
            }
            closedir($dir);
        } else {
            return false;
        }
        $totalfilecount = count($allfile);
        if ($totalfilecount === 0) {
            return false;
        }
        $microtime = microtime() * 1000000;
        $mtx2 =  (double)$microtime;
        mt_srand($mtx2);
        $random = mt_rand(0, $totalfilecount - 1);
        if (!(isset($allfile[$random]))) {
            return false;
        }
        if (function_exists("iconv")) {
            $rename = iconv('gbk', 'UTF-8', $allfile[$random]);
        } else {
            $rename = $allfile[$random];
        }
        $this->songname = str_replace('.nbs', '', $rename);
        return $folder . $allfile[$random];
    }
    
    public function checkSum() {
        $dircount = $this->dircount;
        $randfile = $this->randomFile($this->dir, "nbs");
        if ($dircount > 0 && $randfile !== false) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getNoteBlock(int $x, int $y, int $z, Level $level) {
        $near = [];
        $maxmin = [
            $x - 5,
            $x + 5,
            $y - 5,
            $y + 5,
            $z - 5,
            $z + 5
        ];
        for ($x = $maxmin[0]; $x <= $maxmin[1]; ++$x) {
            for ($y = $maxmin[2]; $y <= $maxmin[3]; ++$y) {
                for ($z = $maxmin[4]; $z <= $maxmin[5]; ++$z) {
                    $vec3 = new Vector3($x, $y, $z);
                    $block = $level->getBlock($vec3);
                    if ($block->getID() === 25) {
                        $near[] = $block;
                    }
                }
            }
        }
        return $near;
    }
    
    public function block(int $x, int $y, int $z, Level $level) {
        $chunk = $level->getChunk($x >> 4, $z >> 4, false);
        return $chunk->getFullBlock($x & 0x0f, $y & 0x7f, $z & 0x0f);
    }
    
    public function randomMusic() {
        $dir = $this->randomFile($this->dir, "nbs");
        if ($dir) {
            $song = new Song($this->plugin, $this, $dir);
            return $song;
        } else {
            return false;
        }
    }
    
    public function play($s, $t = 0, $b = 0, Player $player) {
        if (is_numeric($s) && $s > 0) {
            $level = $player->getLevel();
            $ppos = [
                $player->x,
                $player->y,
                $player->z
            ];
            $nb = $this->getNoteBlock($ppos[0], $ppos[1], $ppos[2], $level);
            $nbclone = $nb;
            if (!(empty($nb))) {
                if ($this->song->name !== "") {
                    $player->sendPopup("§eNow playing §a" . $this->song->name . "§e...");
                } else {
                    $player->sendPopup("§eNow playing §a" . $this->name . "§e...");
                }
                $i = 0;
                while ($i < $b) {
                    if (current($nb)) {
                        next($nb);
                        $i++;
                    } else {
                        $nb = $nbclone;
                        $i++;
                    }
                }
                $block = current($nb);
                if ($block) {
                    $pk = new BlockEventPacket();
                    $pk->x = $block->x;
                    $pk->y = $block->y;
                    $pk->z = $block->z;
                    $pk->case1 = $t;
                    $pk->case2 = $s;
                    $player->dataPacket($pk);
                    $pk = new LevelSoundEventPacket();
                    $pk->sound = LevelSoundEventPacket::SOUND_NOTE;
                    $pk->x = $block->x;
                    $pk->y = $block->y;
                    $pk->z = $block->z;
                    $pk->volume = $t;
                    $pk->pitch = $s;
                    $pk->unknownBool = true;
                    $pk->unknownBool2 = true;
                    $player->dataPacket($pk);
                }
            }
        }
    }
    
    public function makeTask() {
        $this->song = $this->randomMusic();
        $this->plugin->getServer()->getScheduler()->cancelTasks($this->plugin);
        $this->task[0] = new PlayMusicTask($this->plugin, $this);
        $task = $this->task[0];
        $songspeed = 2990 / $this->song->speed;
        $this->plugin->getServer()->getScheduler()->scheduleRepeatingTask($task, $songspeed);
    }
    
}