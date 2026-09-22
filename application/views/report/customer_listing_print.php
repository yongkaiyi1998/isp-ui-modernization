<style>
<?php if (isset($print_color)) { ?>
html {
  -webkit-print-color-adjust: exact;
}

@media print {
	.report_th th {
        color: rgba(0, 0, 0, 0);
        text-shadow: 0 0 0 #fff;
	}

	@media print and (-webkit-min-device-pixel-ratio:0) {
		.report_th th {
          color: #fff;
          -webkit-print-color-adjust: exact;
		}
	}
}
<?php } ?>
.menu-icon{
	cursor: pointer;
}
@page {
    size: a4 landscape;
}
</style>

<?php 
//_debug_array( $_POST );
if( isset( $_POST['btSubmit'] ) == true && $_POST['btSubmit'] == 'print' )
	$print = 1 ;
else
	$print = 0 ;
?>

<div class="container" style="width:95%;">
	<div class="panel-default">
	<?php if( $print == 0 ){ ?>
		<div class="panel-heading noprint panel_fontsize hidden-print">
			<span class="panel_space float_right">
				<a  href="<?php echo base_url('report');?>">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<div class="bg-success filter-bar hidden-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					<span>Search </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					<input type='hidden' id='txt_customer_no' name='txt_customer_no' value='<?php echo set_value('txt_customer_no', $txt_customer_no); ?>' />
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<?php
							foreach ($sel_category_list as $val) {
								echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					<span>Status </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php 
							foreach ($sel_status_list as $val) {
								echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>
					
					<select id="sel_status_date" name="sel_status_date">
						<option value="all">All</option>
						<option value="signup" <?php echo set_select('sel_status_date', 'signup', ($sel_status_date =='signup' ? true : false) ); ?> >Sign Up</option>
						<option value="activated" <?php echo set_select('sel_status_date', 'activated', ($sel_status_date =='activated' ? true : false) ); ?> >Activated</option>
						<option value="terminated" <?php echo set_select('sel_status_date', 'terminated', ($sel_status_date =='terminated' ? true : false) ); ?> >Terminated</option>
						<option value="suspended" <?php echo set_select('sel_status_date', 'suspended', ($sel_status_date =='suspended' ? true : false) ); ?> >Suspended</option>						
					</select>
					
					<span>From </span>
					<input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off"
						value="<?php echo set_value('date_from',$date_from); ?>" />
					
					<span>To </span>
					<input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
						value="<?php echo set_value('date_to',$date_to); ?>" />
					
					<br /><br />
					<span>Activated </span>
					<select id="sel_activated" name="sel_activated">
						<option value="all">All</option>
						<?php
						echo "<option value='yes' " . set_select('sel_activated', 'yes', ($sel_activated=='yes' ? true : false) ) . ">Yes</option>";
						echo "<option value='no' " . set_select('sel_activated', 'no', ($sel_activated=='no' ? true : false) ) . ">No</option>";
						?>
					</select>
					
					<span>Building </span>
					<select id="sel_building" name="sel_building" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_building_list as $val) {
								echo "<option value='" . $val['building_no'] . "' " . set_select('sel_building', $val['building_no'], ($val['building_no']==$sel_building ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>
				
					<span>Package </span>
					<select id="sel_package" name="sel_package" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_package_list as $val) {
								echo "<option value='" . $val['package_no'] . "' " . set_select('sel_package', $val['package_no'], ($val['package_no']==$sel_package ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>

					<span>Agent </span>
					<select id="sel_dealer" name="sel_dealer" style='max-width:200px;'>
						<option value="all">All</option>
						<?php 
							foreach ($sel_dealer_list as $val) {
								echo "<option value='" . $val['dealer_no'] . "' " . set_select('sel_dealer', $val['dealer_no'], ($val['dealer_no']==$sel_dealer ? true : false) ) . ">" . $val['name'] . "</option>";
							}
						?>
					</select>				
				
					<input type="hidden" id="order_by" 	 name="order_by"   value="<?php echo $order_by; ?>" />
					<input type="hidden" id="order_type" name="order_type" value="<?php echo $order_type; ?>" />
					
					<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>

					<button id="btExcel" name="btExcel" type="button" value="Excel" class="btn btn-success" onclick="generate_xls_filtered()">
						<i class="menu-icon fa fa-file-code-o" data-toggle="tooltip" title=""></i>
						Excel
					</button>

					<button id="btPrint" name="btPrint" type="button" value="Print" class="btn btn-success">
						<i class="menu-icon fa fa-print" data-toggle="tooltip" title=""></i>
						Print
					</button>


				</form>
			</div>
	
	<?php } ?>


			<div id="table_wrapper">
				<table class="" style="width:100%;">
					<thead>
						<?php $got_logo_header = true; ?>
						<tr>
							<?php if (!empty($logo_1) || !empty($logo_2)) { ?>
							<th colspan="6" class="text-left">
									<?php if (!empty($logo_1)) { ?>
									<img style="width:<?php echo (!empty($logo_1_width) ? $logo_1_width : '150'); ?>px" class="logo" src="<?php echo $logo_1; ?>" alt="Company Logo 1" title="<?php echo $proj_name; ?>">&nbsp;&nbsp;&nbsp;
									<?php } ?>
									<?php if (!empty($logo_2)) { ?>
									<img style="width:<?php echo (!empty($logo_2_width) ? $logo_2_width : '150'); ?>px" class="logo" src="<?php echo $logo_2; ?>" alt="Company Logo 2" title="<?php echo $proj_name; ?>">
									<?php } ?>
							</th>
							<th colspan="6" class="text-right">
								<h3 class="text-right">
									<?php echo $page_title; ?><br>
									<span class="small"><?php echo $filter_text; ?></span>
								</h3>
							</th>
							<?php } else { ?>
							<th colspan="100%">
								<h3 class="text-center">
									<?php echo $page_title; ?><br>
									<span class="small"><?php echo $filter_text; ?></span>
								</h3>
							</th>
							<?php } ?>
						</tr>
						<tr class="report_th" style="background: #289383; font-weight: normal; color: #fff;">
						 <th class="text-left" style="width:40px;">#</th>
							
							<th class="text-left" style="width:80px;">No / Name 
							<i class="menu-icon fa fa-sort" id="customer_name"></i>
							</th>
							
							<th class="text-center">Login
							<i class="menu-icon fa fa-sort" id="login_username"></i>
							</th>
							
							<th class="text-left">Gender
							<i class="menu-icon fa fa-sort" id="gender"></i>
							</th>
							
							<th class="text-center">Mobile
							<i class="menu-icon fa fa-sort" id="mobile_num"></i>
							</th>

							<th class="text-center">Email
							<i class="menu-icon fa fa-sort" id="email_1"></i>
							</th>
							
							<?php //if( $sel_status == '' || $sel_status == 'all' ){ ?>
							<th class="text-center">Status
							<i class="menu-icon fa fa-sort" id="status"></i>
							</th>
							<?php //} ?>

							<th class="text-center">Status Date
							<i class="menu-icon fa fa-sort" id="status_date"></i>
							</th>
							
							<!--
							<th class="text-center col-lg-1">Cat.
							<i class="menu-icon fa fa-sort grey" id="category"></i>
							</th>
							-->
							
							<th class="text-center">Agent
							<i class="menu-icon fa fa-sort" id="dealer"></i>
							</th>
							
							<th class="text-center">Pack.
							<i class="menu-icon fa fa-sort" id="package_name"></i>
							</th>

							<th class="text-center">Pack.<br>Changed
							<i class="menu-icon fa fa-sort" id="package_changed_date"></i>
							</th>
							
							<th class="text-center">Monthly
							<i class="menu-icon fa fa-sort" id="monthly_charge"></i>
							</th>
							
						</tr>
					</thead>
					<tbody>
						<?php 
					
						foreach ($data_row as $key => $val) {
						/*$suspend_date = $val['suspended_date'];
							if ($suspend_date == "0000-00-00"){
								$val['suspended_date'] = "-"; 
							}*/

							//full address
							$full_address = '';
							if (!isset($val['inst_unit_no'])) { $val['inst_unit_no'] = ''; }
							if (!empty($val['building']) || !empty($val['inst_unit_no'])) {
								$building_addr = $val['building'] . ' ' . $val['inst_unit_no'];
								$full_address .=  trim($building_addr) . '<br>';
							} 
							$full_address .= (!empty($val['inst_addr1']) ? $val['inst_addr1'] . ', ' : ''); 
							$full_address .= (!empty($val['inst_addr2']) ? $val['inst_addr2'] . ', ' : ''); 
							if (!empty($val['inst_addr3'])) {
								$full_address .= $val['inst_addr3'] . ', '; 
							}
							$full_address .= (!empty($val['inst_city']) ? $val['inst_city'] . ', ' : '');  
							$full_address .= (!empty($val['inst_postcode']) ? $val['inst_postcode'] . ', ' : ''); 
							$full_address .= (!empty($val['inst_state']) ? $val['inst_state'] : ''); 

							echo "<tr>";
							echo "<td class='text-center'>" .$val['no'] . "</td>";
							echo "<td>" . 
								$val['customer_no'] . '<br>' .
								$val['name'] .
							"</td>";
							echo "<td class='text-center'>" . $val['login_username'] . "</td>";
							echo "<td class='text-center'>" . $val['gender'] . "</td>";
							echo "<td class='text-center'>" . $val['mobile_num'] . "</td>";
							echo "<td class='text-center'>" . $val['email_1'] . ( $val['email_2'] != '' ? '<br />' . $val['email_2'] : '' ) . "</td>";
							//if( $sel_status == '' || $sel_status == 'all' )
								echo "<td class='text-center'>" . $val['status'] . "</td>";
								echo "<td class='text-center'>" . $val['status_date'] . "</td>";
							//echo "<td class='text-center'>" . $val['category'] . "</td>";
							echo "<td class='text-center'>" . $val['dealer'] . "</td>";
							echo "<td class='text-center'>" . $val['package_name'] . "</td>";
							echo "<td class='text-center'>" . $val['package_start'] . "</td>";
							echo "<td class='text-center'>" . $val['monthly_charge'] . "</td>";
							echo "</tr>";
							echo "<tr><td></td></td><td class='' colspan=10>" ;
							echo $full_address;
							echo "</td></tr><tr><td colspan=12 style='border-bottom: 1px solid black;'>&nbsp;</td></tr>";
						}
						?>
					</tbody>
				</table>
			</div>
			
		</div>
	</div>
</div>

<script src="<?php echo base_url("js/itelco/report.js?".cssjs_ver()); ?>" ></script>

<script>
	var orderBy		= "<?php echo $order_by; ?>";
	var orderType	= "<?php echo $order_type; ?>";
	
	if( orderBy != '' ){
		$("#table_wrapper > table > thead").find("[id*=\'"+orderBy+"\']").removeClass('fa-sort').addClass( 'fa-sort-' + orderType );
	}
	
	
	$("#date_from").datepicker({format: 'yyyy-mm-dd'});
	$("#date_to").datepicker({format: 'yyyy-mm-dd'});
		
	function generate_xls_filtered()
	{
		var form = $('form');
		form.attr('action', base_url + 'report/export_customer_listing');
		//form.attr('target', '_blank');
		$('#btFilter').click();
		form.attr('action', base_url + 'report/customer_listing') ;
		//form.removeAttr('target');
	}

$(document).on("click", ".fa-sort", function(e){
	$(this).removeClass('fa-sort').addClass('fa-sort-desc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	$('#report').submit();
});

$(document).on("click", ".fa-sort-asc", function(e){
	$(this).removeClass('fa-sort-asc').addClass('fa-sort');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'desc' );	
	$('#report').submit();
});
	
$(document).on("click", ".fa-sort-desc", function(e){
	$(this).removeClass('fa-sort-desc').addClass('fa-sort-asc');
	$('#order_by').val( $(this).attr('id') );
	$('#order_type').val( 'asc' );
	$('#report').submit();
});

$( document ).on("click", "#btPrint", function(e){
	var form = $('form');
	form.attr('action', base_url + 'report/print_customer_listing');
	form.attr('target', '_blank');
	$('#btFilter').val("print");
	$('#btFilter').click();
	form.attr('action', base_url + 'report/customer_listing');
	$('#btFilter').val("filter");
	form.removeAttr('target');
});

var print = '<?php echo $print == 1 ? 1 : 0 ; ?>';

if( print == 1 ){
	$('div').css('padding','0');
	$('.fa-sort').css('display','none');
}



</script>

