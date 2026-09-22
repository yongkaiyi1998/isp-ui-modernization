<style type="text/css" media="print"> .noprint { display: none; } </style>
<style>

#popupDetail {
    position: fixed;
    top: 40%;
    left: 50%;
    transform: translate(-50%, -40%);
    width: 60%;
    z-index:200;

    font-family: Verdana, Tahoma, sans-serif;

}

.ui-autocomplete {
	z-index: 200;
}
</style>

<?php if (isset($send_btn)) { ?>
<style>
#popupDetail h4 {
    font-size: 18px;
    font-weight: normal;
    font-family: "Open Sans", "Helvetica Neue", Helvetica, Arial, sans-serif;
}

#popupDetail .container {
	width: 100%;
}

#popupDetail .btn {
    display: inline-block;
    color: #FFF;
    text-shadow: 0 -1px 0 rgba(0, 0, 0, 0.25);
    background-image: none;
    /* border: 5px solid #FFF; */
    border-radius: 0;
    box-shadow: none;
    -webkit-transition: background-color 0.15s, border-color 0.15s, opacity 0.15s;
    -o-transition: background-color 0.15s, border-color 0.15s, opacity 0.15s;
    transition: background-color 0.15s, border-color 0.15s, opacity 0.15s;
    cursor: pointer;
    vertical-align: middle;
    margin: 0;
    position: relative;
    -webkit-border-radius: 3px;
    -moz-border-radius: 3px;
    border-radius: 3px;
}

#popupDetail .btn-info, .btn-info:focus {
    background-color: #3b96d3;
    border-color: #3b96d3;
}

#popupDetail .table > thead > tr {
    color: #fff;
    font-weight: normal;
    background: #289383;
    /* background: #2d457f; */
}

#popupDetail .table > thead > tr > th, 
#popupDetail .table > tbody > tr > th, 
#popupDetail .table > tfoot > tr > th, 
#popupDetail .table > thead > tr > td, 
#popupDetail .table > tbody > tr > td, 
#popupDetail .table > tfoot > tr > td {
    padding: 4px 2px;
    line-height: normal;
    font-size: 15px;
    font-weight: normal;
}

#popupDetail .form-control {
    height: 25px;
    padding:0px;
}

.ui-menu {
    -webkit-box-sizing: content-box;
    -moz-box-sizing: content-box;
    box-sizing: content-box;
    width: 150px;
    -webkit-box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    background-color: #FFF;
    border: 1px solid rgba(0, 0, 0, 0.2);
    padding: 3px;
}

.ui-menu .ui-menu-item {
    padding: 5px 10px 6px;
    color: #444;
    cursor: pointer;
    display: block;
    -webkit-box-sizing: inherit;
    -moz-box-sizing: inherit;
    box-sizing: inherit;
}

.ui-menu .ui-menu-item:hover,
.ui-menu .ui-state-focus,
.ui-menu .ui-state-active,
.ui-menu .ui-menu-item:hover > .ui-menu-icon,
.ui-menu .ui-state-focus > .ui-menu-icon,
.ui-menu .ui-state-active > .ui-menu-icon {
  text-decoration: none;
  background-color: #4f99c6;
  color: #FFF;
  margin: auto;
  font-weight: normal;
}
.ui-menu .ui-menu-item:hover .ui-menu-icon,
.ui-menu .ui-state-focus .ui-menu-icon,
.ui-menu .ui-state-active .ui-menu-icon,
.ui-menu .ui-menu-item:hover > .ui-menu-icon .ui-menu-icon,
.ui-menu .ui-state-focus > .ui-menu-icon .ui-menu-icon,
.ui-menu .ui-state-active > .ui-menu-icon .ui-menu-icon {
  color: #FFF;
}

.msg-print .alert {
    padding: 10px;
    margin-bottom: 0px;
    border: 1px solid transparent;
    border-radius: 4px;
    position: relative;
    top: -32px;
    left: 160px;
}

</style>
<?php } ?>

<nav class="navbar navbar-default navbar-fixed-top">
  <div class="container">
	<div class="navbar-header" style="margin-top:4px;">
	  <div class="navbar-brand">
		  <a  style="cursor:pointer" onclick="closeWindowIfHistoryIsEmpty();" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_back')); ?>">
		   <i class="menu-icon fa fa-chevron-left fa-1x grey"></i>
		  </a>
          <?php if (isset($equip_btn)) { ?>
       &nbsp;
       &nbsp;
          <a style="cursor: pointer;" onclick="open_equipment_popup();" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_equipment')); ?>">
           <i class="fa fa-wrench" aria-hidden="true"></i>
          </a>
            <?php } ?>
          &nbsp;
          &nbsp;
		  <a style="cursor: pointer;" onclick="window.print()" data-toggle="tooltip" title="<?php echo ucwords(lang('tooltip_print')); ?>">
		   <i class="menu-icon fa fa-print light-red"></i>
		  </a>
		  <?php if (isset($send_btn)) { ?>
        &nbsp;
        &nbsp;
            <input type="hidden" id="trigger_include_customer" name="trigger_include_customer" value="<?php if (isset($include_customer)) { echo $include_customer; } else { echo "0"; } ?>" />
            <a style="cursor: pointer;" onclick="open_send_popup();" data-toggle="tooltip" title="Send">
              <i class="menu-icon fa fa-send grey"></i>
            </a>

            &nbsp;
            &nbsp;
            <form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
              <input type="hidden" id="contact_list" name="contact_list" value="" />
            </form>

		  <?php } ?>

        <div class="msg-print">
            <?php if (isset($current_status) && isset($current_termination_flow)) { ?>
                <?php if (!empty($current_status) && $current_termination_flow != 'A') { ?>
                    <?php if ($current_termination_flow == 'F') { ?>
                        <div id="success" class="alert alert-danger" role="alert"><?php echo $current_status; ?> - Reason:<?php echo $reject_reason; ?></div>
                    <?php } else { ?>
                        <div id="success" class="alert alert-success" role="alert"><?php echo $current_status; ?></div>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
      
	  </div>
	</div>     
  </div>
</nav>

<div id="popupDetail">
	<div id="popupDetailStd">
		<div id="popupContent">
			&nbsp;
		</div>
	</div>
</div>
<div id="backgroundPopup"></div>

<script>

var base_url = '<?php echo $this->config->item('base_url'); ?>';
var default_send_user = '';

function open_send_popup() {
	show_popup2('report/send_list/', '');
}
</script>

<script>
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }

    function closeWindowIfHistoryIsEmpty() {
      if (window.history.length <= 1) {
        window.close();
      } else {
        window.history.back();
      }
    }

</script>

<?php if (isset($report_send)) { ?>
<?php if ($report_send == 1) { ?>
<script>
	alert('Report sent.');
</script>
<?php } ?>
<?php } ?>

<?php if (isset($job_order_send)) { ?>
<?php if ($job_order_send == 1) { ?>
<script>
	alert('Job Order sent.');
</script>
<?php } ?>
<?php } ?>

<?php if (!empty($default_send_user)) { ?>
<script>
	var default_send_user = JSON.parse('<?php echo $default_send_user ?>');
</script>
<?php } ?>