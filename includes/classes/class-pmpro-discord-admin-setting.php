<?php
/**
 * Admin setting
 */
class Ets_Pmpro_Admin_Setting {
	function __construct() {
		// Add new menu option in the admin menu.
		add_action( 'admin_menu', array( $this, 'ets_add_new_menu' ) );

		// Add script for back end.
		add_action( 'admin_enqueue_scripts', array( $this, 'ets_add_script' ) );

		// Add script for front end.
		add_action( 'wp_enqueue_scripts', array( $this, 'ets_add_script' ) );

		// Add new button in pmpro profile
		add_action( 'pmpro_show_user_profile', array( $this, 'ets_pmpro_discord_add_connect_discord_button' ) );

		// change hook call on cancel and change
		add_action( 'pmpro_after_change_membership_level', array( $this, 'ets_as_schdule_job_pmpro_cancel' ), 10, 3 );

		// Pmpro expiry
		add_action( 'pmpro_membership_post_membership_expiry', array( $this, 'ets_as_schdule_job_pmpro_expiry' ), 10, 2 );

		add_action( 'pmpro_delete_membership_level', array( $this, 'ets_as_schedule_job_pmpro_level_deleted' ), 10, 1 );

		add_filter( 'pmpro_manage_memberslist_custom_column', array( $this, 'ets_discord_pmpro_extra_cols_body' ), 10, 2 );

		add_filter( 'pmpro_manage_memberslist_columns', array( $this, 'ets_discord_pmpro_manage_memberslist_columns' ) );

		add_filter( 'action_scheduler_queue_runner_batch_size', array( $this, 'ets_discord_pmpro_queue_batch_size' ) );

		add_filter( 'action_scheduler_queue_runner_concurrent_batches', array( $this, 'ets_discord_pmpro_concurrent_batches' ) );

	}

	/**
	 * set action scheuduler concurrent batches number
	 */
	public function ets_discord_pmpro_concurrent_batches() {
		return absint( get_option( 'ets_pmpro_job_queue_concurrency' ) );
	}
	/**
	 * set action scheuduler batch size.
	 */
	public function ets_discord_pmpro_queue_batch_size() {
		return absint( get_option( 'ets_pmpro_job_queue_batch_size' ) );
	}
	/**
	 * Show status of PMPro connection with user
	 *
	 * @param None
	 * @return None
	 */
	public function ets_pmpro_discord_add_connect_discord_button() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( 'Unauthorized user', 401 );
			exit();
		}
		$user_id = sanitize_text_field( trim( get_current_user_id() ) );

		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_access_token', true ) ) );

		$allow_none_member        = sanitize_text_field( trim( get_option( 'ets_allow_none_member' ) ) );
		$default_role             = sanitize_text_field( trim( get_option( 'ets_discord_default_role_id' ) ) );
		$ets_discord_role_mapping = json_decode( get_option( 'ets_discord_role_mapping' ), true );
		$all_roles                = unserialize( get_option( 'ets_discord_all_roles' ) );
		$curr_level_id            = $this->get_current_level_id( $user_id );
		$mapped_role_name         = '';
		if ( $curr_level_id && is_array( $all_roles ) ) {
			if ( is_array( $ets_discord_role_mapping ) && array_key_exists( 'level_id_' . $curr_level_id, $ets_discord_role_mapping ) ) {
				$mapped_role_id = $ets_discord_role_mapping[ 'level_id_' . $curr_level_id ];
				if ( array_key_exists( $mapped_role_id, $all_roles ) ) {
					$mapped_role_name = $all_roles[ $mapped_role_id ];
				}
			}
		}
		$default_role_name = '';
		if ( $default_role != 'none' && is_array( $all_roles ) && array_key_exists( $default_role, $all_roles ) ) {
			$default_role_name = $all_roles[ $default_role ];
		}
		if ( $this->Check_saved_settings_status() ) {

			if ( $access_token ) {
				?>
				<label class="ets-connection-lbl"><?php echo __( 'Discord connection', 'ets_pmpro_discord' ); ?></label>
				<a href="#" class="ets-btn btn-disconnect" id="disconnect-discord" data-user-id="<?php echo $user_id; ?>"><?php echo __( 'Disconnect From Discord ', 'ets_pmpro_discord' ); ?><i class='fab fa-discord'></i></a>
				<span class="ets-spinner"></span>
				<?php
			} elseif ( pmpro_hasMembershipLevel() || $allow_none_member == 'yes' ) {
				?>
				<label class="ets-connection-lbl"><?php echo __( 'Discord connection', 'ets_pmpro_discord' ); ?></label>
				<a href="?action=discord-login" class="btn-connect ets-btn" ><?php echo __( 'Connect To Discord', 'ets_pmpro_discord' ); ?> <i class='fab fa-discord'></i></a>
				<?php if ( $mapped_role_name ) { ?>
					<p class="ets_assigned_role">
					<?php
					echo __( 'Following Roles will be assigned to you in Discord: ', 'ets_pmpro_discord' );
					echo $mapped_role_name;
					if ( $default_role_name ) {
						echo ', ' . $default_role_name; }
					?>
					 </p>
				<?php } ?>
				<?php
			}
		}

	}

	/**
	 * Get pmpro current level id
	 *
	 * @param int $user_id
	 * @return int $curr_level_id
	 */
	public function get_current_level_id( $user_id ) {
			$membership_level = pmpro_getMembershipLevelForUser( $user_id );
		if ( $membership_level ) {
			$curr_level_id = sanitize_text_field( trim( $membership_level->ID ) );
			return $curr_level_id;
		} else {
			return null;
		}

	}

	/**
	 * Method to queue all members into cancel job when pmpro level is deleted.
	 *
	 * @param int $user_id
	 * @return string $status
	 */
	public function ets_as_schedule_job_pmpro_level_deleted( $level_id ) {
		global $wpdb;
		$result                   = $wpdb->get_results( $wpdb->prepare( 'SELECT `user_id` FROM ' . $wpdb->prefix . 'pmpro_memberships_users' . ' WHERE `membership_id` = %d GROUP BY `user_id`', array( $level_id ) ) );
		$ets_discord_role_mapping = json_decode( get_option( 'ets_discord_role_mapping' ), true );
		update_option( 'ets_admin_level_deleted', true );
		foreach ( $result as $key => $ids ) {
			$user_id      = $ids->user_id;
			$access_token = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_access_token', true ) ) );
			if ( $access_token ) {
				as_enqueue_async_action( 'ets_as_handle_pmpro_cancel', array( $user_id, $level_id, $level_id ) );
			}
		}
	}

	/**
	 * Method to save job queue for cancelled pmpro members.
	 *
	 * @param int $level_id
	 * @param int $user_id
	 * @param int $cancel_level
	 * @return None
	 */
	public function ets_as_schdule_job_pmpro_cancel( $level_id, $user_id, $cancel_level ) {

		$membership_status = sanitize_text_field( trim( $this->ets_check_current_membership_status( $user_id ) ) );
		$access_token      = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_access_token', true ) ) );
		if ( ! empty( $cancel_level ) || $membership_status == 'admin_cancelled' ) {

			$args = array(
				'hook'    => 'ets_as_handle_pmpro_cancel',
				'args'    => array( $level_id, $user_id, $cancel_level ),
				'status'  => ActionScheduler_Store::STATUS_PENDING,
				'orderby' => 'date',
			);

			// check if member is already added to job queue.
			$cancl_arr_already_added = as_get_scheduled_actions( $args, ARRAY_A );
			if ( count( $cancl_arr_already_added ) === 0 && $access_token && ( $membership_status == 'cancelled' || $membership_status == 'admin_cancelled' ) ) {
				as_enqueue_async_action( 'ets_as_handle_pmpro_cancel', array( $user_id, $level_id, $cancel_level ) );
			}
		}
	}

	/*
	*  Action schedule to schedule a function to run upon PMPRO Expiry.
	* @param int $user_id
	  * @param int $level_id
	  * @return None
	*/
	public function ets_as_schdule_job_pmpro_expiry( $user_id, $level_id ) {
		$existing_members_queue = sanitize_text_field( trim( get_option( 'ets_queue_of_pmpro_members' ) ) );
		  $membership_status    = sanitize_text_field( trim( $this->ets_check_current_membership_status( $user_id ) ) );
		  $access_token         = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_access_token', true ) ) );
		if ( $membership_status == 'expired' && $access_token ) {
			as_enqueue_async_action( 'ets_as_handle_pmpro_expiry', array( $user_id, $level_id ) );
		}
	}


	/**
	 * Localized script and style
	 *
	 * @param None
	 * @return None
	 */
	public function ets_add_script() {

		wp_register_style(
			'ets_pmpro_add_discord_style',
			ETS_PMPRO_DISCORD_URL . 'assets/css/ets-pmpro-discord-style.css',
			false,
			ETS_PMPRO_VERSION
		);
		wp_enqueue_style( 'ets_pmpro_add_discord_style' );

		wp_register_style(
			'ets_pmpro_font_awesome',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/css/all.min.css'
		);
		wp_enqueue_style( 'ets_pmpro_font_awesome' );

		wp_register_script(
			'ets_pmpro_add_discord_script',
			ETS_PMPRO_DISCORD_URL . 'assets/js/ets-pmpro-add-discord-script.min.js',
			array( 'jquery' ),
			ETS_PMPRO_VERSION
		);
		wp_enqueue_script( 'ets_pmpro_add_discord_script' );

		wp_register_script(
			'ets_fab_icon_script',
			'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.2/js/all.min.js',
			array( 'jquery' )
		);
		wp_enqueue_script( 'ets_fab_icon_script' );

		wp_register_script(
			'jQuery_ui_script',
			'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js',
			array( 'jquery' )
		);
		wp_enqueue_script( 'jQuery_ui_script' );

		$script_params = array(
			'admin_ajax'        => admin_url( 'admin-ajax.php' ),
			'permissions_const' => ETS_DISCORD_BOT_PERMISSIONS,
			'is_admin'          => is_admin(),
			'ets_discord_nonce' => wp_create_nonce( 'ets-discord-ajax-nonce' ),
		);

		wp_localize_script( 'ets_pmpro_add_discord_script', 'etsPmproParams', $script_params );
	}

	/**
	 * Add menu in PmPro membership dashboard sub-menu
	 *
	 * @param None
	 * @return None
	 */
	public function ets_add_new_menu() {
		// Add sub-menu into PmPro main-menus list
		add_submenu_page( 'pmpro-dashboard', __( 'Discord Settings', 'paid-memberships-pro' ), __( 'Discord Settings', 'paid-memberships-pro' ), 'manage_options', 'discord-options', array( $this, 'ets_pmpro_discord_setting_page' ) );
	}

	/**
	 * Get user membership status by user_id
	 *
	 * @param int $user_id
	 * @return string $status
	 */
	public function ets_check_current_membership_status( $user_id ) {
		global $wpdb;
		$sql    = $wpdb->prepare( 'SELECT `status` FROM ' . $wpdb->prefix . 'pmpro_memberships_users' . ' WHERE `user_id`= %d ORDER BY `id` DESC limit 1', array( $user_id ) );
		$result = $wpdb->get_results( $sql );
		return $result[0]->status;
	}

	/**
	 * Define plugin settings rules
	 *
	 * @param None
	 * @return None
	 */
	public function ets_pmpro_discord_setting_page() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}
		$ets_discord_client_id = isset( $_POST['ets_discord_client_id'] ) ? sanitize_text_field( trim( $_POST['ets_discord_client_id'] ) ) : '';

		$discord_client_secret = isset( $_POST['ets_discord_client_secret'] ) ? sanitize_text_field( trim( $_POST['ets_discord_client_secret'] ) ) : '';

		$discord_bot_token = isset( $_POST['ets_discord_bot_token'] ) ? sanitize_text_field( trim( $_POST['ets_discord_bot_token'] ) ) : '';

		$ets_discord_redirect_url = isset( $_POST['ets_discord_redirect_url'] ) ? sanitize_text_field( trim( $_POST['ets_discord_redirect_url'] ) ) : '';

		$ets_discord_guild_id = isset( $_POST['ets_discord_guild_id'] ) ? sanitize_text_field( trim( $_POST['ets_discord_guild_id'] ) ) : '';

		$ets_discord_roles = isset( $_POST['ets_discord_role_mapping'] ) ? sanitize_textarea_field( trim( $_POST['ets_discord_role_mapping'] ) ) : '';

		$ets_discord_default_role_id = isset( $_POST['defaultRole'] ) ? sanitize_textarea_field( trim( $_POST['defaultRole'] ) ) : '';

		$upon_expiry = isset( $_POST['upon_expiry'] ) ? sanitize_textarea_field( trim( $_POST['upon_expiry'] ) ) : '';

		$allow_none_member = isset( $_POST['allow_none_member'] ) ? sanitize_textarea_field( trim( $_POST['allow_none_member'] ) ) : '';

		$set_job_cnrc = isset( $_POST['set_job_cnrc'] ) ? sanitize_textarea_field( trim( $_POST['set_job_cnrc'] ) ) : '';

		$set_job_q_batch_size = isset( $_POST['set_job_q_batch_size'] ) ? sanitize_textarea_field( trim( $_POST['set_job_q_batch_size'] ) ) : '';

		$deactivate_plugin = isset( $_POST['deactivate_plugin'] ) ? sanitize_textarea_field( trim( $_POST['deactivate_plugin'] ) ) : '';

		if ( isset( $_POST['submit'] ) && ! isset( $_POST['ets_discord_role_mapping'] ) ) {
			if ( isset( $_POST['ets_discord_save_settings'] ) && wp_verify_nonce( $_POST['ets_discord_save_settings'], 'save_discord_settings' ) ) {
				if ( $ets_discord_client_id ) {
					update_option( 'ets_discord_client_id', $ets_discord_client_id );
				}

				if ( $discord_client_secret ) {
					update_option( 'ets_discord_client_secret', $discord_client_secret );
				}

				if ( $discord_bot_token ) {
					update_option( 'ets_discord_bot_token', $discord_bot_token );
				}

				if ( $ets_discord_redirect_url ) {
					// add a query string param `via` GH #185.
					$ets_discord_redirect_url = get_formated_discord_redirect_url( $ets_discord_redirect_url );
					update_option( 'ets_discord_redirect_url', $ets_discord_redirect_url );
				}

				if ( $ets_discord_guild_id ) {
					update_option( 'ets_discord_guild_id', $ets_discord_guild_id );
				}
				add_action( 'admin_notices', $this->ets_save_settings_admin_notice() );
			}
		}
		if ( isset( $_POST['adv_submit'] ) ) {
			if ( isset( $_POST['ets_discord_save_adv_settings'] ) && wp_verify_nonce( $_POST['ets_discord_save_adv_settings'], 'save_discord_adv_settings' ) ) {
				if ( isset( $_POST['upon_failed_payment'] ) ) {
					update_option( 'ets_pmpro_discord_payment_failed', true );
				} else {
					update_option( 'ets_pmpro_discord_payment_failed', false );
				}

				if ( isset( $_POST['log_api_res'] ) ) {
					update_option( 'ets_pmpro_log_api_response', true );
				} else {
					update_option( 'ets_pmpro_log_api_response', false );
				}

				if ( isset( $_POST['set_job_cnrc'] ) ) {
					if ( $set_job_cnrc < 1 ) {
						update_option( 'ets_pmpro_job_queue_concurrency', 1 );
					} else {
						update_option( 'ets_pmpro_job_queue_concurrency', $set_job_cnrc );
					}
				}

				if ( isset( $_POST['set_job_q_batch_size'] ) ) {
					if ( $set_job_q_batch_size < 1 ) {
						update_option( 'ets_pmpro_job_queue_batch_size', 1 );
					} else {
						update_option( 'ets_pmpro_job_queue_batch_size', $set_job_q_batch_size );
					}
				}
				add_action( 'admin_notices', $this->ets_save_settings_admin_notice() );
			}
		}
		if ( $ets_discord_roles ) {
			$ets_discord_roles   = stripslashes( $ets_discord_roles );
			$save_mapping_status = update_option( 'ets_discord_role_mapping', $ets_discord_roles );
			if ( isset( $_POST['ets_discord_role_mappings_nonce'] ) && wp_verify_nonce( $_POST['ets_discord_role_mappings_nonce'], 'discord_role_mappings_nonce' ) ) {
				if ( ( $save_mapping_status || isset( $_POST['ets_discord_role_mapping'] ) ) && ! isset( $_POST['flush'] ) ) {
					if ( $ets_discord_default_role_id ) {
						update_option( 'ets_discord_default_role_id', $ets_discord_default_role_id );
					}

					if ( $upon_expiry ) {
						update_option( 'ets_upon_expiry', $upon_expiry );
					}

					if ( $allow_none_member ) {
						update_option( 'ets_allow_none_member', $allow_none_member );
					}
					add_action( 'admin_notices', $this->ets_save_mappings_admin_notice() );
				}
				if ( isset( $_POST['flush'] ) ) {
					delete_option( 'ets_discord_role_mapping' );
					delete_option( 'ets_discord_default_role_id' );
					delete_option( 'ets_upon_expiry' );
					delete_option( 'ets_allow_none_member' );
					add_action( 'admin_notices', $this->ets_save_flush_mappings_admin_notice() );
				}
			}
		}

		$ets_discord_client_id    = sanitize_text_field( trim( get_option( 'ets_discord_client_id' ) ) );
		$discord_client_secret    = sanitize_text_field( trim( get_option( 'ets_discord_client_secret' ) ) );
		$discord_bot_token        = sanitize_text_field( trim( get_option( 'ets_discord_bot_token' ) ) );
		$ets_discord_redirect_url = sanitize_text_field( trim( get_option( 'ets_discord_redirect_url' ) ) );
		$ets_discord_roles        = sanitize_text_field( trim( get_option( 'ets_discord_role_mapping' ) ) );
		$ets_discord_guild_id     = sanitize_text_field( trim( get_option( 'ets_discord_guild_id' ) ) );
		?>
		<h1><?php echo __( 'PMPRO Discord Add On Settings', 'ets_pmpro_discord' ); ?></h1>
		<div class="tab ets-tabs">

		  <button class="ets_tablinks" data-identity="settings" data-toggle="tab" data-event="ets_setting"><?php echo __( 'Discord Settings', 'ets_pmpro_discord' ); ?><span class="spinner"></span></button>
		  <?php if ( ! empty( $ets_discord_client_id ) && ! empty( $discord_client_secret ) && ! empty( $discord_bot_token ) && ! empty( $ets_discord_redirect_url ) && ! empty( $ets_discord_guild_id ) ) : ?>
		   <button class="ets_tablinks" data-identity="level-mapping" data-toggle="tab" data-event="ets_level_mapping"><?php echo __( 'Role Mappings', 'ets_pmpro_discord' ); ?></button>
		  <?php endif; ?>
			<button class="ets_tablinks" data-identity="advanced" data-toggle="tab" data-event="ets_advanced"><?php echo __( 'Advanced', 'ets_pmpro_discord' ); ?>	
		  </button>
		  <button class="ets_tablinks" data-identity="logs" data-toggle="tab" data-event="ets_logs"><?php echo __( 'Logs', 'ets_pmpro_discord' ); ?>	
		  </button>
		  <button class="ets_tablinks" data-identity="docs" data-toggle="tab" data-event="ets_docs"><?php echo __( 'Documentation', 'ets_pmpro_discord' ); ?>	
		  </button>
		  <button class="ets_tablinks" data-identity="support" data-toggle="tab" data-event="ets_about_us"><?php echo __( 'Support', 'ets_pmpro_discord' ); ?>	
		  </button>
		</div>

		<div id="ets_setting" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/discord-settings.php'; ?>
		</div>
		<div id="ets_docs" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/documentation.php'; ?>
		</div>
		<div id="ets_about_us" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/get-support.php'; ?>
		</div>
		<div id="ets_advanced" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/advanced.php'; ?>
		</div>
		<div id="ets_logs" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/error_log.php'; ?>
		</div>
		<div id="ets_level_mapping" class="ets_tabcontent">
			<?php include ETS_PMPRO_DISCORD_PATH . 'includes/pages/discord-role-level-map.php'; ?>
		</div>


		<?php
		$this->get_Support_Data();
	}

	/**
	 * Send mail to support form current user
	 *
	 * @param None
	 * @return None
	 */
	public function get_Support_Data() {
		if ( ! current_user_can( 'administrator' ) ) {
			wp_send_json_error( 'You do not have sufficient rights', 403 );
			exit();
		}

		if ( isset( $_POST['save'] ) ) {
			// Check for nonce security
			if ( ! wp_verify_nonce( $_POST['ets_discord_get_support'], 'get_support' ) ) {
				wp_send_json_error( 'You do not have sufficient rights', 403 );
				exit();
			}
			$etsUserName  = isset( $_POST['ets_user_name'] ) ? sanitize_text_field( trim( $_POST['ets_user_name'] ) ) : '';
			$etsUserEmail = isset( $_POST['ets_user_email'] ) ? sanitize_text_field( trim( $_POST['ets_user_email'] ) ) : '';
			$message      = isset( $_POST['ets_support_msg'] ) ? sanitize_text_field( trim( $_POST['ets_support_msg'] ) ) : '';
			$sub          = isset( $_POST['ets_support_subject'] ) ? sanitize_text_field( trim( $_POST['ets_support_subject'] ) ) : '';

			if ( $etsUserName && $etsUserEmail && $message && $sub ) {

				$subject   = $sub;
				$to        = 'contact@expresstechsoftwares.com';
				$content   = 'Name: ' . $etsUserName . '<br>';
				$content  .= 'Contact Email: ' . $etsUserEmail . '<br>';
				$content  .= 'Message: ' . $message;
				$headers   = array();
				$blogemail = get_bloginfo( 'admin_email' );
				$headers[] = 'From: ' . get_bloginfo( 'name' ) . ' <' . $blogemail . '>' . "\r\n";
				$mail      = wp_mail( $to, $subject, $content, $headers );

				if ( $mail ) {
					add_action( 'admin_notices', $this->ets_mail_request_admin_notice() );
				}
			}
		}
	}

	/**
	 * To check settings values saved or not
	 *
	 * @param None
	 * @return boolean $status
	 */
	public function Check_saved_settings_status() {
		$ets_discord_client_id     = get_option( 'ets_discord_client_id' );
		$ets_discord_client_secret = get_option( 'ets_discord_client_secret' );
		$ets_discord_bot_token     = get_option( 'ets_discord_bot_token' );
		$ets_discord_redirect_url  = get_option( 'ets_discord_redirect_url' );
		$ets_discord_guild_id      = get_option( 'ets_discord_guild_id' );

		if ( $ets_discord_client_id && $ets_discord_client_secret && $ets_discord_bot_token && $ets_discord_redirect_url && $ets_discord_guild_id ) {
			$status = true;
		} else {
			$status = false;
		}

		return $status;
	}


	/*
	* Add extra column body into pmpro members list
	* @param int $colname
	* @param int $user
	* @return NONE
	*/
	public function ets_discord_pmpro_extra_cols_body( $colname, $user_id ) {
		$access_token = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_access_token', true ) ) );
		if ( 'discord' === $colname ) {
			if ( $access_token ) {
				$discord_username = sanitize_text_field( trim( get_user_meta( $user_id, 'ets_discord_username', true ) ) );
				echo '<p class="' . $user_id . ' ets-save-success">' . __( 'Success', 'ets_pmpro_discord' ) . '</p><a class="button button-primary ets-run-api" data-uid="' . $user_id . '" href="#">';
				echo __( 'Run API', 'ets_pmpro_discord' );
				echo '</a><span class="' . $user_id . ' spinner"></span>';
				echo $discord_username;
			} else {
				echo __( 'Not Connected', 'ets_pmpro_discord' );
			}
		}

		if ( 'joined_date' === $colname ) {
			echo esc_html( get_user_meta( $user_id, '_ets_pmpro_discord_join_date', true ) );
		}
	}
	/*
	* Add extra column into pmpro members list
	* @param array $columns
	* @return array $columns
	*/
	public function ets_discord_pmpro_manage_memberslist_columns( $columns ) {
		$columns['discord']     = __( 'Discord', 'ets_pmpro_discord' );
		$columns['joined_date'] = __( 'Joined Date', 'ets_pmpro_discord' );
		return $columns;
	}
	
	/*
	* Admin notice for save general settings successfully
	* @param None
	* @return None
	*/
	public function ets_save_settings_admin_notice() {
		?>
				<div class="notice notice-success is-dismissible support-success-msg">
				<p><?php echo __( 'Your settings are saved successfully.', 'ets_pmpro_discord' ); ?></p>
			</div>
			<?php
	}
	
	/*
	* Admin notice for save role mapping settings successfully
	* @param None
	* @return None
	*/
	public function ets_save_mappings_admin_notice() {
		?>
			<div class="notice notice-success is-dismissible support-success-msg">
				<p><?php echo __( 'Your mappings are saved successfully.', 'ets_pmpro_discord' ); ?></p>
			</div>
		<?php
	}

	/*
	* Admin notice after flush role mapping settings successfully
	* @param None
	* @return None
	*/
	public function ets_save_flush_mappings_admin_notice() {
		?>
			<div class="notice notice-success is-dismissible support-success-msg">
				<p><?php echo __( 'Your settings flushed successfully.', 'ets_pmpro_discord' ); ?></p>
			</div>
		<?php
	}

	/*
	* Admin notice for mail request submited successfully
	* @param None
	* @return None
	*/
	public function ets_mail_request_admin_notice() {
		?>
			<div class="notice notice-success is-dismissible support-success-msg">
				<p><?php echo __( 'Your request have been successfully submitted!', 'ets_pmpro_discord' ); ?></p>
			</div>
		<?php
	}
}
new Ets_Pmpro_Admin_Setting();
