<?php

namespace VGCore\lobby\music;

use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;

use pocketmine\Player;

use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;

use pocketmine\scheduler\PluginTask;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
// >>> 
use VGCore\SystemOS;

use VGCore\sound\nbs\NBSNote;
use VGCore\sound\nbs\NBSLayer;
use VGCore\sound\nbs\NBSParser;

use VGCore\task\music\PlayMusicTask;

class MusicPlayer {
    
    public static $songlist = [];
    public static $task = [];
    
    private static $volume = 100;
    private static $playlist= [];
    
    private $plugin;
    
    public static function playRandom() {
        if (empty(self::$songlist)) {
            return "";
        }
        return self::$songlist[array_rand(self::$songlist)];
    }
    
    public static function addSongToPlaylist($filename) {
        self::$playlist[] = $filename;
    }
    
    public static function nextSong() {
        $song = array_pop(self::$playlist);
        if ($song === null) {
            $song = self::playRandom();
        }
        var_dump(self::$songlist);
        return $song;
    }
    
    public static function volume() {
        return self::$volume;
    }
    
    public static function volumePercentage() {
        $volumeperc = self::$volume / 100;
        return 50 - (50 * $volumeperc);
    }
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function playSong($filename) {
        $song = $this->plugin->getDataFolder() . "resources/songlist/" . $filename . ".nbs";
        $this->play($song);
    }
    
    public function play(string $songfile = "") {
        $songfile = self::nextSong();
        if ($songfile === "") {
            $this->plugin->getServer()->getLogger()->info("No songs found. ERROR");
        }
        $song = null;
        try {
            $song = new NBSParser($songfile);
        } catch (PluginException $exception) {
            // needed a catch
        }
        if (!($song instanceof NBSParser)) {
            $this->play();
            return;
        }
        $base = basename($songfile, ".nbs");
        $floor = floor($song->tempo / 100) / 2.5;
        $eq1 = 20 * 3;
        $plugin = $this->plugin;
        $plugin->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new PlayMusicTask($plugin, $base, $song), $eq1, $floor);
    }
    
}