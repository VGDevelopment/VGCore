<?php

namespace VGCore\util;

use pocketmine\math\Vector3 as Scalar;

class Converter {

    /**
     * Convert 2 types of parameters into 2 types of values.
     * 
     * Floats => String,
     * Floats => Scalar,
     * String => Floats,
     * String => Scalar
     * 
     * Returns false if conversion failed due to parameters not being correct.
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @param string $location
     * @param integer $type 0, 1, 2
     * 0 for string.
     * 1 for float packaged in array.
     * 2 for Scalar Object.
     * @return mixed
     */
    public static function convertLocation(float $x = null, float $y = null, float $z = null, string $location = null, int $type = null): mixed {
        $check = [
            "string" => $x !== null & $y !== null && $z !== null,
            "array" => $location !== null,
            "scalar" => $check["string"] === true || $check["array"] === true
        ];
        if ($type === 0 && $check["string"] === true) {
            $package = [
                (string)$x,
                (string)$y,
                (string)$z
            ];
            $return = implode(":", $package);
        } else if ($type === 1 && $check["array"] === true) {
            $return = explode(":", $location);
        } else if ($type === 2 && $check["scalar"] === true) {
            if ($check["string"] === true) {
                $return = new Scalar($x, $y, $z);
            } else if ($check["array"] === true) {
                $format = self::convertLocation(null, null, null, $location, 1);
                $return = new Scalar($format[0], $format[1], $format[2]);
            }
        }
        $check = [
            "string" => is_string($return),
            "array" => is_array($return),
            "scalar" => $return instanceof Scalar
        ];
        if ($check["string"] === true || $check["array"] === true || $check["scalar"] === true) {
            return $return;
        }
        return false;
    }

}