<div class="panel panel-default" style="border:none;">
	<div class="panel-body">
		<div class="col-lg-6" >
			<i>
				<p><b>Payment Information:</b></p>
				<p style="padding-left:4em">
					Please make cheque payable to “<?php echo  (empty($config['comp_name']))?'<font color = "red">N/A</font>':$config['comp_name']; ?>”
					<br><?php echo  (empty($config['payment_info']))?'<font color = "red">N/A</font>':$config['payment_info']; ?>
				</p>
			</i>
		</div>
	</div>	
</div>

<div class="panel panel-default">
		<div class="panel-body">
			
			 <div class="col-lg-6" >
				<p>&nbsp;
<!--
					Some Contents Here...
-->
				</p>
			</div>

			<div class="col-lg-6" style="padding-left:20px; border-left: 1px solid #ccc;" >
				<p><b> Approved By:</b><br>&nbsp;<br>
				<span style="padding-left:4em"><i>Computer generated document, no signature required</i></span>
				<br>
				<span style="padding-left:8em" ><i>DOC ID: <?php //echo (empty($preview['invoice_num']))?'<font color = "red">N/A</font>':$preview['invoice_num']; ?></i></span>								
				</p>
			</div>
		</div>
</div>
<hr>
<div class="col-lg-12 text-center">
	<?php echo  (empty($config['comp_addr_1']))?'<font color = "red">N/A</font>':$config['comp_addr_1']; ?>
	<?php echo  (empty($config['comp_addr_2']))?'<font color = "red">N/A</font>':$config['comp_addr_2']; ?>
	<?php echo  (empty($config['comp_addr_3']))?'<font color = "red">N/A</font>':$config['comp_addr_3']; ?>
	<br>			
	Tel : <?php echo  (empty($config['comp_tel']))?'<font color = "red">N/A</font>':$config['comp_tel']; ?>&nbsp;&nbsp;
	Fax : <?php echo  (empty($config['comp_fax']))?'<font color = "red">N/A</font>':$config['comp_fax']; ?>
</div>


