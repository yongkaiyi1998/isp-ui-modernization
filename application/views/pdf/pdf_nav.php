<style type="text/css" media="print">
	.noprint
	{ 
		display: none; 	
	}
</style>
<nav class="navbar navbar-default navbar-fixed-top noprint">
  <div class="container">
	<div class="navbar-header" style="margin-top:4px;">
	  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
		<span class="sr-only">Toggle navigation</span>
		<span class="icon-bar"></span>
		<span class="icon-bar"></span>
		<span class="icon-bar"> </span>
	  </button>
	  <div class="navbar-brand">
		  <a class="btn_print_back2" onclick="window.history.back()" style="cursor:pointer" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_back')); ?>">
		   <i class="menu-icon fa fa-chevron-left fa-1x grey"></i>
		  </a>
		  &nbsp;
		  &nbsp;
	  	  <?php //if(!empty($show_send_btn) && !isset($show_report_print_btn)):?>
			  <!--<a  href="#" onclick="window.print()" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_print')); ?>" id="print-btn" data-id="<?php echo $doc_ref; ?>">
			   <i class="btn_print_document menu-icon fa fa-print light-red"></i>
			  </a>-->
		  <?php //endif; ?>
	  	  <?php //if(isset($show_report_print_btn)):?>
	  	  	  <?php //if(!empty($show_report_print_btn)):?>
	  	  	  <!--<style type="text/css" media="print">
	  	  	  	  	@page { size: landscape; }
	  	  	  	  	@media print {
					    			html, body {
					        		height: 99%;    
					    			}
								}
	  	  	  </style>
				  <a  href="#" onclick="window.print()" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_print')); ?>" id="print-btn" data-id="<?php echo $doc_ref; ?>">
				   <i class="btn_print_document menu-icon fa fa-print light-red"></i>
				  </a>-->
			  <?php //endif; ?>
		  <?php //endif; ?>
		  &nbsp;
		  &nbsp;

	  	<?php //if(!empty($show_send_btn)):?>
		  <!--<a class="" style="cursor:pointer" data-toggle="modal" data-target="#contactModal" title="Send" data-backdrop="static" data-keyboard="false" id="send-btn" data-id="<?php echo $doc_ref; ?>">
			 <i class="menu-icon fa fa-send-o grey"></i>
		  </a>-->
			<?php //include_once( APPPATH . 'views/templates/modal_html_contact.php'); ?>
			<?php //endif; ?>

	  </div>
	</div>
  </div>
</nav>
