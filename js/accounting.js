var popupStatus = 0;

function getAjaxObj() {
  var ajax;
  try
    {
    // Firefox, Opera 8.0+, Safari
    ajax=new XMLHttpRequest();
    }
  catch (e)
    {
    // Internet Explorer
    try
      {
      ajax=new ActiveXObject("Msxml2.XMLHTTP");
      }
    catch (e)
      {
      try
        {
        ajax=new ActiveXObject("Microsoft.XMLHTTP");
        }
      catch (e)
        {
        alert("Your browser does not support AJAX!");
        return false;
        }
      }
    }
  return ajax;
}

function valueChange(sender) {
	// eatbet = document.getElementById('eatbet').value;
	// if (eatbet=='EAT') {
	// 	document.getElementById('leftrow').className = "";
	// 	document.getElementById('leftrow').className = "tableEntryRow roweat";
	// 	document.getElementById('rightrow').className = "";
	// 	document.getElementById('rightrow').className = "tableEntryRow rowbet";
	// 	document.getElementById('eatbet2').innerText='BET';
	// }
	// else {
	// 	document.getElementById('leftrow').className = "";
	// 	document.getElementById('leftrow').className = "tableEntryRow rowbet";
	// 	document.getElementById('rightrow').className = "";
	// 	document.getElementById('rightrow').className = "tableEntryRow roweat";
	// 	document.getElementById('eatbet2').innerText='EAT';
	// }

	//document.getElementById('hs2').innerText=document.getElementById('hs').value;

	//win=parseInt(document.getElementById('win').value.replace(',',''));
	// if (isNaN(win)) {
	// 	win=0;
	// 	document.getElementById('win').value='';
	// 	document.getElementById('win2').innerText='';
	// }
	// else {
	// 	winStr=addCommas(win);
	// 	document.getElementById('win').value=winStr;
	// 	document.getElementById('win2').innerText=winStr;

	// 	if (sender==document.getElementById('win')) {
	// 		document.getElementById('plc').value=win;
	// 		document.getElementById('plc2').innerText=winStr;
	// 	}
	// }

	// plc=parseInt(document.getElementById('plc').value.replace(',',''));
	// if (isNaN(plc)) {
	// 	plc=0;
	// 	document.getElementById('plc').value='';
	// 	document.getElementById('plc2').innerText='';
	// }
	// else {
	// 	plcStr=addCommas(plc);
	// 	document.getElementById('plc').value=plcStr;
	// 	document.getElementById('plc2').innerText=plcStr;
	// }

	// price=parseFloat(document.getElementById('price').value);
	// if (isNaN(price)) {
	// 	price=0;
	// 	document.getElementById('price').value='';
	// 	document.getElementById('price2').innerText='';
	// }
	// else {
	// 	priceStr=price.toFixed(2);
	// 	document.getElementById('price').value=priceStr;
	// 	document.getElementById('price2').innerText=priceStr;
	// }

	if (sender!=document.getElementById('amount') && sender!=document.getElementById('eatbet')
	&& !isNaN(win) && !isNaN(plc) && !isNaN(price)) {
		if (eatbet=='EAT')
			amount = Math.round((win+plc)*price);
		else
			amount = Math.round((win+plc)*price*-1);
	}
	else {
		amount=parseInt(document.getElementById('amount').value.replace('(','-').replace('$','').replace(',','').replace(')',''));
	}

	if (!isNaN(amount)) {
		if (amount>0) {
			amountStr=addCommas(Math.round(amount));
			document.getElementById('amount').value='$'+amountStr;
			document.getElementById('amount').className='clearable profit';
			document.getElementById('amount2').innerText='$('+amountStr+')';
			document.getElementById('amount2').className='loss';
		}
		else {
			amount2=amount*-1;
			amountStr=addCommas(Math.round(amount2));
			document.getElementById('amount').value='$('+amountStr+')';
			document.getElementById('amount').className='clearable loss';
			document.getElementById('amount2').innerText='$'+amountStr;
			document.getElementById('amount2').className='profit';
		}
	}
	else {
		document.getElementById('amount').value='';
		document.getElementById('amount2').innerText='';
	}
}

function profitChange(sender) {
	amount1=parseInt(document.getElementById('shareamount1').value.replace('(','-').replace('$','').replace(',','').replace(')',''));
	amount2=parseInt(document.getElementById('shareamount2').value.replace('(','-').replace('$','').replace(',','').replace(')',''));
	if (!isNaN(amount1)) {
		if (amount1<0) {
			displayAmount=amount1*-1;
			document.getElementById('shareamount1').value='$('+addCommas(Math.round(displayAmount))+')';
			document.getElementById('shareamount1').className='clearable loss';
		}
		else {
			document.getElementById('shareamount1').value='$'+addCommas(Math.round(amount1));
			document.getElementById('shareamount1').className='clearable profit';
		}
	}
	if (!isNaN(amount2)) {
		if (amount2<0) {
			displayAmount=amount2*-1;
			document.getElementById('shareamount2').value='$('+addCommas(Math.round(displayAmount))+')';
			document.getElementById('shareamount2').className='clearable loss';
		}
		else {
			document.getElementById('shareamount2').value='$'+addCommas(Math.round(amount2));
			document.getElementById('shareamount2').className='clearable profit';
		}
	}
	if (!isNaN(amount1) && !isNaN(amount2)) {
		profit = (amount1+amount2)/2;
		amount = profit-amount1;
		if (profit<0) {
			profitStr='$('+addCommas(Math.round(profit))+')';
			document.getElementById('profit1').innerText=profitStr;
			document.getElementById('profit1').className='loss';
			document.getElementById('profit2').innerText=profitStr;
			document.getElementById('profit2').className='loss';
		}
		else {
			profitStr='$'+addCommas(Math.round(profit));
			document.getElementById('profit1').innerText=profitStr;
			document.getElementById('profit1').className='profit';
			document.getElementById('profit2').innerText=profitStr;
			document.getElementById('profit2').className='profit';
		}

		document.getElementById('amount').value=Math.round(amount);
		valueChange(document.getElementById('amount'));
	}
}

function addCommas(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ',' + '$2');
	}
	return x1 + x2;
}

function centerPopup(){
    //request data for centering
    var windowWidth = document.documentElement.clientWidth;
    var windowHeight = document.documentElement.clientHeight;
    var popupHeight = $("#popupDetail").height();
    var popupWidth = $("#popupDetail").width();
    //centering
    $("#popupDetail").css({
		"position": "absolute",
		//"top": windowHeight/2-popupHeight/2,
		"top": "50px",
		"left": windowWidth/2-popupWidth/2
	    });
	    //only need force for IE6
	    $("#backgroundPopup").css({
		"height": windowHeight
    });
}

function loadPopup(cell){
    //loads popup only if it is disabled
    if(popupStatus==0){
		$("#backgroundPopup").css({"opacity": "0.7"});
		$("#backgroundPopup").fadeIn("fast");
		$("#popupDetail").fadeIn("fast");
		popupStatus = 1;
		var trx_id = cell.childNodes[0].innerText.trim();
		loadTrans(trx_id);
    }
}

function disablePopup(){
    	//disables popup only if it is enabled
	    if(popupStatus==1){
		$("#backgroundPopup").fadeOut("fast");
		$("#popupDetail").fadeOut("fast");
		popupStatus = 0;
    }
}

function loadTrans(trx_id) {
    xmlHttp=getAjaxObj();
    xmlHttp.onreadystatechange=function()
    {
	if(xmlHttp.readyState==4)
	{
	    response=xmlHttp.responseText;
	    if ((response.substring(0,7) == 'http://')||(response.substring(0,8) == 'https://'))
	    {
			document.location.href=response;
	    }
	    else
	    {
			div=document.getElementById('txndetail');
			div.innerHTML=response;
	    }
	}
    }
    xmlHttp.open("GET",urld+'/'+trx_id,true);
    xmlHttp.send(null);
}

function rpt_today() {
	var today = new Date();
	$('#startdate')[0].value=$.datepicker.formatDate('yy-mm-dd',today);
	$('#enddate')[0].value=$.datepicker.formatDate('yy-mm-dd',today);
	accounts.submit();
}

function rpt_thisWeek() {
	var today = new Date()
	  , day = today.getDay()
	  , diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1)
	  , thisMonday = new Date(today.setDate(diffToMonday))
	  , thisSunday = new Date(today.setDate(diffToMonday + 6));
	if (thisSunday.getDate() < thisMonday.getDate()) {
		mnth = thisSunday.getMonth();
		if (mnth == 11) {
			thisSunday.setMonth(0);
			yr = thisSunday.getYear();
			thisSunday.setYear(yr+1);
		}
		else
			thisSunday.setMonth(mnth+1);
	}
	$('#startdate')[0].value=$.datepicker.formatDate('yy-mm-dd',thisMonday);
	$('#enddate')[0].value=$.datepicker.formatDate('yy-mm-dd',thisSunday);
	accounts.submit();
}

function rpt_lastWeek() {
	var beforeOneWeek = new Date(new Date().getTime() - 60 * 60 * 24 * 7 * 1000)
	  , day = beforeOneWeek.getDay()
	  , diffToMonday = beforeOneWeek.getDate() - day + (day === 0 ? -6 : 1)
	  , lastMonday = new Date(beforeOneWeek.setDate(diffToMonday))
	  , lastSunday = new Date(beforeOneWeek.setDate(diffToMonday + 6));
	 if (lastSunday.getDate() < lastMonday.getDate()) {
		mnth = lastSunday.getMonth();
		if (mnth == 11) {
			lastSunday.setMonth(0);
			yr = lastSunday.getYear();
			lastSunday.setYear(yr+1);
		}
		else
			lastSunday.setMonth(mnth+1);
	}
	$('#startdate')[0].value=$.datepicker.formatDate('yy-mm-dd',lastMonday);
	$('#enddate')[0].value=$.datepicker.formatDate('yy-mm-dd',lastSunday);
	accounts.submit();
}

