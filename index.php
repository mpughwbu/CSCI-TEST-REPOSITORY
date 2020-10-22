<!DOCTYPE HTML>
<html>
<head>

  <title>Team Tracker Report</title>
  <?php
  include 'civiCRM.php';
  require __DIR__ . '/vendor/autoload.php';
  require __DIR__ . '/gsheetSetup.php';
  ?>
  <link rel="stylesheet" type="text/css" href="css/style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>

        <script type="text/javascript" src="js/main.js"></script>
</head>
<body>

<header class="main-header">
    <img src="images/Climbing-AdobeStock_285907187.jpg" alt="" width="600" align="center"><br>
   <br>
    <nav>
        <ul><li><a href="index.php">Home</a></li>
            <li><a href="about.html">About Us</a></li>
            <li><a href="">Extra</a></li>
            <li><a href="">Extra2</a></li>
            <li><a href="">Extra3</a></li>
        </ul>
    </nav>
    <hr>
</header>
<br><br><br>



<?php


// Read the Tracker data from the Antarctica Team Tracker Google Sheet here. This gives you a list of contacts.
//$contact_list = get_contacts_from_googlesheet(); // you'll need to write some type of function like this perhaps

$range = 'Sue!B21:G35';
// $range = 'Joe!B21:G35';

$rows = $sheets->spreadsheets_values->get($spreadsheetId, $range);
$values = $rows->getValues();

echo "<h2>Data collected from 'Antarctica Team Tracker' Google Sheet:</h2>";



if (empty($values)) {
    echo "No data found.\n";
} else {
    foreach ($values as $row) {
   /*     echo "Rank: " . $row[0] . "<br>";
        echo "Name: " . $row[1] . "<br>";
        echo "Email: " . $row[2] . "<br>";
        echo "Pool: " . $row[3] . "<br>";
        echo "Next Meeting Plan: " . $row[4] . "<br>";
        echo "Status: " . $row[5] . "<br><br>";
*/

        echo "<table border='1'>";
       // echo "<tr>";
        echo "<th >Rank</th>";
        echo "<th>Name</th>";
        echo "<th>Email</th>";
        echo "<th>Pool</th>";
        echo "<th>Next Meeting Plan</th>";
        echo "<th>Status</th>";
       // echo "</tr>";


        echo "<tr>";
        echo "<td>$row[0]</td>"."<br>";
        echo "<td>$row[1]</td>";
        echo "<td>$row[2]</td>";
        echo "<td>$row[3]</td>";
        echo "<td>$row[4]</td>";
        echo "<td>$row[5]</td>";
        echo "</tr>";
        echo "</table>";
    }
}





// Lookup each contact in CiviCRM by email
//foreach ($contact_list as $contact) {

  // Sample Code to lookup a contact in CiviCRM by email
  $contact_json = get_contact_civicrm( 'Lisandra.Meller@foomail.com', NULL );

  debug_to_console( $contact_json );

  if (!empty($contact_json)) { // If the email exists

    echo "<h2>Sample Contact Returned from CiviCRM:</h2>";
    echo "<br>Contact ID: " . $contact_json["contact_id"];

    echo "<br>Last Contact Date: " . $contact_json["custom_422"]; // Last Contact Date
    echo "<br>Phone: " . $contact_json["phone"];
    echo "<br>Street: " . $contact_json["street_address"];
    echo "<br>City: " . $contact_json["city"];
    echo "<br>State: " . $contact_json["state_province_name"];
    echo "<br>Zip: " . $contact_json["postal_code"];
    echo "<br>Phone: " . $contact_json["phone"];

    echo "<p>Donor State: <span id='donor_state'>" . $contact_json["custom_413"] . "</span></p>"; // Donor State -- used for the AJAX example below


  } else {

    echo "Not found.";
  }

//}


?>



</table>

<br><br><br>
<hr>



<!-- 
  Example of how to update a value in CiviCRM using AJAX. This example updates a field called Donor Date using what is selected in the menu.
  You can do something similiar to allow the user to update the editable fields in the table.
 -->
 <h2>Select a Donor State to update this value in CiviCRM for this contact:</h2>
<select data-contact-id="<?php echo $contact_json["contact_id"];?>" class="donorState">
  <option value=""<?php echo $contact_json["custom_413"] == '' ? ' selected=""' : '';?>> - select a value -</option>
  <option value="1"<?php echo $contact_json["custom_413"] == '1' ? ' selected="selected"' : '';?>>Contact for Appointment</option>
  <option value="2"<?php echo $contact_json["custom_413"] == '2' ? ' selected="selected"' : '';?>>Financial-Partner</option>
  <option value="3"<?php echo $contact_json["custom_413"] == '3' ? ' selected="selected"' : '';?>>Decided not to Give</option>


</select>
<hr>
<footer>
    <p>Please visit our social media pages.</p>
    <p></p>

    <ul>
        <span><li><a href="https://facebook.com" target="_blank"><img src="images/Facebook.png" width="30"></a></li></span>
        <span><li><a href="https://youtube.com" target="_blank"><img src="images/YouTube.png" width="50"></a></li> </span>
        <span><li><a href="https://twitter.com" target="_blank"><img src="images/Twitter.png" width="30"></a></li> </span>
    </ul>
</footer>

<br><br><br>

<div id='response2'></div>  


</body>
</html>