<?php

namespace VGCore\task;

use pocketmine\scheduler\ServerScheduler as Scheduler;
// >>>
use VGCore\SystemOS;

use VGCore\task\{
    faction\WarTimerTask
};

class TaskManager {

    private static $os;
    private static $scheduler;
    private static $task = [];
    private static $taskend = [];

    public static function start(SystemOS $os): void {
        self::$os = $os;
        self::$scheduler = $os->getServer()->getScheduler();
    }

    /**
     * Returns the Operating SystemOS for the tasks.
     *
     * @return SystemOS
     */
    public static function getOS(): SystemOS {
        return self::$os;
    }

    /**
     * Force ends the task and returns a true if ended and false if ID doesn't exist.
     *
     * @param integer $id
     * @return boolean
     */
    public static function forceEndTask(int $id): bool {
        if (array_key_exists($id, self::$task)) {
            self::$scheduler->cancelTask($id);
            $name = self::$task[$id];
            unset(self::$task[$id]);
            /*
            A further check to see it all went fine.
            */
            if (array_key_exists($id, self::$task)) {
                return false;
            }
            self::$taskend[$id] = $name;
            return true;
        }
        return false;
    }

    /**
     * Removes task from the array self::$task .
     *
     * @param integer $id
     * @return boolean
     */
    public static function removeTask(int $id): bool {
        if (array_key_exists($id, self::$task)) {
            $name = self::$task[$id];
            unset(self::$task[$id]);
            self::$taskend[$id] = $name;
            return true;
        }
        return false;
    }

    /**
     * Adds the task to the taskmanager array of all tasks with the index being the TaskID.
     *
     * @param string $name
     * @param integer $id
     * @return void
     */
    public static function addTask(string $name, int $id): void {
        if (array_key_exists($id, self::task)) {
            return;
        }
        if (array_key_exists($id, self::$taskend)) {
            unset(self::$taskend[$id]);
        }
        self::$task[$id] = $name;
    }

    /**
     * Returns the ServerScheduler Object (aka Scheduler)
     *
     * @return Scheduler
     */
    public static function getSchedulerObject(): Scheduler {
        return self::$scheduler;
    }

    /**
     * Starts the given task based on the string. Tasks run addTask() on construct.
     * 
     * TODO :
     * - Have all tasks running from Task Manager to add a singular manager. Rather than PMMP's useless objective TaskID manager.
     *
     * @param string $task
     * @return boolean
     */
    public static function startTask(string $task): string {
        switch ($task) {
            case "WarTimerTask": {
                $task = new WarTimerTask();
                self::$scheduler->scheduleRepeatingTask($task, 20); // 20t = s
                return $task->sendTaskID();
            }
        }
        return "ERROR";
    }

    /**
     * Returns the runtime task array.
     *
     * @return array
     */
    public static function showRuntimeTask(): array {
        return self::$task;
    }

    /**
     * Returns the task that have ended.
     *
     * @return array
     */
    public static function showEndedTask(): array {
        return self::$taskend;
    }

    public static function isTaskRunning(string $taskid): bool {
        $taskid = (int)$taskid;
        if (array_key_exists($id, self::$task)) {
            return true;
        } else if (array_key_exists($id, self::$taskend)) {
            return false;
        }
        return null;
    }

}