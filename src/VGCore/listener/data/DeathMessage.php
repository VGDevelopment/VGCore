<?php

namespace VGCore\listener\data;

class DeathMessage {

    const DEATH = 0;
    const FIRE = 1;
    const BREATH = 2;
    const FALL = 3;

    const PLAYER = 0;
    const ACCIDENTAL = 1;

    // oh how I hate that I had to use "his/her" for the sake of respecting females. Uhh!
    const DEATH_MESSAGE = [
        "fire" => [
            "player" => [
                [
                    "got burned alive by",
                    "aka Son of The Dragon ROAGNAR"
                ],
                [
                    "was made into a BBQ by",
                    "with help from ROAGNAR"
                ],
                [
                    "brought a sword to a battle of fire and got burned by",
                    "using flames of AZEROTH"
                ]
            ],
            "accidental" => [
                "forgot to use Fire Resistance",
                "thought fire was cool",
                "became too hot for his/her own good",
                "melted",
                "didn't enjoy the hot tub",
                "got fired by REMCOOLE"      
            ]
        ],
        "breath" => [
            "player" => [
                [
                    "lost all of his/her breath to",
                    "and forgot to keep some for himself"
                ]
            ],
            "accidental" => [
                "forgot to use Water Breathing",
                "thought he/she was a fish",
                "forgot to hold the jump button",
                "forget he wasn't an olympic swimmer",
                "lost a breath holding contest"
            ]
        ],
        "fall" => [
            "player" => [
                [
                    "was thrown off by",
                    "and lost his life"
                ],
                [
                    "became a bird with no wings after",
                    "used a wicked spell"
                ]
            ],
            "accidental" => [

            ]
        ]
    ];

    /**
     * Gets a random death message based on cause and type of death.
     *
     * @param integer $cause
     * @param integer $type
     * @return array
     */
    public static function getRandomDeathMessage(int $cause = self::DEATH, int $type = self::ACCIDENTAL): array {
        $m = [
            "list" => self::getListOfMessage($cause),
            "type" => self::chooseTypeOfDeath($type, $m["list"]),
            "key" => array_rand($m["type"])
        ];
        $message = $m["type"][$m["key"]];
        if (is_array($message)) {
            return $message;
        }
        return [$message];
    }

    /**
     * Gets the list of death messages corresponding to the cause of death.
     *
     * @param integer $cause
     * @return array
     */
    private static function getListOfMessage(int $cause = self::DEATH): array {
        switch ($cause) {
            case self::DEATH: {
                break;
            }
            case self::FIRE: {
                return self::DEATH_MESSAGE["fire"];
                break;
            }
            case self::BREATH: {
                return self::DEATH_MESSAGE["breath"];
                break;
            }
            case self::FALL: {
                return self::DEATH_MESSAGE["fall"];
                break;
            }
        }
    }

    /**
     * Selects the death type.
     *
     * @param integer $type
     * @param array $mlist
     * @return array
     */
    private static function chooseTypeOfDeath(int $type = self::ACCIDENTAL, array $mlist): array {
        switch ($type) {
            case self::ACCIDENTAL: {
                return $mlist["accidental"];
            }
            case self::PLAYER: {
                return $mlist["player"];
            }
        }
    }

}