function auto_print(has_auto_print)
{
	if(has_auto_print == 1) window.print();	
}

function listing_style()
{
	var inv_counter = $('.invoice_detail').length;
	var row_length	= '0';
	for(inv = 0; inv < inv_counter; inv++)
	{
		//cbox		
		$('.invoice_detail th').addClass('text-center');
		//~ $('.invoice_detail td').addClass('text-center');
		
		//item num
		$('.invoice_detail').eq(inv).find('th').eq(0).addClass('col-lg-1');
		$('.invoice_detail').eq(inv).find('td').eq(0).addClass('text-center');
		
		//Discription
		$('.invoice_detail').eq(inv).find('td').eq(1).addClass('text-left');
		
		//amount
		$('.invoice_detail').eq(inv).find('th').eq(2).addClass('col-lg-2');
		$('.invoice_detail').eq(inv).find('td').eq(2).attr('style','padding-right:0.5em;');
		$('.invoice_detail').eq(inv).find('td').eq(2).addClass('text-right');
		
		//tax code
		$('.invoice_detail').eq(inv).find('th').eq(3).addClass('col-lg-2');
		$('.invoice_detail').eq(inv).find('td').eq(3).addClass('text-center');	
	}	
}



function generate_receipt_info(){
	$( "span.cash_amount" ).html($('#customer\\[amount\\]').val());
	$('.customer_no').text($('#customer\\[customer_no\\]').val());	
	$('.customer_name').text($('#customer\\[customer_name\\]').val());	
	$('.package_name').text($('#customer\\[package_name\\]').val());	
	$('.payment_no').text($('#customer\\[payment_no\\]').val());	
	$('.tranx_date').text($('#customer\\[tranx_date\\]').val());	
	$('.email_1').text($('#customer\\[email_1\\]').val());	
	$('.payment_source_name').text($('#customer\\[payment_source_name\\]').val());	
	
	// convert mount value into words
	var amount_val 		= $('#customer\\[amount\\]').val();
	var amount_val_1 	= amount_val.split(".")[0];
	var amount_val_2 	= amount_val.split(".")[1];
	var amount_word_1 	= toWords(amount_val_1);
	var amount_word_2 	= '';
	
	if( amount_val_2 != '00') amount_word_2 = ' and '+toWords(amount_val_2)+' cents';		
	$('.amount_words').text(amount_word_1 + amount_word_2 + ' only');	
	$('.amount_words').css('textTransform', 'capitalize');
	
}









function generate_account_details()
{
	var counter					= $('.page').length;
	var bill_detail_counter 	= '';
	
	var html 					= '';	
	var html1					= '';	
	var html2 					= '';	
	var html3 					= '';
	var html4 					= '';	
	
	var label_previous_balance	= '';	
	var label_payment_received	= '';
	var label_balance			= '';
	
	var html_head				="<li class='list-group-item'><span class='col-lg-4 label label-blank label-left to_capitalized'>";
	var html_mid				="</b></span><span  class='col-lg-1'><b>:</b></span><span  class='col-lg-6 pull-right'><span class='pull-right to_capitalized'>";
	var html_tail				="</span></span></li>";	
		
	var html_head_2				="<li class='list-group-item'><span class='col-lg-4 label label-blank label-left to_capitalized content_account_summary_title'>";
	var html_mid_2				="</b></span><span  class='col-lg-1'><b>:</b></span><span  class='col-lg-6 pull-right'><span class='pull-right to_capitalized content_account_summary_title'>";
	var html_tail_2				="</span></span></li>";
	
	
	for (i = 0; i < counter; i++)
	{		
		bill_detail_target 				= $('.content_body').eq(i).find('.bill_detail');
		bill_detail_counter 			= $('.content_body').eq(i).find('.bill_detail').length;
	
		previous_balance 				= $('#bill\\[previous_balance\\]').eq(i);
		payment_received 				= $('#bill\\[payment_received\\]').eq(i);		
		balance			 				= $('#bill\\[balance\\]').eq(i);
		amount			 				= $('#bill\\[amount\\]').eq(i);
		tax_charges			 			= $('#bill\\[tax_charges\\]').eq(i);
						
		label_total_payment_received	= "total payment received";	
		label_amount					= amount.attr('data-label');
		label_tax_charges				= tax_charges.attr('data-label');
		label_previous_balance			= previous_balance.attr('data-label');	
		label_payment_received			= payment_received.attr('data-label');	
		label_balance					= balance.attr('data-label');	
		
		html		= html_head		+label_previous_balance		+html_mid	+previous_balance.val() 	+html_tail;
		html		+=html_head		+label_payment_received		+html_mid	+payment_received.val() 	+html_tail;
				
		html2	='';
		if(bill_detail_counter != 0)
		{	
			for (j = 0; j < bill_detail_counter; j++)
			{
				//loop out all bill details bill type and bill ammount
				bill_detail_label	=  bill_detail_target.eq(j).attr('data-label');
				bill_detail_value 	=  bill_detail_target.eq(j).val();				
				html2				+= html_head	+bill_detail_label	+html_mid	+bill_detail_value 	+html_tail;
				
				if(bill_detail_label == 'Subscription Fee'){					
					console.log("**********************************Subscription Fee=>remark for this document");
					console.log(bill_detail_target.eq(j).attr('data-remark'));
					bill_period_arr.push(bill_detail_target.eq(j).attr('data-remark'));
				}		
				
			}					
		}else{			
			console.log("**********************************Subscription Fee=>remark for this document");
			console.log("No Subscription Fee");
			bill_period_arr.push("");
		}		
		html2		+= 	html_head		+label_amount					+html_mid		+amount.val() 				+html_tail;	
		html2		+= 	html_head		+label_tax_charges				+html_mid		+tax_charges.val() 			+html_tail;			
		
		html3		= 	html_head		+label_total_payment_received	+html_mid		+payment_received.val() 	+html_tail;			
		html4		= 	html_head_2		+'&nbsp;&nbsp;&nbsp;'+label_balance				+html_mid_2					+balance.val()				+html_tail_2;	
			
		$( "ul.content_account_details_1" ).eq(i).append(html);
		$( "ul.content_account_details_2" ).eq(i).append(html2);		
		$( "ul.content_account_details_3" ).eq(i).append(html3);
		$( "ul.content_account_details_4" ).eq(i).append(html4);
			
		$( "ul.content_account_details_5" ).eq(i).append(html4);	
	}		
}

function generate_header()
{
	var content_title = 'Official Receipt';
	//~ if(typeof letterhead_img == 'undefined') letterhead_img = base_url+'/images/logo_letterhead.jpg';
	if(typeof letterhead_img == 'undefined') letterhead_img = 'https://placehold.it/350x120?text=letterhead';
	
	$( "div.content_header" ).html("<div class='center'><img src='"+letterhead_img+"' /></div>");
	$( "h2.content_title" ).html("<div class='text-left'>&nbsp;"+content_title+"</div>");
}


function generate_footer()
{
	
	//~ if(typeof footer_txt_1_path == 'undefined') footer_txt_1_path 	= base_url+"content/txt/txt_bill_statement.txt";
	//~ if(typeof footer_txt_2 == 'undefined') 		footer_txt_2 		='SERVICE PROVIDED BY XXXXXX SDN.BHD.';
	//~ 
	//~ 
	//~ $.ajax({
		//~ url : footer_txt_1_path,
		//~ dataType: "text",
		//~ success : function (data) {
			//~ $(".content_footer_info").html("<span class='footer_txt_1_path'>"+data+"<span>");
		//~ }
	//~ });
	//~ 
	//~ $(".content_footer_info2").html(footer_txt_2);	
}






