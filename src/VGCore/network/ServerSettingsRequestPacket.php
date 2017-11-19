<?php

namespace VGCore\network;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

class ServerSettingsRequestPacket extends DataPacket {
    
    const NETWORK_ID = ProtocolInfo::SERVER_SETTINGS_REQUEST_PACKET;
    
    public function decodePayload() {
		// Nothing to decode
	}
	
	public function encodePayload() {
		// Nothing to encode
	}
	
	public function handle(NetworkSession $session): bool{
		return true;
	}
    
}