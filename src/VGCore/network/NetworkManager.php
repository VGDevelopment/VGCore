<?php

namespace VGCore\network;

use pocketmine\network\mcpe\protocol\{
    PacketPool,
    ClientboundMapItemDataPacket as MapItemDataPacket,
    GameRulesChangedPacket as GameRulePacket,
    DataPacket,
    AddPlayerPacket as BotPacket,
    PlayerSkinPacket as BotSkinPacket,
    MoveEntityPacket
};

use pocketmine\Player;

use pocketmine\utils\Vector3 as Scalar;

use pocketmine\level\{
    Level, 
    format\Chunk
};
// >>>
use VGCore\SystemOS;

use VGCore\network\{
    Database,
    ModalFormRequestPacket,
    ModalFormResponsePacket,
    ServerSettingsRequestPacket,
    ServerSettingsResponsePacket,
    VGServer
};

use VGCore\factory\{
    ItemAPI,
    item\Map
};

class NetworkManager {

    const MAP_PACKET_ID = 0x00;

    private static $packet = [];
    private static $os;
    private static $handledpacket = [];

    /**
     * Loads :
     * - Packets
     * - Database
     * - Server Handler
     *
     * @param SystemOS $os
     * @return boolean
     */
    public static function start(SystemOS $os): bool {
        self::$os = $os;
        self::$packet = [
            new ModalFormRequestPacket(),
            new ModalFormResponsePacket(),
            new ServerSettingsRequestPacket(),
            new ServerSettingsResponsePacket()
        ];
        $a = self::loadPacket();
        $b = self::loadDatabase();
        $c = self::loadServerHandler();
        if ($a === true && $b === true && $c === true) {
            return true;
        }
        return false;
    }

    /**
     * Loads the packets.
     *
     * @return boolean
     */
    private static function loadPacket(): bool {
        foreach (self::$packet as $p) {
            PacketPool::registerPacket($p);
        }
        return true;
    }

    /**
     * Starts up the database connection & checks records.
     *
     * @return boolean
     */
    private static function loadDatabase(): bool {
        Database::createRecord(self::$os);
        return true;
    }

    /**
     * Loads the VirtualGalaxy Private server handler to handle network-wide scenarios!
     *
     * @return boolean
     */
    private static function loadServerHandler(): bool {
        return VGServer::start(self::$os);
    }

    /**
     * Handling the map packet. [INCOMPLETE]
     *
     * @param Map $map
     * @param integer $pkt
     * @return void
     */
    public static function handleMapPacket(Map $map, int $pkt = self::MAP_PACKET_ID): void {
        $pk = new MapItemDataPacket();
        $pk->mapId = $map->getID();
        $pk->type = $pkt;
        $pk->scale = $map->getSize();
        $pk->width = $map->getWidth();
        $pk->height = $map->getHeight();
        $xy = $map->getXY();
        $pk->xOffset = $xy["x"];
        $pk->yOffset = $xy["y"];
        $pk->color = $map->getColor();
        $pk->decorations = $map->getExtra();
        $server = self::$os->getServer();
        $playeronline = $server->getAllOnlinePlayers();
        $server->broadcastPacket($playeronline, $pk);
        self::$handledpacket[] = $pk;
    }

    /**
     * Handles the Game Rules Packet.
     *
     * @param Player $player
     * @param string $type
     * @param integer $byte
     * @param boolean $bool
     * @return DataPacket
     */
    public static function handleGameRulePacket(Player $player, string $type, int $byte = 1, bool $bool = true): DataPacket {
        $pk = new GameRulePacket();
        $pk->gamerules[$type] = [$byte, $bool];
        $player->dataPacket($pk);
        self::$handledpacket[] = $pk;
        return $pk;
    }

    /**
     * Handles the bot packet.
     *
     * @param Player $player
     * @param string $nametag
     * @param string $uuid
     * @param integer $eid
     * @param Scalar $location
     * @param array $itemdata
     * @return boolean
     */
    public static function handleBotPacket(Player $player, string $nametag, string $uuid, int $eid, Scalar $location, array $itemdata = []): bool {
        $pk = new BotPacket();
        $pk->entityRuntimeId = $eid;
        $pk->username = $nametag;
        $pk->position = $location;
        $pk->item = ItemAPI::makeItem($itemdata);
        $skinpk = new BotSkinPacket();
        /* Set UUID */
        $skinpk->uuid = $pk->uuid = $uuid;
        $server = self::$os->getServer();
        $playerarray = [
            $player
        ];
        $server->broadcastPacket($playerarray, $pk);
        return true;
    }

    /**
     * Handle the bot movement.
     *
     * @param Level $level
     * @param Chunk $chunk
     * @param integer $eid
     * @param Scalar $location
     * @param float $yaw
     * @param float $pitch
     * @return boolean
     */
    public static function handleBotAimPacket(Level $level, Chunk $chunk, int $eid, Scalar $location, float $yaw, float $pitch): bool {
        $chunkdata = [
            "x" => $chunk->getX(),
            "z" => $chunk->getZ()
        ];
        $pk = new MoveEntityPacket();
        $pk->entityRuntimeId = $eid;
        $pk->position = $location;
        $pk->yaw = $yaw;
        $pk->headYaw = $yaw;
        $pk->pitch = $pitch;
        $level->addChunkPacket($chunkdata["x"], $chunkdata["z"], $pk);
        return true;
    }

}