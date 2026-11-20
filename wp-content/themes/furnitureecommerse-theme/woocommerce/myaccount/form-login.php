<?php
/**
 * Login & Register Form Override (Clean Separated Tabs/Views)
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : 'login';

// If registration not enabled, force login
if ( ! $registration_enabled ) {
	$action = 'login';
}

do_action( 'woocommerce_before_customer_login_form' ); ?>

<div class="fe-auth-wrapper">
	<div class="fe-auth-card">

		<?php if ( $registration_enabled ) : ?>
			<!-- Modern Tab Switcher -->
			<div class="fe-auth-tabs" role="tablist">
				<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"
				   class="fe-auth-tab <?php echo ( 'login' === $action ) ? 'is-active' : ''; ?>"
				   id="fe-tab-login"
				   data-target="fe-auth-login-pane"
				   role="tab"
				   aria-selected="<?php echo ( 'login' === $action ) ? 'true' : 'false'; ?>">
					<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Sign In', 'furnitureecommerse' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>"
				   class="fe-auth-tab <?php echo ( 'register' === $action ) ? 'is-active' : ''; ?>"
				   id="fe-tab-register"
				   data-target="fe-auth-register-pane"
				   role="tab"
				   aria-selected="<?php echo ( 'register' === $action ) ? 'true' : 'false'; ?>">
					<i class="fas fa-user-plus"></i> <?php esc_html_e( 'Create Account', 'furnitureecommerse' ); ?>
				</a>
			</div>
		<?php else : ?>
			<div class="fe-auth-header-single">
				<h2><?php esc_html_e( 'Sign In', 'woocommerce' ); ?></h2>
				<p><?php esc_html_e( 'Welcome back! Please enter your details.', 'furnitureecommerse' ); ?></p>
			</div>
		<?php endif; ?>

		<div class="fe-auth-panes">
			<!-- PANE 1: LOGIN -->
			<div class="fe-auth-pane <?php echo ( 'login' === $action ) ? 'is-active' : ''; ?>" id="fe-auth-login-pane" role="tabpanel" aria-labelledby="fe-tab-login">
				<div class="fe-auth-pane-header">
					<h3><?php esc_html_e( 'Welcome Back', 'furnitureecommerse' ); ?></h3>
					<p><?php esc_html_e( 'Log in to manage your orders, wishlist, and account details.', 'furnitureecommerse' ); ?></p>
				</div>

				<form class="woocommerce-form woocommerce-form-login login fe-auth-form" method="post" novalidate>
					<?php do_action( 'woocommerce_login_form_start' ); ?>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="username"><?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
						<div class="fe-input-with-icon">
							<i class="far fa-user fe-input-icon"></i>
							<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" placeholder="user@example.com" />
						</div>
					</p>

					<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
						<label for="password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
						<div class="fe-input-with-icon">
							<i class="fas fa-lock fe-input-icon"></i>
							<input class="woocommerce-Input woocommerce-Input--text input-text" type="password" name="password" id="password" autocomplete="current-password" required aria-required="true" placeholder="••••••••" />
						</div>
					</p>

					<?php do_action( 'woocommerce_login_form' ); ?>

					<div class="fe-auth-remember-row">
						<label class="woocommerce-form__label woocommerce-form__label-for-checkbox woocommerce-form-login__rememberme">
							<input class="woocommerce-form__input woocommerce-form__input-checkbox" name="rememberme" type="checkbox" id="rememberme" value="forever" />
							<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
						</label>
						<a class="woocommerce-LostPassword lost_password" href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
							<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
						</a>
					</div>

					<p class="form-row fe-auth-submit-row">
						<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
						<button type="submit" class="woocommerce-button button woocommerce-form-login__submit fe-btn-block" name="login" value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>">
							<i class="fas fa-sign-in-alt"></i> <?php esc_html_e( 'Log In', 'woocommerce' ); ?>
						</button>
					</p>

					<?php do_action( 'woocommerce_login_form_end' ); ?>
				</form>

				<?php if ( $registration_enabled ) : ?>
					<div class="fe-auth-switch-prompt">
						<span><?php esc_html_e( "Don't have an account?", 'furnitureecommerse' ); ?></span>
						<a href="<?php echo esc_url( add_query_arg( 'action', 'register', wc_get_page_permalink( 'myaccount' ) ) ); ?>" class="fe-auth-switch-link" data-target="fe-auth-register-pane">
							<?php esc_html_e( 'Create an account here &rarr;', 'furnitureecommerse' ); ?>
						</a>
					</div>
				<?php endif; ?>
			</div>

			<?php if ( $registration_enabled ) : ?>
				<!-- PANE 2: REGISTER -->
				<div class="fe-auth-pane <?php echo ( 'register' === $action ) ? 'is-active' : ''; ?>" id="fe-auth-register-pane" role="tabpanel" aria-labelledby="fe-tab-register">
					<div class="fe-auth-pane-header">
						<h3><?php esc_html_e( 'Create an Account', 'furnitureecommerse' ); ?></h3>
						<p><?php esc_html_e( 'Register to track shipments, save favorites, and checkout faster.', 'furnitureecommerse' ); ?></p>
					</div>

					<form method="post" class="woocommerce-form woocommerce-form-register register fe-auth-form" <?php do_action( 'woocommerce_register_form_tag' ); ?> >
						<?php do_action( 'woocommerce_register_form_start' ); ?>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_username"><?php esc_html_e( 'Username', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<div class="fe-input-with-icon">
									<i class="far fa-user fe-input-icon"></i>
									<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="reg_username" autocomplete="username" value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; ?>" required aria-required="true" placeholder="Choose a username" />
								</div>
							</p>
						<?php endif; ?>

						<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
							<label for="reg_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
							<div class="fe-input-with-icon">
								<i class="far fa-envelope fe-input-icon"></i>
								<input type="email" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="reg_email" autocomplete="email" value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; ?>" required aria-required="true" placeholder="yourname@example.com" />
							</div>
						</p>

						<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
							<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
								<label for="reg_password"><?php esc_html_e( 'Password', 'woocommerce' ); ?>&nbsp;<span class="required" aria-hidden="true">*</span></label>
								<div class="fe-input-with-icon">
									<i class="fas fa-lock fe-input-icon"></i>
									<input type="password" class="woocommerce-Input woocommerce-Input--text input-text" name="password" id="reg_password" autocomplete="new-password" required aria-required="true" placeholder="••••••••" />
								</div>
							</p>
						<?php else : ?>
							<div class="fe-auth-note">
								<i class="fas fa-info-circle"></i>
								<p><?php esc_html_e( 'A password will be sent to your email address automatically.', 'woocommerce' ); ?></p>
							</div>
						<?php endif; ?>

						<?php do_action( 'woocommerce_register_form' ); ?>

						<p class="woocommerce-form-row form-row fe-auth-submit-row">
							<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
							<button type="submit" class="woocommerce-Button woocommerce-button button woocommerce-form-register__submit fe-btn-block" name="register" value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>">
								<i class="fas fa-user-plus"></i> <?php esc_html_e( 'Register Account', 'woocommerce' ); ?>
							</button>
						</p>

						<?php do_action( 'woocommerce_register_form_end' ); ?>
					</form>

					<div class="fe-auth-switch-prompt">
						<span><?php esc_html_e( 'Already have an account?', 'furnitureecommerse' ); ?></span>
						<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="fe-auth-switch-link" data-target="fe-auth-login-pane">
							<?php esc_html_e( 'Sign in here &rarr;', 'furnitureecommerse' ); ?>
						</a>
					</div>
				</div>
			<?php endif; ?>
		</div>

	</div>
</div>

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
