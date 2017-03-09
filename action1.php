<?php

if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {

	header('Location: /');

	die;

}



require_once dirname(__FILE__) . '/vendor/autoload.php';



function __validate_required($value)

{
	if ($value === '') {
		return 'This field can not be blank';
	}
	return true;
}



function __validate_email($value)

{

	if (preg_match('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $value)) {

		return true;

	}
	return 'This field requires valid email address';

}



$config = array(

	'key' => '2fadd0660de2631f0ae43b60415cd180-us13',

	'listId' => '23ba1d98e1',

);



$fields = array(

	'email' => array(

		'rules' => array(

			'required',

			'email',

		),

	),

);



$response = array(

	'success' => false,

);



if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST') {

	$data = array();

	$errors = array();



	foreach ($fields as $name => $meta) {


		$value = (isset($_POST[$name]) && !is_array($_POST[$name])) ? trim($_POST[$name]) : '';

		$response['email']=$value;

		if (isset($meta['rules'])) {

			foreach ($meta['rules'] as $validator) {

				$method = '__validate_' . $validator;

				if (function_exists($method)) {

					$result = $method($value);

					if ($result !== true) {

						$errors[$name] = $result;

						break;

					}

				}

			}

		}

		if (!isset($errors[$name])) {

			$data[$name] = $value;

		}

	}



	if (empty($errors)) {

		try {

			$mc = new Mailchimp($config['key']);

			$mc->lists->subscribe($config['listId'], array(

				'email' => $data['email'],

			), null, 'html', false);

			$response['success'] = true;

		} catch (Exception $e) {

			//$catch_message=$e->getMessage();
			//$response['message'] = 'Error while subscribing user to the newsletter';
			$response['message'] = $e->getMessage();

		}

	} else {

		$response['errors'] = $errors;

	}
}

die(json_encode($response));

