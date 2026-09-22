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
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no" value="<?php echo $page_item_no; ?>">	
					<span>Search: </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo $txt_search; ?>" placeholder='Search'/>
					
					<span>Category: </span>
					<select id="sel_category" name="sel_category">
						<option value="all" SELECTED="SELECTED" >All</option>
						<?php
							foreach ($sel_category_list as $val) {
								$select = "";
								if ($val['category_code'] == $sel_category) {
									$select = "selected='selected'";
								}
								echo "<option value='" . $val['category_code'] . "' " . $select . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>Status: </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php
							foreach ($sel_status_list as $val) {
								echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
					
					<span>Bill By: </span>
					<select id="sel_bill_by" name="sel_bill_by">
						<option value="all" <?php if ($sel_bill_by == 'all') { echo 'selected="selected"'; } ?> >All</option>
						<option value="p" <?php if ($sel_bill_by == 'p') { echo 'selected="selected"'; } ?> >Post</option>
						<option value="e"  <?php if ($sel_bill_by == 'e') { echo 'selected="selected"'; } ?> >Email</option>
					</select>
					
					<span>Last Bill Date: </span>
					<input id="txt_bill_date" name="txt_bill_date" placeholder="Last Billing Date" autocomplete="off"
						value="<?php echo $txt_bill_date; ?>" />

					<!--<button  id="btFilter" name="btFilter" type="submit" value="filter" class="btn btn-success">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>-->

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

			<!--table rows html here-->
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

<script src="<?php echo base_url("js/itelco/bill.js?".cssjs_ver()); ?>" ></script>
