<?php

require_once "ArithmeticCoding.php";

class Archiver
{
    const EOFChar = '^Z';

    public $code;

    function compress($input, $output)
    {
        $text = $this->getFileText($input);
        $encoder = new ArithmeticEncoder;
        $encoded = $encoder->encode($text . Archiver::EOFChar);

        echo "Encoded: " . $encoded . "\n";

        $this->newDataFile($output, $encoded);
    }

    function extract($compressed, $output)
    {
        $table = $this->getProbabilityTable($compressed);
        $decoder = new ArithmeticDecoder;
        $decoded = $decoder->decode($this->code, $table);

        echo "Decoded: " . $decoded . "\n";

        $this->newDataFile($output, $decoded);
    }

    function newDataFile($filename, $data)
    {
        $newFile = $filename;
        $handle = fopen($newFile, 'w') or die('Could not open file :/');
        fwrite($handle, $data);
        fclose($handle);
    }

    //    ---

    function getFileText($filename)
    {
        $text = '';

        $file = fopen($filename, 'r');

        while (!feof($file)) {
            $text .= fgets($file);
        }

        fclose($file);

        return $text;
    }

    /**
     * Func builds symbols probability table from compressed file data with format:
     * code,symbol1_frequency,symbol2_frequency ... /n
     * symbol1,symbol2,...
     *
     * @param $filename
     * @return array
     */
    function getProbabilityTable($filename)
    {
        $probabilityTable = [];

        $file = fopen($filename, 'r') or die('Could not open file :/');

        $numStr = fgets($file);

        $symbolStr = fgets($file);
        if (!feof($file)) {
            $symbolStr .= fgets($file);
        }

        fclose($file);

        $numbers = explode(',', substr($numStr, 0, strlen($numStr)-1));
        $this->code = floatval(array_shift($numbers));

//        $numbers = array_map('get_float_num', $numbers);
        array_push($numbers, ArithmeticEncoder::$intervalMax);

        $symbols = explode(',', substr($symbolStr, 0, strlen($symbolStr) - 1));
        array_push($symbols, Archiver::EOFChar);

        for ($i = 0, $num = count($numbers); $i < $num; $i++) {
            $probabilityTable[$symbols[$i]] = $numbers[$i];
        }

        return $probabilityTable;
    }
}

function get_float_num($i)
{
    echo '0.' . $i . "\n";
    return floatval('0.' . $i);
}