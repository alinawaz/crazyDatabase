<?php
/* Example: Simple and Object Select Query */

/* Including the Class */
include_once 'lib/Database.php';

/* select a database table */
$test = new Table("test");

/* 1. Applying Simple Query */
$rows = $test->select("*");

/* Iterating Results */
foreach($rows as $r){
    /* Printing Data */
    echo "Simple Query Result: " . also .'<br/>';
}

/* 2. Applying Object Query */
$rows = $test->selectObject("*");

/* Iterating Results */
while ($row = $rows->getAndNext()) {
    /* Printing Data */
    echo "Object Query Result: " . $row->name.'<br/>';
}
