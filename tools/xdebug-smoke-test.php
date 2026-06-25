<?php

echo "Xdebug smoke test started.\n";

if (! extension_loaded('xdebug')) {
    echo "Xdebug extension is not loaded in this PHP runtime.\n";
    exit(1);
}

echo "Xdebug extension loaded: " . phpversion('xdebug') . "\n";
echo "If VS Code is listening, execution should stop on the next line.\n";

xdebug_break();

echo "Xdebug smoke test finished.\n";
