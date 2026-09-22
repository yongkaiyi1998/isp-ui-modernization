var voices = [];
var ischrome = navigator.userAgent.match(/chrome/i);
var u1;
var $summarydiv;
var $txndetail;

function initMyList(lang)
{
	$summarydiv = $("#summarydiv");
	$txndetail = $("#txndetail")
	initSounds();
	initSpeech(lang);
	updateList();
	$("#backgroundPopup").click(function(){ hidePopup(); });
	$("#closeCircle").click(function() { hidePopup(); });
}

function updateList()
{
	//$("#summarydiv").unrepeat().load(base_url+'mylist/summary/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+$("#sort")[0].value).repeat(1000).load(base_url+'mylist/summary/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+$("#sort")[0].value);
	$summarydiv.unrepeat().load(base_url+'mylist/summary/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+$("#sort")[0].value).repeat(1000).load(base_url+'mylist/summary/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+$("#sort")[0].value);
}

function initMyListRows()
{
	$(".txnsum_table").click(function(e){
		var x = e.target;
		if (x.nodeName.toLowerCase() === 'td'){
			key = $(x.parentNode).data("key");
			showDetails(key);
		}
		else if (x.className == 'row_edit') {
			mbrId = $(x).data("key");
			window.location = base_url+'settings/edit/'+mbrId;
		}
    });
}

function showDetails(key)
{
    //$("#txndetail").toggleClass("loadingIcon").load(base_url+'mylist/detail/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+key).toggleClass("loadingIcon");
    $txndetail.empty().toggleClass("loadingIcon").load(base_url+'mylist/detail/'+$("#rcid")[0].value+'_'+$("#rc")[0].value+'_'+key).toggleClass("loadingIcon");
    showPopup();
}

function initSounds()
{
	$.ionSound({
	    sounds: [
	        {
	            name: "alert"
	        },
	    ],
	    volume: 0.5,
	    path: "sounds/",
	    preload: true
	});
}

function playalert() {
	$.ionSound.play("alert");
}

function initSpeech(lang) {
	if(ischrome) {
		voices = window.speechSynthesis.getVoices();
		u1 = new SpeechSynthesisUtterance('');
		u1.lang = lang;
		//u1.lang = 'en-US';
		if (lang == 'zh-CN') {
			u1.voice = voices[9];
			u1.voiceURI = 'Google 中国的';
		}
		else {
			u1.voice = voices[10];
			u1.voiceURI = 'native';
		}
		u1.pitch = 1;
		u1.rate = 1;
		u1.volume = 1;
	}
}

function say(word) {
	speechSynthesis.cancel();
	u1.text=word;
	speechSynthesis.speak(u1);
}