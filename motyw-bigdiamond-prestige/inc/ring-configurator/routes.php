<?php
/**
 * Ring Configurator Routes
 *
 * Registers pages and URL rewriting for ring configurator integration.
 *
 * @package BigDIAMOND_White_Prestige
 * @since   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register configurator page on theme activation
 *
 * @since 1.0.0
 */
function bdwp_create_configurator_page() {
	// Check if page already exists
	$page = get_page_by_path( 'konfigurator-obraczek' );

	if ( ! $page ) {
		$page_id = wp_insert_post( array(
			'post_title'   => __( 'Konfigurator obr?czek', 'bigdiamond-white-prestige' ),
			'post_name'    => 'konfigurator-obraczek',
			'post_content' => '[bdwp_ring_configurator]',
			'post_status'  => 'publish',
			'post_type'    => 'page',
			'post_author'  => 1,
		) );

		update_option( 'bdwp_configurator_page_id', $page_id );
	}
}
add_action( 'after_switch_theme', 'bdwp_create_configurator_page' );

/**
 * Get configurator external URL with UTM parameters
 *
 * @param array $params Optional additional parameters.
 * @return string Configurator URL.
 * @since 1.0.0
 */
function bdwp_get_configurator_url( $params = array() ) {
	$base_url = get_option( 'bdwp_ring_configurator_url', '' );

	if ( empty( $base_url ) ) {
		return home_url( '/konfigurator-obraczek' );
	}

	// Add default UTM parameters
	$default_params = array(
		'utm_source'   => 'bigdiamond',
		'utm_medium'   => 'website',
		'utm_campaign' => 'ring_configurator',
	);

	// Add return URL
	$default_params['return_url'] = home_url( '/konfigurator-obraczek/podsumowanie' );

	// Add webhook URL
	$default_params['webhook_url'] = rest_url( 'bdwp/v1/rings/webhook' );

	// Merge with custom parameters
	$all_params = array_merge( $default_params, $params );

	return add_query_arg( $all_params, $base_url );
}

/**
 * Handle configurator redirect
 *
 * @since 1.0.0
 */
function bdwp_handle_configurator_redirect() {
	if ( ! is_page( 'konfigurator-obraczek' ) || isset( $_GET['action'] ) ) {
		return;
	}

	// Get user ID if logged in for personalization
	$user_id = get_current_user_id();
	$params = array();

	if ( $user_id ) {
		$user = get_userdata( $user_id );
		$params['customer_email'] = $user->user_email;
		$params['customer_name']  = $user->display_name;
	}

	// Redirect to external configurator
	$configurator_url = bdwp_get_configurator_url( $params );

	if ( $configurator_url !== home_url( '/konfigurator-obraczek' ) ) {
		wp_redirect( $configurator_url ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
		exit;
	}
}
add_action( 'template_redirect', 'bdwp_handle_configurator_redirect' );

/**
 * Register configurator summary rewrite endpoint
 *
 * @since 1.0.0
 */
function bdwp_add_configurator_endpoints() {
	add_rewrite_rule(
		'^konfigurator-obraczek/podsumowanie/?$',
		'index.php?pagename=konfigurator-obraczek&config_action=summary',
		'top'
	);

	add_rewrite_tag( '%config_action%', '([^&]+)' );
}
add_action( 'init', 'bdwp_add_configurator_endpoints' );

/**
 * Display configurator summary page
 *
 * @since 1.0.0
 */
function bdwp_configurator_summary_template() {
	$config_action = get_query_var( 'config_action' );

	if ( 'summary' !== $config_action ) {
		return;
	}

	// Get configuration data from session or query params
	$config_id = isset( $_GET['config_id'] ) ? sanitize_text_field( wp_unslash( $_GET['config_id'] ) ) : '';

	if ( empty( $config_id ) ) {
		wp_safe_redirect( home_url() );
		exit;
	}

	// Retrieve configuration from transient (set by webhook)
	$config_data = get_transient( 'bdwp_ring_config_' . $config_id );

	if ( ! $config_data ) {
		echo '<div class="container mx-auto px-4 py-12 text-center">';
		echo '<h1 class="text-3xl font-display mb-4">' . esc_html__( 'Konfiguracja nie zosta?a znaleziona', 'bigdiamond-white-prestige' ) . '</h1>';
		echo '<p class="mb-6">' . esc_html__( 'Twoja konfiguracja mog?a wygasn?? lub nie zosta?a poprawnie zapisana.', 'bigdiamond-white-prestige' ) . '</p>';
		echo '<a href="' . esc_url( home_url( '/konfigurator-obraczek' ) ) . '" class="btn-primary">' . esc_html__( 'Powr?t do konfiguratora', 'bigdiamond-white-prestige' ) . '</a>';
		echo '</div>';
		exit;
	}

	// Display summary
	bdwp_display_ring_configurator_summary( $config_data );
	exit;
}
add_action( 'template_redirect', 'bdwp_configurator_summary_template', 20 );

/**
 * Display ring configurator summary
 *
 * @param array $config_data Configuration data.
 * @since 1.0.0
 */
function bdwp_display_ring_configurator_summary( $config_data ) {
	get_header();

	?>
	<div class="configurator-summary container mx-auto px-4 py-12">
		<h1 class="text-4xl font-display font-bold text-center mb-8">
			<?php esc_html_e( 'Podsumowanie konfiguracji obr?czek', 'bigdiamond-white-prestige' ); ?>
		</h1>

		<div class="grid md:grid-cols-2 gap-12 mb-12">
			<!-- Ring 1 -->
			<div class="ring-summary bg-white rounded-lg shadow-lg p-8">
				<h2 class="text-2xl font-display font-semibold mb-6">
					<?php esc_html_e( 'Obr?czka 1', 'bigdiamond-white-prestige' ); ?>
				</h2>

				<?php if ( ! empty( $config_data['ring1']['image'] ) ) : ?>
					<img src="<?php echo esc_url( $config_data['ring1']['image'] ); ?>" alt="Ring 1" class="w-full h-auto mb-6 rounded">
				<?php endif; ?>

				<dl class="space-y-3">
					<?php foreach ( $config_data['ring1']['specs'] ?? array() as $key => $value ) : ?>
						<div class="flex justify-between border-b border-gray-200 pb-2">
							<dt class="font-medium"><?php echo esc_html( ucfirst( $key ) ); ?>:</dt>
							<dd><?php echo esc_html( $value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( ! empty( $config_data['ring1']['price'] ) ) : ?>
					<div class="mt-6 text-2xl font-bold text-bd-gold text-center">
						<?php echo wc_price( $config_data['ring1']['price'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>

			<!-- Ring 2 -->
			<div class="ring-summary bg-white rounded-lg shadow-lg p-8">
				<h2 class="text-2xl font-display font-semibold mb-6">
					<?php esc_html_e( 'Obr?czka 2', 'bigdiamond-white-prestige' ); ?>
				</h2>

				<?php if ( ! empty( $config_data['ring2']['image'] ) ) : ?>
					<img src="<?php echo esc_url( $config_data['ring2']['image'] ); ?>" alt="Ring 2" class="w-full h-auto mb-6 rounded">
				<?php endif; ?>

				<dl class="space-y-3">
					<?php foreach ( $config_data['ring2']['specs'] ?? array() as $key => $value ) : ?>
						<div class="flex justify-between border-b border-gray-200 pb-2">
							<dt class="font-medium"><?php echo esc_html( ucfirst( $key ) ); ?>:</dt>
							<dd><?php echo esc_html( $value ); ?></dd>
						</div>
					<?php endforeach; ?>
				</dl>

				<?php if ( ! empty( $config_data['ring2']['price'] ) ) : ?>
					<div class="mt-6 text-2xl font-bold text-bd-gold text-center">
						<?php echo wc_price( $config_data['ring2']['price'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<!-- Total Price -->
		<?php
		$total_price = ( $config_data['ring1']['price'] ?? 0 ) + ( $config_data['ring2']['price'] ?? 0 );
		if ( $total_price > 0 ) :
			?>
			<div class="total-price bg-bd-cream rounded-lg p-8 text-center mb-8">
				<h3 class="text-xl font-semibold mb-2"><?php esc_html_e( 'Cena ca?kowita', 'bigdiamond-white-prestige' ); ?></h3>
				<div class="text-4xl font-bold text-bd-gold">
					<?php echo wc_price( $total_price ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				</div>
			</div>
		<?php endif; ?>

		<!-- Actions -->
		<div class="actions flex flex-col md:flex-row gap-4 justify-center">
			<a href="<?php echo esc_url( home_url( '/konfigurator-obraczek' ) ); ?>" class="btn-secondary">
				<?php esc_html_e( 'Edytuj konfiguracj?', 'bigdiamond-white-prestige' ); ?>
			</a>
			<button onclick="bdwpAddRingsToCart()" class="btn-primary">
				<?php esc_html_e( 'Dodaj do koszyka', 'bigdiamond-white-prestige' ); ?>
			</button>
		</div>
	</div>

	<script>
		function bdwpAddRingsToCart() {
			// AJAX call to add custom rings to cart
			fetch('<?php echo esc_url( rest_url( 'bdwp/v1/rings/add-to-cart' ) ); ?>', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': '<?php echo esc_js( wp_create_nonce( 'wp_rest' ) ); ?>'
				},
				body: JSON.stringify({
					config_id: '<?php echo esc_js( $_GET['config_id'] ?? '' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended ?>'
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					window.location.href = '<?php echo esc_url( wc_get_cart_url() ); ?>';
				} else {
					alert(data.message || 'Wyst?pi? b??d');
				}
			})
			.catch(error => {
				console.error('Error:', error);
				alert('Wyst?pi? b??d podczas dodawania do koszyka');
			});
		}
	</script>

	<?php

	get_footer();
}

/**
 * Register ring configurator shortcode
 *
 * @since 1.0.0
 */
function bdwp_ring_configurator_shortcode() {
	ob_start();

	?>
	<div class="ring-configurator-intro text-center py-12">
		<h1 class="text-4xl font-display font-bold mb-6">
			<?php esc_html_e( 'Konfigurator obr?czek ?lubnych', 'bigdiamond-white-prestige' ); ?>
		</h1>
		<p class="text-xl text-bd-gray-700 mb-8 max-w-3xl mx-auto">
			<?php esc_html_e( 'Zaprojektuj idealne obr?czki ?lubne wed?ug swoich upodoba?. Wybierz materia?, wyko?czenie, kamienie i grawerowanie.', 'bigdiamond-white-prestige' ); ?>
		</p>

		<a href="<?php echo esc_url( bdwp_get_configurator_url() ); ?>" class="inline-block bg-bd-gold text-white px-8 py-4 rounded-lg text-lg font-semibold hover:bg-opacity-90 transition-all">
			<?php esc_html_e( 'Rozpocznij projektowanie', 'bigdiamond-white-prestige' ); ?>
		</a>

		<div class="features grid md:grid-cols-3 gap-8 mt-16 max-w-5xl mx-auto">
			<div class="feature">
				<div class="text-5xl mb-4">??</div>
				<h3 class="text-xl font-semibold mb-2"><?php esc_html_e( 'Pe?na personalizacja', 'bigdiamond-white-prestige' ); ?></h3>
				<p class="text-bd-gray-600"><?php esc_html_e( 'Projektuj ka?dy szczeg?? swoich obr?czek', 'bigdiamond-white-prestige' ); ?></p>
			</div>

			<div class="feature">
				<div class="text-5xl mb-4">?</div>
				<h3 class="text-xl font-semibold mb-2"><?php esc_html_e( 'Podgl?d 3D', 'bigdiamond-white-prestige' ); ?></h3>
				<p class="text-bd-gray-600"><?php esc_html_e( 'Zobacz swoje obr?czki w realistycznej wizualizacji', 'bigdiamond-white-prestige' ); ?></p>
			</div>

			<div class="feature">
				<div class="text-5xl mb-4">??</div>
				<h3 class="text-xl font-semibold mb-2"><?php esc_html_e( 'Najwy?sza jako??', 'bigdiamond-white-prestige' ); ?></h3>
				<p class="text-bd-gray-600"><?php esc_html_e( 'Wszystkie obr?czki wykonywane przez mistrz?w jubilerstwa', 'bigdiamond-white-prestige' ); ?></p>
			</div>
		</div>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'bdwp_ring_configurator', 'bdwp_ring_configurator_shortcode' );
