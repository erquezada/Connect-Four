<?php
/*
 *
 * play/index.php
 * This script handles a player's move in the game, processes it, and plays the AI's move.
*/

// Include necessary files
require_once ("../constants/strategy.php"); 
require_once ("../constants/board.php"); 
// Retrieve the 'pid' (game ID) and 'move' from the query string
$pid = isset($_GET['pid']) ? $_GET['pid'] : "";
$move = isset($_GET['move']) ? $_GET['move'] : "";

// Parse the move as an integer if it's numeric
$move = is_numeric($move) ? intval($move) : $move;

// Handle error cases if the required parameters are not provided or invalid
if (empty($pid))
{
    // If no 'pid' is provided, return an error response
    $response = array(
        "response" => false,
        "reason" => "Pid not specified"
    );
}
else if (!file_exists(Constants::$DIR_SAVING . "$pid.txt"))
{
    // If the game ID does not correspond to an existing file, return an error response
    $response = array(
        "response" => false,
        "reason" => "Unknown pid"
    );
}
else if ($move === "")
{
    // If no move is provided, return an error response
    $response = array(
        "response" => false,
        "reason" => "Move not specified"
    );
}
else if (!is_numeric($move) || $move < 0 || $move >= Board::$WIDTH)
{
    // If the move is not numeric or out of bounds, return an error response
    $response = array(
        "response" => false,
        "reason" => "Invalid move: $move"
    );
}
else
{
    // If all parameters are valid, process the move
    // Reconstruct the board from the saved game state (using the pid)
    $board = Board::fromPID($pid);

    // Attempt to drop the player's token in the specified column (move)
    $result = $board->drop_token($move, Board::$PLAYER_TOKEN);

    if ($result == Board::$R_GOOD)
    {
        // If the player's move was successful, make the AI's move
        // Fetch the strategy associated with the game (using the pid)
        $strategy = Strategy::fromPID($pid);

        // Get the AI's column selection based on the board state and the player's move
        $cpuColumn = $strategy->selectColumn($board, $move);

        // Validate the AI's move to ensure it's within bounds and valid
        if ($cpuColumn < 0 || $cpuColumn >= Board::$WIDTH || $board->isColumnFull($cpuColumn))
        {
            // If the AI's selected column is invalid or full, return an error
            $response = array(
                "response" => false,
                "reason" => "Invalid AI move"
            );
        }
        else
        {
            // Prepare the response acknowledging the player's move and AI's move
            $response = array(
                "response" => true,
                "ack_move" => array(
                    "slot" => $move,
                    "isWin" => false,
                    "isDraw" => false,
                    "row" => array() // No winning row yet
                    
                ) ,
                "move" => ai_move_array($board, $cpuColumn) // Include CPU's move in the response
                
            );

            // Save the updated game state (board and strategy) to the file
            $board->toPID($strategy->toString() , $pid);
        }
    }
    else
    {
        // If the player's move is invalid (e.g., the column is full), acknowledge the game outcome
        $response = array(
            "response" => true,
            "ack_move" => array(
                "slot" => $move,
                "isWin" => $result == Board::$R_WIN, // Check if the player won
                "isDraw" => $result == Board::$R_DRAW, // Check if the game is a draw
                "row" => ($result == Board::$R_WIN) ? $board->winning_coords : array() // Provide the winning coordinates if there's a win
                
            )
        );

        // If the game has ended (win or draw), delete the game state file (pid)
        unlink(Constants::$DIR_SAVING . "$pid.txt");
    }
}

// Encode and return the response as a JSON object
echo json_encode($response);

// Helper function to process the AI's move and return the response structure
function ai_move_array($board, $cpuColumn)
{
    // Drop the CPU's token in the selected column
    $cpuResult = $board->drop_token($cpuColumn, Board::$CPU_TOKEN);

    // Return an array with the result of the AI's move (win or draw status)
    return array(
        "slot" => $cpuColumn,
        "isWin" => $cpuResult == Board::$R_WIN,
        "isDraw" => $cpuResult == Board::$R_DRAW,
        "row" => ($cpuResult == Board::$R_WIN) ? $board->winning_coords : array() // Include the winning coordinates if the AI won
        
    );
}
?>
