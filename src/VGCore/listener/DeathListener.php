<?php

namespace VGCore\listener;

use pocketmine\event\{
    Listener,
    player\PlayerDeathEvent,
    entity\EntityDamageEvent,
    entity\EntityDamageByEntityEvent
};

use pocketmine\Player;

use pocketmine\utils\TextFormat as Chat;
// >>>
use VGCore\data\DeathMessage;

use VGCore\user\manager\UserSystem;

class DeathListener implements Listener {

    const DEATH_TIME_THRESHOLD = 6;

    private static $os;
    private static $server;
    private static $killerlist = [];
    private static $ktime = [];

    public function __construct(SystemOS $os) {
        self::$os = $os;
        self::$server = $os->getServer();
    }

    /**
     * @priority MONITOR
     *
     * @param EntityDamageEvent $event
     * @return void
     */
    public function onDamage(EntityDamageEvent $event) {
        if ($event instanceof EntityDamageByEntityEvent) {
            if ($event->isCancelled()) {
                return;
            }
            $entity = [
                "damager" => $event->getDamager(),
                "damaged" => $event->getEntity()
            ];
            $name = [];
            foreach ($entity as $i => $v) {
                if (!($v instanceof Player)) {
                    return;
                }
                $name[$i] = $v->getName();
            }
            $t = time();
            $timesecond = strftime("%S", $t);
            $timeminute = strftime("%M", $t);
            self::$killerlist[$name["damaged"]] = $name["damager"]; // saves name of damaged & damager.
            self::$ktime[$name["damaged"] . ":" . $name["damager"]] = $timehour . ":" . $timesecond; // saves timestamp of damage.
        }
    }

    /**
     * The time calculations is independant of hour change. - I'm too lazy to add multiple parameters. :P
     *
     * @param PlayerDeathEvent $event
     * @return void
     */
    public function onPlayerDeath(PlayerDeathEvent $event) {
        /* @var ARRAY TimeCheck */
        $time = [
            "stamp" => time(),
            "now_s" => strftime("%S", $time["stamp"]),
            "now_m" => strftime("%M", $time["stamp"])
        ];
        /* @var Player */
        $player = $event->getPlayer();
        $pname = $player->getName();
        /* @var Damaging Player = null */
        $killer = null;
        if (array_key_exists($pname, self::$killerlist)) {
            $kname = self::$killerlist[$pname];
            $killer = self::$server->getPlayerByName($kname);
        }
        /* Time calculating. Much WOW that dktapps never thought this might've been good to have in the event itself. Much WOW. */
        $gotcause = "NOT PLAYER";
        if ($killer !== null) {
            $math = self::doTimeMath($time);
            if ($math === true) {
                $gotcause = "PLAYER";
            }
        }
        /* Cause Checking */
        $ldcause = $player->getLastDamageCause();
        $cause = $ldcause->getCause();
        $message = null;
        if ($cause !== EntityDamageEvent::CAUSE_ENTITY_ATTACK && $gotcause !== "PLAYER") { /* SUICIDE PARAM - When both PMMP and VG say it was suicide */
            $message = self::switchTheCause($cause);
            self::updateRAM($pname);
        } else if ($gotcause === "PLAYER" && $killer !== null) { /* PMMP said SUICIDE but VG said KILLED */
            $message = self::switchTheCause($cause);
            self::updateRAM($pname, $kname);
        } else if ($cause instanceof EntityDamageByEntityEvent && $killer !== null) { /* PMMP said KILLED */
            //
            self::updateRam($pname, $kname);
        }
        /* Death message setting */
        if (isset($message)) {
            self::setDeathMessage($event, $message, $pname, $kname);
        }
    }

    /**
     * Set the death message.
     *
     * @param mixed $event
     * @param array $message
     * @param string $player
     * @param string $killer
     * @return boolean
     */
    private static function setDeathMessage(mixed $event, array $message, string $player, string $killer): bool {
        if (count($message) > 1) {
            $text = Chat::RED . $player . Chat::YELLOW . " " . $message[0] . " " . Chat::GREEN . $killer . Chat::YELLOW . " " . $message[1] . "!";
        } else if (count($message) <= 1) {
            $text = Chat::RED . $player . Chat::YELLOW . " " . $message[0] . "!";
        }
        $event->setDeathMessage($text);
        return true;
    }

    /**
     * Switch the death cause.
     *
     * @param integer $cause
     * @return array
     */
    private static function switchTheCause(int $cause): array {
        switch ($cause) {
            case EntityDamageEvent::CAUSE_FALL: {
                $message = DeathMessage::getRandomDeathMessage(DeathMessage::FALL);
                break;
            }
            case EntityDamageEvent::CAUSE_FIRE_TICK:
            case EntityDamageEvent::CAUSE_LAVA:
            case EntityDamageEvent::CAUSE_FIRE: {
                $message = DeathMessage::getRandomDeathMessage(DeathMessage::FIRE);
                break;
            }
            case EntityDamageEvent::CAUSE_DROWNING:
            case EntityDamageEvent::CAUSE_SUFFOCATION: {
                $message = DeathMessage::getRandomDeathMessage(DeathMessage::BREATH);
                break;
            }
        }
        return $message;
    }

    /**
     * Do calculations for time. Less math, more "if".
     *
     * @param array $time
     * @return boolean
     */
    private static function doTimeMath(array $time): bool {
        $gotcause = "NP";
        $time["oflastdamage"] = self::$ktime[$pname . ":" . $kname];
        $told = explode(":", $time["oflastdamage"]);
        $math = (int)$time["now_m"] - (int)$told[0];
        if ($math === 0) {
            $math = (int)$time["now_s"] - (int)$told[1];
            if ($math <= self::DEATH_TIME_THRESHOLD || $math >= -(self::DEATH_TIME_THRESHOLD)) {
                $gotcause = "PLAYER";
            }
        } else if ($math === 1 || $math === -1) {
            if ((int)$time["now_s"] < (int)$told[1]) {
                $math = (int)$time["now_s"] + 60;
                $time["now_s"] = (string)$math;
            }
            $math = (int)$time["now_s"] - (int)$told[1];
            if ($math <= self::DEATH_TIME_THRESHOLD || $math >= -(self::DEATH_TIME_THRESHOLD)) {
                $gotcause = "PLAYER";
            }
        }
        if ($gotcause !== "NP") {
            return true;
        }
        return false;
    }

    /**
     * Updates ram specfics on kill and deaths.
     *
     * @param string $pname
     * @param string $kname
     * @return void
     */
    private static function updateRAM(string $pname = null, string $kname = null): void {
        UserSystem::addDeath($pname);
        if ($kname !== null) {
            UserSystem::addKill($kname);
        }
    }

}