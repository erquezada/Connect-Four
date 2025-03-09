<?php
/*
 * new/index.php
 * This script handles a request to generate a new game with a specified strategy.
*/

// Include necessary files
require_once ("../constants/strategy.php");
require_once ("../constants/board.php");

// Fetch the 'strategy' parameter from the query string, if it exists
$strategy = isset($_GET['strategy']) ? $_GET['strategy'] : "";

// Initialize an empty response array to store the output
$response = array();

// Check if a strategy was provided
if (empty($strategy))
{
    // If no strategy is specified, return an error response
    $response = array(
        "response" => false,
        "reason" => "Strategy not specified."
    );
}
else if (!in_array_ignore_case($strategy, Strategy::$STRATEGIES))
{
    // If the provided strategy is not valid (not in the list of known strategies), return an error response
    $response = array(
        "response" => false,
        "reason" => "Unknown strategy."
    );
}
else
{
    // If a valid strategy is provided, generate a unique game ID (pid) for the new game
    $pid = uniqid();
    $response = array(
        "response" => true,
        "pid" => $pid
    );

    // Get a new empty game board and assign the provided strategy and generated pid to it
    $emptyBoard = Board::getEmptyBoard(); // This now returns a new instance
    // Ensure it's a valid object
    if (!$emptyBoard instanceof Board)
    {
        $response = array(
            "response" => false,
            "reason" => "Failed to create a board object."
        );
        echo json_encode($response);
        exit;
    }

    // Save the board state to a file
    $emptyBoard->toPID($strategy, $pid);
}

// Encode the response array as a JSON object and send it to the client
echo json_encode($response);

// Helper function to check if a value exists in an array, ignoring case
function in_array_ignore_case($needle, $haystack)
{
    return in_array(strtolower($needle) , array_map('strtolower', $haystack));
}
?>
