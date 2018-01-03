<?php

namespace VGCore\sound\nbs;

use pocketmine\nbt\NBT;

use pocketmine\Server;

use pocketmine\utils\Binary;
// >>>
use VGCore\sound\nbs\NBSNote;
use VGCore\sound\nbs\NBSLayer;

class NBSParser {
    
    const PIANO = 0;
    const BASS_TWICE = 1;
    const BASS_DRUM = 2;
    const SNARE = 3;
    const CLICK = 4;
    const GUITAR = 5;
    const FLUTE = 6;
    const BELL = 7;
    const CHIME = 8;
    const XYLOPHONE = 9;
    
    public $buff;
    public $offset;
    public $endian = NBT::LITTLE_ENDIAN;
    public $lenght = 0;
    public $layer = 0;
    public $name = "";
    public $author = "";
    public $singer = "";
    public $songdesc = "";
    public $tempo = 0; // 0 should be fine as default.. right? ¯\_(ツ)_/¯
    public $save = 0; // bool value -> 0 = true
    public $savedur = 60;
    public $tmsign = 4;
    public $min = 0;
    public $lclick = 0;
    public $rclick = 0;
    public $noteadd = 0;
    public $noterm = 0;
    public $filename = "";
    public $note = [];
    public $layerdata = [];
    
    private $data;
    
    public function __construct(string $path) {
        $file = fopen($path, "r");
        $this->buff = fread($file, filesize($path));
        fclose($file);
        $this->length = $this->short();
		$this->layer = $this->short();
		$this->name = $this->string();
		$this->author = $this->string();
		$this->singer = $this->string();
		$this->songdesc = $this->string();
		$this->tempo = $this->short();
		$this->save = $this->byte();
		$this->savedur = $this->byte();
		$this->tmsign = $this->byte();
		$this->min = $this->int();
		$this->lclick = $this->int();
		$this->rclick = $this->int();
		$this->noteadd = $this->int();
		$this->noterm = $this->int();
		$this->filename = $this->string();
		$note = [];
		$instcount = [];
		$layerdata = [];
		$tick = -1;
		$dash = 0;
		while (true) {
            $dash = $this->short();
            if ($dash === 0) {
                break;
            } else {
                $tick += $dash;
                $layer = -1;
                while (true) {
                    $dash = $this->short(); 
                    if ($dash === 0) {
                        break;
                    } else {
                        $layer += $dash;
                        $inst = $this->byte();
                        $key = $this->byte();
                        $note[] = new NBSNote($tick, $layer, $inst, $key);
                        if (isset($instcount[$inst])) {
                            $isntcount[$inst]++;
                        } else {
                            $instcount[$inst] = 1;
                        }
                        if ($layer < $this->layer) {
                            if (isset($layerdata[$layer])) {
                                $layerdata[$layer]++;
                            } else {
                                $layerdata[$layer] = 1;
                            }
                        };
                    }
                }
            }
		}
		$this->note = $note;
		for ($i = 0; $i < $this->layer; $i++) {
            $layer = new NBSLayer($i + 1, $this->string(), $this->byte(), $layerdata[$i] ?? 0);
            $this->layerdata[] = $layer;
		}
    }
    
    public function note() {
        return $This->note;
    }
    
    public function noteTick(int $tick) {
        $note = [];
        foreach ($this->note as $note1) {
            if ($note->tick === $tick) {
                $note[] = $note1;
            }
        }
        return $note;
    }
    
    public function layerData() {
        return $this->layerdata;
    }
    
    public function info($bin) {
        if ($bin < 0) {
            $this->offset = strlen($this->buff) - 1;
            return "";
        } else if ($bin === true) {
            return substr($this->buff, $this->offset);
        }
        return $bin === 1 ? $this->buff[$this->offset++] : substr($this->buff, ($this->offset += $bin) - $bin, $bin);
    }
    
    public function string(bool $net = false): string {
        return $this->info(unpack("I", $this->info(4))[1]);
    }
    
    public function short(): int {
        $bin1 = Binary::readShort($this->info(2));
        $bin2 = Binary::readLShort($this->info(2));
        return $this->endian === NBT::BIG_ENDIAN ? $bin1 : $bin2;
    }
    
    public function byte(): int {
        return Binary::readByte($this->info(1));
    }
    
    public function int(bool $net = false): int {
        if ($net === true) {
            return Binary::readVarInt($this->buff, $this->offset);
        }
        $bin1 = Binary::readShort($this->info(4));
        $bin2 = Binary::readLShort($this->info(4));
        return $this->endian === NBT::BIG_ENDIAN ? $bin1 : $bin2;
    }
    
}