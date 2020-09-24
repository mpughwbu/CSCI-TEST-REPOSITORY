<!DOCTYPE HTML>
<html>
<head>

  <title>Team Tracker Report</title>
  <?php include 'civiCRM.php';?>
  <link rel="stylesheet" type="text/css" href="css/style.css">

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script> 
  <script type="text/javascript" src="js/main.js"></script>

</head>
<body>

<?php 



// Read the Tracker data from the Antarctica Team Tracker Google Sheet here. This gives you a list of contacts.
//$contact_list = get_contacts_from_googlesheet(); // you'll need to write some type of function like this perhaps

// Lookup each contact in CiviCRM by email
//foreach ($contact_list as $contact) {

  // Sample Code to lookup a contact in CiviCRM by email
  $contact_json = get_contact_civicrm( 'john.patton@mobilization.org', NULL );

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

      echo "<br><br>Donor State: " . $contact_json["custom_413"]; // Donor State -- used for the AJAX example below


  } else {

      echo "Not found.";
  }

//}


?>

<br><br><br>

<!-- 
  Example of how to update a value in CiviCRM using AJAX. This example updates a field called Donor Date using to whatever is selected in the menu.
  You can do something similiar to allow the user to update the editable fields in the table.
 -->
 <h2>Select a Donor State to update this value in CiviCRM for this contact:</h2>
<select data-contact-id="<?php echo $contact_json["contact_id"];?>" class="donorState">
  <option value=""<?php echo $contact_json["custom_413"] == '' ? ' selected=""' : '';?>> - select a value -</option>
  <option value="1"<?php echo $contact_json["custom_413"] == '1' ? ' selected="selected"' : '';?>>Contact for Appointment</option>
  <option value="2"<?php echo $contact_json["custom_413"] == '2' ? ' selected="selected"' : '';?>>Financial-Partner</option>
  <option value="3"<?php echo $contact_json["custom_413"] == '3' ? ' selected="selected"' : '';?>>Decided not to Give</option>
</select>


<br><br><br>

<div id='response2'></div>  


</body>
</html>