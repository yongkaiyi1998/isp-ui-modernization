<?php include 'panel_header.php';?>
		<div class="panel-heading noprint">
			<div>
				<h4>
					<?php echo (!empty($page_title)?$page_title:'itelco'); ?>
					<?php if (check_acl('profile', 'M', false)) {?>
					<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15); float:right;">
						<a href="<?php echo base_url('profile/add_profile');?>" style="margin-left: 5px; margin-right: 5px;">
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
				<form id="profile" name="profile" method="post" class="filter-form" action="<?php echo $form_action; ?>" >
					<input type="hidden" id="page_item_no" name="page_item_no">	
					<span>Search: </span>	
					<input type='text' id='txt_search' name='txt_search' value="<?php echo set_value('txt_search', $txt_search); ?>" placeholder='Search'/>
					
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

<script src="<?php echo base_url("js/itelco/profile.js?".cssjs_ver()); ?>" ></script>
<?php include 'panel_footer.php';?>
