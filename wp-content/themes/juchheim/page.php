<?php
get_header();
?>

<div class="content-area">
    <main id="main" class="site-main">
        <div class="lead-in"><h1>Creating Unique Web Experiences for Over 20 Years</h1></div>
		<p>Juchheim Web Development specializes in custom WordPress design and development, delivering visually impactful and highly functional websites that align with your brand's objectives.</p>
		<div class="services-container">
            <div class="services-column">
                <h2>Reliable Web Hosting Services</h2>
                <p>Keep your website operational and secure with my high-performance web hosting. I provide dependable hosting services to ensure your site remains fast, secure, and accessible at all times.</p>
                <a href="#payments" class="btn-hosting">Sign Up Now</a>
            </div>
            <div class="services-column">
                <h2 class="yellow_accent">Professional design and development</h2>
                <p>I leverage years of experience in graphic design and web development to create custom WordPress themes and plugins. My expertise is in HTML, CSS, JavaScript, and PHP.</p>
                <a href="#portfolio" class="btn-portfolio">View Portfolio</a>
            </div>
        </div>


        <div class="bio-section">
            <div class="bio-image">
                <img src="/wp-content/uploads/2024/07/me.jpg" alt="Ernest Juchheim">
            </div>
            <div class="bio-content">
                <h2>Ernest Juchheim</h2>
                <p>Ernest (Trip) Juchheim is a seasoned web developer with a rich background in graphic design and web development. He has 18 years of experience at Hammons and Associates, mastering the art of creating visually appealing and functional websites. Ernest currently serves as the webmaster at the Mississippi Achievement School District.</p>
                <p>Ernest holds a Bachelor of Science in Graphic Design from Mississippi College. His expertise spans technologies including HTML, PHP, MySQL, JavaScript, WordPress, Photoshop, Illustrator, InDesign, and QuarkXPress. Living near Yazoo City, Mississippi, Ernest has extensive experience throughout the Mississippi Delta but services clients anywhere.</p>
                <p>For inquiries, you can reach Ernest at <a href="mailto:ernest@juchheim.online">ernest@juchheim.online</a>.</p>
            </div>
        </div>








        <h3>Know that you can reach me directly at <?php echo '662-897-8747'; ?> when you have a question or to report an issue.</h3>
        <h5>I'm here to help <em>whenever</em> you need assistance.</h5>
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
                    // Get the image, big_image, link, and caption fields
                    $image = $pods->field('image');
                    $big_image = $pods->field('big_image');
                    $link = $pods->field('link');
                    $caption = $pods->field('caption');

                    // Ensure each field is available before using it
                    if ($image && is_array($image)) {
                        $image_url = esc_url($image['guid']);
                        $image_alt = esc_attr($image['post_title']);
                    } else {
                        $image_url = '';
                        $image_alt = '';
                    }

                    if ($big_image && is_array($big_image)) {
                        $big_image_url = esc_url($big_image['guid']);
                    } else {
                        $big_image_url = '';
                    }

                    if ($link) {
                        $link_url = esc_url($link);
                    } else {
                        $link_url = '';
                    }

                    if ($caption) {
                        $caption_text = esc_html($caption);
                    } else {
                        $caption_text = '';
                    }

                    // Output the portfolio item only if image is available
                    if ($image_url) {
                        echo '<figure class="portfolio-item">';
                        echo '<img src="' . $image_url . '" alt="' . $image_alt . '" class="portfolio-thumb" data-big-image="' . $big_image_url . '" data-link="' . $link_url . '" data-caption="' . $caption_text . '">';
                        echo '</figure>';
                    }
                }
                
                echo '</div>';
            } else {
                echo '<p>No portfolio items found.</p>';
            }
            ?>
            <!-- Fullscreen Overlay -->
            <div id="fullscreen-overlay" style="display:none;">
                <img id="fullscreen-image" src="" alt="">
                <div id="fullscreen-caption"></div>
                <button id="view-website-button" style="margin-top: 20px;">View Website</button>
            </div>





        <?php
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
				<?php echo do_shortcode('[juchheim_stripe_forms]'); ?>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
?>

<script>
document.querySelector('.btn-hosting').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('#payments').scrollIntoView({
        behavior: 'smooth'
    });
});

document.querySelector('.btn-portfolio').addEventListener('click', function(e) {
    e.preventDefault();
    document.querySelector('#portfolio').scrollIntoView({
        behavior: 'smooth'
    });
});
</script>

