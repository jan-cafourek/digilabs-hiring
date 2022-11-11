<?php

require_once(__DIR__.'/trivette.php');

$task       = filter_input(INPUT_POST, 'ch_task');
$processor  = digiChuck::getInstance();

if($task === null) {
    $processor::showError('Tento task neumím zpracovat');
} else {
    $processor::processTask($task);
}