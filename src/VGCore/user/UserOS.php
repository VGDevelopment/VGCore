<?php

namespace VGCore\user;

use VGCore\SystemOS;

use VGCore\network\{
    Database as DB
};

abstract class UserOS {

    private static $db;
    private static $os;

    /**
     * Loads up the User OS.
     *
     * @param SystemOS $os
     * @return void
     */
    public static function load(SystemOS $os): void {
        self::$os = $os;
        self::$db = DB::getDatabase();
        self::start(self::$os, self::$db);
    }

    /**
     * Starts up all management systems.
     *
     * @param SystemOS $os
     * @param mixed $db
     * @return void
     */
    protected abstract function start(SystemOS $os, mixed $db): void;

}