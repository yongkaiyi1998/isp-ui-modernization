<?php echo flash_data_helper($msg); ?>
<div class="bg-success filter-bar">
	<form id="email" name="email" method="post" class="filter-form" action="<?php echo $form_action; ?>">
		<input type="hidden" id="page_item_no" name="page_item_no">	
		<span>Search: </span>	
		<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
		<span>Category: </span>
		<select id="sel_category" name="sel_category">
			<option value="all">All</option>
			<?php
				foreach ($sel_category_list as $val) {
					echo "<option value='" . $val['category_code'] . "' " . set_select('sel_category', $val['category_code'], ($val['category_code']==$sel_category ? true : false) ) . ">" . $val['name']."</option> ";
				}
			?>
		</select>
		<span>Status: </span>
		<select id="sel_status" name="sel_status">
			<option value="all">All</option>
			<?php 
				foreach ($sel_status_list as $val) {
					echo "<option value='" . $val['status_code'] . "' " . set_select('sel_status', $val['status_code'], ($val['status_code']==$sel_status ? true : false) ) . ">" . $val['name'] . "</option>";
				}
			?>
		</select>
		
		<!--
		<span>Email: </span>
		<select id="sel_month" name="sel_month">
			<option value="all">All</option>
			<?php 
				/*
				foreach ($sel_month_list as $val) {
					echo "<option value='" . $val['month_code'] . "' " . set_select('sel_month', $val['month_code'], ($val['month_code']==$sel_month ? true : false) ) . ">" . $val['name'] . "</option>";
				}
				*/
			?>
		</select>
		-->
		
		<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
			<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
			Filter
		</button>
		<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
			<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
			Clear
		</button>
		<!--
		<button  id="btReset" name="btFilter" type="button" value="reset" class="btn btn-warning">
			<i class="menu-icon fa fa-refresh white" data-toggle="tooltip" title=""></i>
			Reset Monthly Bill
		</button>
		
		<button  id="btBill" name="btFilter" type="button" value="bill" class="btn btn-info">
			<i class="menu-icon fa fa-file-pdf-o white" data-toggle="tooltip" title=""></i>
			Generate Monthly Bill
		</button>
		-->
	</form>
</div>
<div>

</div>
<div class="table_rows_area"><?php echo $row_html; ?></div>
<script>
	var attachment_folder = '<?php echo $attachment_folder; ?>/';
</script>
<script src="<?php echo base_url("js/itelco/mailing_list.js?".cssjs_ver()); ?>" ></script>
