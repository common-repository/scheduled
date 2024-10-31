<?php

function scheduled_shortcode($atts)
	{
	extract( shortcode_atts( array(
		'id' => 'none',
	), $atts ) );
	return scheduled_item($id, scheduled_admin()?1:0);
	}

function scheduled_item($itm, $admin=1, $uid='')
	{
	if ($admin && !scheduled_admin()) $admin=0;
	if (!file_exists($itmf = scheduled_itemfile($itm))) file_put_contents($itmf, scheduled_emptyfile());
//print "<br/>itmf=$itmf";

	if ($uid=="") $uid=uniqid("sch_");
	$AsDate = scheduled_showasdate();

	$xml = new DomDocument;
	if ($xml->load($itmf, LIBXML_NOWARNING | LIBXML_NOERROR) === false) return file_get_contents($itmf);
	$xsl = new DomDocument;
	$xsl->load(scheduled_transformxsl());

	$xh = new XsltProcessor();
	$xh->registerPHPFunctions();
	$xh->importStyleSheet($xsl);

	$xh->setParameter("", "Itm", $itm);
	$xh->setParameter("", "Now", time());
	$xh->setParameter("", "cutoffDate", time()+86400*7*52);  
	$xh->setParameter("", "PageAdmin", $admin);
	$xh->setParameter("", "uID", $uid);

	$result = $xh->transformToXML($xml);

	unset($xh);
	unset($xml);
	unset($xsl);
//die($result);
//print "<br/>strlen(result)=".strlen($result);
	return $result;
	}

function scheduled_random_number($n)	{	return "".rand(1,$n);	}

function scheduled_styleselect($n)
	{
	$s = "";
	$s = $s . "<select name='$n'>\n";
	foreach(scheduled_styles() as $a)
		$s = $s . " <option>$a</option>\n";
	$s = $s . "</select>\n";
	return $s;
	}

function scheduled_sort($a, $b)
	{
	global $SchedulerDoc;
	$aS = $SchedulerDoc->fetch_part("//items/item[@ix='$a']/to");
	$aA = $SchedulerDoc->fetch_part("//items/item[@ix='$a']/active");
	if ($aA!="") $aS += 1000000;
	$bS = $SchedulerDoc->fetch_part("//items/item[@ix='$b']/to");
	$bA = $SchedulerDoc->fetch_part("//items/item[@ix='$a']/active");
	if ($bA!="") $bS += 1000000;
//print "<br/>aS=$aS, bS=$bS";
	return $aS - $bS;
	}

function scheduled_lookuplist($itm)
	{
	global $SchedulerDoc;
//	$s = "<select name='ScheduledAdminSelect' multiple='multiple' size='10' style='width:350px;'><option>1</option><option>2</option></select>";
//	$D = new DomDocument;
//	$D->loadXML($s);
//	return $s;

	$SchedulerDoc = scheduled_file($itm);
	$n = $SchedulerDoc->count_parts("//items/item");
	$r = $SchedulerDoc->fetch_list("//items/item/@ix");
	usort($r, "scheduled_sort");

	$s = $s . "<select name='ScheduledAdminSelect' id='ScheduledAdminSelect' size='12'>\n";
	foreach($r as $i)
		{
		$a = $SchedulerDoc->fetch_part("//items/item[@ix='$i']/@from");
		if (strtotime($a)!=0) $a = strtotime($a);
		if ($a==0) $a = time()-100000;
		$b = $SchedulerDoc->fetch_part("//items/item[@ix='$i']/@to");
		if (strtotime($b)!=0) $b = strtotime($b);
		if ($b==0) $b = time()+100000;
//print "<br/>a=$a, b=$b";
		$c = $SchedulerDoc->fetch_part("//items/item[@ix='$i']/@title");
		if ($c=="") $c="[NO TITLE]";
		$sel = ($i==1);
		$d = $SchedulerDoc->fetch_part("//items/item[@ix='$i']/@default");
		$e = $SchedulerDoc->fetch_part("//items/item[@ix='$i']/@active");
		$de = ($d!=""?"*": ($e!=""?".":"X"));
		$s = $s . "   <option value='$i'".($sel?" selected='1'":"").">".$de." ".date("m/d/Y", $a)."-".date("m/d/Y", $b)." - ".$c."</option>\n";
		}
	$s = $s . "</select>";
	unset($F);
	unset($SchedulerDoc);
	return $s;
	}

function scheduled_validate_dates(&$FromS, &$ToS)
	{
//print "<br/>scheduled_validate_dates($FromS, $ToS)";
	$FromS = date("m/d/Y", strtotime($FromS)==0?time():strtotime($FromS));
	$ToS = date("m/d/Y", strtotime($ToS)==0?time()+(86400*7):strtotime($ToS));
	if (strtotime($FromS) > strtotime($ToS)) $ToS = date("m/d/Y", strtotime($FromS) + (86400*7));
	}

function scheduled_item_preprocess($c)
	{
//print "<br/>scheduled_item_preprocess($c)";
	while(($a = strpos($c, "<page "))!==false)
		{
		$b = strpos($c, "</page>", $a);
		if ($b!==false) $b = $b + 6; else $b = strpos($c, ">", $a);
		$l = substr($c, $a, $b - $a + 1);
//print "<br/>l=$l";
		$t = ReplacePageLink($l);
//print "<br/>t=$t";
		$c = str_replace($l, $t, $c);
		}
	return $c;
	}