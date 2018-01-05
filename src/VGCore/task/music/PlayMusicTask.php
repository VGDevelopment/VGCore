<?php

namespace VGCore\task\music;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\scheduler\PluginTask;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
// >>>
use VGCore\SystemOS as OS;
use VGCore\lobby\music\MusicPlayer as MP;
use VGCore\sound\nbs\{
    NBSong as Song,
    NBSNote as Note,
    NBSLayer as Layer
};

class PlayMusicTask extends PluginTask {
    
    public $song = null;
    public $songfile = "";
    public $cl = 0;
    public $sl;
    
    private $mp;
    private $os;
    private $pl;
    
    public function __construct(OS $os, MP $mp, string $songfile, Song $song, Player $player) {
        parent::__construct($os);
        $this->os = $os;
        $this->mp = $mp;
        $this->song = $song;
        $this->songfile = $songfile;
        $this->sl = $song->lenght;
        $this->pl = $player;
        MP::$task[] = $this->getTaskId();
    }
    
    public function onRun(int $currentTick) {
        if ($this->cl > $this->sl) {
            $this->getHandler()->cancel();
            MP::instance()->play($this->pl);
            return;
        }
        $floorcl = floor($this->cl);
        $note = $this->song->noteTick($floorcl);
        $this->cl++;
        if (empty($note)) {
            return;
        }
        foreach ($note as $n) {
            $pk = new LevelSoundEventPacket();
            $pk->sound = LevelSoundEventPacket::SOUND_NOTE;
            $i = $n->instrument;
            switch ($i) {
                case Song::PIANO: {
                    $pk->extraData = 0;
                    break;
                }
                case Song::BASS_DRUM: {
                    $pk->extraData = 1;
                    break;
                }
                case Song::SNARE: {
                    $pk->extraData = 2;
                    break;
                }
                case Song::CLICK: {
                    $pk->extraData = 3;
                    break;
                }
                case Song::BASS_TWICE: {
                    $pk->extraData = 4;
                    break;
                }
                case Song::GUITAR: {
                    $pk->extraData = $i;
                    break;
                }
                case Song::FLUTE: {
                    $pk->extraData = $i;
                    break;
                }
                case Song::BELL: {
                    $pk->extraData = $i;
                    break;
                }
                case Song::CHIME: {
                    $pk->extraData = $i;
                    break;
                }
                case Song::XYLOPHONE: {
                    $pk->extraData = $i;
                    break;
                }
            }
            $pk->pitch = intval($n->key - 33);
            $pk2 = clone $pk;
            $pk2->position = $this->pl->asVector3()->add(0, -MP::soundVolume($this->pl) + 1);
            $this->pl->dataPacket($pk2);
        }
    }
    
}