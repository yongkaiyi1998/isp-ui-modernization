<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="bill" name="bill" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search </span>
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
					<span>Category </span>
					<select id="sel_category" name="sel_category">
						<option value="all" SELECTED="SELECTED" >All</option>
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
								echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name']."</option> ";
							}
						?>
					</select>
										
					<span>From </span>
					<input id="txt_date_from" name="txt_date_from" placeholder="From" autocomplete="off"
						value="<?php echo set_value('txt_date_from',$txt_date_from); ?>" />
					
					<span>To </span>
					<input id="txt_date_to" name="txt_date_to" placeholder="To" autocomplete="off"
						value="<?php echo set_value('txt_date_to',$txt_date_to); ?>" />

					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter()">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					
					<button  id="btPrintFiltered" name="btPrintFiltered" type="submit" value="filter" class="btn btn-success" role="button" style="display:none" >
						btPrintFiltered
					</button>

					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
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

<script src="<?php echo base_url("js/itelco/statement_account.js?".cssjs_ver()); ?>" ></script>
