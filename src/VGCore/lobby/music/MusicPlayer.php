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
use VGCore\sound\nbs\{
    NBSong,
    NBSNote,
    NBSLayer
};
use VGCore\task\music\PlayMusicTask;

class MusicPlayer {
    
    // static to make as fast as possible on execution. TIP: True for any language, static is the fastest way of methods.
    
    private static $songlist = [];
    private static $playlist = [];
    private static $vol;
    
    private static $instance = null;
    
    public static $task = [];
    
    private $plugin;
    
    public static function instance(): self {
        return self::$instance;
    }
    
    public static function start(SystemOS $plugin): void {
        self::$instance = new MusicPlayer($plugin);
        $datafolder = $plugin->getDataFolder();
        @mkdir($datafolder . "data/nbs");
        self::$songlist = glob($datafolder . "data/nbs/*.nbs");
        self::$vol = new Config($datafolder . "data/nbs/vol.yml");
    }
    
    public static function songlist(): array {
        return self::$songlist;
    }
    
    public static function volume(Player $player) {
        return self::$vol->get($player->getName(), 100);
    }
    
    public static function setVolume(Player $player, float $volume) {
        self::$vol->set($player->getName(), $volume);
    }
    
    public static function soundVolume(Player $player) {
        $v = self::volume($player) / 100;
        return 50 - (50 * $v);
    }
    
    public static function randomSong(): string {
        if (empty(self::$songlist)) {
            return "";
        } else {
            $random = array_rand(self::$songlist);
            return self::$songlist[$random];
        }
    }
    
    public static function nextSong(): string {
        $newsong = array_pop(self::$songlist);
        if ($newsong === null) {
            $randomsong = self::randomSong();
            return $randomsong;
        } else {
            return $newsong;
        }
    }
    
    public static function playlistAdd(string $songfile): void {
        self::$playlist = $songfile;
    }
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;
    }
    
    public function play(Player $player) {
        $songfile = self::randomSong();
        if ($songfile === "") {
            $this->plugin->getLogger()->info("No songs found...");
            return;
        }
        $song = null;
        try {
            $song = new NBSong($songfile);
        } catch (PluginException $ex) {
            //
        }
        if (!($song instanceof NBSong)) {
            $this->play();
            return;
        }
        $basename = basename($songfile, ".nbs");
        $task = new PlayMusicTask($this->plugin, $this, $basename, $song, $player);
        $server = $this->plugin->getServer();
        $scheduler = $server->getScheduler();
        $floorcalc = floor($song->tempo / 100) / 2.5;
        $scheduler->scheduleDelayedRepeatingTask($task, 20 * 3, $floorcalc);
    }
    
}