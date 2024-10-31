<?php 

function scheduled_options_defaults()
	{
	return array(
		'scheduled_version'		=>	'1.0.0',
		'scheduled_minimum_editor'	=>	'Administrator',
		'scheduled_source_dir'	=>	'/items/',
		'scheduled_log_ajax'		=>	'1',
		);
	}

function scheduled_init_options()
	{
	$operation = $_POST["submit"] != '' ? "saving" : $_POST["delete"];
	foreach(scheduled_options_defaults() as $opt=>$def)
		{
		if ($operation == "delete") {delete_option($opt);continue;}
		add_option($opt, $def);							// whether saving or initializing, set option defaults
		if ($operation == "saving") update_option($opt, $_POST[$opt]);
		}
	if ($operation=="saving") scheduled_msg("Options Updated.");
	}

function scheduled_admin_panel()
	{
?>

<div class="wrap">
<?php    echo "<h2>" . __( 'Scheduled Options' ) . "</h2>"; ?>

	<p>
	These are the settings for Scheduled.
	</p>

<form name="option_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">

	<?php    echo "<h4>" . __( 'HOP DB Options' ) . "</h4>"; ?>
	<p>
	<table>
		<tr><td><?php _e("Minimum Editor:  " ); ?></td><td><?php echo scheduled_role_select("scheduled_minimum_editor",get_option('scheduled_minimum_editor')) ?></td></tr>
		<tr><td><?php _e("Dir:  " ); ?></td><td><input type="text" name="scheduled_source_dir" size='30' value="<?php echo get_option('scheduled_source_dir'); ?>" size="20"></td></tr>
		<tr><td><?php _e("Log Ajax: " ); ?></td><td><input type="text" name="scheduled_log_ajax" size='30' value="<?php echo get_option('scheduled_log_ajax'); ?>" size="20"></td></tr>
	</table>
	</p>
	

	<p class="submit">
	<input type="submit" name="submit" value="Save" class='button-primary'/>
	</p>
</form>
<hr/>
<form name='Scheduled'>
<table>
<tr><td colspan='2'>Path: <?php echo scheduled_source_dir() ?> <br/></td></tr>
<tr><td>
Existing Items: <br/><select name='Scheduled_ItemSelect' >
<option></option>
<?php

foreach(scandir(scheduled_source_dir()) as $f)
	{
	if ($f=="." || $f=="..") continue;
	echo "<option>".str_replace(scheduled_extension(), "", $f)."</option>\n";
	}

?>
</select>
<input type="submit" name="submit" value="Show" class="button-primary" onClick='return Scheduled_Refresh("Scheduled_Admin_Display", document.Scheduled.Scheduled_ItemSelect.value)' />
<?php
// <input type="submit" name="submit" value="Delete" class="button-secondary" onClick='return Scheduled_Refresh("Scheduled_Admin_Display", document.Scheduled.Scheduled_ItemSelect.value)' />
?>
<br/>
<input name='Scheduled_NewItem' /><input type="submit" name="submit" value="New Item" class="button-primary" onClick='return Scheduled_Refresh("Scheduled_Admin_Display", document.Scheduled.Scheduled_NewItem.value)'/>
</td>
<td>
<div id='Scheduled_Admin_Display'></div>
</td>
</tr>
</table>
</form>
</div>
<?php
	}
?>