<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('adjustment');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form id="adjustment_detail" name="adjustment_detail" class="filter-form" method="post" class="filter-form" action="<?php echo $form_action; ?>">
				<input type="hidden" id="is_lock" name="is_lock" value="<?php echo $input['is_lock']?>">
				<input id="task" name="task" type="hidden" value=""> 
				<input id="current_status" name="current_status" type="hidden" value="<?= $input['status'] ?>"> 
				<fieldset class='category-border-main'>	
					<div class="category-border-main bg-success text-center" >
						ADJUSTMENT
					</div>
					<div class="col-lg-12">
						<div class="col-lg-12">&nbsp;</div>
						<?php if($approval_required == 1) { ?>
							<ul class="steps">
								<li class="complete">
									<span class="step"></span>
									<span class="title">Draft</span>
								</li>
								<?php if(count($lvl1_approvers)>0) { ?>
								<li class="<?= $input['status'] != 'D' && $input['status'] != null ? 'complete' : ''; ?>" id="pending_approval_1">
									<span class="step"></span>
									<span class="title">Pending Approval 1</span>
								</li>
								<?php } ?>
								<?php if(count($lvl2_approvers)>0) { ?>
									<li class="<?= $input['status'] != 'D' && $input['status'] != 'A' && $input['status'] != null ? 'complete' : ''; ?>" id="pending_approval_2">
										<span class="step"></span>
										<span class="title">Pending Approval 2</span>
									</li>
								<?php } ?>
								<li class="<?= $input['status'] == 'C' && $input['status'] != null ? 'complete' : ''; ?>">
									<span class="step"></span>
									<span class="title">Completed</span>
								</li>
							</ul>
							<br><br>
						<?php } ?>
						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Adjustment No</span>
									<input type="text" class="form-control" id="adj_no" name="adj_no" value="<?php echo set_value('adj_no', $input['adj_no']); ?>" placeholder="Adjustment No" readonly>
								</div>
							</div>
						</div>

						<div class="col-lg-12">&nbsp;</div>

						<div class="row">

							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Adjust to</span>

									<select id="adjust_by" name="adjust_by" style="width: 100%;">
										<option value="i"
											<?php echo set_select(
												'adjust_by',
												'i',
												($input['adjust_by'] == 'i')
											); ?>>
											Individually
										</option>

										<option value="b"
											<?php echo set_select(
												'adjust_by',
												'b',
												($input['adjust_by'] == 'b')
											); ?>>
											Building
										</option>

										<option value="a"
											<?php echo set_select(
												'adjust_by',
												'a',
												($input['adjust_by'] == 'a')
											); ?>>
											Area
										</option>
									</select>
								</div>

								<!-- Individually -->
								<div id="individual_section">

									<div class="input-group">
										<span class="input-group-addon input_group">Search</span>

										<input type="text"
											class="form-control"
											id="txt_search_autocomplete"
											name="txt_search_autocomplete"
											value="<?php echo set_value(
												'txt_search_autocomplete',
												$input['txt_search_autocomplete']
											); ?>"
											placeholder="Search">
									</div>

									<div class="input-group">
										<span class="input-group-addon input_group">
											Customer No<span class="red">*</span>
										</span>

										<input type="text"
											class="form-control"
											id="customer_no"
											name="customer_no"
											value="<?php echo set_value(
												'customer_no',
												$input['customer_no']
											); ?>"
											placeholder="Customer No"
											readonly>
									</div>

									<div class="input-group">
										<span class="input-group-addon input_group">
											Customer Name<span class="red">*</span>
										</span>

										<input type="text"
											class="form-control"
											id="customer_name"
											name="customer_name"
											value="<?php echo set_value(
												'customer_name',
												$input['customer_name']
											); ?>"
											placeholder="Customer Name"
											readonly>
									</div>

								</div>

								<!-- Building -->
								<div class="input-group" id="building_section">
									<span class="input-group-addon input_group">Building</span>

									<select id="building"
											name="building"
											style="width: 100%;">

										<?php foreach ($sel_building_list as $val) { ?>

											<option value="<?php echo $val['building_no']; ?>"
												<?php echo set_select(
													'building',
													$val['building_no'],
													($val['building_no'] == $input['building'])
												); ?>>

												<?php echo $val['name']; ?>

											</option>

										<?php } ?>

									</select>
								</div>

								<!-- Area -->
								<div class="input-group" id="area_section">
									<span class="input-group-addon input_group">Area</span>

									<select id="area"
											name="area"
											style="width: 100%;">

										<?php foreach ($sel_area_list as $val) { ?>

											<option value="<?php echo $val['id']; ?>"
												<?php echo set_select(
													'area',
													$val['id'],
													($val['id'] == $input['area'])
												); ?>>

												<?php echo $val['name']; ?>

											</option>

										<?php } ?>

									</select>
								</div>
							</div>

							<div class="col-lg-6">
								<?php if(count($lvl1_approvers)>0) 	{ ?>
									<div class="input-group">
										<span class="input-group-addon input_group">Level 1 Approver<span class="red">*</span></span>
										<select multiple="multiple" id="lvl1_approver" name="lvl1_approver[]" class="select2" style="width:100%;">
											<?php foreach($lvl1_approvers as $approver): ?>
												<option value="<?= $approver['user_id'] ?>" <?= in_array($approver['user_id'], $selected_lvl1_approver) ? 'selected' : '' ?>><?= $approver['display_name'] ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php } ?>
								<?php if(count($lvl2_approvers)>0) 	{ ?>
									<div class="input-group">
										<span class="input-group-addon input_group">Level 2 Approver<span class="red">*</span></span>
										<select multiple="multiple" id="lvl2_approver" name="lvl2_approver[]" class="select2" style="width:100%;">
											<?php foreach($lvl2_approvers as $approver): ?>
												<option value="<?= $approver['user_id'] ?>" <?= in_array($approver['user_id'], $selected_lvl2_approver) ? 'selected' : '' ?>><?= $approver['display_name'] ?></option>
											<?php endforeach; ?>
										</select>
									</div>
								<?php } ?>
							</div>
						</div>

						<div class="col-lg-12">&nbsp;</div>

						<div class="row">
							<div class="col-lg-6">
								<div class="input-group">
									<span class="input-group-addon input_group">Trans Date</span>
									<input type="text" class="form-control" id="tranx_date" name="tranx_date" value="<?php echo set_value('tranx_date', $input['tranx_date']); ?>" placeholder="Trans Date" autocomplete="off">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Bill Type</span>
									<select id="bill_type" name="bill_type" style="width: 100%;">
										<?php
											foreach ($sel_bill_type_list as $val) {
												echo "<option value='" . $val['bill_type_id'] . "' " . set_select('bill_type', $val['bill_type_id'], ( $val['bill_type_id']==$input['bill_type'] ? true : false) ) . ">" . $val['name']."</option>";
											}
										?>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Adjust Type</span>
									<select id="adjust_type" name="adjust_type" style="width: 100%;">
										<option value="cr" <?php echo set_select('adjust_type', 'cr', ($input['adjust_type'] == 'cr' ? true : false) ); ?> >Credit</option>
										<option value="dr" <?php echo set_select('adjust_type', 'dr', ($input['adjust_type'] == 'dr' ? true : false) ); ?> >Debit</option>
									</select>
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Amount</span>
									<input type="text" class="form-control" id="amount" name="amount" value="<?php echo set_value('amount', $input['amount']); ?>" placeholder="Amount">
								</div>
								<div class="input-group">
									<span class="input-group-addon input_group">Remark</span>
									<input type="text" class="form-control" id="remark" name="remark" value="<?php echo set_value('remark', $input['remark']); ?>" placeholder="Remark">
								</div>
							</div>
						</div>
					</div>
				</fieldset>
				<div class="col-md-12">
					<div class="button-group">
						<?php if($approval_required == 0 || ($input['status'] != 'C' && ($input['status'] == 'D' || $is_approver == 1))){ ?>
							<button  id="btCancel" name="btCancel" type="button" value="cancel" class="btn btn-warning"
							onclick="window.history.back()" <?php echo tooltip_helper('Cancel / Discard'); ?> >
								<i class="menu-icon fa fa-times white" data-toggle="tooltip" ></i> Cancel
							</button>
							<button id="btSave" name="btSave" type="submit" value="submit" class="btn btn-success" <?php
							echo tooltip_helper('Click To Save Record'); ?> <?php echo check_acl_btn('bill_manual', 'M');?>>
								<i class="menu-icon fa fa-save white" data-toggle="tooltip" ></i> Save
							</button>
						<?php } ?>
						<?php if ($approval_required == 1 && $input['status'] == 'D') { ?>
							<?php if($input['adj_no'] != ''){ ?>
								<?php if (count($lvl1_approvers) == 0 && count($lvl2_approvers) == 0) { ?>
									<button id="btComplete" name="btComplete" type="submit" value="complete" class="btn btn-primary" 
										<?= tooltip_helper('Mark as Completed') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
										<i class="fa fa-check-circle white" data-toggle="tooltip"></i> Mark as Completed
									</button>
								<?php } else { ?>
									<button id="btSendForReview" name="btSendForReview" type="submit" value="sendforreview" class="btn btn-primary" 
										<?= tooltip_helper('Send to Review') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
										<i class="fa fa-paper-plane white" data-toggle="tooltip"></i> Send for Review
									</button>
								<?php } ?>
							<?php } ?>
						<?php } ?>

						<?php if ($approval_required == 1 && ($input['status'] == 'A' || $input['status'] == 'B') && $is_approver == 1) { ?>
							<button id="btApprove" name="btApprove" type="submit" value="approve" class="btn btn-primary" 
								<?= tooltip_helper('Approve Bill') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
								<i class="fa fa-thumbs-up white" data-toggle="tooltip"></i> Approve
							</button>
							<button id="btReject" name="btReject" type="submit" value="reject" class="btn btn-danger" 
								<?= tooltip_helper('Reject Bill') ?> <?= check_acl_btn('bill_manual', 'M') ?>>
								<i class="fa fa-times-circle white" data-toggle="tooltip"></i> Reject
							</button>
						<?php } ?>
					</div>					
				</div>
			</form>
		</div>
	</div>
	
	<div id="clear" style="clear:both;"></div>
	<div id="popupDetail">
		<div id="popupDetailStd" onclick="disablePopup();">
			<div id="popupContent">
				&nbsp;
			</div>
		</div>
	</div>
</div>
<div id="backgroundPopup" onclick="hide_popup();"></div>
<script src="<?php echo base_url("js/itelco/adjustment.js?".cssjs_ver()); ?>" ></script>
<script>
$('body').on('keydown', 'input, select', function(e) {
    var self = $(this)
      , form = self.parents('form:eq(0)')
      , focusable
      , next
      ;
    if (e.keyCode == 13) {
        focusable = form.find('input,a,select,textarea').filter(':visible:not([readonly])');
        next = focusable.eq(focusable.index(this)+1);
        
        if (next.length) {
            next.focus();
            
        } else {
			
            form.submit();
        }
        return false;
    }
});

$('body').on('change' , '#bill_type' , function(e){
	
	//~ var bill_type = $('#bill_type').val();
	//~ if( bill_type == '4' || bill_type == '' || '11' || bill_type == '14' ){
		//~ $.ajax({
			//~ dataType: "json",
			//~ type: "post",
			//~ data: { 
					//~ customer_no : $('#customer_no').val()
				  //~ },
			//~ url: baseUrl+"customer/ajax_get_deposit_detail?"+Math.floor((Math.random() * 10000) + 1),
			//~ success: function(data){
				//~ console.log(data);

			//~ }
		//~ });
	//~ }
	
});

</script>