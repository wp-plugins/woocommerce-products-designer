<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function woocommerce_add_parts() {
        if (isset( $_GET['error'] )) {
		echo $_GET['error'];
	} 
	// Action to perform: add, edit, delete or none
	$action = '';
	if ( ! empty( $_POST['add_new_part'] ) ) {
		$action = 'add';
	} elseif ( ! empty( $_POST['save_part'] ) && ! empty( $_GET['edit'] ) ) {
		$action = 'edit';
	} elseif ( ! empty( $_GET['delete'] ) ) {
		$action = 'delete';
	}
	// Add or edit an attribute
	if ( 'add' === $action || 'edit' === $action ) {
		// Security check
		if ( 'add' === $action ) {
			check_admin_referer( 'woocommerce-add-new_part' );
		}
		if ( 'edit' === $action ) {
			$part_key = absint( $_GET['edit'] );
			check_admin_referer( 'woocommerce-save-part_'.$part_key );
		}
		// Grab the submitted data
		$part_label   = ( isset( $_POST['part_label'] ) )   ? (string) stripslashes( $_POST['part_label'] ) : '';
                if($part_label)
                {
                    if ('add' === $action ) {   
                        $parts=get_option('wpc-parts');
                        if(empty($parts))
                        {
                            $i=1;
                            $parts[$i]=$part_label;
                        }
                        else
                        {
                            if(in_array($part_label, $parts))
                                $error='<div class=error>This part exist !</div>';
                            else
                                $parts[]=$part_label;
                        }            
                        update_option('wpc-parts',$parts);
                        $action_completed = true;
                    }
                    // Edit existing attribute
                    if ( 'edit' === $action ) {
                            $parts=get_option('wpc-parts');
                            $edit=$_GET['edit'];
                            $parts[$edit]=$part_label;
                            update_option('wpc-parts',$parts);
                            $action_completed = true;
                    }
    //                flush_rewrite_rules();
                }
                else
                {
                    $error='<div class=error>Missing part name.</div>';
                    $action_completed = true;
                }
         }

	// Delete an attribute
	if ( 'delete' === $action ) {
		// Security check
		$part_id = absint( $_GET['delete'] );
                $parts=get_option('wpc-parts');
                unset($parts[$part_id]);
                update_option('wpc-parts',$parts);
	}

	// If an attribute was added, edited or deleted: clear cache and redirect
	if ( ! empty( $action_completed ) ) {
		//delete_transient( 'wc_attribute_taxonomies' );
                if(!empty($error))
                    wp_safe_redirect( get_admin_url().'admin.php?page=wpc-manage-parts&error='.urlencode($error));
                else{
                    wp_safe_redirect( get_admin_url().'admin.php?page=wpc-manage-parts');
                }
		exit;
	}
	// Show 
        // admin interface
	if (!empty($_GET['edit']))
		woocommerce_edit_part();
	else
		woocommerce_add_part();
}

function woocommerce_edit_part() {
    $edit = absint( $_GET['edit'] );
    $parts=get_option('wpc-parts');
    $part_label=$parts[$edit];
?>
	<div class="wrap woocommerce">
		<div class="icon32 icon32-attributes" id="icon-woocommerce"><br/></div>
                <h2><?php _e( 'Edit Part', 'wpd' ) ?></h2>
		<form action="admin.php?page=wpc-manage-parts&amp;edit=<?php echo absint( $edit ); ?>&amp;noheader=true" method="post">
			<table class="form-table">
				<tbody>
                                    <tr class="form-field form-required">
                                        <th scope="row" valign="top">
                                                <label for="part_label"><?php _e( 'Name', 'wpd' ); ?></label>
                                        </th>
                                        <td>
                                                <input name="part_label" id="part_label" type="text" value="<?php echo esc_attr($part_label); ?>" />
                                                <p class="description"><?php _e( 'Name for the attribute (shown on the front-end).', 'wpd' ); ?></p>
                                        </td>
                                    </tr>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="save_part" id="submit" class="button-primary" value="<?php _e( 'Update', 'wpd' ); ?>"></p>
			<?php wp_nonce_field( 'woocommerce-save-part_'.$edit ); ?>
		</form>
	</div>
	<?php
}

 function woocommerce_add_part() {
	global $woocommerce;
	?>
	<div class="wrap woocommerce">
            <div class="icon32 icon32-attributes" id="icon-woocommerce"><br/></div>
	    <h2><?php _e( 'Add Parts', 'wpd' ) ?></h2>
	    <br class="clear" />
	    <div id="col-container">
	    	<div id="col-right">
	    		<div class="col-wrap">
		    		<table class="widefat fixed" style="width:100%">
				        <thead>
				            <tr>
				                <th scope="col"><?php _e( 'Name', 'wpd' ) ?></th>
				            </tr>
				        </thead>
				        <tbody>
				        	<?php
                                                    $parts=get_option('wpc-parts');
                                                    if($parts) :
                                                            foreach ($parts as $key=>$part) :   
                                                                    ?><tr>

                                                                            <td><a href="<?php echo esc_url( add_query_arg('edit', $key, 'admin.php?page=wpc-manage-parts') ); ?>"><?php echo esc_html( $part); ?></a>

                                                                            <div class="row-actions"><span class="edit"><a href="<?php echo esc_url( add_query_arg('edit', $key, 'admin.php?page=wpc-manage-parts') ); ?>"><?php _e( 'Edit', 'wpd' ); ?></a> | </span><span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg('delete', $key, 'admin.php?page=wpc-manage-parts'), 'woocommerce-delete-attribute_' . $key ) ); ?>"><?php _e( 'Delete', 'wpd' ); ?></a></span></div>
                                                                            </td>
                                                                      </tr><?php
                                                            endforeach;
                                                    else :
				        			?><tr><td colspan="6"><?php _e( 'No parts currently exist.', 'wpd' ) ?></td></tr><?php
                                                    endif;
				        	?>
				        </tbody>
                                </table>
	    		</div>
	    	</div>
	    	<div id="col-left">
                    <div class="col-wrap">
                        <div class="form-wrap">
                            <h3><?php _e( 'Add New Part', 'wpd' ) ?></h3>
                            <form action="admin.php?page=wpc-manage-parts&amp;noheader=true" method="post">
                                <div class="form-field">
                                        <label for="part_label"><?php _e( 'Name', 'wpd' ); ?></label>
                                        <input name="part_label" id="part_label" type="text" value="" />
                                        <p class="description"><?php _e( 'Name for the part (shown on the front-end).', 'wpd' ); ?></p>
                                </div>
                                <p class="submit"><input type="submit" name="add_new_part" id="submit" class="button" value="<?php _e( 'Add Part', 'wpd' ); ?>"></p>
                                <?php wp_nonce_field( 'woocommerce-add-new_part' ); ?>
                            </form>
                        </div>
	    		</div>
	    	</div>
	    </div>
	    <script type="text/javascript">
			jQuery('a.delete').click(function(){
	    		var answer = confirm ("<?php _e( 'Are you sure you want to delete this part?', 'wpd' ); ?>");
				if (answer) return true;
				return false;
	    	});
            </script>
	</div>
	<?php
}