<?php
namespace CSVInterface;
use Exception;

class Reader {
    protected $csvHandler;
    protected string $separator;
    protected array $headers;
    protected array $rows;
    protected int $columnCount;
    protected int $rowCount = 0;

    /**
     * Reader constructor.
     * @param string $filePath Path to CSV file on local filesystem
     * @param string $separator Separator for CSV files
     * @throws Exception If filePath does not exist on local filesystem
     */
    public function __construct(string $filePath, string $separator=','){
        if (!file_exists($filePath)) {
            throw new Exception("$filePath does not exist on local filesystem.");
        }

        $this->csvHandler = fopen($filePath, 'r');
        $this->separator = $separator;

        $headers = fgetcsv($this->csvHandler, 0, $separator);
        $this->headers = $headers;
        $this->columnCount = count($headers);

        while ($row = fgetcsv($this->csvHandler, 0, $this->separator)) {
            $this->rows[] = $row;
            $this->rowCount++;
        }
    }

    /**
     * Fetch the headers from the CSV
     * @return array
     */
    public function fetchHeaders():array {
        return $this->headers;
    }

    /**
     * @param int $row
     * @param int $column
     * @return string|null
     */
    public function fetchCell(int $row, int $column):string|null {
        if ($row < 0 || $row > count($this->rows) - 1) return null;
        if ($column < 0 || $column > count($this->rows[$row]) - 1) return null;
        return $this->rows[$row][$column] ?? null;
    }

    /**
     * @return int
     */
    public function fetchRowCount():int{
        return $this->rowCount;
    }

    /**
     * Fetch a particular row from the CSV file
     * @param int $row
     * @return array|null
     * @throws Exception
     */
    public function fetchRow(int $row):array|null {
        if ($row < 0 || $row > $this->rowCount - 1)
            throw new Exception("Invalid row number $row requested. Rows available: ".$this->rowCount);

        return $this->rows[$row] ?? null;
    }

    /**
     * @return int
     */
    public function fetchColumnCount():int{
        return $this->columnCount;
    }

    /**
     * @param int $column
     * @return array|null
     * @throws Exception
     */
    public function fetchColumn(int $column):array|null {
        if ($column < 0 || $column > $this->columnCount - 1)
            throw new Exception("Invalid column number $column requested. Columns available: ".$this->columnCount);

        return array_column($this->rows, $column);
    }


    /**
     * @param int $row
     * @param string $regex
     * @return bool
     * @throws Exception
     */
    public function validateRow(int $row, string $regex):bool{
        if ($row < 0 || $row > $this->rowCount - 1)
            throw new Exception("Invalid row number $row requested. Rows available: ".$this->rowCount);

        $allValid = true;
        foreach($this->rows[$row] as $cell) {
            $match = preg_match($regex, $cell);
            if ($match === false) throw new Exception("Invalid regex $regex provided to validateRow");
            if (!$match) {
                $allValid = false;
                break;
            }
        }
        return $allValid;
    }

    /**
     * @param int $column
     * @param string $regex
     * @return bool
     * @throws Exception
     */
    public function validateColumn(int $column, string $regex):bool{
        if ($column < 0 || $column > $this->columnCount - 1)
            throw new Exception("Invalid column number $column requested. Columns available: ".$this->columnCount);

        $allValid = true;
        foreach(array_column($this->rows, $column) as $cell){
            $match = preg_match($regex, $cell);
            if ($match === false) throw new Exception("Invalid regex $regex provided to validateRow");
            if (!$match) {
                $allValid = false;
                break;
            }
        }
        return $allValid;
    }
}
