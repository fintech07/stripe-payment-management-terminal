<?php require 'lib/init.php'; ?>
<?php
// show specific errors
if (isset($install_error) && preg_match('/(1045|1049|2005)/', $install_error)) {
	$error = $install_error;
}
?>
<!DOCTYPE html>
<html lang="en-US">
<?php template('head'); ?>

<body class="terminal-body">

	<noscript>
		<div class="alert alert-danger mt20neg">
			<div class="container aligncenter">
				<strong>Oops!</strong> It looks like your browser doesn't have Javascript enabled. Please enable Javascript to use this website.
			</div>
		</div>
	</noscript>

	<div class="container terminal-wrapper">

		<?php template('message', false); ?>

		<div class="page-header">
			<h2 class="colorprimary">Stripe Advanced Payment Terminal <small>Installation</small></h2>
		</div>

		<?php if (isset($error)) : ?>
		<div class="alert alert-danger">
			<strong><i class="fa fa-exclamation-circle"></i> Uh oh...</strong><br>
			<?php echo $error; ?>
		</div>
		<?php else : ?>

		<form action="process.php?action=install" method="post" class="validate form-horizontal" id="install_form">


			<div class="form-group">
				<label class="col-md-3 control-label">Admin Username</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['admin_username']; ?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label">Admin Password</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['admin_password']; ?>
				</div>
			</div>

			<hr>

			<div class="form-group">
				<label class="col-md-3 control-label">Database Host</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['db_host']; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label">Database Username</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['db_username']; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label">Database Password</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['db_password']; ?>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-3 control-label">Database Name</label>
				<div class="col-md-9 form-control-static">
					<?php echo $config['db_name']; ?>
				</div>
			</div>

			<hr>

			<div class="form-group">
				<div class="col-md-9 col-md-offset-3">
					<button type="submit" class="btn btn-primary btn-lg" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Installing...'>Install Application <i class="fa fa-angle-right"></i></button>
				</div>
			</div>

		</form>

		<div class="install-success displaynone">

			<div class="alert alert-success">
				<i class="fa fa-check"></i> Installation has been completed successfully!
			</div>

			<div class="mt20">
				<a href="<?php echo url('admin.php'); ?>" class="btn btn-lg btn-primary" target="_blank">Go to Admin <i class="fa fa-angle-right"></i></a>
				<a href="<?php echo url('index.php'); ?>" class="btn btn-lg btn-primary ml20" target="_blank">Go to Frontend <i class="fa fa-angle-right"></i></a>
			</div>

		</div>

		<?php endif; ?>

	</div>

</body>

</html>
<?php require 'lib/close.php'; ?>