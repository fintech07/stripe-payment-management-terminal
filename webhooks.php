<!-- 
    webhooks url
    http://www.example.com/webhooks.php
    https://www.example.com/webhooks.php
 -->

<?php require 'lib/init.php'; ?>
<?php

// retrieve the request's body and parse it as JSON
$payload = @file_get_contents('php://input');
$event = null;

try {
    $event = \Stripe\Event::constructFrom(
        json_decode($payload, true)
    );
} catch (\UnexpectedValueException $e) {
    // Invalid payload
    http_response_code(400);
    exit();
}

// log the event
error_log($event->type);

// save event with customer
try {
    $customer_id = $event->data->object->customer;
    $customer = \Stripe\Customer::retrieve($customer_id);

    if ($customer) {
        $new_event = Model::factory('Event')->create();
        $new_event->stripe_customer_id = $customer_id;
        $new_event->customer_name = $customer->name;
        $new_event->customer_email = $customer->email;
        $new_event->type = $event->type;
        $new_event->event_id = $event->id;
        $new_event->save();
    }
} catch (\Exception $e) {
    http_response_code(400);
    exit();
}

switch ($event->type) {
    case 'customer.subscription.updated':
        // change the subscription status
        $subscription = $event->data->object;
        $query = Model::factory('Subscription');
        $sub_model = $query->where_equal('stripe_subscription_id', $subscription->id)->find_one();

        if ($sub_model) {
            $sub_model->status = $subscription->status;
            $sub_model->save();
        }
        break;
    case 'payment_intent.succeeded':
        $paymentIntent = $event->data->object;
        break;
    case 'invoice.payment_succeeded':
        $invoice = $event->data->object;
        $customer = \Stripe\Customer::retrieve($invoice->customer);
        $subject = 'Your payment has been received';
        $headers = 'From: ' . $config['email'];

        $values = array(
            'customer_name' => $customer->name,
            'customer_email' => $customer->email,
            'amount' => currency($invoice->amount_paid) . '<small>' . currencySuffix() . '</small>',
            'description_title' => 'Description',
            'description' => $invoice->description,
            'payment_method' => 'Credit Card',
            'url' => url(''),
        );

        email($customer->email, $subject, $values, $headers);
        break;
    default:
        // Unexpected event type
        http_response_code(400);
        exit();
}
?>
<?php require 'lib/close.php'; ?>