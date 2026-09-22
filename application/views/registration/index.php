<?php include 'panel_header.php';?>
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('registration', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('registration/add_registration');?>" style="margin-left: 5px; margin-right: 5px;">
							<i class="ui-menu-icon fa fa-user-plus green"></i>
						</a>
					</span>
					<?php }?>
				</h4>
			</div>
		</div>
		<div class="panel-body">
			<?php echo flash_data_helper($msg); ?>
			<div class="bg-success filter-bar">
				<form id="registration" name="registration"  method="post" class="filter-form" action="<?php echo $form_action; ?>" >
					<input type="hidden" id="page_item_no" name="page_item_no">	

					<span>Status: </span>
					<select id="sel_status" name="sel_status">
						<option value="all">All</option>
						<?php
							foreach ($sel_status_list as $key => $val) {
								echo "<option value='" . $key . "' " . set_select('sel_status', $key, ($key==$sel_status ? true : false) ) . ">" . $val."</option> ";
							}
						?>
					</select>

					<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>

					<span>Building: </span>
					<select id="sel_building" name="sel_building">
						<option value="all">All</option>
						<?php 
							foreach ($sel_building_list as $val) {
								$select = "";
								if ($val['building_no'] == $sel_building) {
									$select = "selected='selected'";
								}
								echo "<option value='" . $val['building_no'] . "' " . $select . ">" . $val['name'] . "</option>";
							}
						?>
					</select>
					
					<span>Installation: </span>
					<select id="sel_installation" name="sel_installation">
						<option value="all">All</option>
						<option value="upcoming">Upcoming</option>
						<option value="overdue">Overdue</option>
						<option value="unscheduled">Unscheduled</option>
					</select>

					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter(1)">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
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

<script src="<?php echo base_url("js/itelco/registration.js?".cssjs_ver()); ?>" ></script>
<?php include 'panel_footer.php';?>
