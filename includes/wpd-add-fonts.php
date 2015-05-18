<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function woocommerce_add_fonts() {
        if (isset( $_GET['error'] )) {
		echo $_GET['error'];
	} 
	// Action to perform: add, edit, delete or none
	$action = '';
	if ( ! empty( $_POST['add_new_font'] ) ) {
		$action = 'add';
	} elseif ( ! empty( $_POST['save_font'] ) && ! empty( $_GET['edit'] ) ) {
		$action = 'edit';
	} elseif ( ! empty( $_GET['delete'] ) ) {
		$action = 'delete';
	}
	// Add or edit an attribute
	if ( 'add' === $action || 'edit' === $action ) {
		// Security check
		if ( 'add' === $action ) {
			check_admin_referer( 'woocommerce-add-new_font' );
		}
		if ( 'edit' === $action ) {
			$font_key = absint( $_GET['edit'] );
			check_admin_referer( 'woocommerce-save-font_'.$font_key );
		}
		// Grab the submitted data
		$font_label = ( isset( $_POST['font_label'] ) )   ? (string) stripslashes( $_POST['font_label'] ) : '';
                $font_url = ( isset( $_POST['font_url'] ) )   ? (string) stripslashes( $_POST['font_url'] ) : '';
//                $font_family=( isset( $_POST['font_family'] ) )   ? (string) stripslashes( $_POST['font_family'] ) : '';
		if ('add' === $action ) {
                    if($font_label)
                    {
                        $fonts=get_option('wpc-fonts');
                        if(empty($fonts))
                        {
                            $i=1;
                            $fonts[$i]=array($font_label,$font_url);
                        }
                        else
                        {
                            $font_labels=array_map(create_function('$o', 'return $o[0];'), $fonts);
//                            var_dump($font_labels);
                            if(in_array($font_label, $font_labels ))
                                $error='<div class=error>This font exist !</div>';
                            else
                                $fonts[]=array($font_label,$font_url);
                        }
                        update_option('wpc-fonts',$fonts);
                        $action_completed = true;
                    }
                    else
                    {
                        $error='<div class=error>Missing font name.</div>';
                        $action_completed = true;
                    }
                }
                // Edit existing attribute
                if ( 'edit' === $action ) {
                        $fonts=get_option('wpc-fonts');
                        $edit=$_GET['edit'];
                        $fonts[$edit]=array($font_label,$font_url);
                        update_option('wpc-fonts',$fonts);
                        $action_completed = true;
                }
//                flush_rewrite_rules();
         }

	// Delete an attribute
	if ( 'delete' === $action ) {
		// Security check
		$font_id = absint( $_GET['delete'] );
                $fonts=get_option('wpc-fonts');
                unset($fonts[$font_id]);
                update_option('wpc-fonts',$fonts);
	}

	// If an attribute was added, edited or deleted: clear cache and redirect
	if ( ! empty( $action_completed ) ) {
		//delete_transient( 'wc_attribute_taxonomies' );
                if(!empty($error))
                    wp_safe_redirect( get_admin_url().'admin.php?page=wpc-manage-fonts&error='.urlencode($error));
                else{
                    wp_safe_redirect( get_admin_url().'admin.php?page=wpc-manage-fonts');
                }
		exit;
	}
	// Show 
        // admin interface
	if (!empty($_GET['edit']))
		woocommerce_edit_font();
	else
		woocommerce_add_font();
}

function woocommerce_edit_font() {
    $edit = absint( $_GET['edit'] );
    $fonts=get_option('wpc-fonts');
    $font_label=$fonts[$edit][0];
    $font_url=$fonts[$edit][1];
//    $font_family=$fonts[$edit][2];
?>
	<div class="wrap woocommerce">
		<div class="icon32 icon32-attributes" id="icon-woocommerce"><br/></div>
                <h2><?php _e( 'Edit Font', 'wpd' ) ?></h2>
		<form action="admin.php?page=wpc-manage-fonts&amp;edit=<?php echo absint( $edit ); ?>&amp;noheader=true" method="post">
			<table class="form-table">
				<tbody>
                                    <tr class="form-field form-required">
                                        <th scope="row" valign="top">
                                                <label for="font_label"><?php _e( 'Name', 'wpd' ); ?></label>
                                        </th>
                                        <td>
                                                <input name="font_label" id="font_label" type="text" value="<?php echo esc_attr($font_label); ?>" />
                                                <p class="description"><?php _e( 'Name for the attribute (shown on the front-end).', 'wpd' ); ?></p>
                                        </td>
                                    </tr>
                                    <tr class="form-field">
<!--                                        <th scope="row" valign="top">
                                                <label for="font_family"><?php // _e( 'Font family', 'wpd' ); ?></label>
                                        </th>-->
<!--                                        <td>
                                                <input name="font_family" id="font_family" type="text" value="<?php // echo esc_attr($font_family); ?>" />
                                                <p class="description"><?php // _e( 'Font family property (used for serif or sans serif fonts). If not provided, the font name will be used.', 'wpd' ); ?></p>
                                        </td>                                            -->
                                    </tr>
                                    <tr class="form-field">
                                        <th scope="row" valign="top">
                                                <label for="font_label"><?php _e( 'URL', 'wpd' ); ?></label>
                                        </th>
                                        <td>
                                                <input name="font_url" id="font_label" type="text" value="<?php echo esc_attr($font_url); ?>" />
                                                <p class="description"><?php _e( 'Google font URL. Leave this field empty if the font is already loaded by the theme.', 'wpd' ); ?></p>
                                        </td>                                            
                                    </tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="save_font" id="submit" class="button-primary" value="<?php _e( 'Update', 'wpd' ); ?>"></p>
			<?php wp_nonce_field( 'woocommerce-save-font_'.$edit ); ?>
		</form>
	</div>
	<?php
}

 function woocommerce_add_font() {
	?>
	<div class="wrap woocommerce">
            <div class="icon32 icon32-attributes" id="icon-woocommerce"><br/></div>
	    <h2><?php _e( 'Add Fonts', 'wpd' ) ?></h2>
	    <br class="clear" />
	    <div id="col-container">
	    	<div id="col-right">
	    		<div class="col-wrap">
		    		<table class="widefat fixed" style="width:100%">
				        <thead>
				            <tr>
				                <th scope="col"><?php _e( 'Name', 'wpd' ) ?></th>
                                                <!--<th><?php // _e( 'Font family', 'wpd' ) ?></th>-->
                                                <th scope="col"><?php _e( 'URL', 'wpd' ) ?></th>
				            </tr>
				        </thead>
				        <tbody>
				        	<?php
                                                    $fonts=get_option('wpc-fonts');
                                                    if($fonts) :
                                                            foreach ($fonts as $key=>$font_arr) :   
                                                                $font=$font_arr[0];
                                                                $font_url=$font_arr[1];
//                                                                $font_family=$font_arr[2];
                                                                    ?><tr>

                                                                            <td><a href="<?php echo esc_url( add_query_arg('edit', $key, 'admin.php?page=wpc-manage-fonts') ); ?>"><?php echo esc_html( $font); ?></a>

                                                                            <div class="row-actions"><span class="edit"><a href="<?php echo esc_url( add_query_arg('edit', $key, 'admin.php?page=wpc-manage-fonts') ); ?>"><?php _e( 'Edit', 'wpd' ); ?></a> | </span><span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg('delete', $key, 'admin.php?page=wpc-manage-fonts'), 'woocommerce-delete-attribute_' . $key ) ); ?>"><?php _e( 'Delete', 'wpd' ); ?></a></span></div>
                                                                            </td>
<!--                                                                            <td>
                                                                                <?php // echo esc_html( $font_family); ?>
                                                                            </td>-->
                                                                            <td><?php echo esc_html( $font_url); ?>
                                                                            </td>
                                                                      </tr><?php
                                                            endforeach;
                                                    else :
				        			?><tr><td colspan="6"><?php _e( 'No fonts currently exist.', 'wpd' ) ?></td></tr><?php
                                                    endif;
				        	?>
				        </tbody>
                                </table>
	    		</div>
	    	</div>
	    	<div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h3><?php _e( 'Add New Font', 'wpd' ) ?></h3>
                            <form action="admin.php?page=wpc-manage-fonts&amp;noheader=true" method="post">
                                <div class="form-field">
                                        <label for="font_label"><?php _e( 'Name', 'wpd' ); ?></label>
                                        <input name="font_label" id="font_label" type="text" value="" />
                                        <p class="description"><?php _e( 'Name for the font (shown on the front-end).', 'wpd' ); ?></p>
                                </div>
                                <div class="form-field">
                                        <label for="font_url"><?php _e( 'URL', 'wpd' ); ?></label>
                                        <input name="font_url" id="font_label" type="text" value="" />
                                        <p class="description"><?php _e( 'Google font URL. Leave this field empty if the font is already loaded by the theme.', 'wpd' ); ?></p>
                                </div>
                                <p class="submit"><input type="submit" name="add_new_font" id="submit" class="button" value="<?php _e( 'Add Font', 'wpd' ); ?>"></p>
                                <?php wp_nonce_field( 'woocommerce-add-new_font' ); ?>
                            </form>
                        </div>
	    		</div>
	    	</div>
	    </div>
	    <script type="text/javascript">
			jQuery('a.delete').click(function(){
	    		var answer = confirm ("<?php _e( 'Are you sure you want to delete this font?', 'wpd' ); ?>");
				if (answer) return true;
				return false;
	    	});
            </script>
	</div>
	<?php
}