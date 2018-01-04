<?php

namespace VGCore\task\music;

use pocketmine\Level;
use pocketmine\Server;
use pocketmine\scheduler\PluginTask;
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

class PlayMusicTask extends PluginTask {
    
    private $plugin;
    private $mp;
    private $song;
    private $sound;
    private $songtick;
    private $songlenght;
    
    public function __construct(SystemOS $plugin, MusicPlayer $mp) {
        parent::__construct($plugin);
        $this->plugin = $plugin;
        $this->mp = $mp;
        $this->song = $mp->song;
        $this->sound = $this->song->nbsound;
        $this->songtick = $this->song->tick;
        $this->songlenght = $this->song->lenght;
    }
    
    public function onRun(int $currentTick) {
        $songsound = $this->sound[$this->songtick];
        if (isset($songsound)) {
            $i = 0;
            foreach ($songsound as $sd) {
                $this->mp->play($sd[0], $sd[1], $i);
                $i++;
            }
        }
        $this->songtick = $this->mp->song->tick++;
        if ($this->songtick > $this->songlenght) {
            $this->mp->makeTask();
        }
    }
    
}