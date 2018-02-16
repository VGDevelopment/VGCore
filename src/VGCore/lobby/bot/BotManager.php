<?php

namespace VGCore\lobby\bot;

use pocketmine\Player;

use pocketmine\Entity\Entity;

use pocketmine\util\{
    UUID
};
// >>>
use VGCore\util\Converter;

use VGCore\network\NetworkManager;

use VGCore\data\BotData;

class BotManager implements BotData {

    /**
     * An array containing the data regarding bots.
     * $data = [
     *     $nametag = [
     *         self::LOC = "xxx:xxx:xxx",
     *         self::EXEC = "command here",
     *         self::WORLD = "world name",
     *         self::ID = "int",
     *         self::UUID = "string uuid",
     *         self::EID = "int eid",
     *         self::SCALAR = Scalar Object to define NPC location.
     *         self::AIM = Scalar Object to denote the part where the aim is at.
     *     ]
     * ]
     *
     * @var array
     */
    private static $data = [];
    private static $runtimedata = [];
    private static $id = 0;

    /**
     * Startup the bots by making them.
     *
     * @return boolean
     */
    public static function start(): bool {
        foreach (self::BOT as $nametag => $data) {
            $location = $data[self::LOC];
            $locationstring = Converter::convertLocation($location[0], $location[1], $location[2], null, 0);
            $bot = self::makeBot($nametag, $locationstring, $data[self::WORLD], $data[self::EXEC], self::$id++);
            if ($bot === false || $bot === null) {
                return false;
            }
        }
        return true;
    }

    /**
     * Makes the bot instance.
     *
     * @param string $nametag
     * @param string $location
     * @param string $world
     * @param string $execution
     * @param integer $id
     * @return boolean
     */
    protected static function makeBot(string $nametag, string $location, string $world, string $execution, int $id): bool {
        // Saves all the stuff into the data array. 
        self::$data[$nametag] = [
            self::LOC => $location,
            self::EXEC => $execution,
            self::WORLD => $world,
            self::ID => $id,
            self::UUID => UUID::fromRandom(),
            self::EID => null,
            self::SCALAR => Converter::convertLocation(null, null, null, $location, 2),
            self::AIM => null
        ];
        self::$data[$nametag][self::AIM] = self::createAimScalar($nametag);
        return true;
    }

    /**
     * Creates the aim object scalar.
     *
     * @param string $nametag
     * @return mixed
     */
    private static function createAimScalar(string $nametag): mixed {
        $scalar = self::$data[$nametag][self::SCALAR];
        $scalar->y = $scalar->y + 1.62;
        return clone $scalar; // so the object isn't same.
    }

    /**
     * Spawns the bot for each player.
     *
     * @param Player $player
     * @return boolean
     */
    public static function spawnBotPerPlayer(Player $player): bool {
        $a = [
            "spawn" => [],
            "move" => [] 
        ];
        foreach (self::$data as $nametag => $data) {
            /* Set the entity ID */
            self::$data[$nametag][self::EID] = Entity::$entityCount++;
            /* Done */
            $a["spawn"][] = NetworkManager::handleBotPacket($player, $nametag, $data[self::ID], $data[self::EID], $data[self::SCALAR]);
            /* Set the runtime parameters */
            self::$data[$nametag][self::EID] = Entity::$entityCount++;
            self::$runtimedata[$nametag] = $data;
            $a["move"][] = self::aimAtSpawn();
            /* Done */
        }
        foreach ($a as $i) {
            foreach ($i as $v) {
                if ($v === false) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Aims the bot at player.
     *
     * @param Player $player
     * @param string $nametag
     * @return boolean
     */
    private static function aimAtSpawn(Player $player, string $nametag): bool {
        $data = self::$data[$nametag];
        $scalar = $data[self::SCALAR];
        $level = $player->getLevel();
        $spawn = $level->getSpawnLocation();
        $player = [
            "x" => $spawn->x,
            "y" => $spawn->y,
            "z" => $spawn->z,
            "chunk" => $player->chunk
        ];
        $s = [
            "x" => $scalar->x,
            "y" => $scalar->y,
            "z" => $scalar->z
        ];
        $x = $s["x"] - $player["x"];
        $y = $s["y"] - $player["y"];
        $z = $s["z"] - $player["z"];
        $math = [
            "x^2" => $x ** 2,
            "z^2" => $z ** 2,
            "y^2" => $y ** 2,
            "pie" => 3.14,
            "circumfrence" => 360,
            "semi-circle" => $math["pie"] * (0.5 * $math["circumfrence"]),
            "edge" => sqrt($math["x^2"] + $math["z^2"]),
            "triangle" => sqrt($math["x^2"] + $math["y^2"] + $math["z^2"]),
            "alt-sine-x" => asin($x / $math["edge"]),
            "alt-sine-y" => asin($y / $math["triangle"]),
            "newtonrange-yaw" => $math["alt-sin-x"] / $math["semi-circle"],
            "newtonrange-pitch" => $math["alt-sin-y"] / $math["semi-circle"]
        ];
        if ($math["edge"] === 0 && $math["triangle"] === 0) {
            return true; // correct aim.
        }
        $yaw = $math["newtonrange-yaw"];
        $pitch = round($math["newtonrange-pitch"]);
        if ($z > 0) {
            $yaw = $yaw + 180; // in-case of opposite angle.
        }
        $scalar = self::$data[$nametag][self::AIM];
        $eid = self::$data[$nametag][self::EID];
        return NetworkManager::handleBotAimPacket($level, $player["chunk"], $eid, $scalar, $yaw, $pitch);
    }

    /**
     * Get the runtime data.
     *
     * @return array
     */
    public static function getRuntimeData(): array {
        return self::$runtimedata;
    }

    // TODO : MAKE API METHODS. 

}