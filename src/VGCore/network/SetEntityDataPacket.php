<?php

namespace VGCore\network;

use pocketmine\network\mcpe\{
    NetworkSession,
    protocol\ProtocolInfo as PI
};
// >>>
use VGCore\network\FireworkPacket;

class SetEntityDataPacket extends FireworkPacket {
    
    public const NETWORK_ID = PI::SET_ENTITY_DATA_PACKET;
    
    public $entityRuntimeId; // int
    public $metadata; // array
    
    protected function encodePayload() {
        $this->putEntityRuntimeId($this->entityRuntimeId);
        $this->setMetaDataOfEntity($this->metadata);
        var_dump($this->entityruntimeid);
        var_dump($this->md);
    }
    
    protected function decodePayload() {
        $this->entityRuntimeId = $this->getEntityRuntimeId();
        $this->metadata = $this->getMetaDataOfEntity();
    }
    
    public function handle(NetworkSession $session): bool {
        // sets the packet object as the entity data - thanks Steadfast2 <3
        return $session->handleSetEntityData($this);
    }
    
}