<?php

class ArithmeticEncoder
{
    public $probabilityTable;

    function __construct($text)
    {
        $this->buildProbabilityTable($text);
        $this->probabilityTableOutput();

        $encoded = $this->encode($text);
        echo "Encoded: " . $encoded . "\n";
    }

//    ---

    function buildProbabilityTable($text)
    {
        for ($i = 0, $len = strlen($text); $i < $len; $i++) {
            $symbol = strval($text[$i]);

            if (isset($this->probabilityTable[$symbol])) {
                $this->probabilityTable[$symbol]++;
            } else {
                $this->probabilityTable[$symbol] = 1;
            }
        }

        $num = strlen($text);
        foreach ($this->probabilityTable as $key => $val) {
            $this->probabilityTable[$key] = $val / $num;
        }

        // sort DESC
        arsort($this->probabilityTable, SORT_DESC);

        $prev = 0;
        foreach ($this->probabilityTable as $key => $val) {
            $this->probabilityTable[$key] += $prev;
            $prev += $val;
        }
    }

    function probabilityTableOutput()
    {
        foreach ($this->probabilityTable as $key => $val) {
            echo $val . " " . $key . "\n";
        }
    }

//    ----

    function encode($text)
    {
        echo " =-------=\n";

        $intervalEnd = $this->findInterval($text[0], $intervalStart);
        $encoded = $intervalEnd;

        echo "Symb Prev Int Encoded \n";

        for ($i = 1, $num = strlen($text); $i < $num; $i++) {
            $symbol = $text[$i];

            $oldStart = $intervalStart;
            $oldInterval = $intervalEnd - $intervalStart;

            $intervalEnd = $this->findInterval($symbol, $intervalStart);
            $encoded = $oldStart + $oldInterval * $intervalEnd;

            echo $symbol . " ";
            echo $intervalEnd . " ";
            echo $encoded . "\n";
        }

        return $encoded;
    }

    function findInterval($symbol, &$intervalStart)
    {
        $counter = 0;
        $prevKey = $this->arr_key_first($this->probabilityTable);

        foreach ($this->probabilityTable as $key => $val) {
            if ($symbol == $key) {
                $prevVal = $this->probabilityTable[$prevKey];
                if ($counter == 0) {
                    $prevVal = 0;
                }

                $intervalStart = $prevVal;

                if ($val == 1) {
                    return $prevVal + ($val - $prevVal) / 2;
                }

                return $val;
            }

            $counter++;
            $prevKey = $key;
        }

        return 0;
    }

    function arr_key_first($arr)
    {
        foreach ($arr as $key => $val) {
            return $key;
        }
    }

}

class ArithmeticDecoder
{

//    ---

    function decode($encoded, $table)
    {

        echo "---------\n";
        $decoded = '';
        $intervalEnd = 1;

        for ($i = 0; $i < 7; $i++) {
            $encoded /= $intervalEnd;
            echo $intervalEnd . " ";
            echo $encoded . " ";
            $symbol = $this->findSymbol($encoded, $table, $intervalEnd);
            echo $symbol . "\n";
            $decoded .= $symbol;
        }

        return $decoded;
    }

    /**
     * @param $num
     * @param $table
     * @param $intervalEnd
     * @return string
     * @throws ErrorException
     */
    function findSymbol($num, $table, &$intervalEnd)
    {
        $prevKey = 0;
        foreach ($table as $key => $val) {
            if ($num < $val) {
                $intervalEnd = $val;
                return $key;
            }
        }

        throw new ErrorException('Incorrect encoded message');
    }
}

