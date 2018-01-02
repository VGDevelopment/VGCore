<?php

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\Player;

use pocketmine\scheduler\PluginTask;
// >>>
use VGCore\sound\nbs\NBSNote;
use VGCore\sound\nbs\NBSLayer;
use VGCore\sound\nbs\NBSParser;

use VGCore\SystemOS;

use VGCore\lobby\music\MusicPlayer;

class PlayMusicTask extends PluginTask {
    
    private $musicplayer;
    
    public $song = null;
    public $songfile = "";
    public $lenght = 0;
    
    public function __construct(SystemOS $plugin, MusicPlayer $musicplayer, string $songfile, NBSParser $song) {
        parent::__construct($plugin);
        $this->musicplayer = $musicplayer;
        $this->song = $song;
        $this->songfile = $songfile;
        MusicPlayer::$task[] = $this->getTaskId();
    }
    
    public function onRun(int $currentTick) {
        if ($this->lenght > $this->song->lenght) {
            $this->getHandler()->cancel();
            $this->musicplayer->play();
            return;
        }
        $note = $this->song->noteTick(floor($this->lenght));
        $this->lenght++;
        if (empty($note)) {
            return;
        }
        foreach ($note as $n) {
            $pk = new LevelSoundEventPacket();
            $pk->sound = LevelSoundEventPacket::SOUND_NOTE;
            $value = $n->inst;
            switch ($value) {
                case NBSParser::PIANO: {
                    $pk->extraData = 0;
                    break;
                }
                case NBSParser::BASS_TWICE: {
                    $pk->extraData = 4;
                    break;
                }
                case NBSParser::BASS_DRUM: {
                    $pk->extraData = 1;
                    break;
                }
                case NBSParser::SNARE: {
                    $pk->extraData = 2;
                    break;
                }
                case NBSParser::CLICK: {
                    $pk->extraData = 3;
                    break;
                }
                default: {
                    $pk->extraData = $value;
                    break;
                }
            }
            $pk->pitch = intval($n->key - 33);
            $allp = $this->plugin->getServer()->getOnlinePlayers();
            foreach ($allp as $p) {
                $pk2 = clone $pk;
                $pk2->position = $p->asVector3()->add(0, -MusicPlayer::volumePercentage() + 1);
                $p->dataPacket($pk2);
            }
        }
    }
    
}