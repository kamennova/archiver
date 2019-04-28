<?php

require_once "Archiver.php";

$outFilename = 'compressed.txt';

$myArchiver = new Archiver;
$myArchiver->compress('test.txt', $outFilename);
$myArchiver->extract($outFilename, 'new.txt');

//$encoder = new ArithmeticEncoder();
//$code = $encoder->encode('abca' . Archiver::EOFChar);

//$myDecoder = new ArithmeticDecoder;
//$decoded = $myDecoder->decode($code, $encoder->probabilityTable);

//'b65ac8a po po o^Z'