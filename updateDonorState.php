<?php
include 'civiCRM.php';

$donor_state = isset($_POST['donorState']) ? $_POST['donorState'] : '';
$contact_id = isset($_POST['contactId']) ? $_POST['contactId'] : '';

//if (!empty($donor_state) && !empty($contact_id)) { 

if (!is_null($donor_state) && !empty($contact_id)) { 

  // Set Donor State in CiviCRM
  $contact_id = add_contact_civicrm( $contact_id, NULL, array('donorstate' => $donor_state));

  if (!$contact_id) {
    echo "Error";
  } else {
    echo "Success! Donor State saved to CiviCRM.<br>";
    echo "Donor State: " . $donor_state . ' Contact Id: ' . $contact_id;
  }

} else {
  echo "Donor State was blank. Nothing saved.";
}
  

?>
