<?php
/*
 * info/index.php
 * This script generates information about the game board and available strategies.
*/

// Include necessary files
require_once ("..\constants\strategy.php");
require_once ("..\constants\board.php");

// Prepare the output data array
$output = array(
    // Set the board width from the Board class constant
    "width" => Board::$WIDTH,
    // Set the board height from the Board class constant
    "height" => Board::$HEIGHT,
    // Get the available strategies from the Strategy class
    "strategies" => Strategy::$STRATEGIES
);

// Encode the output array to JSON format and send it as a response
echo json_encode($output);

?>
