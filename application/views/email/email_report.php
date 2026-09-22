<style>
@media print{@page {size: landscape}}
</style>

<div class="container">
	<div class="panel-default">
		<div class="panel-heading noprint panel_fontsize hidden-print">
			<span class="panel_space float_right">
				<a href="#" onclick="window.history.back()">
					<i class="menu-icon fa fa-times red" <?php echo tooltip_helper('Cancel / Discard'); ?>></i>
				</a>
			</span>
			<?php echo $page_title; ?>
		</div>
		<div class="panel-body">
			<div class="bg-success filter-bar hidden-print">
				<?php echo flash_data_helper($msg); ?>
				<form id="report" name="report" method="post" class="filter-form" action="<?php echo $form_action; ?>">
					<input type="hidden" id="is_postback" name="is_postback" value=true>
					
					<div class="input-group" style="float:left">
					<span>From </span>
					<input style='width:80px;' id="date_from" name="date_from" placeholder="Date from" autocomplete="off" 
						value="<?php echo $date_from; ?>" />
					
					<span>To </span>
					<input style='width:80px;' id="date_to" name="date_to" placeholder="Date to" autocomplete="off"
						value="<?php echo $date_to; ?>" />
						
						
					<span>Title</span>
					</div>
					<!--
					<span>Title  </span>
					<input style='width:300px' id="email_title" name="email_title" placeholder="" value="<?php echo $email_title; ?>" />
					<i class="ace-icon fa fa-times red" id="clear_customer" style="margin-top:5px;cursor:pointer;"></i>
					-->

					<div class="input-group" style="float:left;margin-right:25px;">
						<span class="input-icon input-icon-right" style="display:inline;">
							<span class="ui-helper-hidden-accessible" aria-live="polite" role="status"></span>
							<input 	autocomplete="off" class="form-control ui-autocomplete-input" 
									id="email_title" name="email_title" placeholder="" 
									value="<?php echo $email_title;  ?>" 
									type="text" style="width:600px;" />
									
							<i 	class="ace-icon fa fa-times red" id="clear_title" 
								style="top:-2px;cursor:pointer;"></i>
						</span>
								
						<input class="form-control" id="scheduler_id" name="scheduler_id" value="<?php echo $scheduler_id; ?>" type="hidden" />
					</div>
					
					<button  id="btFilter" name="btFilter" type="button" value="filter" class="btn btn-success" onclick="ajax_filter()">
						<i class="menu-icon fa fa-filter white" data-toggle="tooltip" title=""></i>
						Filter
					</button>
					<button  id="btClear" name="btClear" type="button" value="clear" class="btn btn-info" onclick="ajax_clear()">
						<i class="menu-icon fa fa-times white" data-toggle="tooltip" title=""></i>
						Clear
					</button>
				</form>
			</div>

			<div class="table_rows_area"><?php echo $row_html; ?></div>
			
		</div>
	</div>
</div>

<script>
$("#date_from").datepicker({format: 'yyyy-mm-dd'});
$("#date_to").datepicker({format: 'yyyy-mm-dd'});

$( "#email_title" ).autocomplete({
	source: function( request, response ) {
		$.ajax({
			type: "POST",
			url: base_url + "email/autocomplete_load_email_title",
			dataType: "json",
			data: { 
					keyword		: request.term ,
					date_from  	: $('#date_from').val(),
					date_to		: $('#date_to').val()
				  },
			success: function( data ) {
				console.log(data);
				var transformed = $.map(data, function (el) {
					return {
							label		: el.email_title + " scheduled on " + el.created_date ,
							id			: el.scheduler_id,
							val			: el
					};
				});					
				response(transformed);
			},
			error: function (xhr, ajaxOptions, thrownError) {
				response([]);
				console.log(xhr);
				console.log(ajaxOptions);
				console.log(thrownError);
				
			}
		});
	},
	messages: {
		noResults: '',
		results: function() {}
	},
	select: function (event, ui) {			
		console.log(ui.item.val);
		$("#scheduler_id").val( ui.item.val.scheduler_id );
		$("#email_title").attr("readonly" , true);
		
	}
	
});

$( document ).on( "click", "#clear_title", function(e){
	$("#email_title").val( "" );
	$("#email_title").attr("readonly" , false);
	$("#scheduler_id").val( "" );
	
});
	
function ajax_filter() {
	//post values

	$.ajax({
		type: "POST",
		url: base_url + "email/email_report_rows/0",
		data: { 
			date_from: $("#date_from").val(),
			date_to: $("#date_to").val(),
			email_title: $("#email_title").val(),
			scheduler_id: $("#scheduler_id").val()
		},
		success: function (data) {
			$('.table_rows_area').html(data);
		},
		error: function (xhr, ajaxOptions, thrownError) {
			console.log(xhr);
			console.log(ajaxOptions);
			console.log(thrownError);
		}
	});
}

function ajax_clear() {
	let today = new Date();
    let formatDate = d => `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    let todayDate = formatDate(new Date(today.getFullYear(), today.getMonth(), today.getDate()));

	$("#date_from").val(todayDate);
	$("#date_to").val(todayDate);
	$("#date_from").datepicker('setDate', todayDate);
	$("#date_to").datepicker('setDate', todayDate);
	$("#email_title").val('');
	$("#scheduler_id").val('');

	ajax_filter();
	ajax_clear_session('email', 'email_report_filter');
}

</script>
