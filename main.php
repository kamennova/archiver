<?php

require_once "Archiver.php";

$outFilename = 'compressed.txt';

$myArchiver = new Archiver;
$myArchiver->compress('input.txt', $outFilename);
//return;
$myArchiver->extract($outFilename, 'output.txt');