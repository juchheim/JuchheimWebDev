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
 *
 * Note that this function is hooked into the after_setup_theme hook, which
 * runs before the init hook. The init hook is too late for some features, such
 * as indicating support for post thumbnails.
 */
function juchheim_setup() {
	/*
		* Make theme available for translation.
		* Translations can be filed in the /languages/ directory.
		* If you're building a theme based on Juchheim, use a find and replace
		* to change 'juchheim' to the name of your theme in all the template files.
		*/
	load_theme_textdomain( 'juchheim', get_template_directory() . '/languages' );

	// Add default posts and comments RSS feed links to head.
	add_theme_support( 'automatic-feed-links' );

	/*
		* Let WordPress manage the document title.
		* By adding theme support, we declare that this theme does not use a
		* hard-coded <title> tag in the document head, and expect WordPress to
		* provide it for us.
		*/
	add_theme_support( 'title-tag' );

	/*
		* Enable support for Post Thumbnails on posts and pages.
		*
		* @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		*/
	add_theme_support( 'post-thumbnails' );

	// This theme uses wp_nav_menu() in one location.
	register_nav_menus(
		array(
			'menu-1' => esc_html__( 'Primary', 'juchheim' ),
		)
	);

	/*
		* Switch default core markup for search form, comment form, and comments
		* to output valid HTML5.
		*/
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

	// Set up the WordPress core custom background feature.
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

	// Add theme support for selective refresh for widgets.
	add_theme_support( 'customize-selective-refresh-widgets' );

	/**
	 * Add support for core custom logo.
	 *
	 * @link https://codex.wordpress.org/Theme_Logo
	 */
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

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function juchheim_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'juchheim_content_width', 640 );
}
add_action( 'after_setup_theme', 'juchheim_content_width', 0 );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
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

/**
 * Enqueue scripts and styles.
 */
function juchheim_scripts() {
	wp_enqueue_style( 'juchheim-style', get_stylesheet_uri(), array(), _S_VERSION );
	wp_style_add_data( 'juchheim-style', 'rtl', 'replace' );

	wp_enqueue_script( 'juchheim-navigation', get_template_directory_uri() . '/js/navigation.js', array(), _S_VERSION, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
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





function enqueue_custom_assets() {
    $script_url = get_template_directory_uri() . '/js/script.js';
    error_log('Enqueuing script: ' . $script_url);

    wp_enqueue_script('custom-script', $script_url, array(), '1.0', true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_assets');


function enqueue_custom_scripts() {
	wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/script.js', array(), '1.0', true);

    // Enqueue Stripe.js
    wp_enqueue_script('stripe-js', 'https://js.stripe.com/v3/', [], null, true);

    // Enqueue your custom stripe.js file
    wp_enqueue_script('custom-stripe-js', get_template_directory_uri() . '/js/stripe.js', ['stripe-js'], null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');






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

function handle_stripe_webhook() {
    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
    $endpoint_secret = 'your_stripe_webhook_secret'; // Replace with your actual Stripe webhook secret

    $event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
            $payload, $sig_header, $endpoint_secret
        );
    } catch(\UnexpectedValueException $e) {
        // Invalid payload
        http_response_code(400);
        exit();
    } catch(\Stripe\Exception\SignatureVerificationException $e) {
        // Invalid signature
        http_response_code(400);
        exit();
    }

    // Handle the event
    if ($event['type'] === 'checkout.session.completed') {
        $session = $event['data']['object'];

        $customer_email = $session['customer_details']['email'];
        $plan = $session['display_items'][0]['custom']['name'];
        // Retrieve metadata (name, password)
        $metadata = $session['metadata'];
        $name = $metadata['name'];
        $password = $metadata['password'];

        // Register the user in WordPress
        $user_id = wp_create_user($name, $password, $customer_email);
        if (is_wp_error($user_id)) {
            error_log('User registration failed: ' . $user_id->get_error_message());
        } else {
            wp_update_user([
                'ID' => $user_id,
                'role' => 'subscriber',
            ]);
        }
    }

    http_response_code(200);
}
?>
