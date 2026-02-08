<?php

$content = file_get_contents('src/Ksfraser/FA_Hooks/HookManager.php');
echo 'Opening braces: ' . substr_count($content, '{') . PHP_EOL;
echo 'Closing braces: ' . substr_count($content, '}') . PHP_EOL;