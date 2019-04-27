<?php

class ArithmeticEncoder
{
    const EOFChar = '^Z';

    public $probabilityTable;

    function encode($text)
    {
        echo "Encoding " . $text . "\n";
        $this->buildProbabilityTable($text);
        $this->probabilityTableOutput();

        return $this->encodeFunc($text);
    }

    //---

    function buildProbabilityTable($text)
    {
        $len = strlen($text) - strlen(Archiver::EOFChar);
        for ($i = 0; $i < $len; $i++) {
            $symbol = strval($text[$i]);

            if (isset($this->probabilityTable[$symbol])) {
                $this->probabilityTable[$symbol]++;
            } else {
                $this->probabilityTable[$symbol] = 1;
            }
        }

        $this->probabilityTable[ArithmeticEncoder::EOFChar] = 1;

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

    function encodeFunc($text)
    {
        $symbolIntEnd = $this->findInterval($text[0], $symbolIntStart);
        $oldStart = $symbolIntStart;
        $encoded = $symbolIntEnd;

        for ($i = 1, $num = strlen($text); $i < $num; $i++) {
            $symbol = $text[$i];
            $symbolIntEnd = $this->findInterval($symbol, $intervalStart);

            $oldLen = $encoded - $oldStart;

            $encoded = $oldStart + $oldLen * $symbolIntEnd;
            $oldStart = $oldStart + $oldLen * $intervalStart;
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

        return false;
    }

}

class ArithmeticDecoder
{

    function decodeFunc($encoded, $table)
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