<?php

namespace VGCore\util;

class Utility {

    private static $util = [];

    /**
     * Set the utility array.
     *
     * @param array $util
     * @return boolean
     */
    public static function setUtil(array $util): bool {
        self::$util = $util;
    }

    /**
     * Get the utility array.
     *
     * @return array
     */
    public static function getUtil(): array {
        return self::$util;
    }

}