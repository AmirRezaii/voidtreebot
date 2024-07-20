<?php

function f_log(string $text, string $file = "") : void {
    if ($file) {
        $myfile = fopen("{$file}_log.txt", "w") or die("Unable to open file!");

    } else {
        $myfile = fopen("log.txt", "w") or die("Unable to open file!");
    }

    fwrite($myfile, $text);
    fclose($myfile); 
}
