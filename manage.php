<?php require 'lib/init.php'; ?>
<?php
if (!isset($_GET['subscription_id'])) {
	$error = 'Invalid request.';
} else {
	$subscription = Model::factory('Subscription')->where('unique_id', $_GET['subscription_id'])->find_one();
	if (!$subscription) {
		$error = 'Subscription not found.';
	} else {
		if ($subscription->stripe_customer_id && $subscription->stripe_subscription_id) {

			try {
				$customer = Stripe_Customer::retrieve($subscription->stripe_customer_id);
				$subscription_s = $customer->subscriptions->retrieve($subscription->stripe_subscription_id);
			} catch (Exception $e) {
				if ($subscription->status == 'Active') {
					go('process.php?action=cancel_subscription&subscription_id=' . $subscription->id . '&prevent_msg=true');
				}
			}

			$provider = 'stripe';
		} elseif ($subscription->paypal_subscription_id) {
			$provider = 'paypal';
		} else {
			$error = 'Subscription not found.';
		}
	}
}
?>
<!DOCTYPE html>
<html lang="en-US">
<?php template('head'); ?>

<body class="terminal-body">

	<div class="container terminal-wrapper">

		<?php template('message', false); ?>

		<div class="page-header">
			<h2 class="colorprimary">Manage Subscription</h2>
		</div>

		<?php if (!isset($error)) : ?>

		<div class="form-horizontal">

			<div class="form-group">
				<label class="col-md-2 control-label">Customer</label>
				<div class="col-md-10 form-control-static">
					<?php echo $subscription->name; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Price</label>
				<div class="col-md-10 form-control-static">
					<?php echo currency($subscription->price); ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Description</label>
				<div class="col-md-10 form-control-static">
					<?php echo $subscription->description; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Billing Day</label>
				<div class="col-md-10 form-control-static">
					<?php echo $subscription->billing_day . date('S', strtotime('2000-01-' . $subscription->billing_day)); ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Billing Cycle</label>
				<div class="col-md-10 form-control-static">
					Every <?php echo $subscription->interval ?> month(s)
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Billing Length</label>
				<div class="col-md-10 form-control-static">
					<?php if ($subscription->length) : ?>
					Expires after <?php echo $subscription->length; ?> billing cycles
					<?php else : ?>
					No end date
					<?php endif; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Subscription ID</label>
				<div class="col-md-10 form-control-static">
					<?php echo $provider == 'stripe' ? $subscription->stripe_subscription_id : ($provider == 'paypal' ?  $subscription->paypal_subscription_id : ''); ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Status</label>
				<div class="col-md-10 form-control-static <?php echo $subscription->status == 'Active' ? 'colorsuccess' : 'colordanger'; ?>">

					<?php if ($subscription->status == 'Active' && strtotime($subscription->date_trial_ends) >= strtotime(date('Y-m-d'))) : ?>
					Trial Period <small class="colorgray">(Your free trial will end on <?php echo date('m/d/Y', strtotime($subscription->date_trial_ends)); ?>)</small>
					<?php else : ?>
					<?php echo $subscription->status; ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-2 control-label">Date Created</label>
				<div class="col-md-10 form-control-static">
					<?php echo date('F jS, Y', strtotime($subscription->date_created)); ?>
				</div>
			</div>
			<?php if ($subscription->date_canceled) : ?>
			<div class="form-group">
				<label class="col-md-2 control-label">Date Canceled</label>
				<div class="col-md-10 form-control-static">
					<?php echo date('F jS, Y', strtotime($subscription->date_canceled)); ?>
				</div>
			</div>
			<?php endif; ?>
			<?php if ($subscription->status == 'Active') : ?>
			<div class="form-group">
				<div class="col-sm-10 col-sm-offset-2">
					<?php if ($provider == 'stripe') : ?>
					<a href="<?php echo url('process.php?action=cancel_subscription&subscription_id=' . $subscription->id); ?>" class="btn btn-default confirm-click" data-text="Yes, cancel it!">
						<fa class="fa fa-times colordanger"></fa> Cancel Subscription
					</a>
					<?php elseif ($provider == 'paypal' && $subscription->status == 'Active') : ?>
					<div class="alert alert-info">
						<strong><i class="fa fa-info-circle"></i> Want to cancel this subscription?</strong><br>
						PayPal subscriptions and recurring payments must be canceled directly through PayPal.<br>To learn how to cancel your PayPal subscription, please read the following article:<br>
						<a href="https://www.paypal.com/us/webapps/helpcenter/helphub/article/?articleID=FAQ2327" target="_blank">https://www.paypal.com/us/webapps/helpcenter/helphub/article/?articleID=FAQ2327</a>
					</div>
					<?php endif; ?>
				</div>
			</div>
			<?php endif; ?>

		</div>

		<?php else : ?>

		<div class="alert alert-danger">
			<strong><i class="fa fa-exclamation-circle"></i> Oops!</strong><br>
			<?php echo $error; ?>
		</div>

		<?php endif; ?>

</body>

</html>
<?php require 'lib/close.php'; ?>