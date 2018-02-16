<?php

namespace VGCore\data;

use pocketmine\utils\TextFormat as Chat;

interface BotData {

    const BOT = [
        self::LOBBY_FAC => [
            self::LOC => [
                161.5,
                7,
                137.5,
            ],
            self::EXEC => "transferserver dev.vgpe.me 29838",
            self::WORLD => "Sam2"
        ]
    ];

    const LOBBY_FAC = Chat::YELLOW . "Click me to play" . Chat::EOL . Chat::BOLD . Chat::GREEN . "FACTIONS" . Chat::RESET . Chat::RED . "[ALPHA]";

    // Encrypted indexes so the client has a hard time tampering with the NPC data through hacks. (this can be done and has been leading to false usage of NPC client-sided)
    const LOC = "cn1evj6hjm";
    const EXEC = "vcs02p65yv";
    const WORLD = "ef8txy0abn";
    const ID = "ds2xh6m10i";
    const UUID = "5yi3ef2fdc";
    const EID = "k3tfmr8u1t";
    const SCALAR = "uc2y05bgjb";
    const AIM = "566vzhojs1";

}