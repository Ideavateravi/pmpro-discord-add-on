<?php
$user_id             = sanitize_text_field( trim( get_current_user_id() ) );
$pmpro_levels        = pmpro_getAllLevels( true, true );
$membership_level    = pmpro_getMembershipLevelForUser( $user_id );
$default_role        = sanitize_text_field( trim( get_option( '_ets_pmpro_discord_default_role_id' ) ) );
$allow_none_member_s = sanitize_text_field( trim( get_option( 'ets_pmpro_allow_none_member' ) ) );
?>
<div class="notice notice-warning ets-notice">
  <p><i class='fas fa-info'></i> <?php echo __( 'Drag and Drop the Discord Roles over to the PMPRO Levels', 'ets_pmpro_discord' ); ?></p>
</div>
<div class="notice notice-warning ets-notice">
  <p><i class='fas fa-info'></i> <?php echo __( 'Note: Inactive levels will not display', 'ets_pmpro_discord' ); ?></p>
</div>
<div class="row-container">
  <div class="ets-column discord-roles-col">
	<h2><?php echo __( 'Discord Roles', 'ets_pmpro_discord' ); ?></h2>
	<hr>
	<div class="discord-roles">
	  <span class="spinner"></span>
	</div>
  </div>
  <div class="ets-column">
	<h2><?php echo __( 'PMPRO Levels', 'ets_pmpro_discord' ); ?></h2>
	<hr>
	<div class="pmpro-levels">
	<?php
	foreach ( $pmpro_levels as $key => $value ) {
		if ( $value->allow_signups != 0 ) :
			?>
		  <div class="makeMeDroppable" data-level_id="<?php echo esc_attr($value->id); ?>" ><span><?php echo esc_html($value->name); ?></span></div>
			<?php
		endif;
	}
	?>
	</div>
  </div>
</div>
<form method="post" action="<?php echo get_site_url().'/wp-admin/admin-post.php' ?>">
 <input type="hidden" name="action" value="pmpro_discord_save_role_mapping">
  <table class="form-table" role="presentation">
	<tbody>
	  <tr>
		<th scope="row"><label for="defaultRole"><?php echo __( 'Default Role', 'ets_pmpro_discord' ); ?></label></th>
		<td>
		  <?php wp_nonce_field( 'discord_role_mappings_nonce', 'ets_pmpor_discord_role_mappings_nonce' ); ?>
		  <input type="hidden" id="selected_default_role" value="<?php echo $default_role; ?>">
		  <select id="defaultRole" name="defaultRole">
			<option value="none"><?php echo __( '-None-', 'ets_pmpro_discord' ); ?></option>
		  </select>
		  <p class="description"><?php echo __( 'This Role will be assigned to all level members', 'ets_pmpro_discord' ); ?></p>
		</td>
	  </tr>
	  <tr>
		<th scope="row"><label><?php echo __( 'Allow non-members', 'ets_pmpro_discord' ); ?></label></th>
		<td>
		  <fieldset>
		  <label><input type="radio" name="allow_none_member" value="yes"  
		  <?php
			if ( $allow_none_member_s == 'yes' ) {
				echo 'checked="checked"'; }
			?>
			 > <span><?php echo __( 'Yes', 'ets_pmpro_discord' ); ?></span></label><br>
		  <label><input type="radio" name="allow_none_member" value="no" 
		  <?php
			if ( empty( $allow_none_member_s ) || $allow_none_member_s == 'no' ) {
				echo 'checked="checked"'; }
			?>
			 > <span><?php echo __( 'No', 'ets_pmpro_discord' ); ?></span></label>
		  <p class="description"><?php echo __( 'This setting will apply on Cancel and Expiry of Membership' ); ?></p>
		  </fieldset>
		</td>
	  </tr>
	</tbody>
  </table>
	<br>
  <div class="mapping-json">
	<textarea id="maaping_json_val" name="ets_pmpor_discord_role_mapping">
	<?php
	if ( isset( $ets_discord_roles ) ) {
		echo stripslashes( $ets_discord_roles );}
	?>
	</textarea>
  </div>
  <div class="bottom-btn">
	<button type="submit" name="submit" value="ets_submit" class="ets-submit ets-bg-green">
	  <?php echo __( 'Save Settings', 'ets_pmpro_discord' ); ?>
	</button>
	<button id="revertMapping" name="flush" class="ets-submit ets-bg-red">
	  <?php echo __( 'Flush Mappings', 'ets_pmpro_discord' ); ?>
	</button>
  </div>
</form>
