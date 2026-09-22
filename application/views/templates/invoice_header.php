<div class="invoice-header">
	<h2>
		<div>
			<div class="pull-right">
				<img src="
				<?php echo (empty($config['comp_logo']))?'<font color = "red">N/A</font>':$config['comp_logo']; ?>">
			</div>		
			<font size="6em">
				<b><?php echo  (empty($config['comp_name']))?'<font color = "red">N/A</font>':$config['comp_name']; ?></b>
			</font>
			<font size="-3em">
				(<?php echo (empty($config['comp_code']))?'<font color = "red">N/A</font>':$config['comp_code']; ?>)
			</font><br>						
			<font size="4em">
				<div><!--<div style="line-height:100%"></div>-->
					<b><i><?php echo  (empty($config['comp_slogan']))?'<font color = "red">N/A</font>':$config['comp_slogan']; ?></i></b>
				</div>
			</font>

		</div>
	</h2>
</div>
    			
