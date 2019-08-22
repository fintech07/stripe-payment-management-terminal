<div class="modal fade" id="add_item" tabindex="-1" role="dialog" aria-labelledby="add_item" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="process.php" method="post" class="validate form-horizontal">
				<input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
	    		<input type="hidden" name="action" value="add_item">
				<div class="modal-header">
					<button type="button" class="close" data-hide="add_item"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">Add Item</h4>
				</div>
				<div class="modal-body">
		    		<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Name</label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control" placeholder="Name" data-rule-required="true">
						</div>
					</div>	
					<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Price</label>
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-<?php echo currencyCode(); ?>"></i></span>
								<input type="text" name="price" class="form-control" placeholder="0.00" data-rule-required="true" data-rule-number="true">
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
<div class="modal fade" id="edit_item" tabindex="-1" role="dialog" aria-labelledby="edit_item" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<form action="process.php" method="post" class="validate form-horizontal">
				<input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
	    		<input type="hidden" name="action" value="edit_item">
	    		<input type="hidden" name="id" value="">
				<div class="modal-header">
					<button type="button" class="close" data-hide="edit_item"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">Edit Item</h4>
				</div>
				<div class="modal-body">
		    		<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Name</label>
						<div class="col-sm-10">
							<input type="text" name="name" class="form-control" placeholder="Name" data-rule-required="true">
						</div>
					</div>	
					<div class="form-group">
						<label class="col-sm-2 control-label"><span class="colordanger">*</span>Price</label>
						<div class="col-sm-10">
							<div class="input-group">
								<span class="input-group-addon"><i class="fa fa-<?php echo currencyCode(); ?>"></i></span>
								<input type="text" name="price" class="form-control" placeholder="0.00" data-rule-required="true" data-rule-number="true">
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