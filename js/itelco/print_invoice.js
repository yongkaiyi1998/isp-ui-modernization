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
	var customer_no				= label_customer_no 			= ''; 	
	var addr					= label_addr					= '';
	var city					= label_city					= '';
	var state					= label_state					= '';
	var postcode				= label_postcode				= '';	
	
	var html_head				="<li class='list-group-item'><span class='label label-blank label-left to_capitalized'>";
	var html_mid				="</span><span>: ";
	var html_tail				="</span></li>";
	
	var customer_name			= ''; //col 2 line 1
	var reg_no 					= ''; //col 2 line 1
	var	inv_addr1				= ''; //col 2 line 2
	var	inv_addr2				= ''; //col 2 line 2	
	var	inv_postcode			= ''; //col 2 line 3	
	var	inv_city				= ''; //col 2 line 3			
	var	inv_state				= ''; //col 2 line 4		
	
	var label_tel 				= ''; //col 2 set 2 line 1
	var label_fax 				= ''; //col 2 set 2 line 1
	var label_attn 				= ''; //col 2 set 2 line 2
	
	
	
	for (i = 0; i < counter; i++)
	{			
		// col 2 lines set 1		
		customer_name 					= $('#customer\\[customer_name\\]').eq(i);		
		reg_no 							= $('#customer\\[reg_no\\]').eq(i);	
			
		inst_addr1						= $('#customer\\[inst_addr1\\]').eq(i);
		inst_addr2						= $('#customer\\[inst_addr2\\]').eq(i);		
		inst_city						= $('#customer\\[inst_city\\]').eq(i);
		inst_postcode					= $('#customer\\[inst_postcode\\]').eq(i);
		inst_state						= $('#customer\\[inst_state\\]').eq(i);		
		
		
		bill_addr1						= $('#customer\\[bill_addr1\\]').eq(i);
		bill_addr2						= $('#customer\\[bill_addr2\\]').eq(i);
		bill_city						= $('#customer\\[bill_city\\]').eq(i);
		bill_postcode					= $('#customer\\[bill_postcode\\]').eq(i);
		bill_state						= $('#customer\\[bill_state\\]').eq(i);
		
		$('.invoice_detail').eq(i).find('.taxTotal').text($('#customer\\[tax_charge\\]').eq(i).val());
		$('.invoice_detail').eq(i).find('.sTotal').text($('#customer\\[charge\\]').eq(i).val());
		$('.invoice_detail').eq(i).find('.gTotal').text($('#customer\\[amount\\]').eq(i).val());
		
		
		
		if(bill_addr1.val() != ''){		
			inv_addr1					= bill_addr1.val();
			inv_addr2					= bill_addr2.val();
			inv_city					= bill_city.val();		
			inv_postcode				= bill_postcode.val();		
			inv_state					= bill_state.val();	
		}else{
			inv_addr1					= inst_addr1.val();		
			inv_addr2					= inst_addr2.val();		
			inv_city					= inst_city.val();		
			inv_postcode				= inst_postcode.val();		
			inv_state					= inst_state.val();	
		}
		
		
		
		receiver_name					= customer_name.val()+' ('+reg_no.val()+') ';
		addr							= inv_addr1;
		if(inv_addr2 != '') 			addr += '<br>'+inv_addr2;		
		postcode						= inv_postcode;		
		city							= inv_city;
		state							= inv_state
		
		// col 2 lines set 2	
		label_tel 						= 'Tel : ';
		label_fax 						= 'Fax : ';	
		label_attn						= 'Attn : ';		
		inv_tel_num						= $('#customer\\[tel_num\\]').eq(i);
		inv_fax_num						= $('#customer\\[fax_num\\]').eq(i);
		inv_attn1						= $('#customer\\[pic_name\\]').eq(i);
		inv_attn2						= $('#customer\\[bill_name\\]').eq(i);		
		inv_attn						= '';	
		tel_val							= "<span style='padding-left:10em;'></span>";
		if(inv_tel_num.val() != '') tel_val = inv_tel_num.val()+'&nbsp;&nbsp;';			
		inv_attn_val					= '';	
		inv_attn_val					+= inv_attn1.val();			
		//~ inv_attn_val				+= inv_attn2.val();		
		//~ if((inv_attn1.val() != '') && (inv_attn2.val() != '')) inv_attn_val = inv_attn1.val() + ' / ' +inv_attn2.val();
					
		// col 3 lines 		
		inv_inv_no						= $('#customer\\[bill_no\\]').eq(i);		
		inv_date						= $('#customer\\[bill_date\\]').eq(i);		
		inv_payment_term				= $('#customer\\[payment_term\\]').eq(i);		
		inv_sub_no						= $('#customer\\[customer_no\\]').eq(i);		
		
		label_tax 						= 'GST Reg. No';
		label_inv_no 					= 'Invoice No.';
		label_inv_date					= 'Invoice Date';
		label_payment_term				= 'payment term';
		label_sub_no					= 'Subscriber No';	
		
		inv_date_val					= inv_date.val();
		inv_payment_term_val			= inv_payment_term.val();		
		if(inv_payment_term.val() == '0') inv_payment_term_val = 'COD';	
			
		
		html 	=  "<div class='text-left to_uppercase'>"	+receiver_name			+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+addr					+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+postcode+" "+city		+"</div>";
		html 	+= "<div class='text-left to_uppercase'>"	+state					+"</div>";				
		
		html1 	= html_head		+label_tel		+tel_val	+label_fax	+inv_fax_num.val()	+html_tail;
		html1 	+= html_head	+label_attn		+inv_attn_val								+html_tail;
					
		html2 	= html_head		+label_tax				+html_mid	+''							+html_tail;
		html2 	+= html_head	+label_inv_no			+html_mid	+inv_inv_no.val()			+html_tail;
		html2 	+= html_head	+label_inv_date			+html_mid	+inv_date_val				+html_tail;
		html2 	+= html_head	+label_payment_term		+html_mid	+inv_payment_term_val		+html_tail;
		html2 	+= html_head	+label_sub_no			+html_mid	+inv_sub_no.val()			+html_tail;
		
		
		
		$( "div.content_customer_info_1_top" ).eq(i).append(html);
		$( "ul.content_customer_info_1_bottom" ).eq(i).append(html1);
		$( "ul.content_customer_info_2" ).eq(i).append(html2);
	}	
}

function generate_header()
{
	//~ if(typeof letterhead_img == 'undefined') letterhead_img = base_url+'/images/logo_letterhead.jpg';
	if(typeof letterhead_img == 'undefined') letterhead_img = 'https://placehold.it/350x120?text=letterhead';
	
	$( "div.content_header" ).html("<div class='left'><img src='"+letterhead_img+"' /></div>");
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


