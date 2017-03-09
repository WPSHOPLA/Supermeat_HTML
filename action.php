<?php
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) || $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest') {
	header('Location: /');
	die;
}

function __validate_required($value)
{
	if ($value === '') {
		return 'Need to add something';
	}
	return true;
}

function __validate_email($value)
{
	if (preg_match('/^[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+(?:\.[a-zA-Z0-9!#$%&\'*+\\/=?^_`{|}~-]+)*@(?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?$/', $value)) {
		return true;
	}
	return 'Nead to fill a message';
}

$from = array(
	'GearEye',
	'contact@geareye.co',
);

$recipients = array(
	'contact' => 'contact@geareye.co'
);

$subjects = array(
	'contact' => 'New message on geareye.co website',
);

$fields = array(
	'contact' => array(
		'NAME' => array(
			'label' => 'Name',
			'rules' => array(
				'required',
			),
		),
		'EMAIL' => array(
			'label' => 'Email',
			'rules' => array(
				'required',
				'email',
			),
		),
		'MESSAGE' => array(
			'label' => 'Message',
			'rules' => array(
				'required',
			),
		),
	)
);

$response = array(
	'success' => false,
);

if (isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) == 'POST' && isset($_POST['action']) && isset($fields[$_POST['action']])) {
	$data = array();
	$errors = array();

	foreach ($fields[$_POST['action']] as $name => $meta) {
		$value = isset($_POST[$name]) && !is_array($_POST[$name]) ? trim($_POST[$name]) : '';
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
		$message = '<html><body><table>';
		foreach ($fields[$_POST['action']] as $name => $meta) {
			if ($data[$name] === '') {
				continue;
			}
			$value = htmlspecialchars($data[$name], ENT_QUOTES, 'UTF-8');
			if ($field == 'message') {
				$value = nl2br($value);
			}
			$message .= '<tr><th>' . $meta['label'] . '</th><td>' . $value . '</td></tr>';
		}
		$message .= '</table></body></html>';

		$headers = 'From: =?utf-8?B?' . base64_encode($from[0]) . '?= <' . $from[1] . ">\r\n";
		if (!empty($data['email'])) {
			$headers .= 'Reply-To: ' . $data['email'] . "\r\n";
		}
		$headers .= "MIME-Version: 1.0\r\n";
		$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

		if (mail($recipients[$_POST['action']], '=?utf-8?B?' . base64_encode($subjects[$_POST['action']]) . '?=', $message, $headers)) {
			$response = array(
				'success' => true,
			);
		} else {
			$response['message'] = 'Some problem on messege sending';
		}
	} else {
		$response['errors'] = $errors;
	}
}

die(json_encode($response));
