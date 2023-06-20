<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);

require __DIR__ . '/config.php';

$request = $_POST;
register_log( print_r( $request, true ) );

$req = file_get_contents("php://input");
register_log( "req: ". $req );


Podio::setup( CLIENT_ID, CLIENT_SECRET);
Podio::authenticate_with_app( ORGANISATION_APP_ID, ORGANISATION_TOKEN);

$hook_type = !empty( $_POST['type'] ) ? $_POST['type'] : "";
$hook_id   = !empty( $_POST['hook_id'] ) ? $_POST['hook_id'] : "";
$code      = !empty( $_POST['code'] ) ? $_POST['code'] : "";

switch ( $hook_type ) {
  	case 'hook.verify':
	    // Validate the webhook
	    PodioHook::validate($hook_id, array('code' => $code));
	    break;
  	/* 
  		case 'item.create':
	    Do something. item_id is available in $_POST['item_id']
	  	case 'item.update':
	    Do something. item_id is available in $_POST['item_id']
	  	case 'item.delete':
	    Do something. item_id is available in $_POST['item_id']
    */
   
  	case 'comment.create':
		sendSMSHandler();

		break; // Switch break
	case 'item.update':
		// moveToFormerClient();
		// moveToActiveClient();

		break; // Switch break
	
	default:
		register_log("Hook type does not exist");
		break;
}




// $hook_id = json_decode( $request );


// PodioHook::verify( $hook_id );
// PodioHook::validate( $hook_id, 1 );
