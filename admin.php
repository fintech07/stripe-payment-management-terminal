<?php require 'lib/init.php'; ?>
<!DOCTYPE html>
<html lang="en-US">
<?php template('head'); ?>

<body>

	<?php if (isset($_SESSION['admin_username']) && $_SESSION['admin_username'] == $config['admin_username']) : ?>

	<header class="admin-header">
		<div class="container">
			<div class="row">
				<div class="col-sm-8">
					<h4 class="fontnormal">Administration <small><?php echo $config['page_title']; ?></small></h4>
				</div>
				<div class="col-sm-4 alignright mt10">
					Welcome, <?php echo $config['admin_username']; ?> <span class="colorgray mh5">|</span>
					<a href="<?php echo url('process.php?action=logout'); ?>"><i class="fa fa-power-off colordanger"></i> Logout</a>
				</div>
			</div>
		</div>
	</header>

	<header class="notification-header">
		<div class="alert notification-alert">
			<div class="container">
				<div class="floatright"><a href="#" class="disable-notification colorprimary font12"><i class="fa fa-times"></i> Don't Show Again</a></div>
				<div class="notification-message"></div>
			</div>
		</div>
	</header>

	<?php template('message'); ?>

	<div class="container">


		<div role="tabpanel">

			<!-- Nav tabs -->
			<ul class="nav nav-tabs hash-tabs mb30" role="tablist">
				<li role="presentation"><a href="#payments" aria-controls="payments" role="tab" data-toggle="tab">Payments</a></li>
				<li role="presentation"><a href="#subscriptions" aria-controls="subscriptions" role="tab" data-toggle="tab">Subscriptions</a></li>
				<li role="presentation"><a href="#invoices" aria-controls="invoices" role="tab" data-toggle="tab">Invoices</a></li>
				<li role="presentation"><a href="#items" aria-controls="items" role="tab" data-toggle="tab">Items</a></li>
				<li role="presentation"><a href="#events" aria-controls="events" role="tab" data-toggle="tab">Events</a></li>
				<li role="presentation"><a href="#settings" aria-controls="settings" role="tab" data-toggle="tab">Settings</a></li>
			</ul>
			<!-- Tab panes -->
			<div class="tab-content">


				<div role="tabpanel" class="tab-pane" id="payments">


					<div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<input type="text" class="form-control filter" placeholder="Search...">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</div>
					</div>


					<table class="payments-table table table-striped table-hover mt10">
						<thead>
							<tr>
								<th>Customer</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Address</th>
								<th>Source</th>
								<th>Transaction ID</th>
								<th>Date Created</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php $payments = Model::factory('Payment')->orderByDesc('date_created')->findMany(); ?>
							<?php foreach ($payments as $payment) : ?>
							<tr>
								<td>
									<?php echo $payment->name; ?><br><small class="colorgray"><?php echo $payment->email; ?></small>
								</td>
								<td>
									<?php echo currency($payment->amount); ?>
								</td>
								<td>
									<span class="displaynone"><?php echo $payment->description; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $payment->description; ?>"><i class="fa fa-file-text-o colorprimary"></i></a>
								</td>
								<td>
									<?php if ($payment->address) : ?>
									<?php $address = $payment->address . '<br>' . $payment->city . ', ' . $payment->state . ' ' . $payment->zip; ?>
									<span class="displaynone"><?php echo $address; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $address; ?>"><i class="fa fa-map-marker colorprimary"></i></a>
									<?php endif; ?>
								</td>
								<td>
									<?php echo $payment->stripe_transaction_id ? 'Stripe' : ($payment->paypal_transaction_id ? 'PayPal' : ''); ?>
								</td>
								<td>
									<?php echo $payment->stripe_transaction_id ? $payment->stripe_transaction_id : ($payment->paypal_transaction_id ? $payment->paypal_transaction_id : ''); ?>
								</td>
								<td>
									<?php echo date('m/d/Y', strtotime($payment->date_created)); ?>
								</td>
								<td class="alignright">
									<a href="<?php echo url('process.php?action=delete_payment&id=' . $payment->id); ?>" class="btn btn-sm btn-default confirm-click"><i class="fa fa-trash-o colordanger"></i> Delete</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot class="<?php echo empty($payments) ? '' : 'displaynone'; ?>">
							<tr>
								<td colspan="8" class="aligncenter">
									<em>no payments found</em>
								</td>
							</tr>
						</tfoot>
					</table>


				</div>


				<div role="tabpanel" class="tab-pane" id="subscriptions">


					<div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<input type="text" class="form-control filter" placeholder="Search...">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</div>
					</div>
					<table class="subscriptions-table table table-striped table-hover mt10">
						<thead>
							<tr>
								<th>Customer</th>
								<th>Amount</th>
								<th>Details</th>
								<th>Description</th>
								<th>Address</th>
								<th>Source</th>
								<th>Status</th>
								<th>Date Created</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php $subscriptions = Model::factory('Subscription')->orderByDesc('date_created')->findMany(); ?>
							<?php foreach ($subscriptions as $subscription) : ?>
							<tr>
								<td><?php echo $subscription->name; ?><br><small class="colorgray"><?php echo $subscription->email; ?></small></td>
								<td><?php echo currency($subscription->price); ?></td>
								<td>
									<?php
											$details = 'Billing Day: ' . $subscription->billing_day . date('S', strtotime('2000-01-' . $subscription->billing_day)) . '<br>';
											$details .= 'Length: ' . ($subscription->length > 0 ? $subscription->length . ' billing cycles' : 'No end date') . '<br>';
											$details .= 'Interval: ' . $subscription->interval . ' month(s)<br>';
											$details .= 'Subscription ID: ' . ($subscription->stripe_subscription_id ? $subscription->stripe_subscription_id : ($subscription->paypal_subscription_id ? $subscription->paypal_subscription_id : ''));
											if ($subscription->trial_days) {
												$details .= '<br>Free Trial: ' . $subscription->trial_days . ' days';
											}
											?>
									<span class="displaynone"><?php echo $details; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $details; ?>"><i class="fa fa-list-ul colorprimary"></i></a>
								</td>
								<td>
									<span class="displaynone"><?php echo $subscription->description; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $subscription->description; ?>"><i class="fa fa-file-text-o colorprimary"></i></a>
								</td>
								<td>
									<?php if ($subscription->address) : ?>
									<?php $address = $subscription->address . '<br>' . $subscription->city . ', ' . $subscription->state . ' ' . $subscription->zip; ?>
									<span class="displaynone"><?php echo $address; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $address; ?>"><i class="fa fa-map-marker colorprimary"></i></a>
									<?php endif; ?>
								</td>
								<td><?php echo $subscription->stripe_subscription_id ? 'Stripe' : ($subscription->paypal_subscription_id ? 'PayPal' : ''); ?></td>
								<td class="<?php echo $subscription->status == 'active' ? 'colorsuccess' : 'colorwarning'; ?>">

									<?php if ($subscription->status == 'Active' && strtotime($subscription->date_trial_ends) >= strtotime(date('Y-m-d'))) : ?>
									Trial <a href="#" class="colorgray" data-toggle="tooltip" data-title="Trial ends on <?php echo date('m/d/Y', strtotime($subscription->date_trial_ends)); ?>"><sup><i class="fa fa-calendar"></i></sup></a>
									<?php else : ?>
									<?php echo $subscription->status; ?>
									<?php endif; ?>

									<?php if ($subscription->date_canceled) : ?>
									<a href="#" class="colorgray" data-toggle="tooltip" data-title="Canceled on <?php echo date('m/d/Y', strtotime($subscription->date_canceled)); ?>"><sup><i class="fa fa-calendar"></i></sup></a>
									<?php endif; ?>

								</td>
								<td><?php echo date('m/d/Y', strtotime($subscription->date_created)); ?></td>
								<td class="alignright">

									<?php if ($subscription->status == 'active') : ?>

									<?php if ($subscription->paypal_subscription_id) : ?>
									<div class="displayinlineblock" data-toggle="tooltip" data-title="PayPal subscriptions can only be canceled directly through your PayPal account.">
										<a href="#" class="btn btn-sm btn-default confirm-click disabled">
											<fa class="fa fa-times colorwarning"></fa> Cancel
										</a>
									</div>
									<?php else : ?>
									<a href="<?php echo url('process.php?action=cancel_subscription&subscription_id=' . $subscription->id . '&return=admin'); ?>" class="btn btn-sm btn-default confirm-click" data-text="Yes, cancel it!">
										<fa class="fa fa-times colorwarning"></fa> Cancel
									</a>
									<?php endif; ?>

									<?php endif; ?>

									<a href="<?php echo url('process.php?action=delete_subscription&id=' . $subscription->id); ?>" class="btn btn-sm btn-default confirm-click"><i class="fa fa-trash-o colordanger"></i> Delete</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot class="<?php echo empty($subscriptions) ? '' : 'displaynone'; ?>">
							<tr>
								<td colspan="8" class="aligncenter">
									<em>no subscriptions found</em>
								</td>
							</tr>
						</tfoot>
					</table>




				</div>

				<div role="tabpanel" class="tab-pane" id="invoices">


					<div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<input type="text" class="form-control filter" placeholder="Search...">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</div>
						<div class="col-sm-9 alignright">
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#create_invoice"><i class="fa fa-plus"></i> Create Invoice</button>
						</div>
					</div>

					<table class="items-table table table-striped table-hover mt10">
						<thead>
							<tr>
								<th>Email</th>
								<th>Amount</th>
								<th>Description</th>
								<th>Status</th>
								<th>Number</th>
								<th>Due Date</th>
								<th>Date Created</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php $invoices = Model::factory('Invoice')->orderByDesc('date_created')->findMany(); ?>
							<?php foreach ($invoices as $invoice) : ?>
							<tr>
								<td><?php echo $invoice->email; ?></td>
								<td><?php echo currency($invoice->amount); ?></td>
								<td>
									<span class="displaynone"><?php echo $invoice->description; ?></span>
									<a href="#" data-toggle="tooltip" data-title="<?php echo $invoice->description; ?>"><i class="fa fa-file-text-o colorprimary"></i></a>
								</td>
								<td class="<?php echo $invoice->status == 'Paid' ? 'colorsuccess' : 'colorwarning'; ?>">
									<?php echo $invoice->status; ?>
									<?php if ($invoice->date_paid) : ?>
									<a href="#" class="colorgray" data-toggle="tooltip" data-title="Paid on <?php echo date('m/d/Y', strtotime($invoice->date_paid)); ?>"><sup><i class="fa fa-calendar"></i></sup></a>
									<?php endif; ?>
								</td>
								<td><?php echo $invoice->number; ?></td>
								<td class="<?php echo strtotime($invoice->date_due) < time() ? 'colordanger' : ''; ?>"><?php echo !is_null($invoice->date_due) ? date('m/d/Y', strtotime($invoice->date_due)) : ''; ?></td>
								<td><?php echo date('m/d/Y', strtotime($invoice->date_created)); ?></td>
								<td class="alignright">
									<a href="<?php echo url('?invoice_id=' . $invoice->unique_id); ?>" class="btn btn-sm btn-default" target="_blank"><i class="fa fa-external-link colorprimary"></i> View</a>
									<a href="<?php echo url('process.php?action=delete_invoice&id=' . $invoice->id); ?>" class="btn btn-sm btn-default confirm-click"><i class="fa fa-trash-o colordanger"></i> Delete</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot class="<?php echo empty($invoices) ? '' : 'displaynone'; ?>">
							<tr>
								<td colspan="8" class="aligncenter">
									<em>no invoices found</em>
								</td>
							</tr>
						</tfoot>
					</table>
					<?php template('invoice-modals'); ?>

				</div>


				<div role="tabpanel" class="tab-pane" id="items">



					<div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<input type="text" class="form-control filter" placeholder="Search...">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</div>
						<div class="col-sm-9 alignright">
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_item"><i class="fa fa-plus"></i> Add Item</button>
						</div>
					</div>

					<table class="items-table table table-striped table-hover mt10">
						<thead>
							<tr>
								<th>Name</th>
								<th>Price</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php $items = Model::factory('Item')->findMany(); ?>
							<?php foreach ($items as $item) : ?>
							<tr data-item-id="<?php echo $item->id; ?>" data-item-name="<?php echo $item->name; ?>" data-item-price="<?php echo $item->price; ?>">
								<td><?php echo $item->name; ?></td>
								<td><?php echo currency($item->price); ?></td>
								<td class="alignright">
									<a href="<?php echo url('?item_id=' . $item->id); ?>" class="btn btn-sm btn-default" target="_blank" data-toggle="tooltip" data-title="<?php echo url('?item_id=' . $item->id); ?>"><i class="fa fa-external-link colorprimary"></i> Direct Link</a>
									<button type="button" class="btn btn-default btn-sm" data-toggle="modal" data-target="#edit_item"><i class="fa fa-edit colorsuccess"></i> Edit</button>
									<a href="<?php echo url('process.php?action=delete_item&id=' . $item->id); ?>" class="btn btn-sm btn-default confirm-click"><i class="fa fa-trash-o colordanger"></i> Delete</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot class="<?php echo empty($items) ? '' : 'displaynone'; ?>">
							<tr>
								<td colspan="3" class="aligncenter">
									<em>no items found</em>
								</td>
							</tr>
						</tfoot>
					</table>
					<?php template('item-modals'); ?>
				</div>
				<div role="tabpanel" class="tab-pane" id="events">



					<div class="row">
						<div class="col-sm-3">
							<div class="input-group">
								<input type="text" class="form-control filter" placeholder="Search...">
								<span class="input-group-addon"><i class="fa fa-search"></i></span>
							</div>
						</div>
						<div class="col-sm-9 alignright">
							<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add_event"><i class="fa fa-plus"></i> Add Event</button>
						</div>
					</div>

					<table class="events-table table table-striped table-hover mt10">
						<thead>
							<tr>
								<th>Customer</th>
								<th>Type</th>
								<th>Time</th>
								<th></th>
							</tr>
						</thead>
						<tbody>
							<?php $events = Model::factory('Event')->findMany(); ?>
							<?php foreach ($events as $event) : ?>
							<tr>
								<td><?php echo $event->customer_name; ?><br><small class="colorgray"><?php echo $event->customer_email; ?></small></td>
								<td><?php echo $event->type; ?></td>
								<td><?php echo $event->date_created; ?></td>
								<td class="alignright">
									<a href="<?php echo url('process.php?action=delete_event&id=' . $event->id); ?>" class="btn btn-sm btn-default confirm-click"><i class="fa fa-trash-o colordanger"></i> Delete</a>
								</td>
							</tr>
							<?php endforeach; ?>
						</tbody>
						<tfoot class="<?php echo empty($events) ? '' : 'displaynone'; ?>">
							<tr>
								<td colspan="3" class="aligncenter">
									<em>no items found</em>
								</td>
							</tr>
						</tfoot>
					</table>
				</div>

				<div role="tabpanel" class="tab-pane" id="settings">

					<div class="row">
						<div class="col-sm-3 config-left">
							<ul class="nav nav-pills nav-stacked nav-pill-control">
								<li role="presentation" class="active"><a href="#your_details">Your Details</a></li>
								<li role="presentation"><a href="#general_settings">General Settings</a></li>
								<li role="presentation"><a href="#subscription_settings">Subscription Settings</a></li>
								<li role="presentation"><a href="#stripe_info">Stripe Info</a></li>
								<li role="presentation"><a href="#paypal_info">PayPal Info</a></li>
							</ul>
						</div>
						<div class="col-sm-9 config-right">

							<form action="<?php echo url('process.php'); ?>" method="post" class="validate form-horizontal">
								<input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
								<input type="hidden" name="action" value="save_config">


								<div class="nav-pill-pane active" id="your_details">
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Name</label>
										<div class="col-sm-9">
											<input type="text" name="config[name]" class="form-control" value="<?php echo $config['name']; ?>" data-rule-required="true">
											<span class="help-block">Enter your name or your business name. This will be used for all emails that get sent out.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Email</label>
										<div class="col-sm-9">
											<input type="text" name="config[email]" class="form-control" value="<?php echo $config['email']; ?>" data-rule-required="true" data-rule-email="true">
											<span class="help-block">Email address where payment confirmation emails will be sent to.</span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
										</div>
									</div>
								</div>

								<div class="nav-pill-pane" id="general_settings">
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Page Title</label>
										<div class="col-sm-9">
											<input type="text" name="config[page_title]" class="form-control" value="<?php echo $config['page_title']; ?>" data-rule-required="true">
											<span class="help-block">Title text to be displayed at the top of the payment terminal page.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Payment Type</label>
										<div class="col-sm-9">
											<select name="config[payment_type]" class="form-control" data-rule-required="true">
												<option value="item" <?php echo $config['payment_type'] == 'item' ? 'selected' : ''; ?>>Item</option>
												<option value="input" <?php echo $config['payment_type'] == 'input' ? 'selected' : ''; ?>>Input</option>
											</select>
											<span class="help-block">"Item" will show the list of preconfigured items that you have set. "Input" will show a text input field where an amount can be entered.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Show Description</label>
										<div class="col-sm-9">
											<select name="config[show_description]" class="form-control" data-rule-required="true">
												<option value="1" <?php echo $config['show_description'] ? 'selected' : ''; ?>>Yes</option>
												<option value="0" <?php echo !$config['show_description'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Whether or not to show the description field. This only applies if you have "Input" set as the payment type.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Show Billing Address</label>
										<div class="col-sm-9">
											<select name="config[show_billing_address]" class="form-control" data-rule-required="true">
												<option value="1" <?php echo $config['show_billing_address'] ? 'selected' : ''; ?>>Yes</option>
												<option value="0" <?php echo !$config['show_billing_address'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Whether or not to show the billing address fields on the payment terminal page.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Currency</label>
										<div class="col-sm-9">
											<select name="config[currency]" class="form-control" data-rule-required="true">
												<option value="USD" <?php echo $config['currency'] == 'USD' ? 'selected' : ''; ?>>USD</option>
												<option value="EUR" <?php echo $config['currency'] == 'EUR' ? 'selected' : ''; ?>>EUR</option>
												<option value="GBP" <?php echo $config['currency'] == 'GBP' ? 'selected' : ''; ?>>GBP</option>
												<option value="AUD" <?php echo $config['currency'] == 'AUD' ? 'selected' : ''; ?>>AUD</option>
												<option value="CAD" <?php echo $config['currency'] == 'CAD' ? 'selected' : ''; ?>>CAD</option>
											</select>
											<span class="help-block">Select the currency that you want to accept payments in.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Https Redirect</label>
										<div class="col-sm-9">
											<select name="config[https_redirect]" class="form-control" data-rule-required="true">
												<option value="1" <?php echo $config['https_redirect'] ? 'selected' : ''; ?>>Yes</option>
												<option value="0" <?php echo !$config['https_redirect'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Automatically redirect non-https requests to https.</span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
										</div>
									</div>
								</div>

								<div class="nav-pill-pane" id="subscription_settings">
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Enable Subscriptions</label>
										<div class="col-sm-9">
											<select name="config[enable_subscriptions]" class="form-control" data-rule-required="true">
												<option value="stripe_and_paypal" <?php echo $config['enable_subscriptions'] === 'stripe_and_paypal' ? 'selected' : ''; ?>>Stripe &amp; PayPal</option>
												<option value="stripe_only" <?php echo $config['enable_subscriptions'] === 'stripe_only' ? 'selected' : ''; ?>>Stripe Only</option>
												<option value="paypal_only" <?php echo $config['enable_subscriptions'] === 'paypal_only' ? 'selected' : ''; ?>>PayPal Only</option>
												<option value="0" <?php echo !$config['enable_subscriptions'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Whether or not to allow subscription/recurring payments and which payment methods you want to allow for subscriptions.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Subscription Interval</label>
										<div class="col-sm-9">
											<input type="text" name="config[subscription_interval]" class="form-control" value="<?php echo $config['subscription_interval'] ?>" data-rule-required="true">
											<span class="help-block">How often, in months, should customers be charged for subscriptions?</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Subscription Length</label>
										<div class="col-sm-9">
											<input type="text" name="config[subscription_length]" class="form-control" value="<?php echo $config['subscription_length'] ?>" data-rule-required="true">
											<span class="help-block">How many billing periods should subscriptions last? For a never ending subscription, enter 0. <span class="colordanger">NOTE:</span> This setting only applies to PayPal subscriptions. Stripe subscriptions, by default, will never expire and must be canceled in order to stop billing.</span>
										</div>
									</div>

									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Enable Free Trial</label>
										<div class="col-sm-9">
											<select name="config[enable_trial]" class="form-control" data-rule-required="true">
												<option value="1" <?php echo $config['enable_trial'] ? 'selected' : ''; ?>>Yes</option>
												<option value="0" <?php echo !$config['enable_trial'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Whether or not to offer a free trial for subscriptions/recurring payments.</span>
										</div>
									</div>

									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Free Trial Length</label>
										<div class="col-sm-9">
											<input type="text" name="config[trial_days]" class="form-control" value="<?php echo $config['trial_days'] ?>" data-rule-required="true">
											<span class="help-block">Specify how many days the free trial should last. This setting only applies if you have enabled the free trial setting.</span>
										</div>
									</div>

									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
										</div>
									</div>
								</div>

								<div class="nav-pill-pane" id="stripe_info">
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Stripe Secret Key</label>
										<div class="col-sm-9">
											<input type="text" name="config[stripe_secret_key]" class="form-control" value="<?php echo $config['stripe_secret_key']; ?>" data-rule-required="true">
											<span class="help-block">Set your Stripe secret key.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Stripe Publishable Key</label>
										<div class="col-sm-9">
											<input type="text" name="config[stripe_publishable_key]" class="form-control" value="<?php echo $config['stripe_publishable_key']; ?>" data-rule-required="true">
											<span class="help-block">Set your Stripe publishable key.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Stripe Webhook Secret Key</label>
										<div class="col-sm-9">
											<input type="text" name="config[stripe_whsec_key]" class="form-control" value="<?php echo $config['stripe_whsec_key']; ?>" data-rule-required="true">
											<span class="help-block">Set your Stripe webhook signing secret key.</span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
										</div>
									</div>

								</div>

								<div class="nav-pill-pane" id="paypal_info">
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>Enable PayPal</label>
										<div class="col-sm-9">
											<select name="config[enable_paypal]" class="form-control" data-rule-required="true">
												<option value="1" <?php echo $config['enable_paypal'] ? 'selected' : ''; ?>>Yes</option>
												<option value="0" <?php echo !$config['enable_paypal'] ? 'selected' : ''; ?>>No</option>
											</select>
											<span class="help-block">Whether or not to accept PayPal Standard payments in addition to Stripe credit card payments.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label"><span class="colordanger">*</span>PayPal Environment</label>
										<div class="col-sm-9">
											<select name="config[paypal_environment]" class="form-control" data-rule-required="true">
												<option value="production" <?php echo $config['paypal_environment'] == 'production' ? 'selected' : ''; ?>>Production</option>
												<option value="sandbox" <?php echo $config['paypal_environment'] == 'sandbox' ? 'selected' : ''; ?>>Sandbox</option>
											</select>
											<span class="help-block">Set the environment that you want to use for PayPal payments.</span>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-3 control-label">PayPal Email</label>
										<div class="col-sm-9">
											<input type="text" name="config[paypal_email]" class="form-control" value="<?php echo $config['paypal_email']; ?>" data-rule-email="true">
											<span class="help-block">Email address PayPal payments will be sent to. This only applies if you have enabled PayPal.</span>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-9 col-sm-offset-3">
											<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
										</div>
									</div>
								</div>



							</form>



						</div>
					</div>

				</div>
			</div>

		</div>

	</div>

	<footer class="admin-footer">
		<div class="container">
			<div class="aligncenter">
				<?php echo $config['page_title']; ?>
			</div>
		</div>
	</footer>

	<?php else : ?>

	<?php template('message'); ?>
	<div class="container">
		<div class="row">
			<div class="col-sm-4 col-sm-offset-4">
				<div class="page-header">
					<h1>Admin Login</h1>
				</div>
				<form action="<?php echo url('process.php'); ?>" method="post" class="validate">
					<input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
					<input type="hidden" name="action" value="login">
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-user"></i></span>
							<input type="text" name="admin_username" class="form-control" placeholder="Username" data-rule-required="true" autofocus>
						</div>
					</div>
					<div class="form-group">
						<div class="input-group">
							<span class="input-group-addon"><i class="fa fa-key"></i></span>
							<input type="password" name="admin_password" class="form-control" placeholder="Password" data-rule-required="true">
						</div>
					</div>
					<div>
						<button type="submit" class="btn btn-primary btn-block" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Logging In...'><i class="fa fa-check"></i> Login</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<?php endif; ?>

</body>

</html>
<?php require 'lib/close.php'; ?>