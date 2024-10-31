<?php

function scheduled_ajax_fail($reason)
	{
	die("-Failure: $reason");
	}

function scheduled_ajax_success($fields)
	{
	$r = "+OK\n";
	foreach($fields as $a=>$b)
		{
		if ($a=="content") continue;
		$r = $r . "$b\n";
		}
	if ($fields[content]!='')
	$r = $r . $fields[content];
	die($r);
	}

function scheduled_ajax()
	{
	$op = $_REQUEST["op"];
	$id = $_REQUEST["id"];
	$it = $_REQUEST["it"];
	$ix = $_REQUEST["ix"];
//	if (get_option("scheduled_log_ajax")!="") file_put_contents(scheduled_plugin_dir("/ajax.txt"), $_SERVER['QUERY_STRING'] . "\n", FILE_APPEND);
	
	if ($it=="") return scheduled_ajax_fail("No item given for option load.");
	$F = scheduled_file($it);

	if (!scheduled_admin($F->fetch_part("//items/@access"))) return "-No Access";

	switch($op)
		{
		case "optionload":	return scheduled_ajax_options_load($F);
		case "optionsave":	return scheduled_ajax_options_save($F);
		case "itemload":	return scheduled_ajax_item_load($F, $ix);
		case "itemsave":	return scheduled_ajax_item_save($F, $ix);
		case "itemdelete":	return scheduled_ajax_item_delete($F, $ix);
		case "itemremove":	return scheduled_ajax_item_remove($it);
		case "refresh":	return scheduled_ajax_refresh($id, $it);
		default: 		return scheduled_ajax_fail("unknown operation $op");
		}
	}

function scheduled_ajax_options_load($F)
	{
	$op = array();
	$op[name] = $F->fetch_part("//items/@name");
	$op[style] = $F->fetch_part("//items/@style");
	$op[xsl] = $F->fetch_part("//items/@xsl");
	$op[access] = $F->fetch_part("//items/@access");
	unset($F);
	return scheduled_ajax_success($op);
	}

function scheduled_ajax_options_save($F)
	{
	$name = $_REQUEST["name"];
	if ($name=="") return scheduled_ajax_fail("Invalid name on options save");
	$style = $_REQUEST["style"];
	if ($style=="") return scheduled_ajax_fail("Invalid style on options save");
	$xsl = $_REQUEST["xsl"];
	$F->set_part("//items/@name", $name);
	$F->set_part("//items/@style", $style);
	$F->set_part("//items/@xsl", $xsl);
	$F->set_part("//items/@access", $xsl);
	$F->save();
	unset($F);
	return scheduled_ajax_success(array());
	}

function scheduled_ajax_item_load($F, $ix)
	{
	if ($ix=="") return scheduled_ajax_fail("No id given for item load.");
	$op = array();
	$op[name] = $F->fetch_part("//items/item[@ix='$ix']/@title");
	$op[from] = $F->fetch_part("//items/item[@ix='$ix']/@from");
	if ($op[from]=="") $op[from]=time();
	if (strtotime($op[from])==0) $op[from] = date("m/d/Y", $op[from]);
	$op[to] = $F->fetch_part("//items/item[@ix='$ix']/@to");
	if ($op[to]=="") $op[to]=time();
	if (strtotime($op[to])==0) $op[to] = date("m/d/Y", $op[to]);
	$op['default'] = $F->fetch_part("//items/item[@ix='$ix']/@default");
	$op[active] = $F->fetch_part("//items/item[@ix='$ix']/@active");
	$op[content] = trim($F->fetch_part("//items/item[@ix='$ix']"));
	unset($F);
	return scheduled_ajax_success($op);
	}

function scheduled_ajax_item_save($F, $ix)
	{
	if ($ix=="") $ix = uniqid();			// create
	$title= $_REQUEST["title"];
	if ($title=="") return scheduled_ajax_fail("Please Specify a Title");
	$from = strtotime($_REQUEST["from"]);
	$to = strtotime($_REQUEST["to"]);
	$content = $_REQUEST["content"];
	$content = str_replace("]]>","]]&gt;", $content);
	$content = str_replace("\'", "'", $content);
	if ($content == "") return scheduled_ajax_fail("Item is empty.  Please enter some content.");
//print "<br/>content=$content";
	$default = $_REQUEST["default"];
	if ($default=="false") $default="";
	$active = $_REQUEST["active"];
	if ($active=="false") $active="";

	$F->set_part("//items/item[@ix='$ix']/@title", $title);
	$F->set_part("//items/item[@ix='$ix']/@from", $from);
	$F->set_part("//items/item[@ix='$ix']/@to", $to);
	$F->set_part("//items/item[@ix='$ix']/@active", $active);
	$F->set_part("//items/item[@ix='$ix']/@default", $default);
	$F->set_part("//items/item[@ix='$ix']", $content);
	$F->save();
	unset($F);
	return scheduled_ajax_success(array());
	}

function scheduled_ajax_item_delete($F, $ix)
	{
	if ($ix=="") return scheduled_ajax_fail("No ix given for item delete.");
	$F->delete_part("//items/item[@ix='$ix']");
	$F->save();
	unset($F);
	return scheduled_ajax_success(array());
	}

function scheduled_ajax_item_remove($it)
	{
	if ($it=="") return scheduled_ajax_fail("No item given for item save.");
	unlink(scheduled_itemfile($it));
	return scheduled_ajax_success(array());
	}

function scheduled_ajax_refresh($id, $it)
	{
	if ($it=="") return scheduled_ajax_fail("No item given for item save.");
	if ($id=="") return scheduled_ajax_fail("No id given for item save.");
	include_once("scheduled_item.php");
	$s = scheduled_item($it,1,$id);			// if they got here, they're already admin..
	die($s);
	}

?>