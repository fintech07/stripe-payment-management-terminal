<div class="modal fade" id="create_invoice" tabindex="-1" role="dialog" aria-labelledby="create_invoice" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="process.php" method="post" class="validate form-horizontal">
				<input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
	    		<input type="hidden" name="action" value="create_invoice">
				<div class="modal-header">
					<button type="button" class="close" data-hide="create_invoice"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">Create Invoice</h4>
				</div>
				<div class="modal-body">
		    		<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Email</label>
						<div class="col-sm-10">
							<input type="text" name="email" class="form-control" placeholder="Email" data-rule-required="true" data-rule-email="true">
							<div class="checkbox">
								<label class="text-muted">
									<input type="checkbox" name="send_email" value="1" checked>
									Send invoice to the above email address?
								</label>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Amount</label>
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-<?php echo currencyCode(); ?>"></i></span>
								<input type="text" name="amount" class="form-control" placeholder="0.00" data-rule-required="true" data-rule-number="true">
							</div>
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Description</label>
						<div class="col-sm-10">
							<textarea name="description" class="form-control h55" placeholder="Description" data-rule-required="true"></textarea>
						</div>
					</div>	
					<div class="form-group">
						<label class="col-sm-2 control-label">Number</label>
						<div class="col-sm-10">
							<input type="text" name="number" class="form-control" placeholder="Invoice Number">
						</div>
					</div>
					<div class="form-group">
						<label class="col-sm-2 control-label">Due Date</label>
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								<input type="text" name="date_due" class="form-control datepicker" placeholder="<?php echo date('m/d/Y', strtotime('+1 month')) ?>" data-date-autoclose="true">
							</div>
						</div>
					</div>					
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary" data-loading-text='<i class="fa fa-spinner fa-spin"></i> Saving...'><i class="fa fa-check"></i> Save</button>
				</div>
			</form>
		</div>
	</div>
</div>