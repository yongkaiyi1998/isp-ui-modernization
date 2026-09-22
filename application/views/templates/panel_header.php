<div class="container" >
	<div class="panel panel-default">
		<div class="panel-heading noprint panel_fontsize">
			<span class="panel_space float_right">	
				<a class="delete_button hide" style="cursor:pointer" >						
					<i class="ui-menu-icon fa fa-trash red" <?php echo tooltip_helper('Delete Record'); ?>></i>
				</a>
				<a class="add_button hide" style="cursor:pointer" >
					<i class="ui-menu-icon fa fa-plus green" <?php echo tooltip_helper('Add New Record'); ?>></i>
				</a>
				<a class="cancel_button hide" style="cursor:pointer " >
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<h4>
				<?php echo (!empty($panel_title)?$panel_title:'Sample Title'); ?>
			</h4>
		</div>
		
		<div class="panel-body">
