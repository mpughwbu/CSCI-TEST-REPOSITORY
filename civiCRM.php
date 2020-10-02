
<?php


  function debug_to_console( $data ) {
    if ( is_array( $data ) )
      $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
      $output = "<script>console.log( 'Debug Objects: " . esc_attr($data) . "' );</script>";
    echo $output;
  }

  // Worker method to make a CiviCRM API call
  function civicrm_api( $entity, $action, $jsondata )
  {
    require 'C:/Sites/civi_api_key.php'; // @todo Be sure to update this path. It is different on production and localdev

    $service_url = 'http://staging.mobilization.org/sites/all/modules/civicrm/extern/rest.php';
    $curl = curl_init($service_url);

    $curl_post_data = array(
      "entity" => $entity,
      "action" => $action,
      "json" => $jsondata,
      "key" => '567228f88d1759d1d86ce4b961046583',  // staging
      "api_key" => $civi_api_key
    );
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curl_post_data);
    $curl_response = curl_exec($curl);
    curl_close($curl);

    return $curl_response;
  }

  // Update a contact in CiviCRM. First attempts to get the contact from CiviCRM by email, then uses
  // add_contact_civicrm to either add a new contact or update the existing contact.
  // Email (text)
  // @returns array of key/value pairs or NULL if new contact was created
  function update_contact_civicrm($contact_data, $civicrm_group) {
    $contact_json = get_contact_civicrm( $contact_data['email'], NULL );
    /*echo '<pre>'; print_r($contact_json); echo '</pre>';*/

    if (empty($contact_json)) { // If the email doesn't yet exist

      // Add a new contact with just the email address and add to group
      $contact_id = add_contact_civicrm( NULL, $civicrm_group, $contact_data );
      return $contact_data;

    } else {
      // Update the contact and add to group

      $address_json = get_address_civicrm( $contact_json['contact_id'] );
      $zip = trim($address_json['values'][0]['postal_code']);
      $zip = !empty($zip)?$zip:NULL;

      $civi_contact_data = array(
        'fname' => trim($contact_json['first_name']),
        'lname' => trim($contact_json['last_name']),
        'email' => $email,
        'state' => NULL,
        //'state' => trim($contact_json['state_province_id']),
        'country' => trim($contact_json['country_id']),
        'zip' => $zip,
        'ministry' => trim($contact_json['current_employer']),
        'role' => trim($contact_json['custom_53']),
        'nationality' => trim($contact_json['custom_223']),
        'ministrytype' => trim($contact_json['custom_224']),
        'jobtitle' => trim($contact_json['job_title']),
        //MH 6/30/20 added donor state field we are updating it in CiviContactMgr program
        'donorState' => trim($contact_json['custom_413'])
      );
      $new_contact_data = array_merge($civi_contact_data, $contact_data);

      $contact_id = add_contact_civicrm( $contact_json['contact_id'], $civicrm_group, $new_contact_data );

      /*$existing_subscriber = true;
      $existing_subscriber_message = '<h2 style="text-align:center; font-weight:400; color:#8a8a8a; max-width:560px; margin: 0 auto 1.5em;">Looks like we\'ve met before! Please confirm the following info.</h2>';*/
      return $new_contact_data;
    }
    
  }  

  // Get PRIMARY email for a contact in CiviCRM.
  // contact_id (int)
  // @returns email or NULL if not found
  function get_email_civicrm($contact_id) {

    //debug_to_console( "checking for existing email..." );
    // $jsondata = '{"sequential":1,"return":["email"],"contact_id":'.$contact_id.',"location_type_id":3,"is_primary":1}';
    $jsondata = '{"sequential":1,"return":["email"],"contact_id":'.$contact_id.',"is_primary":1}';
    //debug_to_console( $jsondata );
    $curl_response = civicrm_api( 'Email', 'get', $jsondata );
    //debug_to_console( $curl_response );
    $json = json_decode($curl_response, true);
    //debug_to_console( $json );
    $values = $json['values'];
    $current_email = $values[0]['email'];
    //debug_to_console("Current email: ".$current_email);

  }  

  // Add or update a contact in CiviCRM. To update, provide contact_id.
  // First name (text)
  // Last name (text)
  // Ministry (text)
  // Role (dropdown)
  // Country (dropdown)
  // State (dropdown)
  // @returns $contact_id
  function add_contact_civicrm($contact_id = NULL, $group_id = NULL, $contact_data = array()) {

    $defaults = array(
      'fname' => NULL,
      'lname' => NULL,
      'email' => NULL,
      'state' => NULL,
      'country' => NULL, // id
      'zip' => NULL,
      'ministry' => NULL,
      'role' => NULL,
      'nationality' => NULL, // string
      'ministrytype' => NULL,
      'jobtitle' => NULL,
      //MH 6/30/20 added donor state field we are updating it in CiviContactMgr program
      'donorstate' => NULL
      );
    $config = array_merge($defaults, $contact_data);

    if (!is_null($contact_id)) {
      $current_email = get_email_civicrm($contact_id);
    }

    //debug_to_console( "add_contact_to_civicrm" );
    //debug_to_console( "contact_id:".$contact_id );

    $contactid_str = is_null($contact_id) ? "" : ',"id":"'.$contact_id.'"';

    $fname_str = "";
    if (!is_null($config['fname'])) {
      $fname_str = ',"first_name":"';
      $fname_str .= is_null($config['fname']) ? " " : $config['fname'];
      $fname_str .= '"';
    }

    $lname_str = "";
    if (!is_null($config['lname'])) {    
      $lname_str = ',"last_name":"';
      $lname_str .= is_null($config['lname']) ? " " : $config['lname'];
      $lname_str .= '"';
    }

    $ministry_str = "";
    if (!is_null($config['ministry'])) {
      $ministry_str = ',"current_employer":"'.$config['ministry'].'"';
    }
    $jobtitle_str = "";
    if (!is_null($config['jobtitle'])) {
      $jobtitle_str = ',"job_title":"'.$config['jobtitle'].'"';
    }

    // Custom fields -- see civicrm_custom_field table in CiviCRM database

    $role_str = "";
    if (!is_null($config['role'])) {
      $role_str = ',"custom_53":"'.$config['role'].'"';  // 53: Job Position : Other Demographics : String/Select
    }
    $nationality_str = "";
    if (!is_null($config['nationality'])) {
      $nationality_str = ',"custom_223":"'.$config['nationality'].'"'; // 223: Nationality : Other Demographics : String/Text
    }
    $ministrytype_str = "";
    if (!is_null($config['ministrytype'])) {
      $ministrytype_str = ',"custom_224":"'.$config['ministrytype'].'"'; // 224: Ministry Type : Other Demographics : String/Select
    }
    //MH 6/30/20 added donor state field 
    $donorstate_str = "";
    if (!is_null($config['donorstate'])) {
      $donorstate_str = ',"custom_413":"'.$config['donorstate'].'"'; // 413: Donor State : Other Demographics : String/Select
    }

//		@todo Add State/Province

//		@todo Add Download Activity for:
// Intended Use : CMM Resource Download (Activity)
// Media Format : CMM Resource Download (Activity)
// Print Quantity : CMM Resource Download (Activity)

//		@todo Add Event Attended Activity for: Attended a Weave Big Story Training

    $jsdata = '{"sequential":1'.$contactid_str.',"contact_type":"Individual"';
    $jsdata .= $fname_str;
    $jsdata .= $lname_str;
    $jsdata .= $ministry_str;
    $jsdata .= $role_str;
    $jsdata .= $nationality_str;
    $jsdata .= $ministrytype_str;
    $jsdata .= $jobtitle_str;
    $jsdata .= $donorstate_str;

    // Check if MAIN PRIMARY email is equal to email param (this prevents creating a duplicate email address)
    if (!is_null($config['email']) && $current_email != $config['email']) {
      $email_row = array(
        "sequential" => 1,
        "contact_id" => "\$value.id",
        "location_type_id" => 3,
        "email" => $config['email']
      );
      $emailjson = json_encode($email_row);
      $jsdata .= ',"api.Email.create":'.$emailjson;
    }

//		if (!is_null($config['zip'])) {
//			$addr_row = array(
//				"sequential" => 1,
//				"contact_id" => "\$value.id",
//				"location_type_id" => 3,
//				"postal_code" => $config['zip']
//			);
//			$addrjson = json_encode($addr_row);
//			$jsdata .= ',"api.Address.create":'.$addrjson;
//		}

//		if (!is_null($config['state']) || !is_null($config['country']) || !is_null($config['zip'])) {
//			$addr_row = array(
//				"sequential" => 1,
//				"contact_id" => "\$value.id",
//				"location_type_id" => 3,
//				"is_primary" => 1
//			);
//
//			if (!is_null($config['state']))
//				$addr_row["state_province_id"] = $config['state'];
//			if (!is_null($config['country']))
//				$addr_row["country_id"] = $config['country'];
//			if (!is_null($config['zip']))
//				$addr_row["postal_code"] = $config['zip'];
//
//			$addrjson = json_encode($addr_row);
//			$jsdata .= ',"api.Address.create":'.$addrjson;
//		}

    //"api.GroupContact.create":{"sequential":1,"group_id":42,"contact_id":"$value.id"}
    if (!is_null($group_id)) {
      $group_row = array(
        "sequential" => 1,
        "group_id" => $group_id,
        "contact_id" => "\$value.id"
      );
      $groupjson = json_encode($group_row);
      $jsdata .= ',"api.GroupContact.create":'.$groupjson;
    }

    $jsdata .= "}";

    //debug_to_console( "JSON" );
    //debug_to_console( $jsdata );

    //$jsdata = '{"sequential":1,'.$contactid_str.'"contact_type":"Individual","first_name":"'.$config['fname'].'","last_name":"'.$config['lname'].'","api.Email.create":{"sequential":1,"contact_id":"$value.id","location_type_id":3,"email":"'.$config['email'].'"},"api.Address.create":{"sequential":1,"contact_id":"$value.id","location_type_id":3,"postal_code":"'.$config['zip'].'"},"api.GroupContact.create":{"sequential":1,"group_id":42,"contact_id":"$value.id"}}';

    $curl_response = civicrm_api( 'Contact', 'create', $jsdata );

    //debug_to_console( $curl_response );

    $json = json_decode($curl_response, true);
    //debug_to_console( $json );
    $contact_id = $json['id'];

    // Update (or add) the address
    if (!is_null($config['state']) || !is_null($config['country']) || !is_null($config['zip'])) {
      add_address_civicrm($contact_id, $config['state'], $config['country'], $config['zip']);
    }

    return $contact_id;
  }

  // Add a contact to a group in CiviCRM.
  // Contact ID
  // Group ID
  // @returns 0 if success, else error code
  function add_contact_to_group_civicrm($contact_id, $group_id) {

    //debug_to_console( "add_contact_to_group_civicrm" );
    //debug_to_console( "contact_id:".$contact_id );

    $groupid_str = is_null($group_id) ? "" : ',"group_id":"'.$group_id.'"';
    $contactid_str = is_null($contact_id) ? "" : ',"contact_id":"'.$contact_id.'"';

    $jsondata = '{"sequential":1'.$groupid_str.$contactid_str;

    $jsondata .= '}';

    //debug_to_console( "JSON" );
    //debug_to_console( $jsondata );

    // ?entity=GroupContact&action=create&api_key=userkey&key=sitekey&json={"sequential":1,"group_id":"Weave_Mail_Test_255","contact_id":1}

    $curl_response = civicrm_api( 'GroupContact', 'create', $jsondata );

    //debug_to_console( $curl_response );

    $json = json_decode($curl_response, true);
    //debug_to_console( $json );

    return $json['is_error'];
  }

  // Add or update an address in CiviCRM.
  // First, we search for a MAIN PRIMARY address for the contact, if found it is updated.
  // If not found, a new MAIN PRIMARY address is created.
  // @param $contact_id (int)
  // @param $state (string)
  // @param $country (string)
  // @param $zip (int)
  function add_address_civicrm( $contact_id, $state = NULL, $country = NULL, $zip = NULL ) {
    //debug_to_console( "add_address_civicrm" );

    // Get PRIMARY address for a contact.
    $address_json = get_address_civicrm( $contact_id );
    $address_id = $address_json['id'];

    //debug_to_console( $address_id );

    $addressid_str = "";
    if (!is_null($address_id)) {
      $addressid_str = ',"id":"'.$address_id.'"';
    }
    $country_str = "";
    if (!is_null($country)) {
      $country_str = ',"country_id":"'.$country.'"';
    }
    $state_str = "";
    if (!is_null($state)) {
      $state_str = ',"state_province_id":"'.$state.'"';
    }
    $zip_str = "";
    if (!is_null($zip)) {
      $zip_str = ',"postal_code":"'.$zip.'"';
    }

    $jsondata = '{"sequential":1,"contact_id":"'.$contact_id.'"';
    $jsondata .= $addressid_str;
    $jsondata .= ',"location_type_id":3,"is_primary":1';
    $jsondata .= $country_str;
    $jsondata .= $state_str;
    $jsondata .= $zip_str;
    $jsondata .= '}';

    //debug_to_console( "JSON" );
    //debug_to_console( $jsondata );

    $curl_response = civicrm_api( 'Address', 'create', $jsondata );

    //debug_to_console( $curl_response );
  }

function add_activity_civicrm($with_contact_id = NULL, $activity_data = array(), $addedby_contact_id) {
  /*MH the input with_contact_id was being put into source and target contact id fields, so added parameter addedby_contact_id 
    so that the two ids can be entered separately.*/

  $defaults = array(
    'activity_type_id' => NULL, // text eg., "Weave Big Story Training"
    'status_id' => NULL, // text eg., "Completed"
    'subject' => NULL, // text eg., "From Big Story Series download on weavefamily.org"
    'language' => NULL, // custom_422: Language of the resource that was downloaded eg., "English"
    'intendeduse' => NULL, // custom_358: Intended Use : String/Select
    'mediaformat' => NULL, // custom_359: Media Format : String/Select
    'printqty' => NULL // custom_360: Print Quantity : Int/Text
  );
  $config = array_merge($defaults, $activity_data);

  $activity_type_str = (is_null($config['activity_type_id'])) ? '' : ',"activity_type_id":"'.$config['activity_type_id'].'"';
  $status_str = (is_null($config['status_id'])) ? '' : ',"status_id":"'.$config['status_id'].'"';
  $subject_str = (is_null($config['subject'])) ? '' : ',"subject":"'.$config['subject'].'"';

  $language_str = (is_null($config['language'])) ? '' : ',"custom_422":"'.$config['language'].'"';
  $intendeduse_str = (is_null($config['intendeduse'])) ? '' : ',"custom_358":"'.$config['intendeduse'].'"';
  $mediaformat_str = (is_null($config['mediaformat'])) ? '' : ',"custom_359":"'.$config['mediaformat'].'"';
  $printqty_str = (is_null($config['printqty'])) ? '' : ',"custom_360":"'.$config['printqty'].'"';

  //MH changed the addedby_contact_id parameter to use the $addedby_contact_id
  $jsondata = '{"sequential":1,';
  $jsondata .= '"addedby_contact_id":"';
  $jsondata .= $addedby_contact_id;
  $jsondata .= '"';
  $jsondata .= $activity_type_str;
  $jsondata .= $status_str;
  $jsondata .= $subject_str;
  $jsondata .= $language_str;
  $jsondata .= $intendeduse_str;
  $jsondata .= $mediaformat_str;
  $jsondata .= $printqty_str;
  $jsondata .= ',"target_id":"';
  $jsondata .= $with_contact_id;
  $jsondata .= '"}';

  $curl_response = civicrm_api( 'Activity', 'create', $jsondata );

  $json = json_decode($curl_response, true);

  //debug_to_console( $json );

  $is_error = $json['is_error'];
  return $is_error;

  }

  // Search for a contact by email in CiviCRM, in the specified group and return these fields:
  // @param $email - primary email address to search, if null will return all contacts in the group
  // @param $group_id - group id of the group to restrict the search to, NULL to search all contacts
  // @returns json data with:
  //   First name
  //   Last name
  //   Country (from PRIMARY ADDRESS in CiviCRM)
  //   State (from PRIMARY ADDRESS in CiviCRM)
  //   Current Employer (Ministry Name)
  //   Job Title
  //   Job Position (Role) - custom_53
  //   Nationality - custom_223
  //   Ministry Type - custom_224
  function get_contact_civicrm( $email, $group_id ) {

    //debug_to_console( "get_contact_civicrm" );

    $groupid_str = "";
    if (!is_null($group_id)) {
      $groupid_str = ',"group":"'.$group_id.'"';
    }

    $emailstr = "";
    if(!empty($email))
      $emailstr = ',"email":"'.$email.'"';

    //$jsondata = '{"sequential":1,"contact_type":"Individual","group":'.$group_id.',"return":"id","email":"'.$email.'"}';
    //$jsondata = '{"sequential":1,"contact_type":"Individual","return":["id","first_name","last_name","country","state_province","current_employer","custom_53"],"email":"'.$email.'"}';
    $jsondata = '{"sequential":1,"contact_type":"Individual"'.$groupid_str.',"return":[';
    $jsondata .= '"id",';
    $jsondata .= '"first_name",';
    $jsondata .= '"last_name",';
    $jsondata .= '"email",';
    $jsondata .= '"phone",';
    $jsondata .= '"country",';
    $jsondata .= '"state_province",';
    $jsondata .= '"street_address",';
    $jsondata .= '"city",';
    $jsondata .= '"postal_code",';
    $jsondata .= '"current_employer",';
    $jsondata .= '"job_title",';
    $jsondata .= '"custom_53",';
    $jsondata .= '"custom_223",';
    $jsondata .= '"custom_224",';
    $jsondata .= '"custom_371",';
    $jsondata .= '"custom_389",';
    $jsondata .= '"custom_390",';
    $jsondata .= '"custom_395",';
    $jsondata .= '"custom_403",';
    $jsondata .= '"custom_404",';
    $jsondata .= '"custom_409",';
    $jsondata .= '"custom_413",'; // donor state
    $jsondata .= '"custom_302",'; // bio - Other Demographics
    $jsondata .= '"custom_388",'; // region - Catalytic
    $jsondata .= '"custom_297",'; // resourced with - Catalytic
    $jsondata .= '"custom_298",';  // trained to lead - Catalytic
    $jsondata .= '"custom_422"';  // Last Contact Date
    $jsondata .= ']'.$emailstr.'}';

    $curl_response = civicrm_api( 'Contact', 'get', $jsondata );

    //debug_to_console( $curl_response );

    //echo json_encode($curl_response, JSON_PRETTY_PRINT);

    $json = json_decode($curl_response, true);

    //debug_to_console( $json );

    $count = $json['count'];
    $values = $json['values'];

    // Civi returns LIKE matches for email. We need to ensure EXACT matches.
    if(!empty($email)) {
      foreach ($values as $value) {
        if (strcasecmp(trim($value['email']), trim($email)) == 0) {
          return $value;
        }
      }

//		Useful for debugging
//		$firstname = $values[0]['first_name'];
//		$lastname = $values[0]['last_name'];
//		$country = $values[0]['country'];
//		$stateprovince = $values[0]['state_province'];
//		$ministry = $values[0]['current_employer'];
//		$role = $values[0]['custom_53'];
//		$nationality = $values[0]['custom_223'];
//		$ministrytype = $values[0]['custom_224'];
//		debug_to_console( $count );
//		//debug_to_console( $contact_id );
//		debug_to_console( $firstname );
//		debug_to_console( $lastname );
//		debug_to_console( $country );
//		debug_to_console( $stateprovince );
//		debug_to_console( $ministry );
//		debug_to_console( $role );
//		debug_to_console( $nationality );
//		debug_to_console( $ministrytype );
//		end Useful for debugging

    //get_address_civicrm( $contact_id );


      return null;
      
    } else {
      return $values;
    }

  }

  //MH 7/9/2020 Get Contact's activities. Returns all or just the most recent based on parameter. Returns array of activities
  function get_activity_civicrm($contact_id, $last_activity_only=NULL){

    //setup the jsondata for getting the activities
    //$jsondata = '{"sequential":1,"return":"activity_type_id,subject,activity_date_time"
    //  ,"target_contact_id":'.$contact_id.
    //  ',"options":{"sort":"activity_date_time DESC"},"activity_type_id":"Donor Appeal"}';
    $jsondata = '{"sequential":1,"target_contact_id":'.$contact_id.',"activity_type_id":"Donor Appeal"
      ,"return":["activity_type_id","subject","activity_date_time"],"options":{"sort":"activity_date_time DESC"}}';


    $curl_response = civicrm_api( 'Activity', 'get', $jsondata );

    //debug_to_console( $curl_response );

    //echo json_encode($curl_response, JSON_PRETTY_PRINT);

    $json = json_decode($curl_response, true);

    //debug_to_console( $json );

    $count = $json['count'];
    $values = $json['values'];

    //when only the most recent is requested then 
    if (!is_null($last_activity_only)){
      if($count > 0){
        //just get the first one
        $values = [$values[0]];
      } else {
        //there are none so put the none exist values in the array
        $values = array(0 => array('subject'=>'No activities exist', 'activity_date_time'=>''));
      }
    }

    return $values;
  }

  // Get PRIMARY address for a contact.
  // @returns json
  function get_address_civicrm( $contact_id ) {

//		debug_to_console( "get_address_civicrm" );
//		debug_to_console( $contact_id );

    $jsondata = '{"sequential":1,"contact_id":'.$contact_id.',"return":["id","country_id","state_province_id","postal_code"],"is_primary":1}';

    $curl_response = civicrm_api( 'Address', 'get', $jsondata );

    //debug_to_console( $curl_response );
    //echo json_encode($curl_response, JSON_PRETTY_PRINT);

    $json = json_decode($curl_response, true);

    //debug_to_console( $json );

    $count = $json['count'];
    $address_id = $json['id'];
    $values = $json['values'];

    $country_id = $values[0]['country_id'];
    $stateprovince_id = $values[0]['state_province_id'];
    $postal_code = $values[0]['postal_code'];

    //debug_to_console( $count );
    //debug_to_console( $address_id );
    //debug_to_console( $country_id );
    //debug_to_console( $stateprovince_id );
    //debug_to_console( $postal_code );
    //get_country_name_civicrm( $country_id );

    return $json;
  }

  // Get country name -- THIS METHOD DOES NOT WORK WITH OUR VERSION OF CIVICRM
  function get_country_name_civicrm( $country_id ) {

    //debug_to_console( "get_country_name_civicrm" );
    //debug_to_console( $country_id );

    $jsondata = '{"sequential":1,"contact_id":'.$country_id.',"return":["name"]}';

    $curl_response = civicrm_api( 'Country', 'get', $jsondata );

    //debug_to_console( $curl_response );
    $json = json_decode($curl_response, true);
    //debug_to_console( $json );
  }

  function get_contact_civicrm_ex() {

    //debug_to_console( "get_contact_civicrm" );

    $jsondata = '{"sequential":1,"entity_id":150894}';

    $curl_response = civicrm_api( 'CustomValue', 'get', $jsondata );

    //echo json_encode($curl_response, JSON_PRETTY_PRINT);
    //debug_to_console( $curl_response );
  }

  // Get value and label for Multiple Choice custom field
  // @param $customfield - e.g., custom_297
  function get_customfield_options_civicrm( $customfield ) {

    $jsondata = '{"sequential":1,';
    $jsondata .= '"field":"'.$customfield.'"';
    $jsondata .= '}';

    $curl_response = civicrm_api( 'Contact', 'getoptions', $jsondata );

    //debug_to_console( $curl_response );
    //echo json_encode($curl_response, JSON_PRETTY_PRINT);

    $json = json_decode($curl_response, true);

    //debug_to_console( $json );

    $count = $json['count'];
    $values = $json['values'];

    if ($count > 0) {

      // transform to key-value array
      foreach ($values as $val) {
        $valarr[$val['key']] = $val['value'];
      }  

      return $valarr;
    } else {
      return null;
    }

  }


?>

