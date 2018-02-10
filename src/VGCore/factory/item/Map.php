<?php

namespace VGCore\factory\item;

use pocketmine\item\Item;

use pocketmine\nbt\tag\{
    CompoundTag,
    StringTag
};

use pocketmine\Server;

use pocketmine\utils\{
    Color
};
// >>>
use VGCore\SystemOS;

use VGCore\network\NetworkManager as NM;

class Map extends Item {

    const ID_TAG = "ID";

    private $id = -1;
    private $width = 128;
    private $height = 128;
    private $color = [];
    private $extra = [];
    private $size = 0;
    // horizontal image so only x, y
    private $x = 0;
    private $y = 0;

    /**
     * Constructs the map item with image data.
     *
     * @param integer $id
     * @param integer $width
     * @param integer $height
     * @param integer $size
     * @param integer $x
     * @param integer $y
     * @param array $color
     * @param array $extra
     */
    public function __construct(int $id = -1, int $width = 128, int $height = 128, int $size = 0, int $x = 0, int $y = 0 , array $color = [], array $extra = []) {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->size = $size;
        $this->x = $x;
        $this->y = $y;
        $this->color = $color;
        $this->extra = $extra;
    }

    /**
     * Set the integer ID of the object.
     *
     * @param integer $id
     * @return boolean
     */
    public function setID(int $id): bool {
        $this->id = $id;
        $strval = strval($id);
        $stag = new StringTag(self::ID_TAG, $strval);
        $this->setNamedTagEntry($stag);
        return true;
    }

    /**
     * Get the integer ID of the object.
     *
     * @return integer
     */
    public function getID(): int {
        $id = $this->getNamedTagEntry(self::ID_TAG);
        $v = $id->getValue();
        $check = intval($this->id === -1 ? $v : $this->id);
        return $check;
    }

    /**
     * Sets the size of the object.
     *
     * @param integer $size
     * @return boolean
     */
    public function setSize(int $size): bool {
        $this->size = $size;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Get the value of the size integer.
     *
     * @return integer
     */
    public function getScale(): int {
        return $this->size;
    }

    /**
     * Sets the width of the object.
     *
     * @param integer $width
     * @return boolean
     */
    public function setWidth(int $width): bool {
        $this->width = $width;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Gets the value of the width integer.
     *
     * @return integer
     */
    public function getWidth(): int {
        return $this->width;
    }

    /**
     * Sets the height of the object.
     *
     * @param integer $height
     * @return boolean
     */
    public function setHeight(int $height): bool {
        $this->height = $height;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Gets the value of the height integer.
     *
     * @return integer
     */
    public function getHeight(): int {
        return $this->height;
    }

    /**
     * Set the X property of the object.
     *
     * @param integer $x
     * @return boolean
     */
    public function setX(int $x): bool {
        $this->x = $x;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Set the Y property of the object.
     *
     * @param integer $y
     * @return boolean
     */
    public function setY(int $y): bool {
        $this->y = $y;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Get the X and Y integer values in an array. Array contents : [x, y]
     *
     * @return array
     */
    public function getXY(): array {
        return [
            "x" => $this->x,
            "y" => $this->y
        ];
    }

    /**
     * Set the color property of the object.
     *
     * @param array $color
     * @return boolean
     */
    public function setColor(array $color): bool {
        $this->color = $color;
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Set the color property at a specific position on the object.
     *
     * @param Color $color
     * @param integer $x
     * @param integer $y
     * @return boolean
     */
    public function setColorOnXY(Color $color, int $x, int $y): bool {
        $this->color[$y][$x] = $color; // y needs to be 1st index, x needs to be secondary index to make a 2d position.
        NM::handleMapPacket($this, 0x02);
        return true;
    }

    /**
     * Get the color object-array property of the object.
     *
     * @return array
     */
    public function getColor(): array {
        return $this->color;
    }

    /**
     * Get the color object on a specific position on the object.
     *
     * @param integer $x
     * @param integer $y
     * @return Color
     */
    public function getColorOnXY(int $x, int $y): Color {
        $c1 = isset($this->color[$y]);
        $c2 = isset($this->color[$y][$x]);
        if ($c1 && $c2) {
            return $this->color[$y][$x];
        }
    }

    /**
     * Add the extra beauty in the object.
     *
     * @param mixed $beauty
     * @return integer
     */
    public function addExtra(mixed $beauty): int {
        $this->extra[] = $beauty;
        end($this->extra);
        NM::handleMapPacket($this, 0x04);
        $key = key($this->extra);
        return $key;
    }

    /**
     * Sets the extra property of the object.
     *
     * @param array $extra
     * @return boolean
     */
    public function setExtra(array $extra): bool {
        $this->extra = $extra;
        NM::handleMapPacket($this, 0x04);
        return true;
    }

    /**
     * Get the extra property of the object.
     *
     * @return array
     */
    public function getExtra(): array {
        return $this->extra;
    }

    /**
     * Gets the extra beauty from the extra array.
     *
     * @param integer $key
     * @return mixed
     */
    public function getExtraAtKey(int $key): mixed {
        return $this->extra[$key];
    }

    public function exportToData(): mixed {
        //
    }

}