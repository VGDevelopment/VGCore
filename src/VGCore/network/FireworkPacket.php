<?php

namespace VGCore\network;

use pocketmine\entity\Entity;
use pocketmine\item\ItemFactory;
use pocketmine\network\mcpe\{
    NetworkSession,
    protocol\DataPacket
};

abstract class FirworkPacket extends DataPacket {
    
    abstract public function handle(NetworkSession $session): bool;
    
    public function getMetaDataOfEntity(bool $type = true): array {
        $data = [];
        $noe = $this->getUnsignedVarInt();
        for ($i = 0; $i < $noe; ++$i) {
            $i = $this->getUnsignedVarInt();
            $t = $this->getUnsignedVarInt();
            $v = null;
            switch ($t) {
                case ENTITY::DATA_TYPE_BYTE:
                    $v = $this->getByte();
                    break;
                case ENTITY::DATA_TYPE_INT:
                    $v = $this->getVarInt();
                    break;
                case ENTITY::DATA_TYPE_FLOAT:
                    $v = $this->getLFloat();
                    break;
                case ENTITY::DATA_TYPE_STRING:
                    $v = $this->getString();
                    break;
                case ENTITY::DATA_TYPE_SHORT:
                    $v = $this->getSignedLShort();
                    break;
                case ENTITY::DATA_TYPE_LONG:
                    $v = $this->getVarLong();
                    break;
                case ENTITY::DATA_TYPE_SLOT:
                    // thanks @gurun 
                    $entity = $this->getSlot();
                    $v = [
                        0 => $entity->getId(),
                        1 => $entity->getCount(),
                        2 => $entity->getDamage(),
                        3 => $item->getCompoundTag()
                    ];
                    break;
                case ENTITY::DATA_TYPE_POS:
                    $v = [
                        0,
                        0,
                        0
                    ];
                    // thanks @gurun for this as well
                    $this->getSignedBlockPosition(...$v);
                    break;
                case ENTITY::DATA_TYPE_VECTOR3F:
                    $v = [
                        0.0,
                        0.0,
                        0.0
                    ];
                    $this->getVector3f(...$v);
                    break;
                default:
                    // so $v !== null
                    $v = [];
            }
            if ($type = true) {
                $data[$i] = [$t, $v];
            } else {
                $data[$i] = $v;
            }
        }
        return $data;
    }
    
    public function setMetaDataOfEntity(array $md): void {
        $c = count($md);
        $this->putUnsignedVarInt($c);
        foreach ($md as $i => $v) {
            // thanks @gurun for this as well
            $this->putUnsignedVarInt($i);
            $this->putUnsignedVarInt($v[0]);
            // saves index and the data[0]
            switch ($v[0]) {
                case ENTITY::DATA_TYPE_BYTE:
                    $this->putByte($v[1]);
                    break;
                case ENTITY::DATA_TYPE_INT:
                    $this->putVarInt($v[1]);
                    break;
                case ENTITY::DATA_TYPE_FLOAT:
                    $this->putLFloat($v[1]);
                    break;
                case ENTITY::DATA_TYPE_STRING:
                    $this->putString($v[1]);
                    break;
                case ENTITY::DATA_TYPE_SHORT:
                    $this->putLShort($v[1]); // shouldn't this be signed? and how I wonder...
                    break;
                case ENTITY::DATA_TYPE_LONG:
                    $this->putVarLong($v[1]);
                    break;
                case ENTITY::DATA_TYPE_SLOT:
                    // thanks @gurun 
                    $entity = ItemFactory::get($v[1][0], $v[1][1], $v[1][2], $v[1][3] ?? ""); // should be ID, DAMAGE, COUNT, NBT
                    $this->putSlot($entity);
                    break;
                case ENTITY::DATA_TYPE_POS:
                    // thanks @gurun for this as well
                    $this->putUnsignedBlockPosition(...$v[1]); // shouldn't this be signed? and how I wonder...
                    break;
                case ENTITY::DATA_TYPE_VECTOR3F:
                    $this->putVector3f(...$v[1]); // should be the cordinates of point - ex. X, Y, Z.
                    break;
            }
        }
    }
    
}