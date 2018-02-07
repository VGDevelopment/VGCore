<?php

namespace VGCore\task\faction;

use pocketmine\PluginTask;

use pocketmine\utils\TextFormat as Chat;
//
use VGCore\SystemOS;

use VGCore\task\TaskManager;

use VGCore\listener\FactionListener;

class WarTimerTask extends PluginTask {

    const TASKNAME = "WarTimerTask";

    private static $os;

    public function __construct() {
        $os = TaskManager::getOS();
        self::$os = $os;
        parent::__construct($os);
        $id = $this->getTaskId();
        TaskManager::addTask(self::TASKNAME, $id);
    }

    public function onRun(int $currentTick) {
        if ($currentTick > 200) {
            FactionListener::$timerun = 0; 
        } else if ($currentTick > 0 && $currentTick < 100) {
            FactionListener::$timerun = 1;
        } else if ($currentTick === 0) {
            $playerlist = $server->getAllOnlinePlayers();
            FactionListener::$timerun = 2;
            $id = $this->getTaskId();
            TaskManager::removeTask($id);
            foreach ($player as $v) {
                /*
                Planning on changing this message. Don't be annoyed.
                */
                $v->sendMessage(Chat::GREEN . "WAR has STARTED! GO, GO, GO!");
            }
        }
        return;
    }

}