var ScheduledxmlHttp;
function Scheduled_GetXmlHttpObject()
	{
	try {ScheduledxmlHttp=new XMLHttpRequest();} // Firefox, Opera 8.0+, Safari
	catch (e)  {try {ScheduledxmlHttp=new ActiveXObject("Msxml2.XMLHTTP");}// Internet Explorer
	  catch (e)  {try {ScheduledxmlHttp=new ActiveXObject("Microsoft.XMLHTTP");}
		catch(e) {}
	  } }
	return ScheduledxmlHttp;
	}


function Scheduled_OnOff(id,value,center)
	{	
//alert("Scheduled_OnOff("+id+", "+value+")");
	x = document.getElementById(id);
	if (x && x.style) x.style.display=(value?"inline":"none");	
	if (value && center)
		{
		Scheduled_center(x);
		}

	}

function Scheduled_GetItem(id, field)
	{
	switch(field)
		{
		case "main.showasdate":	return eval("document.ScheduledShowAs_"+id+".ShowAsDate.value");
		case "options.name":		return eval("document.ScheduledOptionForm_"+id+".name.value");
		case "options.style":	return eval("document.ScheduledOptionForm_"+id+".style.value");
		case "options.xsl":		return eval("document.ScheduledOptionForm_"+id+".xsl.value");
		case "options.access":	return eval("document.ScheduledOptionForm_"+id+".access.value");
		case "edit.ix":		return eval("document.ScheduledEditForm_"+id+".ix.value");
		case "edit.title":		return eval("document.ScheduledEditForm_"+id+".itemtitle.value");
		case "edit.from":		return eval("document.ScheduledEditForm_"+id+".from.value");
		case "edit.to":		return eval("document.ScheduledEditForm_"+id+".to.value");
		case "edit.content":		return eval("document.ScheduledEditForm_"+id+".content.value");
		case "edit.default":		return eval("document.ScheduledEditForm_"+id+".default.checked");
		case "edit.active":		return eval("document.ScheduledEditForm_"+id+".active.checked");
		default:  alert("unknown field: "+field);
		}
	}

function Scheduled_SetItem(id, field, value)
	{
//alert("ScheduledOptionForm_"+id);
	switch(field)
		{
		case "main.showasdate":	cb=eval("document.ScheduledShowAs_"+id+".ShowAsDate");break;
		case "options.name":		cb=eval("document.ScheduledOptionForm_"+id+".name");break;
		case "options.style":	cb=eval("document.ScheduledOptionForm_"+id+".style");break;
		case "options.xsl":		cb=eval("document.ScheduledOptionForm_"+id+".xsl");break;
		case "options.access":	cb=eval("document.ScheduledOptionForm_"+id+".access");break;
		case "edit.ix":		cb=eval("document.ScheduledEditForm_"+id+".ix");break;
		case "edit.title":		cb=eval("document.ScheduledEditForm_"+id+".itemtitle");break;
		case "edit.from":		cb=eval("document.ScheduledEditForm_"+id+".from");break;
		case "edit.to":		cb=eval("document.ScheduledEditForm_"+id+".to");break;
		case "edit.default":		cb=eval("document.ScheduledEditForm_"+id+".default.checked="+(value==''?'false':'true'));return;
		case "edit.active":		cb=eval("document.ScheduledEditForm_"+id+".active.checked="+(value==''?'false':'true'));return;
		case "edit.content":		cb=eval("document.ScheduledEditForm_"+id+".content");break;
		default:  alert("unknown field: "+field);
		}
	cb.value=value;
	}

function Scheduled_SelectedIx(id)
	{
	rr=eval("document.ScheduledAdminForm_"+id+".ScheduledAdminSelect");
	if (rr)
		{
		rv = rr.options[rr.selectedIndex];
		if (rv) ix = rr.options[rr.selectedIndex].value; else ix = 0;
		}
	else ix = 0;
//alert(ix);
	return ix;
	}

function Scheduled_Admin(operation, id, itm)
	{
//alert("Scheduled_Admin("+operation+", "+id+", "+itm+")");
	switch(operation)
		{
		case "main":		showpage = "main"; break;
		case "edit":		if (Scheduled_SelectedIx(id)=="") {alert("No Item Selected.");return false;}
					Scheduled_LoadItem(id, itm, Scheduled_SelectedIx(id)); showpage = "edit"; break;
		case "delete":	Scheduled_DeleteItem(id, itm, Scheduled_SelectedIx(id)); showpage = "main"; break;
		case "create":	Scheduled_LoadItem(id, '', ''); showpage = "edit"; break;
		case "options":	Scheduled_LoadOptions(id, ''); Scheduled_LoadOptions(id, itm); showpage = "options"; break;
		case "close":		showpage = "";
		}
//	Scheduled_OnOff(id+"_open", showpage=="",true);
	Scheduled_OnOff(id+"_main", showpage=="main",true);
	Scheduled_OnOff(id+"_options", showpage=="options",true);
	Scheduled_OnOff(id+"_options_loader", showpage=="options",false);
	Scheduled_OnOff(id+"_edit", showpage=="edit",true);
	Scheduled_OnOff(id+"_edit_loader", showpage=="edit" && operation!="create",false);
	return false;
	}

function Scheduled_Ajax_URL(op, it)
	{
	var url = location.href;
	var url_parts = url.split('?');
	var x = url_parts[0] + "?SCHEDULED=1";
	x = x + "&op=" + op;
	x = x + "&it=" + it;
	return x;
	}

function Scheduled_LoadOptions(id, itm)
	{
	Scheduled_SetItem(id, "options.name", '');
	Scheduled_SetItem(id, "options.style", '');
	Scheduled_SetItem(id, "options.xsl", '');
	Scheduled_SetItem(id, "options.access", '');
	if (itm=="") return;

	var u = Scheduled_Ajax_URL("optionload", itm);
	ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
//alert("requesting options, itm="+itm+": "+u);
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
//alert("options returned");
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') alert(r[0]);
			else
				{
				if (r.length>=2) Scheduled_SetItem(id, "options.name", r[1]);
				if (r.length>=3) Scheduled_SetItem(id, "options.style", r[2]);
				if (r.length>=4) Scheduled_SetItem(id, "options.xsl", r[3]);
				if (r.length>=5) Scheduled_SetItem(id, "options.access", r[4]);
				Scheduled_OnOff(id+"_options_loader", false,false);
				}
			Scheduled_OnOff(id+"_options_loader", false,false);
			}
		}
	
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();
	}

function Scheduled_SaveOptions(id, itm)
	{
	n = Scheduled_GetItem(id, "options.name");
	s = Scheduled_GetItem(id, "options.style");
	x = Scheduled_GetItem(id, "options.xsl");
	a = Scheduled_GetItem(id, "options.access");

//alert(x);
	var u = Scheduled_Ajax_URL("optionsave", itm) + "&name="+ n + "&style=" + s + "&xsl=" + x + "&access=" + a;
	ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') alert(r[0]);
			Scheduled_Refresh(id, itm);
			}
		}
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();
	Scheduled_OnOff(id+"_options_loader", true, false);
	}

function Scheduled_LoadItem(id, itm, ix)
	{
	Scheduled_SetItem(id, "edit.ix", ix);
	Scheduled_SetItem(id, "edit.title", '');
	Scheduled_SetItem(id, "edit.from", '');
	Scheduled_SetItem(id, "edit.to", '');
	Scheduled_SetItem(id, "edit.default", '');
	Scheduled_SetItem(id, "edit.active", 'true');
	Scheduled_SetItem(id, "edit.content", '');
	if (ix=="") return;

	var u = Scheduled_Ajax_URL("itemload", itm) + "&ix="+ix;
	ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
//alert("item returned");
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') alert(r[0]);
			else
				{
				if (r.length>=2) Scheduled_SetItem(id, "edit.title", r[1]);
				if (r.length>=3) Scheduled_SetItem(id, "edit.from", r[2]);
				if (r.length>=4) Scheduled_SetItem(id, "edit.to", r[3]);
				if (r.length>=5) Scheduled_SetItem(id, "edit.default", r[4]);
				if (r.length>=6) Scheduled_SetItem(id, "edit.active", r[5]);
				if (r.length>=7) 
					{
					rst = r.slice(6);
					cont = rst.join("\n");
					Scheduled_SetItem(id, "edit.content", cont);
					}
				}
			Scheduled_OnOff(id+"_edit_loader", false, false);
			}
		}
	
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();
	}

function Scheduled_SaveItem(id, itm)
	{
//alert("Scheduled_SaveItem("+id+", "+itm+")");
	ix  = Scheduled_GetItem(id, "edit.ix");
	tit = Scheduled_GetItem(id, "edit.title");
	frm = Scheduled_GetItem(id, "edit.from");
	too = Scheduled_GetItem(id, "edit.to");
	con = Scheduled_GetItem(id, "edit.content");
	def = Scheduled_GetItem(id, "edit.default");
	act = Scheduled_GetItem(id, "edit.active");

	var u = Scheduled_Ajax_URL("itemsave", itm);
	u = u + "&ix=" + escape(ix);
	u = u + "&title=" + escape(tit);
	u = u + "&from=" + escape(frm);
	u = u + "&to=" + escape(too);
	u = u + "&active=" + escape(act);
	u = u + "&default=" + escape(def);
	u = u + "&content=" + escape(con);

	par = "content=" + escape(con);

	var ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') 
				{
				alert(r[0]);
				Scheduled_OnOff(id+"_edit_loader", false, false);
				}
			else Scheduled_Refresh(id, itm);
			}
		}
	ScheduledxmlHttp.open("GET", u, true);
//	ScheduledxmlHttp.open("POST", u, true);
	ScheduledxmlHttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ScheduledxmlHttp.send();
//	ScheduledxmlHttp.send(par);

	Scheduled_OnOff(id+"_edit_loader", true, false);
	return false;
	}


function Scheduled_DeleteItem(id, itm, ix)
	{
	var u = Scheduled_Ajax_URL("itemdelete", itm) + "&ix="+ix;
	var ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') alert(r[0]);
			Scheduled_Refresh(id, itm);
			}
		}
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();

	Scheduled_OnOff(id+"_main_loader", true, false);
	return false;
	}

function Scheduled_RemoveItem(id, itm)
	{
	var u = Scheduled_Ajax_URL("itemremove", itm)
	var ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
			s = ScheduledxmlHttp.responseText;
			r = s.split("\n");
			if (r[0].substring(0,1)!='+') alert(r[0]);
			Scheduled_Refresh(id, '');
			}
		}
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();

	Scheduled_OnOff(id+"_main_loader", true, false);
	return false;
	}

function Scheduled_Refresh(id, itm)
	{
	var u = Scheduled_Ajax_URL("refresh", itm) + "&id="+id;
	ScheduledxmlHttp = Scheduled_GetXmlHttpObject();
	ScheduledxmlHttp.onreadystatechange=function()
		{
		if (ScheduledxmlHttp.readyState==4 && ScheduledxmlHttp.status==200)
			{
			s = ScheduledxmlHttp.responseText;
			r = document.getElementById(id).innerHTML = s;
			Scheduled_Admin("main", id, itm);
			}
		}
	
	ScheduledxmlHttp.open("GET", u, true);
	ScheduledxmlHttp.send();
	return false;x
	}


function Scheduled_WindowWidth()
	{
	if(typeof( window.innerWidth ) == 'number' )	
		return parseInt(window.innerWidth);			//Non-IE

	if(document.documentElement && document.documentElement.clientWidth)
		return parseInt(document.documentElement.clientWidth);	//IE 6+ in 'standards compliant mode'

	if(document.body && document.body.clientWidth)
		return parseInt(document.body.clientWidth);		//IE 4 compatible

	return parseInt(document.width);
	}

function Scheduled_WindowHeight()
	{
	if(typeof( window.innerHeight ) == 'number' )	
		return parseInt(window.innerHeight);			//Non-IE

	if(document.documentElement && document.documentElement.clientHeight)
		return parseInt(document.documentElement.clientHeight);	//IE 6+ in 'standards compliant mode'

	if(document.body && document.body.clientHeight)
		return parseInt(document.body.clientHeight);		//IE 4 compatible
	return parseInt(document.height);
	}
function Scheduled_WindowScrollY()
	{
	if(typeof(window.pageYOffset) == 'number' ) 	return parseInt(window.pageYOffset);    		//Netscape compliant
	if(document.body &&  document.body.scrollTop)	return parseInt(document.body.scrollTop);			//DOM compliant
	if(document.documentElement && document.documentElement.scrollTop)
								return parseInt(document.documentElement.scrollTop);	//IE6 standards compliant mode
	return 0;
	}

function Scheduled_WindowScrollX()
	{
	if(typeof(window.pageXOffset) == 'number' )	return parseInt(window.pageXOffset);    		//Netscape compliant
	if(document.body &&  document.body.scrollLeft)	return parseInt(document.body.scrollLeft);	//DOM compliant
	if(document.documentElement && document.documentElement.scrollLeft)
								return parseInt(document.documentElement.scrollLeft);	//IE6 standards compliant mode
	return 0;
	}


function Scheduled_center(o)
	{
	if (!o) return;
	o.style.left = ((Scheduled_WindowWidth()  - parseInt(o.offsetWidth )) / 2 + Scheduled_WindowScrollX())+'px';
	o.style.top  = ((Scheduled_WindowHeight() - parseInt(o.offsetHeight)) / 2 + Scheduled_WindowScrollY())+'px';
	if (parseInt(o.style.top)<0) o.style.top='0px';
	}