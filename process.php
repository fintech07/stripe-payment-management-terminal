<?php
require 'lib/init.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

// manually set action for paypal recurring payments return
if (empty($action) && isset($_GET['auth'])) {
	$action = 'paypal_subscription_success';
}

function generatePaymentResponse($intent)
{
	header('Content-Type: application/json');
	if (
		$intent->status == 'requires_action' &&
		$intent->next_action->type == 'use_stripe_sdk'
	) {
		# Tell the client to handle the action
		die(json_encode([
			'requires_action' => true,
			'payment_intent_client_secret' => $intent->client_secret
		]));
	} else if ($intent->status == 'succeeded') {
		# The payment didn’t need any additional actions and completed!
		# Handle post-payment fulfillment
		die(json_encode([
			"success" => true
		]));
	} else {
		# Invalid status
		http_response_code(500);
		die(json_encode(['error' => 'Invalid PaymentIntent status']));
	}
}

if (!empty($action)) {

	// check for csrf token first
	if ($action != 'paypal_ipn') {
		if (!empty($_POST) && (!isset($_POST['csrf']) || empty($_POST['csrf']) || $_POST['csrf'] != $csrf)) {
			msg('Invalid CSRF token, please try submitting again.', 'warning');
			go('index.php');
		}
	}

	switch ($action) {

			/***********************************************************************************************************************/
		case 'sca_payment':
			header('Content-Type: application/json');
			$json_str = file_get_contents('php://input');
			$json_obj = json_decode($json_str);
			$payment_type = $json_obj->payment_type;

			$intent = null;
			try {
				if ($payment_type == 'one_time') {
					if (isset($json_obj->payment_method_id)) {
						$intent = \Stripe\PaymentIntent::create([
							'payment_method' => $json_obj->payment_method_id,
							'amount' => $json_obj->amount * 100,
							'currency' => $json_obj->currency,
							'confirmation_method' => 'manual',
							'confirm' => true,
							'description' => $json_obj->description,
						]);
					}
					if (isset($json_obj->payment_intent_id)) {
						$intent = \Stripe\PaymentIntent::retrieve(
							$json_obj->payment_intent_id
						);
						$intent->confirm();

						$payment = Model::factory('Payment')->create();
						$payment->invoice_id = ($intent->invoice) ? $intent->invoice : null;
						$payment->name = $json_obj->name;
						$payment->email = $json_obj->email;
						$payment->amount = $json_obj->amount;
						$payment->description = $json_obj->description;
						$payment->address = $json_obj->address;
						$payment->city = $json_obj->city;
						$payment->state = $json_obj->state;
						$payment->zip = $json_obj->zip;
						$payment->country = $json_obj->country;
						$payment->type = $intent->payment_method_types;
						$payment->stripe_transaction_id = $intent->id;
						$payment->save();
					}
					generatePaymentResponse($intent);
				}

				if ($payment_type == 'recurring') {
					$name = $json_obj->name;
					$email = $json_obj->email;
					$amount = $json_obj->amount * 100;
					$token = $json_obj->stripeToken;
					$currency = $json_obj->currency;
					$description = $json_obj->description;

					$customer = \Stripe\Customer::create([
						'name' => $name,
						'email' => $email,
						'source' => $token,
					]);

					$plan = \Stripe\Plan::create([
						"amount" => $amount,
						"interval" => 'month',
						"product" => [
							"name" => $currency . ' ' . $amount / 100 . ' (' . $description . ')',
						],
						"currency" => $currency,
						"nickname" => $description,
					]);

					$subscription =  \Stripe\Subscription::create([
						"customer" => $customer->id,
						"items" => [
							[
								"plan" => $plan->id,
							],
						],
						'expand' => ['latest_invoice.payment_intent'],
					]);

					$in_id = $subscription->latest_invoice->id;

					$invoice = \Stripe\Invoice::retrieve($in_id);
					$payment_intent = \Stripe\PaymentIntent::retrieve($invoice->payment_intent);
					if ($payment_intent->status == 'requires_action') {

						$unique_subscription_id = uniqid();
						// save subscription record
						$new_subscription = Model::factory('Subscription')->create();
						$new_subscription->unique_id = $unique_subscription_id;
						$new_subscription->stripe_customer_id = $subscription->customer;
						$new_subscription->stripe_subscription_id = $subscription->id;
						$new_subscription->name = $name;
						$new_subscription->email = $email;
						$new_subscription->address = $json_obj->address;
						$new_subscription->city = $json_obj->city;
						$new_subscription->state = $json_obj->state;
						$new_subscription->zip = $json_obj->zip;
						$new_subscription->country = $json_obj->country;
						$new_subscription->description = $json_obj->description;
						$new_subscription->price = $json_obj->amount;
						$new_subscription->billing_day = date('j', $subscription->start);
						$new_subscription->length = 0;
						$new_subscription->interval = $plan->interval_count;
						$new_subscription->trial_days = $config['enable_trial'] ? $config['trial_days'] : null;
						$new_subscription->status = 'incomplete';
						$new_subscription->date_trial_ends = $config['enable_trial'] ? date('Y-m-d', strtotime('+' . $config['trial_days'] . ' days')) : null;
						$new_subscription->save();

						die(json_encode([
							'status' => $payment_intent->status,
							'pi_secret' =>  $payment_intent->client_secret,
						]));
					}

					if ($invoice->paid) {
						$invoice->pay();
					}

					die(json_encode([
						'status' => 'paid'
					]));
				}
			} catch (\Stripe\Error\Base $e) {
				header('Content-Type: application/json');
				die(json_encode([
					'error' => $e->getMessage()
				]));
			}
		case 'paypal_ipn':

			try {


				// die if it's a refund notification
				if (preg_match('/refund/', post('reason_code'))) {
					die();
				}

				// parse our custom field data
				$custom = post('custom');
				if ($custom) {
					parse_str(post('custom'), $data);
				} else {
					$data = array();
				}
				// pull out some values
				$payment_gross = post('payment_gross');
				$item_name = post('item_name');

				// build customer data
				$name = isset($data['name']) && $data['name'] ? $data['name'] : null;
				$name_arr = explode(' ', trim($name));
				$first_name = $name_arr[0];
				$last_name = trim(str_replace($first_name, '', $name));
				$email = isset($data['email']) && $data['email'] ? $data['email'] : null;
				$description = $item_name ? $item_name : 'no description entered';
				$address = isset($data['address']) && $data['address'] ? $data['address'] : null;
				$city = isset($data['city']) && $data['city'] ? $data['city'] : null;
				$state = isset($data['state']) && $data['state'] ? $data['state'] : null;
				$zip = isset($data['zip']) && $data['zip'] ? $data['zip'] : null;
				$country = isset($data['country']) && $data['country'] ? $data['country'] : null;

				// check for invoice first
				if (isset($data['invoice_id']) && $data['invoice_id']) {
					$invoice = Model::factory('Invoice')->find_one($data['invoice_id']);
					$amount = $invoice->amount;
					$type = 'invoice';
					$description = $invoice->description;
					// now check for item
				} elseif (isset($data['item_id']) && $data['item_id']) {
					$item = Model::factory('Item')->find_one($data['item_id']);
					$amount = $item->price;
					$type = 'item';
					// check for input amount
				} elseif ($payment_gross) {
					$amount = $payment_gross;
					$type = 'input';
					// return error if none found
				} else {
					$amount = 0;
					$type = '';
				}

				switch (post('txn_type')) {
					case 'web_accept':

						// save payment record
						$payment = Model::factory('Payment')->create();
						$payment->invoice_id = isset($invoice) ? $invoice->id : null;
						$payment->name = $name;
						$payment->email = $email;
						$payment->amount = $amount;
						$payment->description = isset($item) ? $item->name : $description;
						$payment->address = $address;
						$payment->city = $city;
						$payment->state = $state;
						$payment->zip = $zip;
						$payment->country = $country;
						$payment->type = $type;
						$payment->paypal_transaction_id = post('txn_id');
						$payment->save();

						// update paid invoice
						if (isset($invoice)) {
							$invoice->status = 'Paid';
							$invoice->date_paid = date('Y-m-d H:i:s');
							$invoice->save();
						}

						// build email values first
						$values = array(
							'customer_name' => $payment->name,
							'customer_email' => $payment->email,
							'amount' => currency($payment->amount) . '<small>' . currencySuffix() . '</small>',
							'description_title' => isset($item) ? 'Item' : 'Description',
							'description' => $payment->description,
							'transaction_id' => post('txn_id'),
							'payment_method' => 'PayPal',
							'url' => url(''),
						);
						email($config['email'], 'payment-confirmation-admin', $values, 'You\'ve received a new payment!');
						email($payment->email, 'payment-confirmation-customer', $values, 'Thank you for your payment to ' . $config['name']);

						break;

					case 'subscr_signup':

						try {

							$unique_subscription_id = uniqid();
							// save subscription record
							$subscription = Model::factory('Subscription')->create();
							$subscription->unique_id = $unique_subscription_id;
							$subscription->paypal_subscription_id = post('subscr_id');
							$subscription->name = $name;
							$subscription->email = $email;
							$subscription->address = $address;
							$subscription->city = $city;
							$subscription->state = $state;
							$subscription->zip = $zip;
							$subscription->country = $country;
							$subscription->description = isset($item) ? $item->name : $description;
							$subscription->price = post('amount3');
							$subscription->billing_day = date('j', strtotime(post('subscr_date')));
							$subscription->length = $config['subscription_length'];
							$subscription->interval = $config['subscription_interval'];
							$subscription->trial_days = $config['enable_trial'] ? $config['trial_days'] : null;
							$subscription->status = 'active';
							$subscription->date_trial_ends = $config['enable_trial'] ? date('Y-m-d', strtotime('+' . $config['trial_days'] . ' days')) : null;
							$subscription->save();

							$trial = $subscription->date_trial_ends ? ' <span style="color:#999999;font-size:16px">(Billing starts after your ' . $config['trial_days'] . ' day free trial ends)</span>' : '';
							$values = array(
								'customer_name' => $name,
								'customer_email' => $email,
								'amount' => currency(post('amount3')) . '<small>' . currencySuffix() . '</small>' . $trial,
								'description_title' => isset($item) ? 'Item' : 'Description',
								'description' => isset($item) ? $item->name : $description,
								'payment_method' => 'PayPal',
								'subscription_id' => post('subscr_id'),
								'manage_url' => url('manage.php?subscription_id=' . $unique_subscription_id)
							);
							email($config['email'], 'subscription-confirmation-admin', $values, 'You\'ve received a new recurring payment!');
							email($email, 'subscription-confirmation-customer', $values, 'Thank you for your recurring payment to ' . $config['name']);
						} catch (Exception $e) { }

						break;

					case 'subscr_cancel':
						$subscription = Model::factory('Subscription')->where('paypal_subscription_id', post('subscr_id'))->find_one();
						if ($subscription) {
							$subscription->status = 'Canceled';
							$subscription->date_canceled = date('Y-m-d H:i:s');
							$subscription->save();
							// send subscription cancelation email now
							$values = array(
								'customer_name' => $subscription->name,
								'customer_email' => $subscription->email,
								'amount' => currency($subscription->price) . '<small>' . currencySuffix() . '</small>',
								'description' => $subscription->description,
								'payment_method' => 'PayPal',
								'subscription_id' => $subscription->paypal_subscription_id
							);
							email($config['email'], 'subscription-canceled-admin', $values, 'A recurring payment has been canceled.');
							email($subscription->email, 'subscription-canceled-customer', $values, 'Your recurring payment to ' . $config['name'] . ' has been canceled.');
						}
						break;

					case 'subscr_eot':
						$subscription = Model::factory('Subscription')->where('paypal_subscription_id', post('subscr_id'))->find_one();
						if ($subscription && $subscription->status == 'Active') {
							$subscription->status = 'Expired';
							$subscription->date_canceled = null;
							$subscription->save();
						}
						break;
				}
			} catch (Exception $e) {
				die();
			}

			break;

			/***********************************************************************************************************************/

		case 'paypal_success':
			go('index.php#status=paypal_success');
			break;

			/***********************************************************************************************************************/

		case 'paypal_subscription_success':
			go('index.php#status=paypal_subscription_success');
			break;

			/***********************************************************************************************************************/

		case 'paypal_cancel':
			msg('You canceled your PayPal payment, no payment has been made.', 'warning');
			go('index.php');
			break;

			/***********************************************************************************************************************/

		case 'delete_payment':
			if (isset($_GET['id'])) {
				$payment = Model::factory('Payment')->find_one($_GET['id']);
				$payment->delete();
			}
			msg('Payment has been deleted successfully.', 'success');
			go('admin.php#tab=payments');
			break;

			/***********************************************************************************************************************/

		case 'delete_subscription':
			if (isset($_GET['id'])) {
				$subscription = Model::factory('Subscription')->find_one($_GET['id']);
				$subscription->delete();
			}
			msg('Subscription has been deleted successfully.', 'success');
			go('admin.php#tab=subscriptions');
			break;

			/***********************************************************************************************************************/

		case 'cancel_subscription':
			if (isset($_GET['subscription_id'])) {
				$subscription = Model::factory('Subscription')->find_one($_GET['subscription_id']);
				$subscription->status = 'Canceled';
				$subscription->date_canceled = date('Y-m-d H:i:s');
				$subscription->save();
				try {
					if ($subscription->stripe_customer_id && $subscription->stripe_subscription_id) {


						$customer = \Stripe\Subscription::retrieve($subscription->stripe_customer_id);
						$subscription_s = $customer->subscriptions->retrieve($subscription->stripe_subscription_id);
						$subscription_s->cancel();

						// send subscription cancelation email now
						$values = array(
							'customer_name' => $subscription->name,
							'customer_email' => $subscription->email,
							'amount' => currency($subscription->price) . '<small>' . currencySuffix() . '</small>',
							'description' => $subscription->description,
							'payment_method' => 'Credit Card',
							'subscription_id' => $subscription->stripe_subscription_id
						);
						email($config['email'], 'subscription-canceled-admin', $values, 'A recurring payment has been canceled.');
						email($subscription->email, 'subscription-canceled-customer', $values, 'Your recurring payment to ' . $config['name'] . ' has been canceled.');
					}
				} catch (\Stripe\Error\Card $e) { } catch (\Stripe\Error\InvalidRequest $e) { } catch (\Stripe\Error\Authentication $e) { } catch (\Stripe\Error\Api $e) { } catch (\Stripe\Error $e) { } catch (Exception $e) {
					$error = $e->getMessage();
				}
			}
			if (!isset($_GET['prevent_msg'])) {
				if (isset($error)) {
					msg($error, 'danger');
				} else {
					msg('Your subscription has been canceled successfully.', 'success');
				}
			}
			if (get('return') == 'admin') {
				go('admin.php#tab=subscriptions');
			} else {
				go('manage.php?subscription_id=' . $subscription->unique_id);
			}
			break;

			/***********************************************************************************************************************/

		case 'create_invoice':
			if (post('email') && post('amount') && post('description')) {
				$unique_invoice_id = uniqid();
				$invoice = Model::factory('Invoice')->create();
				$invoice->unique_id = $unique_invoice_id;
				$invoice->email = post('email');
				$invoice->description = post('description');
				$invoice->amount = post('amount');
				$invoice->number = post('number');
				$invoice->status = 'Unpaid';
				$invoice->date_due = post('date_due') ? date('Y-m-d', strtotime(post('date_due'))) : null;
				$invoice->save();
			}
			$number = $invoice->number ? $invoice->number : $invoice->id();
			if (post('send_email') && post('send_email')) {
				$values = array(
					'number' => $number,
					'amount' => currency($invoice->amount) . '<small>' . currencySuffix() . '</small>',
					'description' => $invoice->description,
					'date_due' => !is_null($invoice->date_due) ? date('F jS, Y', strtotime($invoice->date_due)) : '<em>no due date set</em>',
					'url' => url('?invoice_id=' . $unique_invoice_id)
				);
				email($invoice->email, 'invoice', $values, 'Invoice from ' . $config['name']);
				$msg = ' and sent';
			}
			msg('Invoice has been created' . (isset($msg) ? $msg : '') . ' successfully.', 'success');
			go('admin.php#tab=invoices');
			break;

			/***********************************************************************************************************************/

		case 'delete_invoice':
			if (isset($_GET['id'])) {
				$invoice = Model::factory('Invoice')->find_one($_GET['id']);
				$invoice->delete();
			}
			msg('Invoice has been deleted successfully.', 'success');
			go('admin.php#tab=invoices');
			break;

			/***********************************************************************************************************************/

		case 'add_item':
			if (post('name') && post('price')) {
				$item = Model::factory('Item')->create();
				$item->name = post('name');
				$item->price = post('price');
				$item->save();
			}
			msg('Item has been added successfully.', 'success');
			go('admin.php#tab=items');
			break;

			/***********************************************************************************************************************/

		case 'edit_item':
			if (post('id') && post('name') && post('price')) {
				$item = Model::factory('Item')->find_one(post('id'));
				$item->name = post('name');
				$item->price = post('price');
				$item->save();
			}
			msg('Item has been edited successfully.', 'success');
			go('admin.php#tab=items');
			break;

			/***********************************************************************************************************************/

		case 'delete_item':
			if (isset($_GET['id'])) {
				$item = Model::factory('Item')->find_one($_GET['id']);
				$item->delete();
			}
			msg('Item has been deleted successfully.', 'success');
			go('admin.php#tab=items');
			break;

			/***********************************************************************************************************************/

		case 'delete_event':
			if (isset($_GET['id'])) {
				$item = Model::factory('Event')->find_one($_GET['id']);
				$item->delete();
			}
			msg('Event has been deleted successfully.', 'success');
			go('admin.php#tab=events');
			break;

			/***********************************************************************************************************************/

		case 'save_config':

			// prevent login from anything other than the admin page
			if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
				if (!preg_match('/\/admin\.php$/', $_SERVER['HTTP_REFERER'])) {
					msg('Invalid login attempt, please try again.', 'warning');
					go('index.php');
				}
			}

			if (post('config') && is_array(post('config'))) {
				foreach (post('config') as $key => $value) {
					$config = Model::factory('Config')->where('key', $key)->find_one();
					if ($config) {
						$config->value = $value;
						$config->save();
					}
				}
			}
			msg('Your settings have been saved successfully.', 'success');
			go('admin.php#tab=settings');
			break;

			/***********************************************************************************************************************/

		case 'disable_notification':
			$config = Model::factory('Config')->where('key', 'notification_status')->find_one();
			$config->value = 'disabled';
			$config->save();
			break;

			/***********************************************************************************************************************/

		case 'login':

			// prevent login from anything other than the admin page
			if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
				if (!preg_match('/\/admin\.php$/', $_SERVER['HTTP_REFERER'])) {
					msg('Invalid login attempt, please try again.', 'warning');
					go('index.php');
				}
			}

			if (
				post('admin_username') && post('admin_username') == $config['admin_username'] &&
				post('admin_password') && post('admin_password') == $config['admin_password']
			) {
				// login successful, set session
				$_SESSION['admin_username'] = $config['admin_username'];
			} else {
				// login failed, set error message
				msg('Login attempt failed, please try again.', 'danger');
			}
			go('admin.php');
			break;

			/***********************************************************************************************************************/

		case 'logout':
			unset($_SESSION['admin_username']);
			session_destroy();
			session_start();
			msg('You have been logged out successfully.', 'success');
			go('admin.php');
			break;

			/***********************************************************************************************************************/

		case 'install':
			$status = true;
			$message = '';
			try {
				$db = new PDO('mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'], $config['db_username'], $config['db_password']);
				$sql = file_get_contents('lib/sql/install.sql');
				$result = $db->exec($sql);
			} catch (PDOException $e) {
				$status = false;
				$message = $e->getMessage();
			}
			$response = array(
				'status' => $status,
				'message' => $message
			);
			header('Content-Type: application/json');
			die(json_encode($response));
			break;

			/***********************************************************************************************************************/
	}
}
