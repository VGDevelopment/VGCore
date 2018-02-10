<?php

namespace VGCore\network;

use pocketmine\network\mcpe\protocol\{
    PacketPool,
    ClientboundMapItemDataPacket as MapItemDataPacket
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

use VGCore\factory\item\Map;

class NetworkManager {

    const MAP_PACKET_ID = 0x00;

    private static $packet = [];
    private static $os;

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
        VGServer::start(self::$os);
        return true;
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
    }

}