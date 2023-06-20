<?php 

function sendSMS( $number, $smsBody ) {
	try {
		// Configure HTTP basic authorization: BasicAuth
		$config = ClickSend\Configuration::getDefaultConfiguration()
		    ->setUsername( CLICKSEND_USERNAME )
		    ->setPassword( CLICKSEND_APIKEY );

		$apiInstance = new ClickSend\Api\SMSApi(new GuzzleHttp\Client(), $config);
		$msg = new \ClickSend\Model\SmsMessage();
		$msg->setBody( $smsBody ); 
		$msg->setTo( $number );
		$msg->setSource("Podio");

		// \ClickSend\Model\SmsMessageCollection | SmsMessageCollection model
		$sms_messages = new \ClickSend\Model\SmsMessageCollection(); 
		$sms_messages->setMessages([$msg]);

	
		$result = $apiInstance->smsSendPost($sms_messages);
		register_log($result, true);
		register_log( "SMS Sent" );
	} catch (Exception $e) {
		register_log( 'Exception when calling SMSApi->smsSendPost: '. $e->getMessage() );
	}
}


function getContactNumberFromContacts($contact_app_id, $contact_app_item_id) {
	
	try { 
		$contact = PodioItem::get_by_app_item_id( $contact_app_id, $contact_app_item_id );

		$number = getNumberFromContactObject( $contact );
		return $number;

	} catch( Exception $e) {
		register_log( "Exception Occured ". $e->getMessage() );
	}
}

function getNumberFromContactObject( $contact ) {
	
	try {
		$contactFields = $contact->__attribute("fields")->_get_items();

		if( !empty( $contactFields ) ){
			foreach( $contactFields as $fkey => $field ) {
				
				if( $field->__attribute("field_id") == "218851493" ) { // Phone Number field
					return $contactNumber = $field->__attribute("values")[0]["value"];
				}

			}
		}

		return false;

	} catch( Exception $e) {
		register_log( "Exception Occured ". $e->getMessage() );
	}
}



function getContactObjectFromOrganisation( $object ) {
	try{
		$object_items = $object->fields->_get_items( );
		
		if( !empty( $object_items ) ) {

			foreach( $object_items as $key => $item ) {

				if( $item->__attribute("field_id") == "218851330" ) { // main contact field

					return $item->__attribute("values")[0];
				}
			}
		}
	} catch( Exception $e) {
		register_log( "Exception Occured ". $e->getMessage() );
	}
}

function sendSMSHandler() {
	$comment_id = !empty( $_POST['comment_id'] ) ? $_POST['comment_id'] : "";
	// $comment_id = "1241425709";

	// echo "<p>Checkpoint 1</p>";

	if( empty( $comment_id ) ) {
		register_log( "Comment ID id empty. ");
		exit;
	}

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
}


function register_log($log){
	if( defined('DEBUG') ) {
		echo $log;
	} else {
		$filename = dirname(__FILE__) . '/debug.log';
		file_put_contents($filename, $log, FILE_APPEND);
		file_put_contents($filename, "\n", FILE_APPEND);
	}
}