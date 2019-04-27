<?php

require_once "ArithmeticCoding.php";

class Archiver
{
    const EOFChar = '^Z';

    function compress($filename)
    {
        $text = $this->getFileText($filename);
        $encoder = new ArithmeticEncoder;
        $encoded = $encoder->encode($text . Archiver::EOFChar);
        echo "Encoded: " . $encoded . "\n";
    }

    function extract($filename){
        $text = $this->getFileText($filename);
        $decoder = new ArithmeticDecoder;
//        $decoder->decode($text, );
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
}