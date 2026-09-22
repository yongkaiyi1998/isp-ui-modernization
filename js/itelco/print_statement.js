function auto_print(has_auto_print)
{
	if(has_auto_print == 1) window.print();	
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
	}		
}

function generate_customer_info()
{
	var counter					= $('.page').length;
	var html 					= '';
	var html1 					= '';
	var html2 					= '';
	var html3 					= '';
	var html4 					= '';
	var customer_no				= label_customer_no 			= '';
	var customer_name			= label_customer_name			= '';
	var customer_login_username	= label_customer_login_username	= '';
	var customer_package		= label_customer_package		= '';
	var bill_no 				= label_bill_no					= '';
	var bill_date 				= label_bill_date				= '';
	var bill_period				= label_bill_period				= '';
	var bill_due_date			= label_bill_due_date			= '';
	var subscriber_no			= label_subscriber_no			= '';
	
	var	inst_addr1				= '';
	var	inst_addr2				= '';		
	var	inst_city				= '';		
	var	inst_postcode			= '';		
	var	inst_state				= '';		
		
	var	bill_name				= '';		
	var	bill_addr1				= '';		
	var	bill_addr2				= '';		
	var	bill_city				= '';		
	var	bill_postcode			= '';		
	var	bill_state				= '';		
	var	bill_toggle				= '';
	
	var addr					= label_addr					= '';
	var city					= label_city					= '';
	var state					= label_state					= '';
	var postcode				= label_postcode				= '';	
	
	var html_head				="<li class='list-group-item'><span class='label label-blank label-left to_capitalized'>";
	var html_mid				="</span><span>: ";
	var html_tail				="</span></li>";
	
	for (i = 0; i < counter; i++)
	{	
		customer_login_username			= $('#customer\\[login_username\\]').eq(i);	
		customer_no 					= $('#customer\\[customer_no\\]').eq(i);
		customer_package				= $('#customer\\[package\\]').eq(i);
		bill_date 						= $('#bill\\[bill_date\\]').eq(i);			
		bill_no 						= $('#bill\\[bill_no\\]').eq(i);
		bill_due_date 					= $('#bill\\[bill_due_date\\]').eq(i);
		bill_period						= bill_period_arr[i];
					
		customer_name 					= $('#customer\\[name\\]').eq(i);
		inst_addr1						= $('#customer\\[inst_addr1\\]').eq(i);
		inst_addr2						= $('#customer\\[inst_addr2\\]').eq(i);		
		inst_city						= $('#customer\\[inst_city\\]').eq(i);
		inst_postcode					= $('#customer\\[inst_postcode\\]').eq(i);
		inst_state						= $('#customer\\[inst_state\\]').eq(i);		
		
		bill_name						= $('#customer\\[bill_name\\]').eq(i);
		bill_addr1						= $('#customer\\[bill_addr1\\]').eq(i);
		bill_addr2						= $('#customer\\[bill_addr2\\]').eq(i);
		bill_city						= $('#customer\\[bill_city\\]').eq(i);
		bill_postcode					= $('#customer\\[bill_postcode\\]').eq(i);
		bill_state						= $('#customer\\[bill_state\\]').eq(i);		
		bill_toggle						= 0;
		
		receiver_name					= customer_name.val();
		addr							= inst_addr1.val()+' '+inst_addr2.val();
		city							= inst_city.val();
		postcode						= inst_postcode.val();
		state							= inst_state.val();
		
		if(bill_toggle	!= 0){			
			receiver_name					= bill_name.val();
			addr							= bill_addr1.val()+' '+bill_addr2.val();
			city							= bill_city.val();
			postcode						= bill_postcode.val();
			state							= bill_state.val();
		}
		
		//label thats been rename or used in data-label
		label_customer_login_username	= 'user name';
		label_bill_period				= 'bill period';
		label_customer_no 				= 'subscriber no';
		label_bill_due_date				= 'Pay on or Before'; 
		label_customer_package			= customer_package.attr('data-label');
		label_bill_no					= bill_no.attr('data-label');
		label_bill_date					= bill_date.attr('data-label');			
		
		html 	=  "<div class='text-left to_uppercase'>"	+receiver_name			+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+addr					+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+city					+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+postcode+" "+state		+"</div>";				
		
		html1 	= html_head		+label_customer_no				+html_mid	+customer_no.val()				+html_tail;
			
		html2 	= html_head		+label_customer_package			+html_mid	+customer_package.val()			+html_tail;
		html2 	+=html_head		+label_bill_no					+html_mid	+bill_no.val()					+html_tail;
		html2	+=html_head		+label_bill_date				+html_mid	+bill_date.val()				+html_tail;
		html2 	+=html_head		+label_bill_period				+html_mid	+bill_period					+html_tail;
		html2 	+=html_head		+label_bill_due_date			+html_mid	+bill_due_date.val()			+html_tail;
		html2 	+=html_head		+label_customer_login_username	+html_mid	+customer_login_username.val()	+html_tail;
		
		$( "div.content_customer_info_1_top" ).eq(i).append(html);
		$( "ul.content_customer_info_1_bottom" ).eq(i).append(html1);
		$( "ul.content_customer_info_2" ).eq(i).append(html2);
	}	
	
}

function generate_header()
{
	//~ if(typeof letterhead_img == 'undefined') letterhead_img = base_url+'/images/logo_letterhead.jpg';
	if(typeof letterhead_img == 'undefined') letterhead_img = 'https://placehold.it/350x120?text=letterhead';
	
	$( "div.content_header" ).html("<div class='center'><img src='"+letterhead_img+"' /></div>");
}


function generate_footer()
{
	if(typeof footer_txt_1_path == 'undefined') footer_txt_1_path 	= base_url+"content/txt/txt_bill_statement.txt";
	if(typeof footer_txt_2 == 'undefined') 		footer_txt_2 		='SERVICE PROVIDED BY XXXXXX SDN.BHD.';
	
	
	$.ajax({
		url : footer_txt_1_path,
		dataType: "text",
		success : function (data) {
			$(".content_footer_info").html("<span class='footer_txt_1_path'>"+data+"<span>");
		}
	});
	
	$(".content_footer_info2").html(footer_txt_2);	
}


