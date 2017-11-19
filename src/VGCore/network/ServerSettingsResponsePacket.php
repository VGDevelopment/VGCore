<?php

namespace VGCore\network;

use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\DataPacket;

class ServerSettingsResponsePacket extends DataPacket {
    
    const NETWORK_ID = ProtocolInfo::SERVER_SETTINGS_RESPONSE_PACKET;
    
    public $formId; // always int - remember this shit.
    public $formData; // always string (json) - also remember this shit
    
    public function decodePayload() {
		$this->formId = $this->getUnsignedVarInt();
		$this->formData = $this->getString();
	}
	
	public function encodePayload() {
		$this->putUnsignedVarInt($this->formId);
		$this->putString($this->formData);
	}
	
	public function handle(NetworkSession $session): bool {
		return true;
	}
    
}