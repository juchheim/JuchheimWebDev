<?php
/**
 * Juchheim functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Juchheim
 */

if ( ! defined( '_S_VERSION' ) ) {
	// Replace the version number of the theme on each release.
	define( '_S_VERSION', '1.0.0' );
}

/**
 * Sets up theme defaults and registers support for various WordPress features.
 */
function juchheim_setup() {
	load_theme_textdomain( 'juchheim', get_template_directory() . '/languages' );

	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );

	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'juchheim' ),
		)
	);

	add_theme_support(
		'html5',
		array(
			'search-form',
			'comment-form',
			'comment-list',
			'gallery',
			'caption',
			'style',
			'script',
		)
	);

	add_theme_support(
		'custom-background',
		apply_filters(
			'juchheim_custom_background_args',
			array(
				'default-color' => 'ffffff',
				'default-image' => '',
			)
		)
	);

	add_theme_support( 'customize-selective-refresh-widgets' );

	add_theme_support(
		'custom-logo',
		array(
			'height'      => 250,
			'width'       => 250,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);
}
add_action( 'after_setup_theme', 'juchheim_setup' );

function juchheim_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'juchheim_content_width', 640 );
}
add_action( 'after_setup_theme', 'juchheim_content_width', 0 );

function juchheim_widgets_init() {
	register_sidebar(
		array(
			'name'          => esc_html__( 'Sidebar', 'juchheim' ),
			'id'            => 'sidebar-1',
			'description'   => esc_html__( 'Add widgets here.', 'juchheim' ),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action( 'widgets_init', 'juchheim_widgets_init' );

function juchheim_scripts() {
	wp_enqueue_style( 'juchheim-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'juchheim-style', 'rtl', 'replace' );

	wp_enqueue_script( 'juchheim-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}

	wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/script.js', array(), '1.0', true);

	// Enqueue Stripe.js
	wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);

	// Enqueue your custom stripe.js file
	wp_enqueue_script('custom-stripe-js', get_template_directory_uri() . '/js/stripe.js', ['stripe-js'], null, true);
}
add_action( 'wp_enqueue_scripts', 'juchheim_scripts' );

/**
 * Implement the Custom Header feature.
 */
require get_template_directory() . '/inc/custom-header.php';

/**
 * Custom template tags for this theme.
 */
require get_template_directory() . '/inc/template-tags.php';

/**
 * Functions which enhance the theme by hooking into WordPress.
 */
require get_template_directory() . '/inc/template-functions.php';

/**
 * Customizer additions.
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Load Jetpack compatibility file.
 */
if ( defined( 'JETPACK__VERSION' ) ) {
	require get_template_directory() . '/inc/jetpack.php';
}

// Register custom REST API endpoint for handling checkout
add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/checkout', array(
        'methods' => 'POST',
        'callback' => 'handle_checkout',
    ));
});

function handle_checkout($request) {
    $params = $request->get_json_params();
    $name = sanitize_text_field($params['name']);
    $email = sanitize_email($params['email']);
    $password = sanitize_text_field($params['password']);
    $plan = sanitize_text_field($params['plan']);

    $amount = $plan === 'monthly' ? 1000 : 10000; // in cents
    $interval = $plan === 'monthly' ? 'month' : 'year';

    // Stripe API initialization
    \Stripe\Stripe::setApiKey('your_stripe_secret_key'); // Replace with your actual Stripe secret key

    try {
        // Create Stripe Checkout Session
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'product_data' => [
                        'name' => 'Web Hosting Plan',
                    ],
                    'unit_amount' => $amount,
                    'recurring' => [
                        'interval' => $interval,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => home_url('/checkout-success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => home_url('/checkout-cancel'),
        ]);

        return new WP_REST_Response(['id' => $session->id, 'url' => $session->url], 200);
    } catch (Exception $e) {
        return new WP_REST_Response(['message' => $e->getMessage()], 400);
    }
}

// Register custom REST API endpoint for Stripe webhook
add_action('rest_api_init', function() {
    register_rest_route('custom/v1', '/webhook', array(
        'methods' => 'POST',
        'callback' => 'handle_stripe_webhook',
    ));
});


function custom_admin_scripts() {
    ?>
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            var elements = document.querySelectorAll('.wp-menu-name');
            elements.forEach(function(element) {
                if (element.textContent.trim() === 'Price') {
                    element.style.display = 'none';
                }
            });
        });
    </script>
    <?php
}
add_action('admin_footer', 'custom_admin_scripts');


function enqueue_portfolio_scripts() {
    // Enqueue your portfolio.js script
    wp_enqueue_script('portfolio-js', get_template_directory_uri() . '/js/portfolio.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_portfolio_scripts');


add_action('template_redirect', function () {
    if (is_admin()) {
        return;
    }

    global $wp;
    if (!$wp) {
        error_log('Global $wp is not set.');
        return;
    }

    $current_url = home_url(add_query_arg([], $wp->request));

    if (is_404()) {
        error_log('404 error detected for: ' . $current_url);
    }

});

add_action( 'template_redirect', 'redirect_to_login_if_not_logged_in' );

function redirect_to_login_if_not_logged_in() {
    // Check if the custom query parameter is set and the user is not logged in
    if ( isset($_GET['check_login']) && ! is_user_logged_in() ) {
        // Get the current URL
        $current_url = home_url( $_SERVER['REQUEST_URI'] );
        // Redirect to the login page with a redirect_to parameter
        wp_redirect( wp_login_url( $current_url ) );
        exit;
    }

    // If the user is logged in and the custom query parameter is set, redirect to /subscriptions
    if ( isset($_GET['check_login']) && is_user_logged_in() ) {
        wp_redirect( home_url('/subscriptions/') );
        exit;
    }
}



?>
