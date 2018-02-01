<?php

namespace VGCore\network;

class Slack {
    
    const URL = "https://hooks.slack.com/services/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";
    const HEADER = "Content-Type: application/x-www-form-urlencoded";
    const DEFAULT_CHANNEL = "bot";
    
    public static function convertStringToSlackJSON(string $string): string {
        $replace = [
            '"' => "\""   
        ];
        $slackjson = strtr($string, $replace);
        return $slackjson;
    }
    
    public static function sendTextMessage(string $text, $channel = self::DEFAULT_CHANNEL): bool {
        $post = '"text": "' . $text . '"';
        $string = self::convertStringToSlackJSON($post);
        $config = self::makeConfig($string, $channel);
        return self::sendCURLRequest($config);
    }
    
    public static function makeConfig(string $post, string $channel): array {
        return [
            "URL" => self::URL,
            "Header" => self::HEADER,
            "Channel" => self::convertStringToSlackJSON('"channel": "' . $channel . '"'),
            "Post" => $post
        ];
    }
    
    public static function sendCURLRequest(array $config): bool {
        $format = self::formatEOL([$config["Post"], "---", $config["Channel"]]);
        $package = self::makeJSONPackage($format);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $config["URL"]);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $package);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [$config["Header"]]);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        $r = curl_exec($curl);
        if (curl_errno($curl)) {
            var_dump("Error:" . curl_error($curl) . "\n");
        }
        curl_close($curl);
        if ($r === "ok") {
            return true;
        }
        return false;
    }
    
    public static function makeJSONPackage(string $convert): string {
        return "{
                    " . $convert .
                "}";
    }
    
    public static function formatEOL(array $los): string {
        $string = implode($los);
        $eol = [
            "---" => ",
            "     
        ];
        return strtr($string, $eol);
    }
    
}