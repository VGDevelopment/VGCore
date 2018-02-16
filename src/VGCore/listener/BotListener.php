<?php

namespace VGCore\listener;

use pocketmine\event\{
    Listener,
    player\PlayerJoinEvent,
    server\DataPacketReceiveEvent
};

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
// >>> 
use VGCore\SystemOS;

use VGCore\lobby\bot\BotManager;

class BotListener implements Listener {

    private static $os;
    private static $server;

    public function __construct(SystemOS $os) {
        self::$os = $os;
        self::$server = self::$os->getServer();
    }

    /**
     * Spawns the bot after checking whether the player is on the level of bot.
     *
     * @param PlayerJoinEvent $event
     * @return void
     */
    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();
        $level = $player->getLevel();
        foreach (BotManager::BOT as $nametag => $data) {
            if ($level === $data[BotManager::WORLD]) {
                BotManager::spawnBotPerPlayer($player);
            }
        }
    }

    public function onPacket(DataPacketReceiveEvent $event) {
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        $check === [
            "instance" => $pk instanceof InventoryTransactionPacket,
            "t-type" => $pk->transactionType === InventoryTransactionPacket::TYPE_USE_ITEM_ON_ENTITY,
            "a-type" => $pk->trData->actionType === InventoryTransactionPacket::USE_ITEM_ON_ENTITY_ACTION_ATTACK
        ];
        foreach ($check as $i => $v) {
            if ($v === false) {
                return;
            }
        }
        $entityid = $pk->trData->entityRuntimeId;
        $runtimedata = BotManager::getRuntimeData();
        foreach ($runtimedata as $i => $v) {
            if ($entityid === $v[BotManager::EID]) {
                self::$server->getCommandMap()->dispatch($player, $v[BotManager::EXEC]);
            }
        }
    }

}