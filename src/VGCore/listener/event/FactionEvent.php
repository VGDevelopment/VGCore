<?php

namespace VGCore\listener\event;

use pocketmine\event\plugin\PluginEvent;
//
use VGCore\SystemOS;

abstract class FactionEvent extends PluginEvent {

    public static $os;

    public function __construct(SystemOS $os) {
        parent::__construct($os);
    }

    public static function setOS(SystemOS $os): void {
        self::$os = $os;
    }

    public function getOS(): SystemOS {
        return self::$os;
    }

}