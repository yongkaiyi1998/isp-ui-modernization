<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('customer/edit_customer/'.$var['customer_no']);?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $var['page_title']; ?>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<form  method="post" action="<?php echo $var['form_action']; ?>">
				<div class="col-lg-12">
					<fieldset class='category-border'>
						<legend class="category-border"> <?php echo $var['fieldset_2']; ?> </legend>
						<span class="line_item">
							<div class="col-lg-2">
								<input type="hidden" name="customer_deposit[customer_no]" value="<?php echo set_value('customer_deposit[customer_no]', $var['customer_no']); ?>">
								<input type="text" name="customer_deposit[deposit_date]" 
								placeholder=" Deposit Date" class="form-control" value="<?php echo set_value('customer_deposit[deposit_date]', ''); ?>">
							</div>
							<div class="col-lg-4">
								<input type="text" class="form-control" placeholder=" Remark" name="customer_deposit[remark]"
								value="<?php echo set_value('customer_deposit[remark]', ''); ?>">
							</div>
							
							<div class="col-lg-2"> 
								<select name="customer_deposit[payment_source]" class="form-control" >
								<?php 
									$payment_source_arr = array('' => '');
									foreach( $payment_source_opt AS $opt ){
										echo "<option value='".$opt['payment_source_id']."' ".
										set_select('payment_source',$opt, FALSE )
										." >".$opt['name']."</option>";
										$payment_source_arr[$opt['payment_source_id']] = $opt['name'];
									}
								?>
								</select>
							</div>
							
							<div class="col-lg-2">
								<input type="text" class="form-control" placeholder=" Cheque / Bank" name="customer_deposit[payment_info]"
								value="<?php echo set_value('customer_deposit[payment_info]', ''); ?>">
							</div>
						
							<div class="col-lg-2">
								<input type="text" class="form-control text-right" placeholder="Amount " name="customer_deposit[deposit]"
								value="<?php echo set_value('customer_deposit[deposit]', ''); ?>">
							</div>
							
							<div class="col-lg-12">&nbsp;</div>
							<div class="col-lg-10">&nbsp;</div>
							
							<div class="col-lg-2 text-left">
								<?php if ($current_termination_flow == 'A') { ?>
								<button  name="processtype[save]" type="submit" value="save" class="btn btn-success" >
									<i class="menu-icon fa fa-save white" data-toggle="tooltip" title=""></i> Save 
								</button>
								<?php } ?>
								<button  name="processtype[cancel]"  type="submit" value="cancel" class="btn btn-warning">						
									<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i> Cancel 
								</button>
							</div>
						</span>
					</fieldset>
				</div>
				
				<div class="col-lg-12">					
					<div class="alert alert-info text-center" role="alert" style="font-weight:bold;">
						<?php echo $var['fieldset_1']; ?>
					</div>
					<div class="table-responsive">
						<table class="table table-striped">
							<thead>
								<tr>
									<th class="col-lg-1 text-left"> Deposit Date</th>
									<th class="col-lg-3 text-left"> Remark</th>
									<th class="col-lg-2 text-left"> Payment Source</th>	
									<th class="col-lg-2 text-left"> Payment Info</th>
									<th class="col-lg-2 text-right">Amount </th>
									<th class="col-lg-2 text-center">Action</th>
								</tr>
							</thead>
							<tbody>
								<?php if((empty($row_data))):?>
									<tr><td class="text-center" colspan="6">No Record Available</td></tr>
								<?php else: ?>
								<?php
									$hist = 0 ;
									
									
									
									foreach( $row_data as $row ){
								?>
										<tr <?php echo $row['void'] == 1 ?"style='text-decoration:line-through;color:red;'" : ""; ?>>
											<td class="text-left"><?php echo $row['deposit_date']; ?>
											<?php if (( $row['idx'] != '' ) && ($row['is_lock'] == '0')){ ?>
												<input type="hidden" name="update_deposit[idx][]" value="<?php echo $row['idx']; ?>" />
												<input type="hidden" name="update_deposit[origin][]" value="<?php echo $row['origin']; ?>" />
											<?php } ?>
											</td>
											<td class="text-left"><?php echo $row['remark']; ?></td>
											<td class="text-left">
												<?php if (( $row['idx'] != '' ) && ($row['is_lock'] == '0')){ ?>
													<?php if( $row['origin'] == 'customer' || $row['origin'] == 'payment' ){ ?>
														<select name="update_deposit[payment_source][]" class="form-control" style="height:100%;max-width:150px;" >
														<?php 
															foreach( $payment_source_opt AS $opt ){
																echo "<option value='".$opt['payment_source_id']."' ".
																set_select('payment_source',$opt, $row['payment_source_id']==$opt['payment_source_id']?TRUE:FALSE )
																." >".$opt['name']."</option>";
															}
														?>
														</select>
													<?php } else { ?>
														<input type="hidden" name="update_deposit[payment_source][]" value="" />
													<?php } ?>
												<?php } else { echo $payment_source_arr[$row['payment_source_id']]; } ?>
											</td>
											<td class="text-left">
												<?php if (( $row['idx'] != '' ) && ($row['is_lock'] == '0')){ ?>
												<input type="text" class="form-control" placeholder=" Cheque / Bank" name="update_deposit[payment_info][]"
												value="<?php echo set_value('update_deposit[payment_info][]', $row['payment_info']); ?>">
												<?php }else{ ?>
												<?php echo $row['payment_info']; ?>
												<?php } ?>
											</td>
											
											<td class="text-right">
												<?php if (( $row['idx'] != '' ) && ($row['is_lock'] == '0')){ ?>
												<input type="text" 
														class="form-control text-right" 
														placeholder=" Cheque / Bank" 
														name="update_deposit[payment_amount][]"	
														value="<?php echo set_value('update_deposit[payment_amount][]', $row['deposit']); ?>" />
												<?php }else{ echo $row['deposit']; } ?>										
											</td>
											<td class="text-center">
												<?php if ($row['origin'] == 'customer') { ?>
												<a class="print_invoice" data-id ="<?php echo $row['idx']; ?>">	
													<i class="menu-icon fa fa-files-o grey" title="Print Invoice" style="cursor:pointer"></i>&nbsp;
												</a>
												<a class="print_receipt" data-id ="<?php echo $row['idx']; ?>">	
													<i class="menu-icon fa fa-print light-red" title="Print Receipt" style="cursor:pointer"></i> 
												</a>	
												<?php } ?>										
											</td>
										</tr>
								<?php 
										$hist++;
									}
								?>
										<tr style="background-color:rgba(255,255,255,1);">
											<td colspan="4" class="text-right" style="font-size:1.6em;padding-top:0.5em;">Total Amount</td>
											<td class="text-right" style="font-size:1.6em;padding-top:0.5em;">
												<span style="font-weight:bold;"><?php echo number_format( $total_deposit,2,".",""); ?></span>
											</td>
											<td class="text-center">
												<input type="hidden" name="update_deposit[total_row]" value="<?php echo $hist; ?>" />
												<?php if ($current_termination_flow == 'A') { ?>
												<button  name="processtype[update]"  type="submit" value="update" class="btn btn-warning">						
													<i class="menu-icon fa fa-pencil white" data-toggle="tooltip" title=""></i> Update 
												</button>	
												<?php } ?>
											</td>
										</tr>
								<?php endif; ?>
							</tbody>	
						</table>
					</div>
				</div>				
			</form>
		</div>
	</div>
</div>
<script src="<?php echo base_url("js/itelco/customer_deposit.js?".cssjs_ver()); ?>" ></script>
