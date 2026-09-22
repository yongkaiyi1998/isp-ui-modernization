// ========================================================== MISC Function ================================== 
function taxToggle(e) {
	
	//taxToggle(1) ==> item table show tax
	//taxToggle(0) ==> item table hide tax, 
	
	console.log("MFunc: ->taxToggle("+e+")");
	var havTax = e;
	
	if(havTax === 1){
		
		console.log('MFunc: show .tax-toggle');
		$('.tax-toggle').show();
		
	}else{	
		console.log('MFunc: hide .tax-toggle');
		$('.tax-toggle').hide();
		
	}	
	console.log("MFunc: <-taxToggle(e)");
	console.log("++++++++++++++++++++++++++++++");
}
 
// =========================================================  load func ======================================================= 

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
		taxToggle(tHide); 
	}
	else if((configGST  === 0) && (custGST === 1))		
	{
		taxToggle(tShow); 	
	}
	else if((configGST  === 0) && (custGST === null))
	{		
		taxToggle(tHide); 	
	}
	else if((configGST  === 1) && (custGST === 0))	
	{	
		taxToggle(tHide); 	
	}
	else if((configGST  === 1) && (custGST === 1))		
	{
		taxToggle(tShow); 		
	}
	else if((configGST  === 1) && (custGST === null))
	{	 
		taxToggle(tShow); 	
	}
		
	console.log("func: <- gstAppToggle() ");
	console.log("++++++++++++++++++++++++");	
}
