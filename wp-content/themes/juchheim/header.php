<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php if ( ! is_user_logged_in() ) : ?>
        <div class="login-button">
            <a href="<?php echo wp_login_url(); ?>">Login</a>
        </div>
    <?php endif; ?>
    <?php if ( is_user_logged_in() ) : ?>
        <div class="login-button">
            <a href="/subscriptions">Manage Subscriptions</a>
        </div>
    <?php endif; ?>
	<div class="top-links">
        <a href="#portfolio">Portfolio</a>
        <a href="#payments">Payments</a>
    </div>
    <div class="parallax"></div> <!-- Parallax background -->
    <div class="main-content">
        <header id="masthead" class="site-header">
            <div class="header-columns">
                <div class="header-column link"><h4><a href="#portfolio">Portfolio</a></h4></div>
                <div class="header-column">
                    <?php the_custom_logo(); ?>
                </div>
                <div class="header-column link"><h4><a href="#payments">Payments</a></h4></div>
            </div><!-- .header-columns -->
        </header><!-- #masthead -->
        <div class="content">
            <h1>Crafting Unique Web Experiences for Over 20 Years</h1>
        </div>
    </div>
    <script>
        // JavaScript for parallax scrolling effect
        window.addEventListener('scroll', function() {
            var scrollPosition = window.pageYOffset;
            var parallax = document.querySelector('.parallax');
            parallax.style.transform = 'translateY(' + scrollPosition * -0.35 + 'px)';
        });

		// JavaScript for smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                var targetId = this.getAttribute('href');
                var targetElement = document.querySelector(targetId);
                window.scrollTo({
                    top: targetElement.offsetTop,
                    behavior: 'smooth'
                });
            });
        });

    </script>
