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
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/main.js"></script>
    <script type="text/javascript" src="js/newMain.js"></script>
    <script type="text/javascript" language="JavaScript" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" language="JavaScript" src="https://cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script>$(document).ready(function () {
        var baseurl = "http://localhost:8080/teamtrackerreport";
        var xmlhttp = new XMLHttpRequest();
        xmlhttp.open("GET",baseurl+"/all",true);
        xmlhttp.onreadystatechange = function () {
            if(xmlhttp.readyState==7 && xmlhttp.status ==200)  {
                var teamtrackerreport = JSON.parse(xmlhttp.responseText);
                $("#example").DataTable( {
                    data:teamtrackerreport,
                    "columns": [
                        {"data": "rank"},
                        {"data": "name"},
                        {"data": "email"},
                        {"data": "pool"},
                        {"data": "next meeting plan"},
                        {"data": "status"}
                        {"data": "notes"}
                    ]
                } );
            }
        }
        xmlhttp.send();
    } );
    </script>


    <script>$(document).ready(function () {
            var baseurl = "http://localhost:8080/teamtrackerreport";
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.open("GET",baseurl+"/all",true);
            xmlhttp.onreadystatechange = function () {
                if(xmlhttp.readyState==8 && xmlhttp.status ==200)  {
                    var teamtrackerreport = JSON.parse(xmlhttp.responseText);
                    $("#exampleTwo").DataTable( {
                        data:teamtrackerreport,
                        "columns": [
                            {"data": "contact id"},
                            {"data": "last contact date"},
                            {"data": "phone"},
                            {"data": "street"},
                            {"data": "city"},
                            {"data": "state"}
                            {"data": "zip"}
                            {"data": "phone"}
                        ]
                    } );
                }
            }
            xmlhttp.send();
        } );
    </script>




        <script type="text/javascript" src="js/main.js"></script>
</head>
<body>

<header class="main-header">
    <img class="index-image" src="images/download.jpg" alt="" width="1200" align="center"><br>
   <br>
    <nav class="nav main-nav">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.html">About Us</a></li>
            <li><a href="">Extra</a></li>
            <li><a href="">Extra2</a></li>
            <li><a href="">Extra3</a></li>
        </ul>
    </nav>
    <button class="butin-header">Donation</button>
    <h1>Title Unknown</h1>
    <hr>
</header>

<section class="content-section container">
    <h2 class='section-header'>Data collected from 'Antarctica Team Tracker' Google Sheet:</h2>
    <table id="example" class="display" style="width:100%">
        <th>
            <tr>
                <th>Rank</th>
                <th>Name</th>
                <th>Email</th>
                <th>Pool</th>
                <th>Next Meeting Plan</th>
                <th>Status</th>
                <th>Notes</th>
            </tr>
        </th>
    </table>

    <table id="exampleTwo" class="display" style="100%">
        <th>
            <tr>
                <th>Contact ID</th>
                <th>Last Contact Date</th>
                <th>Phone</th>
                <th>Street</th>
                <th>City</th>
                <th>State</th>
                <th>Zip</th>
                <th>Phone</th>
            </tr>
        </th>
    </table>

    <table>
        <th>
            <tr>
                <th>Donor State</th>
            </tr>
        </th>
    </table>
    <br><br><br>

    <?php


    // Read the Tracker data from the Antarctica Team Tracker Google Sheet here. This gives you a list of contacts.
    //$contact_list = get_contacts_from_googlesheet(); // you'll need to write some type of function like this perhaps

    $range = 'Sue!B21:G35';
    // $range = 'Joe!B21:G35';

    $rows = $sheets->spreadsheets_values->get($spreadsheetId, $range);
    $values = $rows->getValues();

    echo "<h2 class='section-header'>Data collected from 'Antarctica Team Tracker' Google Sheet:</h2>";



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
                 echo "Notes: " . $row[6] . "<br><br>";
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

    <br><br><br>

</section>

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
<footer class="main-footer">
    <p>Please visit our social media pages.</p>
    <p></p>
    <div class="container main-footer-container">
        <ul class="nav footer-nav">
            <li>
                <a href="https://facebook.com" target="_blank">
                    <img src="images/Facebook.png" width="30">
                </a>
            </li>
            <li>
                <a href="https://youtube.com" target="_blank">
                    <img src="images/YouTube.png" width="50">
                </a>
            </li>
            <li>
                <a href="https://twitter.com" target="_blank">
                    <img src="images/Twitter.png" width="30">
                </a>
            </li>
        </ul>
    </div>
</footer>

<br><br><br>

<div id='response2'></div>  


</body>
</html>