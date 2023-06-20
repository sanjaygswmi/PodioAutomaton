<?php 


error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/config.php';

define('DEBUG', true);

$hook_id = 21475421;

Podio::setup( CLIENT_ID, CLIENT_SECRET);
Podio::authenticate_with_app( ORGANISATION_APP_ID, ORGANISATION_TOKEN);


// $comment_id = '1239495547'; // Organisation comment ID
$comment_id = '1241425709'; // Contact comment ID

$comment = PodioComment::get( $comment_id );


try {
	$smsBody = $comment->__attribute("value");

	if( stripos($smsBody, 'sms:') !== 0 ){
		echo 'Not an SMS Content';
		exit;
	} else {
		$smsBody = substr( $smsBody, 4);
		$smsBody = trim( $smsBody );
	}

	$object_id = $comment->ref->id;

	$object = PodioItem::get( $object_id );

	$app      = $object->__attribute("app");
	$app_name = $app->__attribute("name");
	$app_id   = $app->__attribute("app_id");

	if( $app_name == 'Contacts') {
		$contactNumber = getNumberFromContactObject( $object );

		sendSMS($contactNumber, $smsBody);

	} else if( $app_name == 'Organization' ) {

		$contactOject        = getContactObjectFromOrganisation( $object ); 

		$contact_app_id      = $contactOject["value"]["app"]["app_id"];
		$contact_app_item_id = $contactOject["value"]["app_item_id"];

		$contactNumber       = getContactNumberFromContacts($contact_app_id, $contact_app_item_id);

		sendSMS($contactNumber, $smsBody);
	
	}
} catch( Exception $e) {
	echo "Exception occured! ". $e->getMessage();
}
