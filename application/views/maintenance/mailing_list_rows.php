<style>
    .action_btn_td a { display: inline-block; margin: 0 4px; padding: 4px; transition: transform 0.1s; }
    .action_btn_td a:hover { transform: scale(1.2); }
</style>
<div class="table-responsive">
<table class="table table-striped table-hover">
	<thead>
		<tr>			
			<th class="text-center" style="width:1em;">ID</th>	
			<th class="col-lg-3 text-center">Customer</th>
			<th class="col-lg-4 text-center">Email</th>
			<th class="text-center">Last Email Sent</th>
			<th class="text-center">Attachment</th>
			<th class="text-center">Actions</th>
		</tr>
	</thead>
	<tbody>
        <?php if(!empty($row_data)): ?>
            {row_data}
                <tr>
                    <td class="text-left" style="vertical-align: middle;">
                        <a href="<?php echo base_url('customer/edit_customer');?>/{customer_no}/email" title="Generate Attachment">
                            {customer_no}
                        </a>
                    </td>
                    <td class="text-left" style="vertical-align: middle; padding-left: 1rem;">
                        <strong>{customer_name}</strong>
                    </td>
                    <td style="vertical-align: middle;">
                        {bill_by_email}
                        {flag_email}
                        <strong>{email_1}</strong>
                    </td>
                    <td class="text-center" style="vertical-align: middle;">{last_email_sent}</td>
                    <td class="text-center" style="vertical-align: middle;">
                        <a class="attach_file btn btn-default btn-xs" data-file="{last_file_gen}" onclick="return viewBill(this);" style="display:inline-block;cursor:pointer; padding: 2px 6px;">
                            <i class="fa fa-file-o"></i> view file
                        </a>     
                    </td>
                    <td class="text-center action_btn_td" style="vertical-align: middle;">
                        <a href="<?php echo base_url('email/generate_bill_statement');?>/{customer_no}" title="Generate New Attachment">                    
                            <i class="fa fa-retweet fa-1g blue"></i>
                        </a>
                        <a href="<?php echo base_url('email/email_bill_statement');?>/{customer_no}" title="Send A Generic Billing Email ">                 
                            <i class="fa fa-envelope fa-1g green"></i>
                        </a>
                        <a href="<?php echo base_url('email/email_suspended_account');?>/{customer_no}" title="Send A Generic Email For Suspended Account">                 
                            <i class="fa fa-envelope fa-1g orange"></i>
                        </a>
                        <a href="<?php echo base_url('email/email_reactivate_account');?>/{customer_no}" title="Send A Generic Email For Reactivate Account">                   
                            <i class="fa fa-envelope fa-1g blue"></i>
                        </a>
                        <!-- <a href="<?php echo base_url('email/email_terminated_account');?>/{customer_no}" title="Send A Generic Email For Terminated Account">                   
                            <i class="fa fa-envelope fa-1g red"></i>
                        </a> -->
                    </td>
                </tr>
            {/row_data}
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center" style="padding: 20px; color: #999;">No Record Available</td>
            </tr>
        <?php endif ?>
    </tbody>
</table>
</div>
<div>
	<div class="col-md-12 text-center">
		<?php echo $pagination; ?>
	</div>
</div>

<script>
	var attachment_folder = '<?php echo $attachment_folder; ?>/';
</script>
<script src="<?php echo base_url("js/itelco/mailing_list.js?".cssjs_ver()); ?>" ></script>