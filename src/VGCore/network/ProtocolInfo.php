<?php

namespace VGCore\network;

class ProtocolInfo {
    
    const MODAL_FORM_REQUEST_PACKET = 0x64; // Request Packet
    const MODAL_FORM_RESPONSE_PACKET = 0x65; // Response Packet
    const SERVER_SETTINGS_REQUEST_PACKET = 0x66; // Client Request Packet
    const SERVER_SETTINGS_RESPONSE_PACKET = 0x67; // Server Response Packet
    const GUI_DATA_PICK_ITEM_PACKET = 0x36; // Correct Data (fetch) Packet 
    
}