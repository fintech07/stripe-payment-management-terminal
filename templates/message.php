<?php
if ( isset($_SESSION['flash']) && is_array($_SESSION['flash']) ) {
	$type = isset($_SESSION['flash']['type']) ? $_SESSION['flash']['type'] : 'danger';
	$message = isset($_SESSION['flash']['message']) ? $_SESSION['flash']['message'] : 'Oops, something went wrong.';
	switch ( $type ) {
        case 'success':
            $icon = 'check-circle';
        break;
        case 'info':
            $icon = 'info-circle';
        break;
        case 'warning':
            $icon = 'exclamation-triangle';
        break;
        case 'danger':
            $icon = 'exclamation-circle';
        break;
        default:
            $icon = 'exclamation-circle';
    }
}
?>
<?php if ( isset($type) && isset($message) ) : ?>
    <?php if ( $container ) : ?>
	<div class="container">
    <?php endif; ?>
		<div class="alert alert-dismissible alert-<?php echo $type; ?> flash-message mt10" role="alert">
			<button type="button" class="close" data-hide="flash-message"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
			<i class="fa fa-<?php echo $icon; ?>"></i> <?php echo $message; ?>
		</div>
	<?php if ( $container ) : ?>
    </div>
    <?php endif; ?>
<?php endif; ?>