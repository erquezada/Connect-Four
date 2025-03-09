<?php
/*
 * board.php - Represents a game board for a two-player game (e.g., Connect Four)
 */
class Board
{
    // Public Board Constants
    static $EMPTY_TOKEN = "empty token"; // Represents an empty space on the board
    static $PLAYER_TOKEN = "1"; // Token for the human player
    static $CPU_TOKEN = "2"; // Token for the computer (AI) player
    static $WIDTH = 7; // Board width (columns)
    static $HEIGHT = 6; // Board height (rows)
    static $R_GOOD = 1; // Move is valid but does not end the game
    static $R_WIN = 2; // Move results in a win
    static $R_DRAW = 3; // Move results in a draw

    // Internal Board Constants for movement directions
    static $D_LEFT = 1;
    static $D_RIGHT = 2;
    static $D_UP = 3;
    static $D_DOWN = 4;
    static $D_LEFT_DIAGONAL_DOWN = 5;
    static $D_RIGHT_DIAGONAL_DOWN = 6;
    static $D_LEFT_DIAGONAL_UP = 7;
    static $D_RIGHT_DIAGONAL_UP = 8;

    // Public Properties
    public $grid; // 2D array representing the game board
    public $winning_coords = []; // Stores winning token positions

    // Private Properties
    private $token_count = 0; // Tracks the number of placed tokens

    // Constructor - Initializes an empty board
    private function __construct()
    {
        for ($row = 0; $row < self::$HEIGHT; $row++) {
            for ($col = 0; $col < self::$WIDTH; $col++) {
                $this->grid[$row][$col] = self::$EMPTY_TOKEN;
            }
        }
    }

    // Factory Methods
    private static $EMPTY_BOARD = "";

    // Returns a singleton instance of an empty board
    public static function getEmptyBoard()
    {
        if (empty(self::$EMPTY_BOARD)) {
            self::$EMPTY_BOARD = new self();
        }
        return self::$EMPTY_BOARD;
    }

    // Creates a Board instance from a saved game file
    public static function fromPID($pid)
    {
        $data = json_decode(
            file_get_contents(Constants::$DIR_SAVING . "$pid.txt"),
            true
        );
        $instance = new self();

        $instance->grid = $data["grid"];
        $instance->winning_coords = $data["winningCoords"];

        return $instance;
    }

    // Saves the current board state to a file
    public function toPID($strategy, $pid)
    {
        $data = [
            "strategy" => $strategy,
            "grid" => $this->grid,
            "winningCoords" => $this->winning_coords,
        ];
        file_put_contents(
            Constants::$DIR_SAVING . "$pid.txt",
            json_encode($data)
        );
    }

    // Drops a token into a column and checks the game state
    public function drop_token($column, $token)
    {
        // Find the lowest empty row in the selected column
        for ($row = self::$HEIGHT - 1; $row >= 0; $row--) {
            if ($this->grid[$row][$column] == self::$EMPTY_TOKEN) {
                $highestRow = $row;
                break;
            }
        }

        // Place the token on the board
        $this->grid[$highestRow][$column] = $token;
        $this->token_count++;

        // Check if the game is a draw (board is full)
        if ($this->token_count == self::$WIDTH * self::$HEIGHT) {
            return self::$R_DRAW; // Return the draw constant
        }

        // Check if the move resulted in a win
        for ($direction = 1; $direction <= 8; $direction++) {
            if ($this->check_win($highestRow, $column, $token, $direction)) {
                array_push($this->winning_coords, $column, $highestRow);
                return self::$R_WIN; // Return the win constant
            }
        }

        // No win detected, clear winning coordinates
        $this->winning_coords = [];
        return self::$R_GOOD; // Return "good" result (valid move)
    }

    // Checks if a move results in a win by checking three adjacent tokens in a direction
    private function check_win($row, $col, $token, $direction)
    {
        $tempCoords = [];
        for ($count = 1; $count <= 3; $count++) {
            $tempRow = $row + $this->get_row_offset($direction) * $count;
            $tempCol = $col + $this->get_col_offset($direction) * $count;

            if ($this->get_token($tempRow, $tempCol) != $token) {
                return false;
            }
            array_push($tempCoords, $tempCol, $tempRow);
        }
        $this->winning_coords = $tempCoords;
        return true;
    }

    // Determines the row offset based on the movement direction
    private function get_row_offset($direction)
    {
        switch ($direction) {
            case self::$D_UP:
            case self::$D_LEFT_DIAGONAL_UP:
            case self::$D_RIGHT_DIAGONAL_UP:
                return 1;
            case self::$D_DOWN:
            case self::$D_LEFT_DIAGONAL_DOWN:
            case self::$D_RIGHT_DIAGONAL_DOWN:
                return -1;
        }
        return 0;
    }

    // Determines the column offset based on the movement direction
    private function get_col_offset($direction)
    {
        switch ($direction) {
            case self::$D_RIGHT:
            case self::$D_RIGHT_DIAGONAL_DOWN:
            case self::$D_RIGHT_DIAGONAL_UP:
                return 1;
            case self::$D_LEFT:
            case self::$D_LEFT_DIAGONAL_DOWN:
            case self::$D_LEFT_DIAGONAL_UP:
                return -1;
        }
        return 0;
    }

    // Retrieves the token at a specified position on the board
    private function get_token($row, $col)
    {
        if (
            $row < 0 ||
            $row >= self::$HEIGHT ||
            $col < 0 ||
            $col >= self::$WIDTH
        ) {
            return false;
        }
        return strval($this->grid[$row][$col]);
    }

    // Prints the board as an HTML-formatted grid
    public function print_html()
    {
        for ($row = 0; $row < self::$HEIGHT; $row++) {
            for ($col = 0; $col < self::$WIDTH; $col++) {
                print strval($this->grid[$row][$col]) . " ";
            }
            print "<br />";
        }
        print "<br />";
    }
    // Example implementation of isColumnFull in the Board class
    public function isColumnFull($column)
    {
        // Check if the topmost slot in the column is already occupied
        return isset($this->grid[0][$column]) &&
            $this->grid[0][$column] != self::$EMPTY_TOKEN;
    }
}
?>
