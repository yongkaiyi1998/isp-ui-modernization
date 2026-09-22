// ========================================================== onChangeEvent ================================== 

$(document).on('change keyup blur','.changesNo',function(){	
	console.log("io: -> class: changesNo");
	calAll();
});

function qtyPriceOnChange(id){		
	console.log("event: -> qtyPriceOnChange(id)");
	
	var checkPrice = $('#'+db_table_detail+'\\['+id+'\\]\\[price\\]');	
	var checkQty = $('#'+db_table_detail+'\\['+id+'\\]\\[quantity\\]');	
	
	//~ console.log("checkPrice.val() is null?");
	//~ console.log(checkPrice.val() === '');
	//~ console.log("checkQty.val() is null?");
	//~ console.log(checkQty.val() === '');
	
	if(checkQty.val() === '' || checkPrice.val() === ''){
		//~ console.log($('#item-table .item-row').eq(id).find('select'));
		$('#item-table .item-row').eq(id).find('select').val('none');	
		$('#item-table .item-row').eq(id).find('input').eq(7).val('');//stotal
		$('#item-table .item-row').eq(id).find('input').eq(8).val('');//gst
		$('#item-table .item-row').eq(id).find('input').eq(9).val('');//total
			
	}	
}



// ========================================================== onfocusOutEvent ==================================

function priceFocusOut(id){
	console.log("event: -> priceFocusOut(id)");
	var lastLine = $('#item-table .item-row').length; 
	var currentLine = id+1; 
	var noGST = $('.config-tax input').eq(1).prop('checked');
	
	if(lastLine == currentLine){		
		if(noGST){
			console.log("||===>is last row, no gst");
		}else{
			console.log("||===>is last row, got gst");
		}
		//console.log('add new line'); 	
		//addAction();
	}else{
		console.log("||===>not last row");
	}
	console.log("end func: priceFocusOut() ");
}

function txTypeFocusOut(id){
	
	console.log("event: -> txTypeFocusOut(id)");
	var lastLine = $('#item-table .item-row').length; 
	var currentLine = id+1; 
				
	//~ console.log(lastLine); 	
	//~ console.log(currentLine);	
	
	calTxtype(id);
	if(lastLine == currentLine){
		//console.log('add new line'); 	
		addAction();
	}
}

function shipFeeFocusOut(e){
	
	console.log("event: -> txTypeFocusOut(e)");
	if(e.value == ''){		
		//var oldVal = element.value;
		console.log('is empty val, make 0.00');
		e.value = 0;
	}else{		
		//console.log(e.value);
		console.log('do nthg');
	}
	chgDecimal(e);
	calAll();
}


// ========================================================== Action Event ================================== 
function upAction(id) {	
	
	console.log("action: -> upAction("+id+")");
	if(id != 1) {
		var currentID 	= parseFloat(id)-parseFloat(1);
		var swapID 	= parseFloat(currentID)-parseFloat(1);
		
		var str = ['num','desc','quantity','uom','price','subtotal','tax_type','tax','total'];	
		var swap 	= [];
		var current = [];
		
		for (counter = 0; counter < str.length; counter++) {
			
			var this_id = str[counter];			
			//get All value from current ID and targeted swap ID 	
			swap[this_id]		= $('#'+db_table_detail+'\\['+swapID+'\\]\\['+this_id+'\\]').val();
			current[this_id]	= $('#'+db_table_detail+'\\['+currentID+'\\]\\['+this_id+'\\]').val();
			
			//get set value to each selector		
			$('#'+db_table_detail+'\\['+swapID+'\\]\\['+this_id+'\\]').val(current[this_id]);
			$('#'+db_table_detail+'\\['+currentID+'\\]\\['+this_id+'\\]').val(swap[this_id]);			
		}		
		console.log("moved!");
	}else{		
		console.log("do nthg!");
	}
			
}

function downAction(id) {							
	
	console.log("action: -> downAction("+id+")");
	
	var checkRow = $('#item-table .item-row').length;	
	if(id != checkRow) {		
		
		var currentID 	= parseFloat(id)-parseFloat(1);
		var swapID 	= id;
		
		var str = ['num','desc','quantity','uom','price','subtotal','tax_type','tax','total'];	
		var swap 	= [];
		var current = [];
		
		for (counter = 0; counter < str.length; counter++) {
			
			var this_id = str[counter];			
			//get All value from current ID and targeted swap ID	
			swap[this_id]		= $('#'+db_table_detail+'\\['+swapID+'\\]\\['+this_id+'\\]').val();
			
			console.log(swap[this_id]);
			
			current[this_id]	= $('#'+db_table_detail+'\\['+currentID+'\\]\\['+this_id+'\\]').val();
			
			
			console.log(current[this_id]);
			
			//get set value to each selector 	
			$('#'+db_table_detail+'\\['+swapID+'\\]\\['+this_id+'\\]').val(current[this_id]);
			$('#'+db_table_detail+'\\['+currentID+'\\]\\['+this_id+'\\]').val(swap[this_id]);						
		}		
		console.log("moved!");
	}else{		
		console.log("do nthg!");
	}
}	
	
// ========================================================== Page Action Event ================================== 
function addAction() {		
	console.log("action: -> addAction()");

	var i		=$('#item-table .item-row').length;
	var ln 		= i+1;	
	var var_a	= generate_table_rows(ln, i, '');	
	
	if (typeof dropoption != 'undefined'){		
		var_a	= generate_table_rows(ln, i, dropoption);	
	}
	
	html	= "<tr class='item-row'>" 
			+ var_a;
	html 	+="</tr>";
	
	//~ console.log(html);
	
	$('#item-table tbody').append(html);		
		
	var toFocus = '#'+db_table_detail+'\\['+i+'\\]\\[num\\]';
	$(toFocus).focus();	
	
	i++;	
	
	console.log('+++++++++++check is GST++++++++');
	var isGSTCheck = $('.config-tax input').eq(1).prop('checked');
	if(isGSTCheck){
		taxToggle(0);					
	}else{
		taxToggle(1);
	}
	disableOnlyLastButon();	
}


	
function deleteAction(id) {	
	console.log("action: -> deleteAction("+id+")");
	var checkTableLength = $('#item-table .item-row').length;
	console.log("action: ||===> checkTableLength :"+checkTableLength);
	if(checkTableLength != 1){
		$('#processtype\\[del_item\\]\\['+id+'\\]').parents("tr").remove();
		i--;
		renameRowID();
		disableOnlyLastButon();	
		calAll();
	} else {
		console.log('reset new line item');
		$('#processtype\\[del_item\\]\\['+id+'\\]').parents("tr").remove();
		i--;
		addAction();
		renameRowID();
		disableOnlyLastButon();
		calAll();
	}
	console.log("action: <- deleteAction()");	
}


function gstAppToggle() {
	console.log("func: -> gstAppToggle()");
	
	console.log("func: ||===>	configGST	: "+configGST);
	console.log("func: ||===>	custGST		: "+custGST);

	
	// configGST=> 0 + if custGST => 0, 	=> $('.tax-toggle').hide(); $('.config-tax').hide();
	// configGST=> 0 + if custGST => null, 	=> $('.tax-toggle').hide(); $('.config-tax').hide();		
	// configGST=> 0 + if custGST => 1,		=> $('.tax-toggle').show();	$('.config-tax').show();
	
	// configGST=> 1 + if custGST => 0, 	=> $('.tax-toggle').hide();	$('.config-tax').show();		
	// configGST=> 1 + if custGST => 1, 	=> $('.tax-toggle').show();	$('.config-tax').show();	
	// configGST=> 1 + if custGST => null, 	=> $('.tax-toggle').show(); $('.config-tax').show();
	
	var tShow = 1;
	var tHide = 0;
	
	if((configGST  === 0) && (custGST === 0))	
	{
		//~ console.log('00');
		$('.config-tax').hide();
		taxToggle(tHide); 
	}
	else if((configGST  === 0) && (custGST === 1))		
	{
		//~ console.log('01');
		taxToggle(tShow); 	
		$('.config-tax').show();
	}
	else if((configGST  === 0) && (custGST === null))
	{		
		//console.log('0null');
		taxToggle(tHide); 
		$('.config-tax').hide();	
	}
	else if((configGST  === 1) && (custGST === 0))	
	{	
		//~ console.log('10');
		taxToggle(tHide); 	
		$('.config-tax').show();
	}
	else if((configGST  === 1) && (custGST === 1))		
	{
		//~ console.log('11');
		taxToggle(tShow); 		
		$('.config-tax').show();
	}
	else if((configGST  === 1) && (custGST === null))
	{	
		//~ console.log('1null'); 
		taxToggle(tShow); 	
		$('.config-tax').show();
	}
		
	console.log("func: <- gstAppToggle() ");
	console.log("++++++++++++++++++++++++");	
}



// ========================================================== MISC Function ================================== 

function taxToggle(e) {
	
	//taxToggle(1) ==> item table show tax
	//taxToggle(0) ==> item table hide tax, recalculate
	
	//var havTax = e.srcElement.value;
	console.log("MFunc: ->taxToggle(e)");
	var havTax = e;
	console.log(havTax);
	
	if(havTax === 1){
		
		console.log('MFunc: show .tax-toggle');
		$('.tax-toggle').show();
		//console.log('show txtype, gst column');
		
	}else{	
		taxReset();
		//console.log('hide txtype, gst column');
		console.log('MFunc: hide .tax-toggle');
		$('.tax-toggle').hide();
		
	}	
	console.log("MFunc: <-taxToggle(e)");
	console.log("++++++++++++++++++++++++++++++");
}

function taxReset(){	
	console.log("MFunc: ->taxReset()");
	var taxSelectRowLength = $('.tax-toggle select').length;
	
	for (taxToggleCounter = 0; taxToggleCounter < taxSelectRowLength; taxToggleCounter++) 
	{		
		//console.log('set txtype => none');
		$('.tax-toggle select').eq(taxToggleCounter).val('none');			
		//console.log('recalculate txtype');
		calTxtype(taxToggleCounter);
	}
		
	//console.log('recalculate all');
	calAll();
	console.log("MFunc: <-taxReset()");
}

function renameRowID() {
	
	console.log("MFunc: -> renameRowID()");		
		
	// check current total row on this table
	var tableRow 	= $('#item-table .item-row').length;	
	var dbTable 	= ''+db_table_detail+'';
	
	//rename 1 select and 9 input
	var totalInput = $('#item-table .item-row').eq(0).find('input').length - 1;
	var totalSelect = $('#item-table .item-row').eq(0).find('select').length;
	
	var renameRowArray	= 0;
	var renameRowLN		= 1;
	var fieldArr = ['','num','line_num','desc','quantity','UOM','price','subtotal','tax','total'];
	//'tax_type',
		
	for (counter = 0; counter < tableRow; counter++) {
		console.log(counter);
		
		console.log(tableRow);
		console.log(counter < tableRow);
		
		//~ //find input id and name and rename according to array
		//~ console.log($('#item-table .item-row').eq(counter).find('input'));
		//~ 
		//~ //find select id and name and rename according to array
		//~ console.log($('#item-table .item-row').eq(counter).find('select'));
		
		
		console.log($('#item-table .item-row').eq(counter).find('i .move-down').attr('data-id'));
		console.log('MFunc: ||===> counter :'+counter);
		
		
		// change select input name and event
		for (fCounter = 0; fCounter < fieldArr.length; fCounter++) {		
			var this_id = fieldArr[fCounter];	
			
			if(fCounter != 0){
				//~ console.log('fieldArr[fCounter]');
				console.log('+++++++++++++++++++');
				console.log(fieldArr[fCounter]);	


				if(fieldArr[fCounter] == 'price'){			
					//console.log($('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onchange'));
					$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onchange','chgDecimal(this);calSubtotal('+counter+');calTxtype('+counter+');qtyPriceOnChange('+counter+');');
					$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onfocusout','qtyPriceOnChange('+counter+');');
				}
				if(fieldArr[fCounter] == 'line_num'){	
					//console.log($('#item-table .item-row').eq(counter).find('input').eq(fCounter).val());
					$('#item-table .item-row').eq(counter).find('input').eq(fCounter).val(counter+1);	
				}
				
				if(fieldArr[fCounter] == 'quantity'){				
					//console.log($('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onchange'));
					$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onchange','calSubtotal('+counter+');calTxtype('+counter+');qtyPriceOnChange('+counter+');');
					$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('onfocusout','qtyPriceOnChange('+counter+');');
				}
		
				$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('id',dbTable+'['+counter+']['+fieldArr[fCounter]+']');
				$('#item-table .item-row').eq(counter).find('input').eq(fCounter).attr('name',dbTable+'['+counter+']['+fieldArr[fCounter]+']')
										
			}			
		}
		
		$('#item-table .item-row').eq(counter).find('select').attr('id',dbTable+'['+counter+'][tax_type]');
		$('#item-table .item-row').eq(counter).find('select').attr('name',dbTable+'['+counter+'][tax_type]');
		$('#item-table .item-row').eq(counter).find('select').attr('data-onload','calTxtype('+counter+')');
		$('#item-table .item-row').eq(counter).find('select').attr('onfocusin','calTxtype('+counter+')');
		$('#item-table .item-row').eq(counter).find('select').attr('onchange','calTxtype('+counter+')');
		$('#item-table .item-row').eq(counter).find('select').attr('onfocusout','txTypeFocusOut('+counter+')');
					
		
		// change 'i' event 
		var actionCounter = 0;
		actionCounter = counter + 1; 
		
		console.log('++++++++++++++++++++++++++++++++++++++');
		console.log(counter);
		
		//change move down id
		$('#item-table .item-row').eq(counter).find('i').eq(2).attr('onclick','downAction('+actionCounter+');');
		$('#item-table .item-row').eq(counter).find('i').eq(2).attr('data-id',actionCounter);
		//~ console.log($('#item-table .item-row').eq(counter).find('i').eq(2).attr('onclick'));
		
		//change move up id
		$('#item-table .item-row').eq(counter).find('i').eq(0).attr('onclick','upAction('+actionCounter+');');
		$('#item-table .item-row').eq(counter).find('i').eq(0).attr('data-id',actionCounter);
		//~ console.log($('#item-table .item-row').eq(counter).find('i').eq(0).attr('onclick'));
				
		//change delete id
		$('#item-table .item-row').eq(counter).find('i').eq(1).attr('onclick','deleteAction('+actionCounter+');');
		$('#item-table .item-row').eq(counter).find('i').eq(1).attr('id','processtype[del_item]['+actionCounter+']');
		//~ console.log($('#item-table .item-row').eq(counter).find('i').eq(1).attr('onclick'));	
		//~ console.log($('#item-table .item-row').eq(counter).find('i').eq(1).attr('id'));		
		
		
	}	
	console.log("MFunc: <- renameRowID()");
	console.log('++++++++++++++++++++++++++++++++++++++');			
}


function disableOnlyLastButon(){
	
	console.log("MFunc: -> disableOnlyLastButon()");	
	var rowLength = $('#item-table .item-row').length;	
	for (counter = 0; counter < rowLength; counter++) 
	{
		
		var dataID 				= counter+1;
		var movDownID 			='.move-down[data-id='+dataID+']';	
		var movUpID 			='.move-up[data-id='+dataID+']';
		var topDeleteSelector 	= $('.item-row').eq(0).find('td i').eq(1);	
		var topMovUpSelector 	= $('.item-row').eq(0).find('td i').eq(0);					
		
		console.log("MFunc: ||===> rowLength :"+rowLength);
		console.log('MFunc: ||===> dataID :'+dataID);
		
		// edit color for move up
		if(dataID == 1){	
			//console.log('movedown chg grey');
			if($(movUpID).hasClass('dark-blue')){		
				$(movUpID).removeClass('dark-blue');
			}
			if($(movUpID).hasClass('point-cursor')){		
				$(movUpID).removeClass('point-cursor');
			}	
			if(!$(movUpID).hasClass('light-grey')){		
				$(movUpID).addClass('light-grey');
			}
			if(!$(movUpID).hasClass('disabled-cursor')){		
				$(movUpID).addClass('disabled-cursor');
			}	
		}
		else 
		{		
			//console.log('movedown chg blue');	
			if($(movUpID).hasClass('light-grey')){		
				$(movUpID).removeClass('light-grey');
			}
			if($(movUpID).hasClass('disabled-cursor')){		
				$(movUpID).removeClass('disabled-cursor');
			}
			
			if(!$(movUpID).hasClass('dark-blue')){		
				$(movUpID).addClass('dark-blue');
			}
			if(!$(movUpID).hasClass('point-cursor')){		
				$(movUpID).addClass('point-cursor');
			}		
		}
		
		// edit color for move down
		if(dataID == rowLength){	
			//console.log('movedown chg grey');
			if($(movDownID).hasClass('dark-blue')){		
				$(movDownID).removeClass('dark-blue');
			}
			if($(movDownID).hasClass('point-cursor')){		
				$(movDownID).removeClass('point-cursor');
			}	
			if(!$(movDownID).hasClass('light-grey')){		
				$(movDownID).addClass('light-grey');
			}
			if(!$(movDownID).hasClass('disabled-cursor')){		
				$(movDownID).addClass('disabled-cursor');
			}	
		}
		else 
		{		
			//console.log('movedown chg blue');	
			if($(movDownID).hasClass('light-grey')){		
				$(movDownID).removeClass('light-grey');
			}
			if($(movDownID).hasClass('disabled-cursor')){		
				$(movDownID).removeClass('disabled-cursor');
			}
			
			if(!$(movDownID).hasClass('dark-blue')){		
				$(movDownID).addClass('dark-blue');
			}
			if(!$(movDownID).hasClass('point-cursor')){		
				$(movDownID).addClass('point-cursor');
			}		
		}	
	}
	if(rowLength == 1){		
		console.log("left 1 row, cant delete change color");
		
		if(topMovUpSelector.hasClass('dark-blue')){		
			topMovUpSelector.removeClass('dark-blue');
		}
		if(topMovUpSelector.hasClass('point-cursor')){		
			topMovUpSelector.removeClass('point-cursor');
		}	
		if(!topMovUpSelector.hasClass('light-grey')){		
			topMovUpSelector.addClass('light-grey');
		}
		if(!topMovUpSelector.hasClass('disabled-cursor')){		
			topMovUpSelector.addClass('disabled-cursor');
		}
		
	}
	
	console.log("MFunc: <- disableOnlyLastButon()");
	console.log("++++++++++++++++++++++++++++++++++++");
}



// ========================================================== Preventive Event ================================== 
function numberKeyPress(e){	
	
	console.log("func: -> numberKeyPress(e)");
	var a = [];
    var k = e.which;
    
    for (i = 48; i < 58; i++)
        a.push(i);
    
    if (!(a.indexOf(k)>=0))
        e.preventDefault();
        
	console.log(k);
	console.log("func: <- numberKeyPress(e)");
}

function numberDotKeyPress(e){	
	
	console.log("func: -> numberDotKeyPress(e)");
	var a = [];
    var k = e.which;
    
    for (i = 46; i < 58; i++)
    {	
		// if prevent user from keypress '/' => 47		
		if(i != 47){
			
			// if prevent user from keypress more than 1 '.' => 46
			if (i == 46){			
				if(e.srcElement.value.split('.').length !== 2)
				{					
					a.push(i);
				}
				else
				{					
					console.log('more than 1 dot is not allow');
				}
			}else
			{			
				//all remaining keypress is allowed	
				a.push(i);
			}
		}
		
	}
	
    if (!(a.indexOf(k)>=0))
        e.preventDefault();
	
	console.log(e.srcElement.value);
	console.log("func: <- numberDotKeyPress(e)");
}

// ========================================================== Logic Function ==================================

function calAll() {	
	console.log("logic: -> calAll()");
	calAllSubtotal();
	calAllTax();
	calGrandTotal();
}

function calGrandTotal() {
	 
	console.log("logic: -> calGrandTotal()");	
    var i = 0;
    var j = 0;
	//var grand_total = 0;
	var grand_total = parseFloat(0).toFixed(2);
    while (j != -1)
    {
		if($('#'+db_table_detail+'\\['+i+'\\]\\[total\\]').length == 0){
			//it doesn't exist
			j=-1;
		}else{			
			//$('#'+db_table_detail+'\\[0\\]\\[total\\]').val();		
			var total = parseFloat(0).toFixed(2);
			if($('#'+db_table_detail+'\\['+i+'\\]\\[total\\]').val() != 0){
				total = parseFloat($('#'+db_table_detail+'\\['+i+'\\]\\[total\\]').val()).toFixed(2);
			}
			
			if (total) {
				grand_total = (parseFloat(grand_total)+parseFloat(total)).toFixed(2);
			}
			i++;
		}				
	}
	
	var gTotal = $('#'+db_table+'\\[amount\\]');
	var shipFee = $('#'+db_table+'\\[shipping_fee\\]');
	//var shipFeeVal = parseFloat(shipFee.val()).toFixed(2);
	
	
	//~ console.log('grand_total:');
	//~ console.log(grand_total);
	//~ console.log('ship_fee:');	
	//~ console.log(shipFeeVal);	
	
	//~ grand_total = (parseFloat(grand_total)+parseFloat(shipFeeVal)).toFixed(2);
	
	//~ console.log('grand_total+shipfee:');
	//~ console.log(grand_total);	
	
	
	//~ $('.shipFeeValue').html(shipFeeVal)
	$('.gtotal').html(grand_total);
	gTotal.val(grand_total);
}

function calAllTax() {
	 
	console.log("logic: -> calAllTax()");	
    var i = 0;
    var j = 0;	
	var allTaxVal = parseFloat(0).toFixed(2);
    while (j != -1)
    {
		if($('#'+db_table_detail+'\\['+i+'\\]\\[tax\\]').length == 0){
			//it doesn't exist
			j=-1;
		}else{
			var eachTaxVal = $('#'+db_table_detail+'\\['+i+'\\]\\[tax\\]').val();				
			if (eachTaxVal) {	
				allTaxVal = (parseFloat(allTaxVal)+parseFloat(eachTaxVal)).toFixed(2);
			}
			i++;
		}				
	}	
	
	//~ console.log('allTaxVal:');
	//~ console.log(allTaxVal);	
	
	$('.taxTotal').html(allTaxVal);
}

function calAllSubtotal() {
	 
	console.log("logic: -> calAllSubtotal()");	
    var i = 0;
    var j = 0;
	var allSubtotal = parseFloat(0).toFixed(2);
    while (j != -1)
    {
		if($('#'+db_table_detail+'\\['+i+'\\]\\[subtotal\\]').length == 0){
			//it doesn't exist
			j=-1;
		}else{
			var subtotal = $('#'+db_table_detail+'\\['+i+'\\]\\[subtotal\\]').val();		
			if (subtotal) {	
				allSubtotal = (parseFloat(allSubtotal)+parseFloat(subtotal)).toFixed(2);
			}
			
			i++;
		}				
	}	
	
	//~ console.log('allSubtotal:');
	//~ console.log(allSubtotal);
	
	$('.stotal').html(allSubtotal);
}

function chgDecimal(element){
	
	console.log("logic: -> chgDecimal("+element+")");	
	var toChg = element;	
	//console.log(element);
	//console.log('chgDecimal');
	//console.log(element.value);
	//console.log(withDecimalVal);
	
	var oldVal = element.value;
	var withDecimalVal =parseFloat(oldVal).toFixed(2);
	
	element.value = withDecimalVal;
}

function calTxtype(id) {	
	
	console.log("logic: -> calTxtype(id)");	
	var ttype = $('#'+db_table_detail+'\\['+id+'\\]\\[tax_type\\]');
	var selected = ttype.find('option:selected');
	
	var currentTaxType = selected.data("txtype");	
	var subtotalVal = $('#'+db_table_detail+'\\['+id+'\\]\\[subtotal\\]').val();	
	
	if( subtotalVal!='' && currentTaxType !='' )
	{
		var ttypeVal = (parseFloat(subtotalVal)*parseFloat(currentTaxType)/parseFloat(100)).toFixed(2);
		$('#'+db_table_detail+'\\['+id+'\\]\\[tax\\]').val(ttypeVal);			 
		calTotal(id,subtotalVal,ttypeVal);
	}else{		
		$('#'+db_table_detail+'\\['+id+'\\]\\[tax\\]').val('');	
		$('#'+db_table_detail+'\\['+id+'\\]\\[total\\]').val('');		
	}
}

function calTotal(id,subtotalVal,ttypeVal) {	
		
	console.log("logic: -> calTotal(id,subtotalVal,ttypeVal)");
	var total = $('#'+db_table_detail+'\\['+id+'\\]\\[total\\]');	
	if( subtotalVal!='' && ttypeVal !='' ) {
		total.val((parseFloat(ttypeVal)+parseFloat(subtotalVal)).toFixed(2));
	} else if( quantity=='' || price =='' ){
		total.val('');			
	}else{			
		total.val('');	
	}
		
}

function calSubtotal(id) {	
	
	console.log("logic: -> calSubtotal(id)");	
	price = $('#'+db_table_detail+'\\['+id+'\\]\\[price\\]').val();
	quantity = $('#'+db_table_detail+'\\['+id+'\\]\\[quantity\\]').val();	
	if( quantity!='' && price !='' ){
		$('#'+db_table_detail+'\\['+id+'\\]\\[subtotal\\]').val( (parseFloat(price)*parseFloat(quantity)).toFixed(2) );	
	}else{		
		$('#'+db_table_detail+'\\['+id+'\\]\\[subtotal\\]').val('');
		$('#'+db_table_detail+'\\['+id+'\\]\\[total\\]').val('');	
	}
}

// =========================================================  unused func ======================================================= ?>

//~ function loadTaxToggle() {
	//~ console.log("func: loadTaxToggle()");
	//~ 
	//~ var noGST = $('.config-tax input').eq(1).prop('checked');
	//~ console.log("||===>noGST: "+noGST);
	//~ 
	//~ if(noGST){
		//~ taxToggle(0);						
	//~ }else{
		//~ taxToggle(1);	
	//~ }
	//~ 
	//~ console.log("end func: loadTaxToggle() ");	
//~ }
