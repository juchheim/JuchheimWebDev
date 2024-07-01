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


// Display custom fields in user profile
function juchheim_show_extra_profile_fields($user) {
    // Retrieve the stored values
    $products_purchased = get_the_author_meta('products_purchased', $user->ID);
    $products_purchased = $products_purchased ? unserialize($products_purchased) : [];

    ?>
    <h3><?php _e('Extra Profile Information', 'juchheim'); ?></h3>

    <table class="form-table">
        <tr>
            <th><label for="product_purchased"><?php _e('Product Purchased', 'juchheim'); ?></label></th>
            <td>
                <?php if (!empty($products_purchased)): ?>
                    <?php foreach ($products_purchased as $index => $purchase): ?>
                        <div>
                            <input type="text" name="products_purchased[<?php echo $index; ?>][product_name]" value="<?php echo esc_attr($purchase['product_name']); ?>" class="regular-text" placeholder="Product Name" />
                            <input type="text" name="products_purchased[<?php echo $index; ?>][purchase_price]" value="<?php echo esc_attr($purchase['purchase_price']); ?>" class="regular-text" placeholder="Purchase Price" />
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div id="extra-fields"></div>
                <button type="button" id="add-field"><?php _e('Add Another Product', 'juchheim'); ?></button>
                <span class="description"><?php _e('Add products purchased and their prices.', 'juchheim'); ?></span>
            </td>
        </tr>
    </table>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#add-field').click(function() {
                var index = $('#extra-fields div').length;
                $('#extra-fields').append(
                    '<div>' +
                        '<input type="text" name="products_purchased[' + index + '][product_name]" class="regular-text" placeholder="Product Name" />' +
                        '<input type="text" name="products_purchased[' + index + '][purchase_price]" class="regular-text" placeholder="Purchase Price" />' +
                    '</div>'
                );
            });
        });
    </script>
    <?php
}
add_action('show_user_profile', 'juchheim_show_extra_profile_fields');
add_action('edit_user_profile', 'juchheim_show_extra_profile_fields');



?>
