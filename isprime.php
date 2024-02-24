<?php

for ($i = 1; $i <= 100; $i++) {
    echo "$i ";
    $multiples = [];

    for ($j = 2; $j < $i; $j++) {
        if ($i % $j == 0) {
            $multiples[] = $j;
        }
    }

    if (count($multiples) === 0) {
        echo "[PRIME]";
    } else {
        echo "[" . implode(", ", $multiples) . "]";
    }
    echo "\n";
}

?>
