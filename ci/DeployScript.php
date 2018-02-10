<?php

// Thankyou Travis Docs!

// Opens up the pipes giving read-write perms on files.
$pipearray = [
    0 => ["pipe", "r"],
    1 => ["pipe", "w"],
    2 => ["pipe", "w"]
];
$server = proc_open(PHP_BINARY . " src/pocketmine/Pocketmine.php --no-wizard --disable-readline", $pipearray, $pipe);
// checks if PHP went alright
fwrite($pipe[0], "makeplugin VGCore\nstop\n\n");
$test = feof($pipe[1]);
while (!$test) {
    echo fgets($pipe[1]);
}
// close the pipes
fclose($pipe[0]);
fclose($pipe[1]);
fclose($pipe[2]);
echo "\n\nReturn value: " . proc_close($server) . "\n";
$filecheck = glob("plugins/DevTools/VGCore*.phar");
if (count($filecheck) === 0) {
    echo "Failed to create build. Travis CI had an error and is now going to bed. Fix to wake it up.\n";
    exit(0); // 0 is for false
} else if (count($filecheck) !== 0) {
    $file = glob("plugins/DevTools/VGCore*");
    rename($file, "plugins/DevTools/VGCore.phar");
    $phar = new Phar(__DIR__ . "plugins/DevTools/VGCore.phar");
    $phar->startBuffering();
    $phar->compress(Phar::GZ);
    $phar->stopBuffering();
    echo "Created build succesfully!\n";
    exit(1); // 1 is for true
}