<?php

namespace VGCore\cosmetic\api;

use pocketmine\entity\Skin;
use pocketmine\Player;
use pocketmine\utils\Config;
// >>>
use VGCore\SystemOS;

class CAPI {
    
    const MULTIPLIER_LEFT = [];
	const MULTIPLIER_RIGHT = [];
	const MULTIPLIER_TOP = [];
    const MULTIPLIER_BOTTOM = [];
	const MULTIPLIER_FRONT = [];
	const MULTIPLIER_BACK = [];
    
    public function __construct(SystemOS $plugin) {
        $this->plugin = $plugin;  
    }
    
    public function jsonSerialise(Skin $skin) {
        $data = [
            "skinId" => $skin->getSkinId(),
			"skinData" => $skin->getSkinData(),
			"capeData" => $skin->getCapeData(),
			"geometryName" => $skin->getGeometryName(),
			"geometryData" => $skin->getGeometryData(),
            ];
        return $data;
    }
    
    public function convertImage($data, $height = 64, $width = 64) { // image geometry - fuck yeah!
        $pixelarray = str_split(bin2hex($data), 8);
        $image = imagecreatetruecolor($width, $height);
        imagealphablending($image, false); // Please don't change :)
        imagesavealpha($image, true);
        $position = count($pixelarray) - 1;
        while (!empty($pixelarray)) {
            $x = $position % $width;
            $px = $position - $x;
            $y = $px / $height;
            $walkable = str_split(array_pop($pixelarray), 2);
            $color = array_map(function ($val) {
                return hexdec($val); 
            }, $walkable);
            $alpha = array_pop($color); // 0 <=>
            $alpha = ((~((int)$alpha)) & 0xff) >> 1; // ^ 0xff - 1
            array_push($color, $alpha);
            imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, ...$color));
            $position--;
        }
        return $image;
    }
    
    public function resourceImage($image) {
        $combine = [];
        for ($y = 0; $y < imagesy($image); $y++) {
            for ($x = 0; $x < imagesx($image); $x++) {
                $color = imagecolorsforindex($image, imagecolorat($image, $x, $y));
                $color['alpha'] = (($color['alpha'] << 1) ^ 0xff) - 1; // alpha is not even (idk how to fix, pls god help)
                $combine[] = sprintf("%02x%02x%02x%02x", $color['red'], $color['green'], $color['blue'], $color['alpha']??0);
            }
        }
        $data = hex2bin(implode('', $combine)); // hex to binary data conversion. 
        return $data; // get data from image (yay?)
    }
    
    public function arraySearch($array, $key, $value, &$results) {
        if (!is_array($array)) {
            return;
        }
        if (isset($array[$key]) && $array[$key] == $value) {
            $results[] = $array;
        }
        foreach ($array as $subarray) {
            $this->arraySearch($subarray, $key, $value, $results);
        }
    }
    
    public function cleanJSON($json, $assoc = false, $depth = 512, $options = 0) {
        $json = preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json); // remove comment line code
        if (version_compare(phpversion(), '5.4.0', '>=')) {
            return json_decode($json, $assoc, $depth, $options);
        } else if (version_compare(phpversion(), '5.3.0', '>=')) {
            return json_decode($json, $assoc, $depth);
        } else {
            return json_decode($json, $assoc);
        }
    }
    
    public function search($array, $key, $value) {
        $results = array();
        $this->arraySearch($array, $key, $value, $results);
        return $results;
    }
    
    public function jsonToSkin(Skin $skin, $json) {
        $skingeometry = $skin->getGeometryData();
        $base = json_decode($skingeometry, true);
        $json = str_replace('%s', $skin->getGeometryName(), $json);
        $extension = $this->cleanJSON($json, true);
        $finished = json_encode(array_merge($base, $extension));
        return $finished;
    }
    
    public function getObject(Skin $skin, $partname, $side = MULTIPLIER_FRONT) {
        $skindata = $skin->getSkinData();
        $image = $this->convertImage($skindata);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        $skingeometry = $skin->getGeometryData();
        $decodesg = json_decode($skingeometry, true);
        // >>> Head
        $query = $this->search($decodesg, 'name', $partname);
        $partgeometry = $query[0];
        $startpos = $partgeometry["cubes"][0]["uv"];
        $size = $partgeometry["cubes"][0]["size"];
        $startpos[0] = $startpos[0] + $size[2];
        $startpos[1] = $startpos[1] + $size[1];
        $part = imagecreatetruecolor($size[0], $size[1]);
        imagealphablending($part, false);
		imagesavealpha($part, true);
		imagecopy($part, $image, 0, 0, $startpos[0], $startpos[1], $size[0], $size[1]);
		return $part;
    }
    
    public function getObjectHead(Skin $skin, $showhat = false) {
        $head = $this->getObject($skin, 'head');
        if ($showhat) {
            $object = $this->getObject($skin, 'hat');
            $return = $this->mergeObject($head, $object);
            return $return;
        }
        $merge = $this->mergeObject($head);
        return $merge;
    }
    
    public function getSkin(Skin $skin, string $path, string $filename) { // FILENAME MUST BE IN JSON
        $config = new Config($path . $filename);
        $config->setAll([$skin->getSkinId() => [$skin->getSkinData(), $skin->getGeometryData()]]);
        $config->save();
        $image = $this->convertImage($skin->getSkinData());
        imagepng($image, $path);
    }
    
    public function getSkinOfPlayer(Player $player, string $path, string $filename) {
        $skin = $player->getSkin();
        $this->getSkin($skin, $path, $filename);
    }
    
    public function mergeObject($parts) {
        $baseimage = $parts[0];
        $base = imagecreatetruecolor(imagesx($baseimage), imagesy($baseimage));
        imagesavealpha($base, true);
        foreach ($parts as $part) {
            $image = $part;
            imagecopy($base, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        }
        return $base;
    }
    
    public function mergeSkinToImage($skins) {
        $baseskin = $skins[0];
        $baseimage = $this->convertImage($baseskin->getSkinData());
        $base = imagecreatetruecolor(imagesx($baseimage), imagesy($baseimage));
        imagesavealpha($base, true);
        imagefill($base, 0, 0, imagecolorallocatealpha($base, 0, 0, 0, 127));
        foreach ($skins as $skin) {
            $image = $this->convertImage($skin->getSkinData());
            imagecopy($base, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        }
        return $base;
    }
    
    public function mergeData($skinDataSets) {
        $baseskin = $skinDataSets[0];
        $baseimage = $this->convertImage($baseskin);
        $base = imagecreatetruecolor(imagesx($baseimage), imagesy($baseimage));
        imagesavealpha($base, true);
		imagefill($base, 0, 0, imagecolorallocatealpha($base, 0, 0, 0, 127));
		foreach ($skinDataSets as $skinData) {
		    $image = $this->convertImage($skinData);
		    imagecopy($base, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
		}
		$return = $this->resourceImage($base);
		return $return;
    }
    
}