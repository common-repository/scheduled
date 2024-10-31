<?php

class scheduled_widget extends WP_Widget 
	{
	function scheduled_widget()
		{
		$widget_ops = array( 
			'classname' => 'scheduled', 
			'description' => 'Displays a scheduled item.' 
			);

		$control_ops = array( 
			'width' => 300, 
			'height' => 350, 
			'id_base' => 'scheduled-widget' );

		$this->WP_Widget( 'scheduled-widget', 'Scheduled Item', $widget_ops, $control_ops );
		}

	function widget( $args, $instance )
		{
		extract( $args );

		$title = $instance['title'];
		$id = $instance['id'];

		$w = "";

///////   Standard widget leadin
		$w .= $before_widget;
		if ($title) $w .= $before_title . $title . $after_title;

///////   Our Widget Display code
		include_once("scheduled_item.php");
//print "<br/>min=".get_option('scheduled_minimum_editor');
//print "<br/>scheduled_admin()=".(scheduled_admin()?"yes":"no");
		$x .= scheduled_item($id, scheduled_admin()?1:0);
		if ($x=="") return;
		$w .= $x;

///////   Standard widget trailer
		$w .= $after_widget;

		print $w;					//  output the widget
		}

	function update( $new_instance, $old_instance )
		{
		$instance = $old_instance;

		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['id'] = scheduled_safename(strip_tags( $new_instance['id'] ));

		return $instance;
		}

	function defaults()
		{
		return array(
			'id'=>'default',
			'title'=>'Item',
			);
		}
	
	function form($instance)
		{
		$instance = wp_parse_args((array) $instance, $this->defaults());
?>

		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<p>
			<label for="<?php echo $this->get_field_id( 'id' ); ?>">ID:</label>
			<input id="<?php echo $this->get_field_id( 'id' ); ?>" name="<?php echo $this->get_field_name( 'id' ); ?>" value="<?php echo $instance['id']; ?>" style="width:100%;" />
		</p>

<?php
		}
	}

?>