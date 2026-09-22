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

function addLoadEvent(func) {
  var oldonload = window.onload;
  if (typeof window.onload != 'function') {
    window.onload = func;
  } else {
    window.onload = function() {
      if (oldonload) {
        oldonload();
      }
      func();
    }
  }
}

function initStats() {
    var el = document.getElementById('script');
    if (el != null)
	eval(el.innerText);
    initLoadGraph();
}

function initLoadGraph() {
    sd=window.clearInterval(sd);
    sd=window.setInterval("loadgraph();", 1000);
}

function loadgraph() {
    document.getElementById('loadingimg').style.display = 'inline';
    var vloc=document.getElementById('rcid').value;
    var vrc=document.getElementById('rc').value;
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
		div=document.getElementById(sumdiv);
		div.innerHTML=response;
		//if (typeof initpage == 'function') initpage();
	    }
	    initStats();
	    document.getElementById('loadingimg').style.display = 'none';
	}
    }

    xmlHttp.open("GET",url+'?rcid='+vloc+'&rc='+vrc,true);
    xmlHttp.send(null);
}

function requestGraphData() {
    document.getElementById('loadingimg').style.display = 'inline';
    var vloc=document.getElementById('rcid').value;
    var vrc=document.getElementById('rc').value;
    requesturl=dataurl+'?rcid='+vloc+'&rc='+vrc;
    $.ajax({
	    url: requesturl,
	    success: function(jsonstring) {
		    var list = jsonstring.split("#");
		    if (list.length==4)
		    {
			var b1 = list[0];
			var b2 = list[1];
			var e1 = list[2];
			var e2 = list[3];
			var bet1 = $.parseJSON(b1);
			var bet2 = $.parseJSON(b2);
			var eat1 = $.parseJSON(e1);
			var eat2 = $.parseJSON(e2);
			//chart.series[0].setData(data,true);
			$.each(chart.series[0].data, function (i, point) {
			     point.update(bet2[i], false);
			});
			$.each(chart.series[1].data, function (i, point) {
			     point.update(bet1[i], false);
			});
			$.each(chart.series[2].data, function (i, point) {
			     point.update(eat2[i], false);
			});
			$.each(chart.series[3].data, function (i, point) {
			     point.update(eat1[i], false);
			});
			chart.redraw();
		    }
		    document.getElementById('loadingimg').style.display = 'none';
		    sd=window.clearInterval(sd);
		    sd=setTimeout(requestGraphData,1000);
	    },
	    cache: false
    });
}

function initPage() {
    $summarydiv = $("#summarydiv");
    initHelp();
    //Click out event!
    $("#backgroundPopup").click(function(){disablePopup();});
    initLoad();
}

function initHelp(){
    $(".sumrow").click(function(){
        centerPopup();
        loadPopup(this);
    });
}

function initLoad() {
    //$("#summarydiv").unrepeat().load(url+'?rcid='+$("#rcid")[0].value+'&rc='+$("#rc")[0].value+'&mbrid='+$("#mbrid")[0].value).repeat(1000).load(url+'?rcid='+$("#rcid")[0].value+'&rc='+$("#rc")[0].value+'&mbrid='+$("#mbrid")[0].value);
    $summarydiv.unrepeat().load(url+'?rcid='+$("#rcid")[0].value+'&rc='+$("#rc")[0].value+'&mbrid='+$("#mbrid")[0].value).repeat(1000).load(url+'?rcid='+$("#rcid")[0].value+'&rc='+$("#rc")[0].value+'&mbrid='+$("#mbrid")[0].value);
}

function initSettingsPage() {
    jQuery(document).ready(function($) {
        $(".userRow").click(function() {
            myListClick($(this));
        });
    });
}

function myListClick(row) {
    mbrId = row.data("key");
    editMember(mbrId);
}

function editMember(mbrid)
{
    $("#popupAddMylist").load(base_url+'settings/editMyListMember/'+mbrid);
    centerPopup();
    $("#backgroundPopup").fadeIn("fast");
    $("#popupDetail").fadeIn("fast");
}

function refreshframe() {
    var vloc=document.getElementById('rcid').value;
    var vrc=document.getElementById('rc').value;
    summaryframe.location=url+'?rcid='+vloc+'&rc='+vrc;
}


function loadDetail(hs,div,mbrid) {
    $("#txndetail").toggleClass("loadingIcon").load(urld+'?rcid='+$("#rcid")[0].value+'&rc='+$("#rc")[0].value+'&mbrid='+mbrid+'&hs='+hs+'&div='+div).toggleClass("loadingIcon");
}

function addFavourite(mbr) {
    $.get(base_url+'/index.php/settings/addfavourite','mbrid='+mbr).done(function() {initLoad();});
}

function removeFavourite(mbr) {
    $.get(base_url+'/index.php/settings/delfavourite','mbrid='+mbr).done(function() {initLoad();});
}

function addIgnore(mbr) {
    $.get(base_url+'/index.php/settings/addignore','mbrid='+mbr).done(function() {initLoad();});
}

function addMyListMbr() {
    $("#popupAddMylist").load(base_url+'settings/addMyList');
    centerPopup();
    $("#backgroundPopup").fadeIn("fast");
    $("#popupDetail").fadeIn("fast");
}

function saveMyListMbr() {
    if ($("#mbrid")[0].value=='') {
        $('#message')[0].innerText='Member ID cannot be empty!';
    }
    else {
        $("#data").load(base_url+"settings/saveMyListMember",$("#editmember").serialize());
    }
}

function delMyListMbr(mbrid) {
    $("#data").load(base_url+"settings/delMyListMember/"+mbrid);
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
        "top": 50,
    	"left": windowWidth/2-popupWidth/2
    });
    //only need force for IE6
    $("#backgroundPopup").css({
    	"height": windowHeight
    });
}

function loadPopup(cell){
    var hs = cell.parentNode.childNodes[0].innerText.trim();
    var div = cell.parentNode.childNodes[1].innerText.trim();
    var mbrid = cell.parentNode.childNodes[5].innerText.trim();
    centerPopup();
    $("#backgroundPopup").fadeIn("fast");
    $("#popupDetail").fadeIn("fast");
    loadDetail(hs,div,mbrid);
}

function disablePopup(){
    $("#backgroundPopup").fadeOut("fast");
    $("#popupDetail").fadeOut("fast");
}

function eat1winChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#eatplc1").removeClass('x');
    }
    else
        $("#eatplc1").addClass('x');
    params.eatplc1.value=sender.value;
    valChanged();
}

function eat2winChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#eatplc2").removeClass('x');
    }
    else
        $("#eatplc2").addClass('x');
    params.eatplc2.value=sender.value;
    valChanged();
}

function eat3winChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#eatplc3").removeClass('x');
    }
    else
        $("#eatplc3").addClass('x');
    params.eatplc3.value=sender.value;
    valChanged();
}

function eat4winChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#eatplc4").removeClass('x');
    }
    else
        $("#eatplc4").addClass('x');
    params.eatplc4.value=sender.value;
    valChanged();
}

function bettotewinChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#betplctote").removeClass('x');
    }
    else
        $("#betplctote").addClass('x');
    params.betplctote.value=sender.value;
    valChanged();
}

function bet1winChanged(sender)
{
    if (sender.value=='') {
        sender.value='0';
        $("#betplc1").removeClass('x');
    }
    else
        $("#betplc1").addClass('x');
    params.betplc1.value=sender.value;
    valChanged();
}


function valChanged()
{
    if (params.eatprice2.value=='') params.eatprice2.value='0';
    if (eatprice2!=params.eatprice2.value) ceatprice2 = params.eatprice2.value;
    if (params.betprice1.value=='') params.betprice1.value='0';
    if (betprice1 != params.betprice1.value) cbetprice1 = params.betprice1.value;
    if (params.eatpayoutwin.value=='') params.eatpayoutwin.value='0';
    if (eatpayoutwin != params.eatpayoutwin.value) ceatpayoutwin = params.eatpayoutwin.value;
    if (params.eatpayoutplc.value=='') params.eatpayoutplc.value='0';
    if (eatpayoutplc != params.eatpayoutplc.value) ceatpayoutplc = params.eatpayoutplc.value;
    if (params.betpayoutwin.value=='') params.betpayoutwin.value='0';
    if (betpayoutwin != params.betpayoutwin.value) cbetpayoutwin = params.betpayoutwin.value;
    if (params.betpayoutplc.value=='') params.betpayoutplc.value='0';
    if (betpayoutplc != params.betpayoutplc.value) cbetpayoutplc = params.betpayoutplc.value;
    if (params.eatwin2.value=='') params.eatwin2.value='0';
    if (eatwin2 != params.eatwin2.value) ceatwin2 = params.eatwin2.value;
    if (params.eatplc2.value=='') params.eatplc2.value='0';
    if (eatplc2 != params.eatplc2.value) ceatplc2 = params.eatplc2.value;
    if (params.betwin1.value=='') params.betwin1.value='0';
    if (betwin1 != params.betwin1.value) cbetwin1 = params.betwin1.value;
    if (params.betplc1.value=='') params.betplc1.value='0';
    if (betplc1 != params.betplc1.value) cbetplc1 = params.betplc1.value;
    if (eatmode2 != $("input[name=eatmode2]:checked")[0].value)
        ceatmode2 = $("input[name=eatmode2]:checked")[0].value;
    if (betmode1 != $("input[name=betmode1]:checked")[0].value)
        cbetmode1 = $("input[name=betmode1]:checked")[0].value;

    if (simple!='1')
    {
        if (params.eatprice1.value=='') params.eatprice1.value='0';
        if (eatprice1!=params.eatprice1.value) ceatprice1 = params.eatprice1.value;
        if (params.eatprice3.value=='') params.eatprice3.value='0';
        if (eatprice3 != params.eatprice3.value) ceatprice3 = params.eatprice3.value;
        if (params.eatprice4.value=='') params.eatprice4.value='0';
        if (eatprice4 != params.eatprice4.value) ceatprice4 = params.eatprice4.value;
        if (params.eatwin1.value=='') params.eatwin1.value='0';
        if (eatwin1 != params.eatwin1.value) ceatwin1 = params.eatwin1.value;
        if (params.eatplc1.value=='') params.eatplc1.value='0';
        if (eatplc1 != params.eatplc1.value) ceatplc1 = params.eatplc1.value;
        if (params.eatwin3.value=='') params.eatwin3.value='0';
        if (eatwin3 != params.eatwin3.value) ceatwin3 = params.eatwin3.value;
        if (params.eatplc3.value=='') params.eatplc3.value='0';
        if (eatplc3 != params.eatplc3.value) ceatplc3 = params.eatplc3.value;
        if (params.eatwin4.value=='') params.eatwin4.value='0';
        if (eatwin4 != params.eatwin4.value) ceatwin4 = params.eatwin4.value;
        if (params.eatplc4.value=='') params.eatplc4.value='0';
        if (eatplc4 != params.eatplc4.value) ceatplc4 = params.eatplc4.value;
        if (params.betwintote.value=='') params.betwintote.value='0';
        if (betwintote != params.betwintote.value) cbetwintote = params.betwintote.value;
        if (params.betplctote.value=='') params.betplctote.value='0';
        if (betplctote != params.betplctote.value) cbetplctote = params.betplctote.value;
        if (eatmode1 != $("input[name=eatmode1]:checked")[0].value)
            ceatmode1 = $("input[name=eatmode1]:checked")[0].value;
        if (eatmode3 != $("input[name=eatmode3]:checked")[0].value)
            ceatmode3 = $("input[name=eatmode3]:checked")[0].value;
        if (eatmode4 != $("input[name=eatmode4]:checked")[0].value)
            ceatmode4 = $("input[name=eatmode4]:checked")[0].value;
        if (betmodetote != $("input[name=betmodetote]:checked")[0].value)
            cbetmodetote = $("input[name=betmodetote]:checked")[0].value;
    }

    updateCalc();
}

function updateCalc()
{
    datastr = "eatprice1="+ceatprice1+"&eatprice2="+ceatprice2+"&eatprice3="+ceatprice3+"&eatprice4="+ceatprice4+"&betprice1="+cbetprice1
            +"&eatpayoutwin="+ceatpayoutwin+"&eatpayoutplc="+ceatpayoutplc+"&betpayoutwin="+cbetpayoutwin+"&betpayoutplc="+cbetpayoutplc
            +"&eatwin1="+ceatwin1+"&eatplc1="+ceatplc1+"&eatwin2="+ceatwin2+"&eatplc2="+ceatplc2+"&eatwin3="+ceatwin3+"&eatplc3="+ceatplc3
            +"&eatwin4="+ceatwin4+"&eatplc4="+ceatplc4+"&betwintote="+cbetwintote+"&betplctote="+cbetplctote+"&betwin1="+cbetwin1+"&betplc1="+cbetplc1
            +"&eatmode1="+ceatmode1+"&eatmode2="+ceatmode2+"&eatmode3="+ceatmode3+"&eatmode4="+ceatmode4+"&betmodetote="+cbetmodetote+"&betmode1="+cbetmode1;
    document.getElementById('loadingimg').style.display = 'inline';
    $.ajax({
        url: calcurl,
        type: "POST",
        cache: false,
        data: datastr,
        success: function(msg) {
            eval(msg);
        },
        complete: function(jqXHR,textStatus) {
            document.getElementById('loadingimg').style.display = 'none';
        }
    });
    ceatprice1 = '';
    ceatprice2 = '';
    ceatprice3 = '';
    ceatprice4 = '';
    cbetprice1 = '';
    ceatpayoutwin = '';
    ceatpayoutplc = '';
    cbetpayoutwin = '';
    cbetpayoutplc = '';
    ceatwin1 = '';
    ceatplc1 = '';
    ceatwin2 = '';
    ceatplc2 = '';
    ceatwin3 = '';
    ceatplc3 = '';
    ceatwin4 = '';
    ceatplc4 = '';
    cbetwintote = '';
    cbetplctote = '';
    cbetwin1 = '';
    cbetplc1 = '';
    ceatmode1 = '';
    ceatmode2 = '';
    ceatmode3 = '';
    ceatmode4 = '';
    cbetmode1 = '';
    cbetmodetote = '';
    document.getElementById('refreshRunning').style.display = 'inline';
}

function initCalc()
{
    sd=window.clearInterval(sd);
    sd=setInterval("updateCalc()",1000);
}

function refreshChanged()
{
    sd=window.clearInterval(sd);
    document.getElementById('refreshRunning').style.display = 'none';
    if (params.autorefresh.value=='Y')
        sd=setInterval("updateCalc()",1000);
}

function updateField(fieldname,value)
{
    if (window[fieldname]!=value)
    {
        if (document.forms['params'][fieldname]!=undefined)
            document.forms['params'][fieldname].value=value;
        $('#'+fieldname+':focus').select();
        window[fieldname]=value;
    }
}

function clearForm()
{
    params.eatpayoutwin.value='0';
    params.eatpayoutplc.value='0';
    params.betpayoutwin.value='0';
    params.betpayoutplc.value='0';
    params.eatprice2.value='0';
    params.betprice1.value='0';
    params.eatwin2.value='0';
    params.eatplc2.value='0';
    params.betwin1.value='0';
    params.betplc1.value='0';
    $("input[name=eatmode2]:checked")[0].value='T';
    $("input[name=betmode1]:checked")[0].value='T';
    if (simple!='1')
    {
        params.eatprice1.value='0';
        params.eatprice3.value='0';
        params.eatprice4.value='0';
        params.eatwin1.value='0';
        params.eatplc1.value='0';
        params.eatwin3.value='0';
        params.eatplc3.value='0';
        params.eatwin4.value='0';
        params.eatplc4.value='0';
        params.betwintote.value='0';
        params.betplctote.value='0';
        $("input[name=eatmode1]:checked")[0].value='M';
        $("input[name=eatmode3]:checked")[0].value='T';
        $("input[name=eatmode4]:checked")[0].value='T';
        $("input[name=betmodetote]:checked")[0].value='M';
    }
    valChanged();
}

function simpleView()
{
    document.location.href = document.location.href.replace(/\&simple=[0,1]/g,'') + '&simple=1';
}

function normalView()
{
    document.location.href = document.location.href.replace(/\&simple=[0,1]/g,'') + '&simple=0';
}

var popupStatus = 0;
var sd;
var $summarydiv;