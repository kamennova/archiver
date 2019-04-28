<?php

class ArithmeticEncoder
{
    /**
     * [char => {float} number]
     */
    public $probabilityTable;

    function encode($text)
    {
        echo "Encoding " . $text . "\n";
        $this->buildProbabilityTable($text);
        $this->probabilityTableOutput();

        $code = $this->encodeFunc($text);
        $tableString = $this->tableToString();

        return $code . "," . $tableString;
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

        $this->probabilityTable[Archiver::EOFChar] = 1;

        $num = $len + 1;
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

    /**
     * Function for converting probability table into lightweight format
     * for future decoding
     *
     * @format:
     * "prob_num, prob_num, .... /n
     * symbol, symbol, ... /n"
     *
     * @return string
     */
    function tableToString()
    {
        $numStr = '';
        $symbolStr = '';
        foreach ($this->probabilityTable as $key => $val) {
            $numStr .= substr($val, 2) . ',';
            $symbolStr .= $key . ",";
        }

        $numStr = substr($numStr, 0, strlen($numStr) - 2) . "\n";
        $symbolStr = substr($symbolStr, 0, strlen($symbolStr) - strlen(Archiver::EOFChar) - 2) . "\n";

        return $numStr . $symbolStr;
    }

    function probabilityTableOutput()
    {
        foreach ($this->probabilityTable as $key => $val) {
            echo $val . " " . $key . "\n";
        }
    }

    //    ----

    /**
     * Example: encoding "baca" . "^Z"
     * Probability table: a: 0 -> 0.4, b: 0.4 -> 0.6, c: 0.6 -> 0.8, ^Z: 0.8 -> 1
     *
     * 1) b: 0.4->0.6
     * 2) a: 0.4 + 0.2*(0->0.4) = 0.4 -> 0.48
     * 3) c: 0.4 + 0.08*(0.6->0.8) = 0.448 -> 0.464
     * 4) a: 0.448 + 0.016*(0->0.4) = 0.448 -> 0.4544
     * 5) ^Z: 0.448 + 0.0064*(0.8-> 0.8(case1)) = 0.4992 -> 0.45376
     *
     * @param $text
     * @return string
     *
     */
    function encodeFunc($text)
    {
        // encoding first symbol
        $this->findInterval($text[0], $symbolIntStart, $symbolIntEnd);
        $encoded = [$symbolIntStart, $symbolIntEnd];

        for ($i = 1, $num = strlen($text) - strlen(Archiver::EOFChar); $i < $num; $i++) {
            $symbol = $text[$i];

            $this->findInterval($symbol, $symbolIntStart, $symbolIntEnd);
            $oldLen = $encoded[1] - $encoded[0];

            $encoded[1] = $encoded[0] + $oldLen * $symbolIntEnd; // new code interval end
            $encoded[0] = $encoded[0] + $oldLen * $symbolIntStart; // code interval start
        }

        // encoding EOF character
        $symbol = Archiver::EOFChar;
        $this->findInterval($symbol, $symbolIntStart, $symbolIntEnd);
        $oldLen = $encoded[1] - $encoded[0];

        $encoded[1] = $encoded[0] + $oldLen * $symbolIntEnd;
        $encoded[0] = $encoded[0] + $oldLen * $symbolIntStart;

        $result = $encoded[0] + ($encoded[1] - $encoded[0]) / 6 * 2;

        return substr($encoded[0], 2);
    }

    /**
     * Function gets symbol probability interval [$intervalStart, $intervalEnd]
     * @param {char} $symbol
     * @param {float} $intervalStart
     * @param {float} $intervalEnd
     */
    function findInterval($symbol, &$intervalStart, &$intervalEnd)
    {
        $counter = 0;
        $prevKey = arr_key_first($this->probabilityTable);

        foreach ($this->probabilityTable as $key => $val) {
            if ($symbol == $key) {
                $prevVal = $this->probabilityTable[$prevKey];
                if ($counter == 0) {
                    $prevVal = 0;
                }

                $intervalStart = $prevVal;

                if ($val == 1) {
                    $intervalEnd = $prevVal + ($val - $prevVal) / 2;
                }

                $intervalEnd = $val;
                return;
            }

            $counter++;
            $prevKey = $key;
        }

        return;
    }
}

class ArithmeticDecoder
{
    function decode($code, $table)
    {
//        echo "---------\n";
//        echo $code;
        $decoded = $this->findSymbol($code, $table, $intervalStart, $intervalEnd);

//        echo "\nS  Cod  St End \n";
//        echo $decoded . "\n";

        for (; ;) {
            $codeInt = number_format($code - $intervalStart, 15);
            $codeAll = number_format($intervalEnd - $intervalStart, 15);
//            echo $codeInt . " " . $codeAll . " ";
//            echo ($codeInt / $codeAll) . " ";
            $code = number_format($codeInt / $codeAll, 15);
            $symbol = $this->findSymbol($code, $table, $intervalStart, $intervalEnd);

            if ($symbol == Archiver::EOFChar) {
                break;
            }

            $decoded .= $symbol;

//            echo $symbol . " " . $code . " " . $intervalStart . " " . $intervalEnd . "\n";
        }

//        echo "Decoded: " . $decoded . "\n";
//        echo (0.32 / 0.4) . " \n";
        return $decoded;
    }

    /**
     * @param $num
     * @param $table
     * @param $intervalStart
     * @param $intervalEnd
     * @return string
     */
    function findSymbol($num, $table, &$intervalStart, &$intervalEnd)
    {
        $counter = 0;
        $prevKey = arr_key_first($table);

        foreach ($table as $key => $val) {
            if ($num < $val) {
                if ($counter == 0) {
                    $intervalStart = 0;
                } else {
                    $intervalStart = $table[$prevKey];
                }

                $intervalEnd = $val;
                return $key;
            }

            $counter++;
            $prevKey = $key;
        }

        return false;
    }
}

// ---

function arr_key_first($arr)
{
    foreach ($arr as $key => $val) {
        return $key;
    }

    return false;
}