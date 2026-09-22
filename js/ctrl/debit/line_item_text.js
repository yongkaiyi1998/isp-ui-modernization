function generate_table_rows(ln, i, dropoption){
	console.log(dropoption);
var the_html =  
'<td class="text-center clear-style noprint"><input tabindex="-1" style="margin: 0px;" class="case" data-toggle="tooltip" title="check to move / delete"  type="checkbox"/></td>'+
'<td class="text-center noprint">'+
'	<i data-id="'+ ln +'" class="move-up menu-icon point-cursor fa fa-chevron-circle-up blue" onclick="upAction('+ ln +');"></i>'+

	

'	<i class="menu-icon point-cursor fa fa-minus-square red" onclick="deleteAction('+ ln +');"></i>'+
	
'	<i data-id="'+ ln +'" id="processtype[del_item]['+ ln +']"  class="move-down menu-icon disabled-cursor fa fa-chevron-circle-down light-grey" onClick="downAction('+ ln +');" ></i>'+
'	<button id="processtype[mov_down]['+ ln +']" name="processtype[mov_down]" type="submit" value="'+ ln +'" hidden></button>'+
	

'</td>'+	


'<td class="text-center invoice-td-lnum">'+
'<input class="text-left invoice-input-item" type="text" maxlength="5" id="'+db_table_detail+'['+ i +'][num]" name="'+db_table_detail+'['+ i +'][num]" placeholder="No." value="" autofocus>'+

'<input type="hidden" class="text-center " name="'+db_table_detail+'['+ i +'][line_num]" value="'+ ln +'">'+

'</td>'+

'<td class="text-center">'+
'	<input type="text" class="text-left invoice-input-item" placeholder="Product Description" id="'+db_table_detail+'['+ i +'][desc]" name="'+db_table_detail+'['+ i +'][desc]" value="">'+					
'</td>'+	
		
'<td class="text-right invoice-td-qty">'+					
'	<input onkeypress="return numberKeyPress(event, false);" onfocusout="qtyPriceOnChange('+ i +')"; onchange="calSubtotal('+ i +');calTxtype('+ i +');" type="number" class="text-right changesNo invoice-input-item" placeholder="qty" id="'+db_table_detail+'['+ i +'][quantity]" name="'+db_table_detail+'['+ i +'][quantity]" value="" min="0">'+					
'</td>'+						
			
'<td class="text-center invoice-td-uom">'+
'	<input type="text" class="text-center changesNo invoice-input-item" placeholder="UOM" id="'+db_table_detail+'['+ i +'][uom]" name="'+db_table_detail+'['+ i +'][uom]" value="">'+
'</td>'+	
		
'<td class="text-right invoice-td-price">'+	
'	<input onkeypress="return numberDotKeyPress(event, false);" onfocusout="qtyPriceOnChange('+ i +');priceFocusOut('+ i +')"; onchange="chgDecimal(this);calSubtotal('+ i +');calTxtype('+ i +');qtyPriceOnChange('+ i +');" type="number" class="text-right changesNo invoice-input-item" step="0.01" placeholder="0.00" id="'+db_table_detail+'['+ i +'][price]" name="'+db_table_detail+'['+ i +'][price]" value="">'+						
'</td>'+					
					
'<td class="text-right invoice-td-stotal">'+
	'<input tabindex="-1" class="text-right invoice-input-item" type="text" name="'+db_table_detail+'['+ i +'][subtotal]" id="'+db_table_detail+'['+ i +'][subtotal]" value="" readonly="">'+						
'</td>'+
	
'<td class="tax-toggle text-center noprint invoice-td-ttype">'+
	'<select data-onload="calTxtype('+ i +')" onfocusin="calTxtype('+ i +')" onfocusout="txTypeFocusOut('+ i +');" onchange="calTxtype('+ i +')" id="'+db_table_detail+'['+ i +'][tax_type]" name="'+db_table_detail+'['+ i +'][tax_type]" class="changesNo invoice-input-item" style="height: 25px;">'+		
	dropoption+			
	'<option data-txtype ="0.0" value="none" selected>-none-</option>'+
	'</select>'+
'</td>'+		
	
'<td class="tax-toggle text-right invoice-td-gst">'+
	'<input tabindex="-1" class="text-right invoice-input-item" type="text" name="'+db_table_detail+'['+ i +'][tax]" id="'+db_table_detail+'['+ i +'][tax]" value="" readonly="">'+
'</td>'+					
	
'<td class="text-right invoice-td-total">'+
	'<input tabindex="-1" class="text-right invoice-input-item" type="text" name="'+db_table_detail+'['+ i +'][total]" id="'+db_table_detail+'['+ i +'][total]" value="" readonly="">'+						
'</td>';
return the_html;
}
