function AjaxSubmit(theform, sub) {
	AjaxLoading();
	var vdata = jQuery('#'+theform).serialize();
	vdata = 'action=wiziva&ajsub=submit&' + vdata;
	jQuery.post(ajax_object.ajax_url+'?isdialog=1&sub='+sub, vdata, function(response) {
		console.log(response);
		eval(response);
	});
}

function AjaxSubmitAdd(theform, sub, add) {
	AjaxLoading();
	var vdata = jQuery('#'+theform).serialize();
	jQuery.ajax({url:rootpath+'ajax.php?s=ajaxsubmit&sub='+sub, type: 'POST', data: vdata+add, success: function(rdata) {console.log(rdata);eval(rdata);}});
}

function AjaxPop(pg, frm) {
	AjaxLoading();
	if (frm) var vdata = jQuery('#'+frm).serialize();
	else var vdata = '';
	vdata = 'action=wiziva&ajsub=page&' + vdata;
	jQuery.post(ajax_object.ajax_url+'?isdialog=1&pg='+pg, vdata, function(response) {
		console.log(response);
		eval(response);
	});
}


function AjaxPageSubmit(pg, theform, newtab) {
	AjaxLoading();
	var vdata = jQuery('#'+theform).serialize();
	jQuery.ajax({url:rootpath+'ajax.php?s=ajaxpage&pg='+pg, type: 'POST', data: vdata, success: function(rdata) {console.log(rdata);eval(rdata);}});
	return false;
}

function AjaxAction(pg, senddata) {
	var vdata = '';
	if (senddata) {
		senddata = senddata.split(' ')
		for (var i=0;i < senddata.length;i++) {
			var fld = senddata[i];
			if (fld.substr(fld.length-1, fld.length) == '_') {
				el = document.getElementsByTagName('input');
				for(j=0; j < el.length; j++) if (el[j].name.indexOf(fld) == 0) vdata += el[j].name + '=' + encodeURIComponent(document.getElementById(el[j].id).value) + '&';
				el = document.getElementsByTagName('textarea');
				for(j=0; j < el.length; j++) if (el[j].name.indexOf(fld) == 0) vdata += el[j].name + '=' + encodeURIComponent(document.getElementById(el[j].id).value) + '&';
			}
			else {
				if (!document.getElementById(fld)) alert(fld);
				vdata += fld + '=' + encodeURIComponent(document.getElementById(fld).value) + '&';
			}
		}
	}
	vdata = 'action=wiziva&ajsub=main&' + vdata;
	jQuery.post(ajax_object.ajax_url+'?a='+pg, vdata, function(response) {
		console.log(response);
		eval(response);
	});
}




function AjaxActionLoading(pg, senddata, contdiv, txt) {
	if (!txt) txt = 'Loading...';
	document.getElementById(contdiv).innerHTML = '<div class="loader-bar">'+txt+'</div>';
	AjaxAction(pg, senddata);
}


function Loading(e) {
	if (e) {
		d = document.getElementById('loading');
		x = findPosX(e)+15;
		y = findPosY(e)+5;
		d.style.left =  x+'px';
		d.style.top =  y+'px';
		d.style.visibility = 'visible';
	}
	else {
		if (parseInt(navigator.appVersion)>3) {
			if (navigator.appName=="Netscape") {
				winW = document.documentElement.clientWidth;
				winH = window.innerHeight;
			}
			if (navigator.appName.indexOf("Microsoft")!=-1) {
				winW = document.documentElement.clientWidth;
				winH = document.documentElement.clientHeight;
			}
		}
		d = document.getElementById('loading');
		if (window.scrollY) scr = window.scrollY;
		else if (document.documentElement.scrollTop) scr = document.documentElement.scrollTop;
		else scr = 0;
		d.style.top =  parseInt(scr+winH/2)-50+ 'px';
		d.style.left =  parseInt(winW/2)-50+ 'px';
		d.style.visibility = 'visible';
	}
}



function CenterDiv(obj, w, h) {
	if (parseInt(navigator.appVersion)>3) {
		if (navigator.appName=="Netscape") {
			winW = document.documentElement.clientWidth;
			winH = window.innerHeight;
		}
		if (navigator.appName.indexOf("Microsoft")!=-1) {
			winW = document.documentElement.clientWidth;
			winH = document.documentElement.clientHeight;
		}
	}
	if (window.scrollY) scr = window.scrollY;
	else if (document.documentElement.scrollTop) scr = document.documentElement.scrollTop;
	else scr = 0;
	t = parseInt(scr + winH/2 - h/2 - 120);
	if (t < 0) t = 0;
	obj.style.top = t + 'px';
	obj.style.left = parseInt(winW/2 - w/2) + 'px';
}

function MarkCheckbox(dname) {
	v = document.getElementById(dname).value;
	if (v=='0') {
		document.getElementById(dname).value = 1;
		document.getElementById('cb'+dname).className = 'chbon';
	}
	else {
		document.getElementById(dname).value = 0;
		document.getElementById('cb'+dname).className = 'chb';
	}
}


function findPosX(e) {
	var posx = 0;
	if (!e) var e = window.event;
	if (e.pageX) posx = e.pageX;
	else if (e.clientX) posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
	return posx;
}

function findPosY(e) {
	var posx = 0;
	if (!e) var e = window.event;
	if (e.pageY) posy = e.pageY;
	else if (e.clientY) posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
	return posy;
}

function ShowLayerAtPos(e, divname, offsetX, offsetY) {
	if (!offsetX) offsetX = 0;
	if (!offsetY) offsetY = 0;
	div = document.getElementById(divname);
	x = findPosX(e);
	y = findPosY(e);
	div.style.left = x+offsetX+'px';
	div.style.top = y+offsetY+'px';
	div.style.visibility = 'visible';
}

function SetLayerPos(e, divname, offsetX, offsetY) {
	if (!offsetX) offsetX = 0;
	if (!offsetY) offsetY = 0;
	div = document.getElementById(divname);
	x = findPosX(e);
	y = findPosY(e);
	div.style.left = x+offsetX+'px';
	div.style.top = y+offsetY+'px';
}

function HideLayer(divname) {
	document.getElementById(divname).style.visibility = "hidden";
}

function ShowLayer(divname) {
	document.getElementById(divname).style.visibility = "visible";
}


function popup( url, winname, width, height ) {
	if (winname == "") winname = "popup";
	if (width == "") width = "400";
	if (height == "") height = "300";
	var top = (screen.height) / 2 - (height / 2);
	var left = (screen.width) / 2 - (width / 2);
	var win_arg = "scrollbars=yes,status=yes,resizable=yes,location=no,toolbar=no,width=" + width + ",height=" + height + ",top=" + top + ",left=" + left;
	window.open(url,winname,win_arg);
}



function CheckAll(formobj) {
	for (i=0; i < formobj.length; i++) if (formobj.elements[i].name.substr(0,8) == 'checked_') formobj.elements[i].checked = formobj.checkall.checked;
}

function CheckAllSpecial(formobj, fldall, prefix) {
	for (i=0; i < formobj.length; i++) if (formobj.elements[i].name.substr(0,prefix.length) == prefix) formobj.elements[i].checked = document.getElementById(fldall).checked;
}

function ConfirmDelete(form, msg) {
	var godel = window.confirm(msg);
	if (godel) {
		form.elements['suredelete'].value = 1;
		form.submit();
	}
}


function selectAll(selectBox,selectAll) {
    if (typeof selectBox == "string") selectBox = document.getElementById(selectBox);
    if (selectBox.type == "select-multiple") 
        for (var i = 0; i < selectBox.options.length; i++) selectBox.options[i].selected = selectAll;
}


function JSNewLines(fld) {
	txt = document.getElementById(fld).value;
	txt = txt.replace(/; /g, '\n');
	document.getElementById(fld).value = txt;
}

function AjaxCB(obj, val) {
	if (obj.checked) obj.value = val;
	else obj.value = 0;
}


function TabSwitch(sub) {
	el = document.getElementById('tabsbox').getElementsByTagName('a');
	for(j=0; j < el.length; j++) if (el[j].id.indexOf('tsub_') == 0) el[j].className = 'tabbut';
	document.getElementById('tsub_'+sub).className = 'tabbutactive';
}



function MarkPlugin(id, ptype) {
	if (document.getElementById('mplid_'+id).className=='man'+ptype+'on') document.getElementById('mplid_'+id).className='man'+ptype;
	else document.getElementById('mplid_'+id).className='man'+ptype+'on';
}

function NewSelectable(fld, stype) {
	obj = document.getElementById(fld);
	id = obj.value;
	if (id!=0) {
		document.getElementById('new_'+fld).value=obj.options[obj.selectedIndex].text;
		document.getElementById('but_'+fld).value='Update';
	}
	else document.getElementById('but_'+fld).value='Add';
	document.getElementById('selbox'+fld).style.display='none';
	document.getElementById('newbox'+fld).style.display='inline';
	document.getElementById('new_'+fld).focus();
}

function CancelNewSelectable(fld) {
	document.getElementById('selbox'+fld).style.display='inline';
	document.getElementById('newbox'+fld).style.display='none';
}

var hideid, hidetimer;
function HideTimer(did) {
	hideid = did;
	hidetimer = setTimeout('HideDiv();', 300);
}

function HideDiv() {
	if (hideid) document.getElementById(hideid).style.display='none';
}


function SwitchDiv(oid) {
	obj = document.getElementById(oid);
	if (obj.style.display != 'block') obj.style.display = 'block';
	else obj.style.display = 'none';
}

function PrepAjaxPop(e, title) {
	document.getElementById('ajaxpop').innerHTML = "<h1 onmousedown=\"ddInit(event, this);\">"+title+"</h1><div class='content'><div class='loader-bar'>Loading...</div></div>";
	document.getElementById('ajaxpop').style.left = (findPosX(e)+10)+'px';
	document.getElementById('ajaxpop').style.top = (findPosY(e)+10)+'px';
	ShowAjax();
}

function PrepAjaxPopR(e, title) {
	document.getElementById('ajaxpop').innerHTML = "<h1 onmousedown=\"ddInit(event, this);\">"+title+"</h1><div class='content'><div class='loader-bar'>Loading...</div></div>";
	document.getElementById('ajaxpop').style.left = (findPosX(e)-400)+'px';
	document.getElementById('ajaxpop').style.top = (findPosY(e)+10)+'px';
	ShowAjax();
}

function PrepAjaxPopC(title, w, h) {
	obj = document.getElementById('ajaxpop');
	obj.innerHTML = "<h1 onmousedown=\"ddInit(event, this);\"style='width:"+w+"px'>"+title+"</h1><div class='content' style='width:"+(w-20)+"px'><div class='loader-bar'>Loading...</div></div>";
	obj.style.width = w + 'px';
	obj.style.height = (h+40) + 'px';
	CenterDiv(obj, w, h);
	ShowAjax();
}

function HideAjax() {
	document.getElementById('dim').style.display = 'none';
	HideLayer('ajaxpop');
}

function ShowAjax() {
	document.getElementById('dim').style.display = 'block';
	document.getElementById('dim').onclick=HideAjax;
	ShowLayer('ajaxpop');
}


function AjaxLoading() {
	document.getElementById('dim').style.display = 'block';
	if (parseInt(navigator.appVersion)>3) {
		if (navigator.appName=="Netscape") {
			winW = document.documentElement.clientWidth;
			winH = window.innerHeight;
		}
		if (navigator.appName.indexOf("Microsoft")!=-1) {
			winW = document.documentElement.clientWidth;
			winH = document.documentElement.clientHeight;
		}
	}
	d = document.getElementById('loading');
	if (window.scrollY) scr = window.scrollY;
	else if (document.documentElement.scrollTop) scr = document.documentElement.scrollTop;
	else scr = 0;
	if (!winH) winH = 500;
	if (!winW) winW = 1000;
	d.style.top =  parseInt(scr+winH/2)-50+ 'px';
	d.style.left =  parseInt(winW/2)-50+ 'px';
	d.style.visibility = 'visible';
}

function AjaxLoaded() {
	document.getElementById('dim').style.display = 'none';
	document.getElementById('loading').style.visibility = 'hidden';
}

function replaceAll(txt, replace, with_this) {
  return txt.replace(new RegExp(replace, 'g'), with_this);
}


function TANewLines() {
	tas = document.getElementsByTagName('textarea');
	for(i=0; i < tas.length; i++) tas[i].value = replaceAll(tas[i].value, '#n#', '\n');
}

function Progress() {
	if (!opinit) {
		opinit = 1;
		pids = document.getElementById('pids').innerHTML;
		AjaxAction('wizstep&do=1&num='+curop+'&pids='+pids);
	}
	progr++;
	if (progr > 5) progr = 0;
	str = '';
	for (i=0; i <=progr; i++) str += '.';
	document.getElementById('progress'+curop).innerHTML = str;
	prtm = setTimeout('Progress()', 200);
}


function ShowSingleDiv(ids, num) {
	ids = ids + '_';
	divs = document.getElementsByTagName('div');
	for(i=0; i < divs.length; i++) 
		if (divs[i].id.substr(0, ids.length) == ids)
			divs[i].style.display='none';
	document.getElementById(ids+num).style.display='block';
}



var htimer = 0;

function ShowBasket() {
	if (htimer) clearTimeout(htimer);
	setTimeout('SlideBasket(1)', 0);
}

function HideBasket() {
	htimer = setTimeout('SlideBasket(0)', 500);
}


function SlideBasket(sdir) {
	obj = document.getElementById('basketbox');
	if (sdir) {
		r = parseInt(obj.style.right)+10;
		if (r >= 0) return ;
	}
	else {
		r = parseInt(obj.style.right)-10;
		if (r < -225) return ;
	}
	obj.style.right = r+'px';
	document.getElementById('basket').style.right = (225+r)+'px';
	setTimeout('SlideBasket('+sdir+')', 10);
}


function RemoveDiv(did) {
	elem = document.getElementById(did);
	elem.parentNode.removeChild(elem);	
}


function SelTheme(tid) {
	v = document.getElementById('themeincl_' + tid).value;
	if (v == 0) {
		document.getElementById('themeincl_' + tid).value = tid;
		document.getElementById('themebox' + tid).style.background = '#c6ffc6';
	}
	else {
		document.getElementById('themeincl_' + tid).value = 0;
		document.getElementById('themebox' + tid).style.background = 'none';
	}
}

function CheckPluginCond(plid) {
	if (document.getElementById('plugincl_'+plid).checked) disp = 'block';
	else disp = 'none';
	el = document.getElementsByTagName('div');
	for(j=0; j < el.length; j++) if (el[j].id.indexOf('cond_'+plid) == 0) el[j].style.display=disp;
}

function CheckThemeCond(plid) {
	if (document.getElementById('themeincl_'+plid).value=='0') disp = 'none';
	else disp = 'block';
	el = document.getElementsByTagName('div');
	for(j=0; j < el.length; j++) if (el[j].id.indexOf('cond_'+plid) == 0) el[j].style.display=disp;
}


function SwitchLogin() {
	if (document.getElementById('loginform').style.display=='none') {
		document.getElementById('signupform').style.display='none';
		document.getElementById('loginform').style.display='block';
		document.getElementById('loginname').focus();
	}
	else {	
		document.getElementById('signupform').style.display='block';
		document.getElementById('loginform').style.display='none';
		document.getElementById('name').focus();
	}
}



function ToggleCB(prefix) {
	cbs = document.getElementsByTagName('input');
	counter = 0;
	for(i=0; i < cbs.length; i++)
		if (cbs[i].id.substr(0, prefix.length) == prefix) {
			cbs[i].checked = !cbs[i].checked;
			v = cbs[i].id.replace(prefix, '');
			if (cbs[i].checked) cbs[i].value = v;
			else cbs[i].value = 0;
		}

}
