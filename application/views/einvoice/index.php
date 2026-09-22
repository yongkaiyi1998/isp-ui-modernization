<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<!--<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0); float:right;">
						<a style="margin-left: 5px; margin-right: 5px;cursor:pointer" onclick="print_bill_filtered('invoice')">
							<i class="menu-icon fa fa-files-o grey" <?php echo tooltip_helper('Print All Invoice'); ?> ></i>
						</a>
					</span>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0); float:right;">
						<a style="margin-left: 5px; margin-right: 5px;cursor:pointer" onclick="print_bill_filtered('bill')">
							<i class="ui-menu-icon fa fa-print light-red" <?php echo tooltip_helper('Print All Bill'); ?> ></i>
						</a>
					</span>-->
				</h4>
			</div>
		</div>
		<div class="panel-body msg-print">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	

					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
					<span>Einvoice Status: </span>
					<select id="sel_status" name="sel_status">
						<?php
							foreach ($sel_status_list as $key => $val) {
								echo "<option value='" . $key . "' " . set_select('sel_status', $key, ($key==$sel_status ? true : false) ) . ">" . $val."</option> ";
							}
						?>
					</select>

					<span>Last Bill Date: </span>
					<input id="txt_bill_date" name="txt_bill_date" placeholder="Last Billing Date" autocomplete="off"
						value="<?php echo set_value('txt_bill_date',$txt_bill_date); ?>" />

					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>
					
					<button  id="btPrintFiltered" name="btPrintFiltered" type="submit" value="filter" class="btn btn-success" role="button" style="display:none" >
						btPrintFiltered
					</button>
					
				</form>
			</div>
			<div class="table_rows_area"><?php echo $row_html; ?></div>
		</div>
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
<div id="backgroundPopup" onclick="hide_popup();"></div>

<script src="<?php echo base_url("js/itelco/einvoice.js?".cssjs_ver()); ?>" ></script>
