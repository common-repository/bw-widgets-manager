<?php
/*
Plugin Name: #BW Widgets Manager
Version: 1.0
Description: Adds BW custom widgets and allows selective deactivation of widgets
Author: #BRITEWEB (Timothy Kraumanis)
Author URI: http://www.briteweb.com/
*/

require_once( dirname(__FILE__).'/bw_menu.php');// Add BW top-level menu

global $dashboard_fields;

define( 'BW_WIDGETS_SETTINGS_OPTION', "bw_widgets_inactive" );

/* ========| DEACTIVE SELECT WIDGETS |======== */


function bw_widgets_disable() {

	global $pagenow;
		
	if ( $pagenow == "widgets.php" ) {

		$fields = get_option( BW_WIDGETS_SETTINGS_OPTION );
		if ( is_array( $fields ) ) {
			foreach ( $fields as $key=>$value ) {
				unregister_widget($key);
			}
		}
	
	}
		

}
add_action( 'widgets_init', 'bw_widgets_disable' );


/* ========| CREATE ADMIN PAGE |======== */

function bw_widgets_admin_menu() {
	add_submenu_page( 'bw_plugin_menu', '#BW Widgets Manager', 'Widgets Manager', 'manage_options', 'bw-widgets', 'bw_widgets_admin' );
}
add_action('admin_menu', 'bw_widgets_admin_menu');

function bw_widgets_admin() {

global $wp_registered_widgets, $wp_widget_factory;

$allwidgets = array();
foreach ( $wp_widget_factory->widgets as $key=>$field ) {
	$allwidgets[$key] = array(
		'class'	=> $key,
		'name'	=> $field->name,
		'description' => $field->widget_options['description']
	);
}
usort( $allwidgets, '_bw_sort_name_callback' );

if ( !empty( $_POST ) && $_POST['action'] == 'bw_widgets_save' && check_admin_referer( 'bw_widgets_save' ) ) {

	$old_fields = get_option( BW_WIDGETS_SETTINGS_OPTION );	
	$new_fields = array();
	
	foreach ( $allwidgets as $key=>$field ) {
		if ( $_POST['field_active'][$field['class']] != 1 ) $new_fields[$field['class']] = 1;
	}
		
	update_option( BW_WIDGETS_SETTINGS_OPTION, $new_fields );

}

$fields = get_option( BW_WIDGETS_SETTINGS_OPTION );

?><div class="wrap">
<div class="icon32" style="width:auto;"><img src="<?php echo plugins_url('/images/bw-page-logo.png', __FILE__); ?>" alt="Briteweb" /><br /></div>
<h2>Widgets Manager</h2>

<?php 
//pre_dump($allwidgets);
?>

<div id="info"></div>

<form method="post">

<div id="bw-widgets-list">
	
	<a href="http://www.briteweb.com" target="_blank" id="bw-list-logo"><img src="<?php echo plugins_url('/images/bw-list-logo.png', __FILE__); ?>" /></a>
	
	<table class="widefat">
	<thead>
		<tr>
			<th scope="col" style="width:2.5em">Active</th>
			<th scope="col">Widget</th>
			<th></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th scope="col">Active</th>
			<th scope="col">Widget</th>
			<th></th>
		</tr>
	</tfoot>
	<tbody class="field-rows">
	<?php foreach ( $allwidgets as $key=>$field ) :  ?>	
		<tr class="field-row">
			<td style="padding-top:6px;"><input type="checkbox" name="field_active[<?php echo $field['class']; ?>]" value="1" <?php if ( empty( $fields[$field['class']] ) ) echo 'checked="checked"'; ?> /></td>
			<td colspan="2"><strong><?php echo $field['name']; ?></strong><br />
			<em style="font-size:11px; color:#999;"><?php echo $field['description']; ?></em></td>
			
		</tr>
	<?php endforeach; ?>
	</tbody>
	</table>
	
</div>

<p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>
<input type="hidden" name="action" value="bw_widgets_save" />
<?php wp_nonce_field( "bw_widgets_save" ); ?>

</form>

<style type="text/css">

#bw-widgets-list {
position: relative;
width: 520px;
}

#bw-widgets-list table {
width: 520px;
}

#bw-list-logo {
top: -8px;
display: block;
position: absolute;
right: -9px;
}

</style>

<?php
	
}

function _bw_sort_name_callback( $a, $b ) {
	return strnatcasecmp( $a['name'], $b['name'] );
}

?>