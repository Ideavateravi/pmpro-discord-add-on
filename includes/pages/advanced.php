<?php
$upon_failed_payment = sanitize_text_field( trim( get_option( 'ETS_PMPRO_PAYMENT_FAILED' ) ) );
$log_api_res = sanitize_text_field( trim( get_option( 'ets_pmpro_log_api_response' ) ) );
$set_job_cnrc = sanitize_text_field( trim( get_option( 'ets_pmpro_job_queue' ) ) );
$set_job_q_batch_size = sanitize_text_field( trim( get_option( 'ets_pmpro_job_queue_batch_size' ) ) );
$deactivate_plugin = sanitize_text_field( trim( get_option( 'ets_discord_remove_data_on_uninstalling' ) ) );
?>
<form method="post" action="#">
  <table class="form-table" role="presentation">
    <tbody>
      <tr>
        <th scope="row"><?php echo __( "Remove Role and Adjust default upon Member Failed Payment", "ets_pmpro_discord" );?></th>
        <td> <fieldset>
        <?php wp_nonce_field( 'save_discord_adv_settings', 'ets_discord_save_adv_settings' ); ?>
        <input name="upon_failed_payment" type="checkbox" id="upon_failed_payment" <?php if ( $upon_failed_payment == true ) { echo 'checked="checked"'; } ?> value="1">
        </fieldset></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( "Log API calls response", "ets_pmpro_discord" );?></th>
        <td> <fieldset>
        <input name="log_api_res" type="checkbox" id="log_api_res" <?php if ( $log_api_res == true ) { echo 'checked="checked"'; } ?> value="1">
        </fieldset></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( "Remove data after uninstalling the plugin", "ets_pmpro_discord" );?></th>
        <td> <fieldset>
        <input name="deactivate_plugin" type="checkbox" id="deactivate_plugin" <?php if ( $deactivate_plugin == true ) { echo 'checked="checked"'; } ?> value="1">
        </fieldset></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( "Set Job Queue Concurrency", "ets_pmpro_discord" );?></th>
        <td> <fieldset>
        <input name="set_job_cnrc" type="text" id="set_job_cnrc" value="<?php if ( isset( $set_job_cnrc ) ) { echo $set_job_cnrc; } else { echo 2; } ?>">
        </fieldset></td>
      </tr>
      <tr>
        <th scope="row"><?php echo __( "Set Job Queue batch size", "ets_pmpro_discord" );?></th>
        <td> <fieldset>
        <input name="set_job_q_batch_size" type="text" id="set_job_q_batch_size" value="<?php if ( isset( $set_job_q_batch_size ) ) { echo $set_job_q_batch_size; } else { echo 10; } ?>">
        </fieldset></td>
      </tr>
    </tbody>
  </table>
  <div class="bottom-btn">
    <button type="submit" name="adv_submit" value="ets_submit" class="ets-submit ets-bg-green">
      <?php echo __( "Save Settings", "ets_pmpro_discord" );?>
    </button>
  </div>
</form>