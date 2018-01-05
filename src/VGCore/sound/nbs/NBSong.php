<?php

namespace VGCore\sound\nbs;

use pocketmine\nbt\NBT;

use pocketmine\Server;

use pocketmine\utils\Binary as Bin;
// >>>
use VGCore\sound\nbs\NBSLayer;
use VGCore\sound\nbs\NBSNote;

class NBSong {
    
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
    
    public $b;
    public $o;
    public $endian = NBT::LITTLE_ENDIAN;
    public $lenght = 0;
    public $layer = 0;
    public $name;
    public $author;
    public $originalauthor;
    public $songdesc;
    public $tempo = 0;
    public $save = 0;
    public $savedur = 0;
    public $ts = 4;
    public $ms = 0;
    public $lc = 0;
    public $rc = 0;
    public $ba = 0;
    public $br = 0;
    public $filename = "";
    
    public $note = [];
    public $layerinfo = [];
    
    private $data;
    
    public function __construct(string $filepath) {
        $file = fopen($filepath, "r");
        $this->b = fread($file, filesize($filepath));
        fclose($file);
        // Making the header of the Binary NBT
        $this->lenght = $this->short();
        $this->layer = $this->short();
        $this->name = $this->string();
        $this->author = $this->string();
        $this->originalauthor = $this->string();
        $this->songdesc = $this->string();
        $this->tempo = $this->short();
        $this->save = $this->byte();
        $this->savedur = $this->byte();
        $this->ts = $this->byte();
        $this->ms = $this->int();
        $this->lc = $this->int();
        $this->rc = $this->int();
        $this->ba = $this->int();
        $this->br = $this->int();
        $this->filename = $this->string();
        // Collecting Data on Binary NBT
        $note = [];
        $ic = [];
        $lc = [];
        $t = -1;
        $j = 0;
        while (true) {
            $j = $this->short();
            if ($j === 0) {
                break;
            }
            $t += $j;
            $l = -1;
            while (true) {
                $j = $this->short();
                if ($j === 0) {
                    break;
                }
                $l += $j;
                $i = $this->byte();
                $k = $this->byte();
                $note[] = new NBSNote($t, $l, $i, $k);
                if (isset($ic[$i])) {
                    $ic[$i]++;
                } else {
                    $ic[$i] = 1;
                }
                if ($l < $this->layer) {
                    if (isset($lc[$l])) {
                        $lc[$l]++;
                    } else {
                        $lc[$l] = 1;
                    }
                };
            }
        }
        // Making layers.
        for ($i = 0; $i < $this->layer; $i++) {
            $layer = new NBSLayer($i + 1, $this->string(), $this->byte(), $lc[$i] ?? 0);
            $this->layerinfo[] = $layer;
        }
    }
    
    public function info($len) {
        if ($len < 0) {
            $this->o = strlen($this->b) - 1;
        } else if ($len === true) {
            return substr($this->b, $this->o);
        }
        $substr = substr($this->b, ($this->o += $len) - $len, $len);
        return $len === 1 ? $this->b{$this->o} : $substr;
    }
    
    public function string(bool $netid = false) {
        $info = $this->info(4);
        $unpack = unpack("I", $info);
        return $this->info($unpack[1]);
    }
    
    public function byte(): int {
        $info = $this->info(1);
        return Bin::readByte($info);
    }
    
    public function int(bool $netid = false): int {
        if ($netid === true) {
            return Bin::readVarInt($this->b, $this->o);
        }
        $info = $this->info(4);
        $b1 = Bin::readInt($info);
        $b2 = Bin::readLInt($info);
        return $this->endian === NBT::BIG_ENDIAN ? $b1 : $b2;
    }
    
    public function short() {
        $info = $this->info(2);
        $b1 = Bin::readShort($info);
        $b2 = Bin::readLShort($info);
        return $this->endian === NBT::BIG_ENDIAN ? $b1 : $b2;
    }
    
    public function note() {
        return $this->note;
    }
    
    public function noteTick(int $tick): array {
        $allnote = [];
        foreach ($this->note as $note) {
            if ($note->tick === $tick) {
                $allnote[] = $note;
            }
        }
        return $allnote;
    }
    
    public function layerInfo(): array {
        $this->layerinfo;
    }
    
}