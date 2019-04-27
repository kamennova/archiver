<?php

require_once "ArithmeticCoding.php";

class Archiver
{
    const EOFChar = '^Z';

    function compress($filename)
    {
        $text = $this->getFileText($filename);
        $encoder = new ArithmeticEncoder;
        $encoded = $encoder->encode($text);
        echo "Encoded: " . $encoded . "\n";
    }

    //    ---

    function getFileText($filename)
    {
        $text = '';

        $file = fopen($filename, 'r');
        while (feof($file)) {
            $text .= fgets($file);
            echo $text;
        }


        fclose($file);

        $text .= Archiver::EOFChar;

        return $text;
    }
}