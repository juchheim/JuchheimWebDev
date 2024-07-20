<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-G5KDCG4E3B"></script>
    <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    gtag('config', 'G-G5KDCG4E3B');
    </script>
    <meta name="description" content="Ernest Juchheim, a professional web developer and designer in Mississippi, specializes in creating custom WordPress websites, themes, and plugins. Serving businesses in the Mississippi Delta region including Greenwood, Yazoo City, Clarksdale, and Greenville. Sign up for web hosting or pay for web development services today." />
    <meta name="keywords" content="web developer, Mississippi, WordPress developer, custom WordPress themes, WordPress plugins, Mississippi web developer, Mississippi Delta web developer, web design, web development, Greenwood, Yazoo City, Clarksdale, Greenville" />
    <meta name="author" content="Ernest Juchheim" />
    <meta property="og:title" content="Ernest Juchheim - Professional Web Developer & Designer in Mississippi" />
    <meta property="og:description" content="Ernest Juchheim, a professional web developer and designer in Yazoo City, Mississippi, specializes in creating custom WordPress websites, themes, and plugins. Serving businesses in the Mississippi Delta region including Greenwood, Yazoo City, Clarksdale, and Greenville. Sign up for web hosting or pay for web development services today." />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="https://www.juchheim.online" />
    <meta name="robots" content="index, follow" />
    <link rel="canonical" href="https://www.juchheim.online" />
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
    <?php if ( ! is_user_logged_in() ) : ?>
        <div class="login-button">
            <a href="<?php echo home_url('/subscriptions/?check_login=true'); ?>">Manage Plan</a>
        </div>
        <div class="chat-button">
            <a href="/chat">Chat</a>
        </div>
    <?php endif; ?>
    <?php if ( is_user_logged_in() ) : ?>
        <div class="login-button">
            <a href="/subscriptions">Manage Plan</a>
        </div>
        <div class="chat-button">
            <a href="/chat">Chat</a>
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
            <h1>Creating Unique Web Experiences for Over 20 Years</h1>
        </div>
    </div>
    <script>
        // JavaScript for parallax scrolling effect
        window.addEventListener('scroll', function() {
            var scrollPosition = window.pageYOffset;
            var parallax = document.querySelector('.parallax');
            parallax.style.transform = 'translateY(' + scrollPosition * -0.32 + 'px)';
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
