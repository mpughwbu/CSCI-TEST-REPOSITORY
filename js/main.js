$(document).ready(function() {

// Save Donor State when value changed in Select menu
$(".donorState").change(function() { /* WHEN YOU CHANGE AND SELECT FROM THE SELECT FIELD */
  // show that something is loading
  $('#response2').html("<b>Saving donor state...</b>");

  var donorState = $(this).val(); /* GET THE VALUE OF THE SELECTED DATA */
  var contactId = $(this).data( "contactId" );
  // var contactId = $(this).dataset.contactId;
  // var dataString = "donorState=" + donorState + "contactId=" + contactId; /* STORE THAT TO A DATA STRING */

  $.ajax({ /* THEN THE AJAX CALL */
    type: "POST", /* TYPE OF METHOD TO USE TO PASS THE DATA */
    url: "updateDonorState.php", /* PAGE WHERE WE WILL PASS THE DATA */
    data: { donorState: donorState, contactId: contactId }
  })    
  .done(function(response) { // if getting done then call.

    if (response == 'Error')
      alert( "Error. Failed to save Donor State to CiviCRM." );
    
    var resultData = JSON.parse(response);
    //$('#tableInfo').html(arrayToTable(resultData));

    // show the response on page
    alert( resultData.message );
    $('#response2').html(resultData.message);
    $('#donor_state').html(resultData.donorState);
  

  })
  .fail(function() { // if fail then getting message

    // just in case posting your form failed
    alert( "AJAX post failed." );

  });
  
  // to prevent refreshing the whole page page
  return false;

});

});

