<?php $thisURL = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>
<?php $index = array_pop(explode('/', $thisURL )); ?>
<?php $index = (empty($index))?'0':$index; ?>
<div class="panel-heading noprint">
	<h4>
		<?php if(!empty($form_var['top_button']) && $form_var['top_button'] == 'add' ): ?>
			<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15);">
				<a href="<?php echo $base_url.$get_this['controller'].'/add/0/'.$index ?>" ><i class="menu-icon fa fa-plus fa-1x green"><?php //echo ucwords($form_var['title']); ?></i></a>&nbsp;
			</span>
		<?php elseif(!empty($form_var['top_button']) && $form_var['top_button'] == 'back' ): ?>
			<span class="myDivider" style="border-right: 1px solid rgba(0, 0, 0, 0.15);">
				<a onclick="window.history.back()" style="cursor:pointer" data-toggle="tooltip" title="back / discard"><i class="menu-icon fa fa-chevron-left fa-1x red">&nbsp;<?php //echo ucwords($form_var['title']); ?></i></a>
			</span>
		<?php endif; ?>
		
		<span class="main_back" style="display:none; border-right: 1px solid rgba(0, 0, 0, 0.15);">
			<a id="main_back" style="cursor:pointer" data-toggle="tooltip" title="back / discard"><i class="menu-icon fa fa-chevron-left fa-1x red"></i></a>&nbsp;	
		</span>		
		
		<span class="main_add" style="display:none; border-right: 1px solid rgba(0, 0, 0, 0.15);">
			<a id="main_add" style="cursor:pointer"><i class="menu-icon fa fa-plus fa-1x green"></i></a>&nbsp;
		</span>
		
		<span class="print" style="display:none; border-left: 1px solid rgba(245, 255, 255, 1);border-right: 1px solid rgba(0, 0, 0, 0.15);">
			<font size="2em">
			&nbsp;<a id="print" style="cursor:pointer" data-toggle="tooltip" title="Print Document"> <i class="menu-icon fa fa-print light-red"></i></a>&nbsp;
			</font>
		</span>		
		<span class="save" style="display:none; border-left: 1px solid rgba(245, 255, 255, 1);border-right: 1px solid rgba(0, 0, 0, 0.15);">
			<font size="2em">
			&nbsp;&nbsp;<a id="save" style="cursor:pointer" data-toggle="tooltip" title="Save & Exit"><i class="menu-icon fa fa-save grey"></i></a>&nbsp;
			</font>
		</span>
		<span class="pdf" style="display:none; border-left: 1px solid rgba(245, 255, 255, 1);border-right: 1px solid rgba(0, 0, 0, 0.15);">
			<font size="2em" color="grey" style="text-decoration:none;">
			&nbsp;&nbsp;<a id="pdf" style="cursor:pointer" data-toggle="tooltip" title="Save & Preview"><i class="menu-icon fa fa-save grey"></i> <i class="menu-icon fa fa-plus grey"></i> <i class="menu-icon fa fa-file-pdf-o grey"></i></a>&nbsp;
			</font>
		</span>	
		
		&nbsp;
		<span class="panel_title_text" style="display:none;">
		<?php echo ucwords((!empty($form_var['title']))?$form_var['title']:'Please defind the page title'); ?>
		</span>
		<script>$(document).ready(function(){$('.panel_title_text').fadeIn();});</script>	
	</h4>
</div>
<div class="panel-body">	
	<?php if(!empty($form_var['msg'])):?>
		<?php foreach ($form_var['msg'] as $type => $str): ?>
			<?php if(!empty($str)):?>					
				<?php $i=0; foreach($str as $msg): ?>
					<?php ($type == 'error_msg')?$msgClass = 'danger':'';?>
					<?php ($type == 'warning_msg')?$msgClass = 'warning':'';?>
					<?php ($type == 'default_msg')?$msgClass = 'success':'';?>
					<div id="<?php echo $type.'_'.$i; ?>" class="alert alert-<?php echo $msgClass; ?>" role="alert"><?php echo $msg; ?></div>
					<script> setTimeout(function(){$('#<?php echo $type.'_'.$i;?>').fadeOut();},10000); </script>		
					<?php $i = $i+1; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php endif; ?>
	

	<?php //flash data ?>			
	<?php if($this->session->flashdata('msg') != ''):?>
		<div id="success" class="alert alert-success" role="alert"><?php echo $this->session->flashdata('msg'); ?></div>
		<script> setTimeout(function(){$('#success').fadeOut();},2000); </script>
	<?php endif; ?>
	<?php if($this->session->flashdata('error_msg') != ''):?>
		<div id="error_msg" class="alert alert-danger" role="alert"><?php echo $this->session->flashdata('error_msg'); ?></div>
		<script> setTimeout(function(){$('#error_msg').fadeOut();},10000); </script>
	<?php endif; ?>
	<?php if($this->session->flashdata('warning_msg') != ''):?>
		<div id="error_msg" class="alert alert-warning" role="alert"><?php echo $this->session->flashdata('warning_msg'); ?></div>
		<script> setTimeout(function(){$('#error_msg').fadeOut();},2000); </script>
	<?php endif; ?>
</div> 
