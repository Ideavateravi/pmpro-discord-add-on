<?php
  $currUserName = '';
  $currentUser  = wp_get_current_user();
if ( $currentUser ) {
	$currUserName = sanitize_text_field( trim( $currentUser->user_login ) );
}
?>
<div class="contact-form " style="min-height: auto!important;">
  <form accept="#" method="post">
	
	  <div class="ets-container">
		<div class="top-logo-title">
		  <img src="<?php echo esc_attr( ETS_PMPRO_DISCORD_URL . 'assets/images/ets-logo.png ' ); ?>" class="img-fluid company-logo" alt="">
		  <h1><?php echo __( 'ExpressTech Softwares Solutions Pvt. Ltd.', 'pmpro-discord-add-on' ); ?></h1>
		  <p><?php echo __( 'ExpressTech Software Solution Pvt. Ltd. is the leading Enterprise WordPress development company.', 'pmpro-discord-add-on' ); ?><br>
		  <?php echo __( 'Contact us for any WordPress Related development projects.', 'pmpro-discord-add-on' ); ?></p>
		</div>

		<ul style="text-align: left;">
			<li class="mp-icon mp-icon-right-big"><?php esc_html_e( 'If you encounter any issues or errors, please report them on our support forum for the Connect Paid Memberships Pro to Discord plugin. Our community will be happy to help you troubleshoot and resolve the issue.', 'pmpro-discord-add-on' ); ?></li>
			<li class="mp-icon mp-icon-right-big">
			<?php
			echo wp_kses(
				'<a target="_blank" href="https://wordpress.org/support/plugin/pmpro-discord-add-on/">Support » Plugin: Connect Paid Memberships Pro to Discord</a>',
				array(
					'a' => array(
						'href'   => array(),
						'target' => array(),
					),
				)
			);
			?>
 </li>
		</ul>		

		<!-- <div class="form-fields-box ">
		  <div class="ets-row ets-mt-5 ets-align-items-center">
			<div class="ets-col-7 ets-offset-md-1">
			  <div class="contact-fields pr-100">
				<div class="ets-form-group">
				  <label><?php echo __( 'Full Name', 'pmpro-discord-add-on' ); ?></label>
				  <input type="text" name="ets_user_name" value="<?php echo esc_attr( $currUserName ); ?>" class="form-control contact-input" placeholder="Write Your Full Name">
				  <?php wp_nonce_field( 'get_support', 'ets_discord_get_support' ); ?>
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Contact Email', 'pmpro-discord-add-on' ); ?></label>
				  <input type="text" name="ets_user_email" class="form-control contact-input" value="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" placeholder="Write Your Email">
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Subject', 'pmpro-discord-add-on' ); ?></label>
				  <input type="text" name="ets_support_subject" class="form-control contact-input" placeholder="Write Your Subject" required="">
				</div>
				<div class="ets-form-group">
				  <label><?php echo __( 'Message', 'pmpro-discord-add-on' ); ?></label>
				  <textarea name="ets_support_msg" class="form-control contact-textarea" required=""></textarea>
				</div>
				<div class="submit-btn d-flex align-items-center w-100 pt-3">
				  <input type="submit" name="save" id="save" class="btn btn-submit ets-bg-green" value="Submit">                  
				  <a href="skype:ravi.soni971?chat" class="btn btn-skype ml-auto"><?php echo __( 'Skype', 'pmpro-discord-add-on' ); ?></a>
				</div>
			  </div>
			</div>
			<div class="ets-col-3">
			  <div class="right-side-box">
				<div class="contact-details d-inline-block w-100 mb-4">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fas fa-envelope title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Email', 'pmpro-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p><a href="mailto:contact@expresstechsoftwares.com">contact@expresstechsoftwares.com</a></p>
					<p><a href="mailto:vinod.tiwari@expresstechsoftwares.com">vinod.tiwari@expresstechsoftwares.com</a></p>
				  </div>
				</div>
				<div class="contact-details d-inline-block w-100 mb-4">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fab fa-skype title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Skype', 'pmpro-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p>ravi.soni971</p>
				  </div>
				</div>
				<div class="contact-details d-inline-block w-100">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fab fa-whatsapp title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Whatsapp / Phone', 'pmpro-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p>+91-9806724185</p>
				  </div>
		</div> -->
		
		<!-- <div class="contact-details d-inline-block w-100">
				  <div class="top-icon-title d-flex align-items-center w-100">
					<i class="fab fa-discord title-icon fa-lg fa-inverse" aria-hidden="true"></i>
					<p><?php echo __( 'Discord', 'pmpro-discord-add-on' ); ?></p>
				  </div>
				  <div class="contact-body mt-3">
					<p>expresstechceo#9284</p>
				  </div>
		</div> -->

			  </div>
			</div>
		  </div>
		</div>
	  </div>
  </form>
</div>
