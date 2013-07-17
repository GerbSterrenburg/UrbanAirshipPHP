<?php

/* Samples for UrbanAirshipPHP
*
*	Use the force, Read the source!
*/

include('libs/UrbanAirShip.php');

// initialize
$airship = new UrbanAirShip('YOUR_KEY', 'YOUR_SECRET');


// registerDevice an iOS device
//
// arguments: type, token, alias, tags, badge
$airship->registerDevice('ios', '1234567890qwertyuiop', 'alias', array('tag1', 'tag2'), 2);


// registerDevice - an Android device
//
// arguments: type, uuid, alias, tags
$airship->registerDevice('android', 'f47ac10b-58cc-4372-a567-0e02b2c3d479', 'alias', array('tag1', 'tag2'));


// deregisterDevice  an iOS device
//
// arguments: type, token
$airship->deregisterDevice('ios', '1234567890qwertyuiop');


// getDeviceInfo
//
// arguments: type, token
$airship->getDeviceInfo('ios', '1234567890qwertyuiop');


// push
//
// arguments: payload, deviceTokens (ios), apids (android), aliases, tags
$payload = array();

$payload['aps'] = array('alert' => 'ios alert', 'badge' => '+1', 'sound' => 'cat.caf');
$payload['example_other_data_for_ios'] = array('key' => 'value');
$payload['android'] = array('alert' => 'android alert', 'extra' => array('key' => 'value'));

$airship->push($payload, null, null, null, array('tag1'));


// broadcast
//
// arguments: payload, excludeTokens (ios)
$payload = array();

$payload['aps'] = array('alert' => 'ios alert', 'badge' => '+1', 'sound' => 'cat.caf');
$payload['example_other_data_for_ios'] = array('key' => 'value');
$payload['android'] = array('alert' => 'android alert', 'extra' => array('key' => 'value'));

$airship->push($payload, null);

?>