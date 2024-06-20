<?php
get_header();
?>

<div class="content-area">
    <main id="main" class="site-main">
        <h1>Crafting Unique Web Experiences for Over 20 Years</h1>
		<p>At Ernest Juchheim Web Development, we specialize in custom WordPress design and development, delivering visually impactful and highly functional websites that align with your brand's objectives.</p>
		<h2>Reliable Web Hosting Services</h2>
		<p>Keep your website operational and secure with our high-performance web hosting solutions. I provide dependable hosting services to ensure your site remains fast, secure, and accessible at all times.</p>
		<h2 class="yellow_accent">Someone you can count on</h2>
		<p>Reach out to me directly via text at <?php echo '662-897-8747'; ?> whenever you have any questions or to report an issue. I'm here to help whenever you need assistance.</p>
		<!-- <a href="#"><img class="down-arrow" src="/wp-content/uploads/2024/06/down_arrow.png" /></a> -->

        <div id="portfolio"></div>
        <h1>Portfolio</h1>
        <?php
        // Fetch the Pods instance for 'portfolio'
        $pods = pods('portfolio', array(
            'limit' => -1 // Ensure we fetch all entries
        ));

        // Check if there are any entries
        if ($pods->total() > 0) {
            echo '<div class="portfolio-grid">';
            
            // Loop through the Pods entries
            while ($pods->fetch()) {
                // Get the image, link, and caption fields
                $image = $pods->field('image');
                $link = $pods->field('link');
                $caption = $pods->field('caption');

                // Output the portfolio item
                echo '<figure class="portfolio-item">';
                if ($link) {
                    echo '<a href="' . esc_url($link) . '" target="_blank">';
                }
                if ($image) {
                    echo '<img src="' . esc_url($image['guid']) . '" alt="' . esc_attr($image['post_title']) . '">';
                }
                if ($link) {
                    echo '</a>';
                }
                if ($caption) {
                    echo '<figcaption>' . esc_html($caption) . '</figcaption>';
                }
                echo '</figure>';
            }
            
            echo '</div>';
        } else {
            echo '<p>No portfolio items found.</p>';
        }

        if (have_posts()) :
            while (have_posts()) : the_post();
                the_content();
            endwhile;
        endif;
        ?>

<div class="content-area">
    <main id="main" class="site-main">
		<div id="payments"></div>
        <h1>Payments</h1>
        <div class="tabs-container">
            <ul class="tabs">
                <li class="tab active" data-tab="web-hosting">Web Hosting</li>
                <li class="tab" data-tab="design-development">Development</li>
                <li class="tab" data-tab="custom">Custom</li>
            </ul>
            <div class="tab-content">
				<?php echo do_shortcode('[stripe_integration_forms]'); ?>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
?>


