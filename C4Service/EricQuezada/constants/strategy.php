<?php
/*
 * strategy.php
*/
require_once ("constants.php");

abstract class Strategy
{
    public static $STRATEGIES = ["Smart", "Random"];

    public static function fromPID($pid)
    {
        $data = json_decode(file_get_contents(Constants::$DIR_SAVING . "$pid.txt") , true);
        return strtolower($data["strategy"]) == "smart" ? new SmartStrategy() : new RandomStrategy();
    }

    public abstract function selectColumn($board, $playerMove);
    public abstract function toString();
}

class SmartStrategy extends Strategy
{
    // Direction Constants
    private static $DIRECTIONS = ["D_LEFT" => [-1, 0], "D_RIGHT" => [1, 0], "D_UP" => [0, 1], "D_DOWN" => [0, -1], "D_LEFT_DIAGONAL_DOWN" => [-1, -1], "D_RIGHT_DIAGONAL_DOWN" => [1, -1], "D_LEFT_DIAGONAL_UP" => [-1, 1], "D_RIGHT_DIAGONAL_UP" => [1, 1]];

    public function selectColumn($board, $playerMove)
    {
        $playerMove = intval($playerMove);
        $grid = $board->grid;

        // Try to win or block the opponent from winning
        for ($col = 0;$col < Board::$WIDTH;$col++)
        {
            for ($row = Board::$HEIGHT - 1;$row >= 0;$row--)
            {
                if ($this->get_token($grid, $row, $col) == Board::$CPU_TOKEN)
                {
                    $winningMove = $this->checkForWinningMove($grid, $row, $col);
                    if ($winningMove !== null) return $winningMove;
                }
            }
        }

        // Block the player from winning
        for ($col = 0;$col < Board::$WIDTH;$col++)
        {
            for ($row = Board::$HEIGHT - 1;$row >= 0;$row--)
            {
                if ($this->get_token($grid, $row, $col) == Board::$PLAYER_TOKEN)
                {
                    $blockingMove = $this->checkForBlockingMove($grid, $row, $col);
                    if ($blockingMove !== null) return $blockingMove;
                }
            }
        }

        // Select a column near the center if no immediate moves are found
        return $this->selectCenterColumn($grid);
    }

    private function checkForWinningMove($grid, $row, $col)
    {
        // Check for winning in all directions
        foreach (self::$DIRECTIONS as $direction => $offset)
        {
            $gameWon = $this->check_win($grid, $row, $col, Board::$CPU_TOKEN, $offset);
            if ($gameWon)
            {
                return $col;
            }
        }
        return null;
    }

    private function checkForBlockingMove($grid, $row, $col)
    {
        // Check if player can win in the next move
        foreach (self::$DIRECTIONS as $direction => $offset)
        {
            $gameWon = $this->check_win($grid, $row, $col, Board::$PLAYER_TOKEN, $offset);
            if ($gameWon)
            {
                return $col;
            }
        }
        return null;
    }

    private function check_win($grid, $row, $col, $token, $direction)
    {
        // Check if the player or AI can win in the specified direction
        $offset = self::$DIRECTIONS[$direction];
        for ($count = 1;$count <= 2;$count++)
        {
            $tempRow = $row + ($offset[0] * $count);
            $tempCol = $col + ($offset[1] * $count);
            if ($this->get_token($grid, $tempRow, $tempCol) != $token) return false;
        }
        return true;
    }

    private function selectCenterColumn($grid)
    {
        $columns = [3, 2, 4, 1, 5, 0, 6];
        foreach ($columns as $col)
        {
            if ($grid[0][$col] == Board::$EMPTY_TOKEN)
            {
                return $col;
            }
        }
        return -1; // No valid move found
        
    }

    private function get_token($grid, $row, $col)
    {
        if ($row < 0 || $row >= Board::$HEIGHT || $col < 0 || $col >= Board::$WIDTH) return null;
        return $grid[$row][$col];
    }

    public function toString()
    {
        return "Smart";
    }
}

class RandomStrategy extends Strategy
{
    public function selectColumn($board, $playerMove)
    {
        $grid = $board->grid;
        $availableColumns = array();

        // Collect all available columns
        for ($col = 0;$col < Board::$WIDTH;$col++)
        {
            if ($grid[0][$col] == Board::$EMPTY_TOKEN)
            {
                $availableColumns[] = $col;
            }
        }

        // Randomly pick a column
        return $availableColumns[array_rand($availableColumns) ];
    }

    public function toString()
    {
        return "Random";
    }
}
?>
