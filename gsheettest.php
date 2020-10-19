<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/gsheetSetup.php';

$range = 'Sue!B21:G35';
// $range = 'Joe!B21:G35';

$rows = $sheets->spreadsheets_values->get($spreadsheetId, $range);
$values = $rows->getValues();

if (empty($values)) {
    echo "No data found.\n";
} else {
    foreach ($values as $row) {
      echo "Rank: " . $row[0] . "<br>";
      echo "Name: " . $row[1] . "<br>";
      echo "Email: " . $row[2] . "<br>";
      echo "Pool: " . $row[3] . "<br>";
      echo "Next Meeting Plan: " . $row[4] . "<br>";
      echo "Status: " . $row[5] . "<br><br>";
    }
}

