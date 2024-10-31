<?php
/*
Plugin Name: Scheduled
Plugin URI: http://www.BlazingGlory.Org/
Description: Scheduled Items
Version: 1.0
Author: Benjamin Hoogterp
Author URI: http://www.BlazingGlory.Org/
License: None
History:
1.0 initial release
*/

/*  Copyright 2011 Benjamin Hoogterp (benjaminhoogterp@gmail.com)

    This is free.

*/

include("scheduled_options.php");			// needed to init the defaults every time
scheduled_init_options();

$scheduled_admin_init = false;
$scheduled_message = "";

function scheduled_admin($a='')			{	if (!current_user_can(strtolower(get_option('scheduled_minimum_editor')))) return false; if ($a && !current_user_can(strtolower($a))) return false; return true;	}
function scheduled_plugin_url($q="")		{	return WP_PLUGIN_URL."/".dirname(plugin_basename(__FILE__)).$q;	}
function scheduled_plugin_dir($q="")		{	return dirname(__FILE__).$q;	}
function scheduled_current_url($qs="qs")	//{	return "http://".$_SERVER[HTTP_HOST].$_SERVER[SCRIPT_NAME].($qs=="qs"&&$_SERVER[QUERY_STRING]!=""?"?".$_SERVER[QUERY_STRING]:($qs=="qs"?"":$qs));	}
	{
	$x = "http://".$_SERVER[HTTP_HOST].$_SERVER[SCRIPT_NAME];
	if ($qs == "qs")
		{
		$x = $_SERVER[REQUEST_URI];
		}
	else
		$x = $x . $qs;
	return $x;
	}

function scheduled_source_dir($q='')		{	return scheduled_plugin_dir(get_option("scheduled_source_dir").$q);	}
function scheduled_safename($itm)			{	return strtolower(str_replace(array(" ","'",".","/","\\"), array('-'), $itm));	}
function scheduled_extension()			{	return ".xml";	}
function scheduled_itemfile($itm)			{	return scheduled_source_dir() . scheduled_safename($itm) . scheduled_extension();	}
function scheduled_file($itm)			{	include_once("class_xml_file.php"); $x = new xml_file(); $x->load(scheduled_itemfile($itm)); return $x;}
function scheduled_autofile($a)			{	return ItemFile(get_querystring_var($a, "i"));	}
function scheduled_emptyfile()			{	return "<?xml version='1.0'?>\n<items name='[new]'/>";	}

function scheduled_transformxsl()			{	return scheduled_plugin_dir("\scheduled_transform.xsl");	}
function scheduled_showasdate()			{	$x = strtotime($_REQUEST["ShowAsDate"]);if (!$x) $x = time(); return date("m/d/Y", $x);}

function scheduled_styles()				{	return array("Scheduled", "Everything", "Ascending", "Descending", "Random", "Itinerary");	}
function scheduled_roles()				{	return array("Administrator","Editor","Contributor","Subscriber");	}
function scheduled_role_select($n, $c="N*NE")	{	return str_replace("<option>$c","<option selected='selected'>$c","<select name='$n'><option>".implode("</option><option>",scheduled_roles())."</option></select>");	}

function scheduled_msg($msg, $style=1)		{	return scheduled_msg_style($style).$msg.scheduled_msg_style($style,1); }
function scheduled_msg_style($style, $end=false, $str="")
	{
	if ($str!="") return scheduled_msg_style($style).$str.scheduled_msg_style($style, true);
	switch($style)
		{
		case 1:	return $end?"</div>":"<div style='display:inline-block;background-color:lightyellow;width:93%;margin:15px 15px 15px 15px;padding:5px 5px 5px 5px;border: solid 1px lightblue;border-radius:3px;'>";
		case 2:	return $end?"</div>":"<div style='display:inline-block;background-color:lightyellow;width:93%;margin:15px 15px 15px 15px;padding:5px 5px 5px 5px;border: solid 1px lightblue;border-radius:3px;'>";
		case 401:	return $end?"</div>":"<div style='display:inline-block;background-color:#CFFFCF;width:93%;margin:15px 15px 15px 15px;padding:5px 5px 5px 5px;border: solid 1px green;border-radius:3px;'>";
		case 501:	return $end?"</div>":"<div style='display:inline-block;background-color:#FFCFCF;width:93%;margin:15px 15px 15px 15px;padding:5px 5px 5px 5px;border: solid 1px red;border-radius:3px;'>";
		case 1001:	return $end?"</span></span>":"<span class='update-plugins count-$str' title='hopdb'><span class='update-count'>";
		case 1002:	return $end?"</span>":"<span id='ab-updates' class='update-count'>";
//<span class='update-plugins count-1' title='title'><span class='update-count'>1</span></span>
		default:	return "";
	}	}


//  hook up
wp_enqueue_script( 'scheduled_js', scheduled_plugin_url("/scheduled.js"));
wp_enqueue_style( 'scheduled_css', scheduled_plugin_url("/scheduled.css"));

add_shortcode( 'scheduled', 'scheduled_shortcode_hook' );
add_action('admin_menu', 'scheduled_admin_menu');
add_action('widgets_init', 'scheduled_widgets_init' );
add_action('init', 'scheduled_ajax_hook' );

add_option("scheduled_source_dir", "/items/");
add_option("scheduled_log_ajax", "1");
add_option("scheduled_minimum_editor", "contributor");

function scheduled_admin_menu()
	{
	global $scheduled_admin_init, $scheduled_message;
	$scheduled_admin_init = true;

	add_options_page('Options', 'Scheduled', 9, 'scheduled_options', 'scheduled_options_hook');

	add_filter('ozh_adminmenu_icon_scheduled_options', 'scheduled_adminmenu_customicons');
	}

function scheduled_adminmenu_customicons($in)
	{
	switch($in)
		{
		default:			return scheduled_plugin_url('/wrench_orange.png');
		}
	}

function scheduled_options_hook   ()	{	scheduled_admin_panel();			}

function scheduled_hop_add_page()		{      include_once("scheduled_edit.php"); print scheduled_hop_add();	}
function scheduled_hop_edit_page()		{      include_once("scheduled_edit.php"); print scheduled_hop_edit();	}
function scheduled_hop_review_page()	{      include_once("scheduled_edit.php"); print scheduled_hop_review();	}
function scheduled_hop_list_page()		{      include_once("scheduled_edit.php"); print scheduled_hop_list();	}
function scheduled_import_page()		{	include_once("scheduled_import.php"); print scheduled_import();	}
function scheduled_hop_checkurl_page()	{	include_once("scheduled_edit.php"); print scheduled_hop_checkurl();	}
function scheduled_hop_feed_page()		{	include_once("scheduled_feed.php"); print scheduled_feeds();	}

function scheduled_shortcode_hook($atts)	{	include_once("scheduled_item.php"); return scheduled_shortcode($atts);	}
function scheduled_widgets_init()		{	include_once("class_scheduled_widget.php"); register_widget( 'scheduled_widget' );	}

function scheduled_ajax_hook()
	{
	if ($_REQUEST[SCHEDULED]!="")		{	include_once("scheduled_ajax.php");return scheduled_ajax();	}
	}
	
?>
