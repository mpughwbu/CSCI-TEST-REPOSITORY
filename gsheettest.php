<?php
require __DIR__ . '/vendor/autoload.php';

/*
 * We need to get a Google_Client object first to handle auth and api calls, etc.
 */
$client = new Google_Client();
$client->setApplicationName('Team Tracker Report');
$client->setScopes([Google_Service_Sheets::SPREADSHEETS]);
$client->setAccessType('offline');

// /*
//  * The JSON auth file can be provided to the Google Client in two ways, one is as a string which is assumed to be the
//  * path to the json file. This is a nice way to keep the creds out of the environment.
//  *
//  * The second option is as an array. For this example I'll pull the JSON from an environment variable, decode it, and
//  * pass along.
//  */
// // $jsonAuth = getenv('JSON_AUTH');
// // $client->setAuthConfig(json_decode($jsonAuth, true));
$client->setAuthConfig(__DIR__ . '/credentials.json');

// /*
//  * With the Google_Client we can get a Google_Service_Sheets service object to interact with sheets
//  */
$sheets = new Google_Service_Sheets($client);

/*
 * To read data from a sheet we need the spreadsheet ID and the range of data we want to retrieve.
 * Range is defined using A1 notation, see https://developers.google.com/sheets/api/guides/concepts#a1_notation
 */

// E.g., the range of A2:H will get columns A through H and all rows starting from row 2
// $spreadsheetId = getenv('SPREADSHEET_ID');
$spreadsheetId = "1_FVroJqJ1R3wav3ygyMp_dWv68xRfdOoMWEkmup783w"; //It is present in your URL

$range = 'Sue!B21:G35';

$rows = $sheets->spreadsheets_values->get($spreadsheetId, $range);
$values = $rows->getValues();

if (empty($values)) {
    echo "No data found.\n";
} else {
    foreach ($values as $row) {
        // Print columns A and E, which correspond to indices 0 and 4.
        echo "Rank: " . $row[0] . "<br>";
        echo "Name: " . $row[1] . "<br>";
        echo "Email: " . $row[2] . "<br>";
        echo "Pool: " . $row[3] . "<br>";
        echo "Next Meeting Plan: " . $row[4] . "<br>";
        echo "Status: " . $row[5] . "<br><br>";
    }
}

