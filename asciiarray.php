<?php

// Generate the array of ASCII characters from comma to pipe
$asciiArray = range(',', '|');
// Display the original array
echo "Original ASCII range: ";
foreach ($asciiArray as $char) {
    echo $char;
}
echo "\n";

// Randomly remove an element
$removedIndex = array_rand($asciiArray);
$removedChar = $asciiArray[$removedIndex];
unset($asciiArray[$removedIndex]);
// Display the removed character for verification
echo "Removed character: $removedChar\n";

// Efficiently find the missing character
$expectedSum = array_sum(range(ord(','), ord('|')));
$actualSum = array_sum(array_map('ord', $asciiArray));

// Calculate the missing character by ASCII value difference
$missingCharAscii = $expectedSum - $actualSum;
$missingChar = chr($missingCharAscii);

echo "Missing character is: $missingChar\n";

?>