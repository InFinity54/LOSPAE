<?php
    if ($handle = opendir(realpath(__DIR__."/../var"))) {
        while (false !== ($file = readdir($handle))) {
            if (!in_array($file, [".", "..", "cache", "log"])) {
                if (filectime(realpath(__DIR__."/../var/".$file)) < (time() - 600)) {
                    unlink(realpath(__DIR__."/../var/".$file));
                }
            }
        }
    }