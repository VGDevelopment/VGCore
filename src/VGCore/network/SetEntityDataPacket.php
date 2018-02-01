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
    
    public $entityruntimeid;
    public $md;
    
    protected function encodePayload() {
        $this->putEntityRuntimeId($this->entityruntimeid);
        $this->setMetaDataOfEntity($this->md);
        var_dump($this->entityruntimeid);
        var_dump($this->md);
    }
    
    protected function decodePayload() {
        $this->entityruntimeid = $this->getEntityRuntimeId();
        $this->md = $this->getMetaDataOfEntity();
    }
    
    public function handle(NetworkSession $session): bool {
        // sets the packet object as the entity data - thanks Steadfast2 <3
        return $session->handleSetEntityData($this);
    }
    
}