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
                <div class="content active" id="web-hosting">
					<form id="web-hosting-form" action="#" method="post">
                        <label for="name">Name:</label>
                        <input type="text" id="name" name="name" required>
                        
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required>
                        
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                        
                        <label for="plan">Choose your plan:</label>
                        <select id="plan" name="plan">
                            <option value="monthly">Monthly - $25</option>
                            <option value="annually">Annually - $250</option>
                        </select>
                        
                        <button type="submit">Submit</button>
                    </form>
                </div>
                <div class="content" id="design-development">
					<form id="development-form" action="#" method="post">
                        <label for="dev-name">Name:</label>
                        <input type="text" id="dev-name" name="name" required>
                        
                        <label for="dev-email">Email:</label>
                        <input type="email" id="dev-email" name="email" required>
                        
                        <label for="dev-password">Password:</label>
                        <input type="password" id="dev-password" name="password" required>
                        
                        <label for="dev-plan">Choose your plan:</label>
                        <select id="dev-plan" name="plan">
						<option value="10-page-no-sub">10-page (no sub pages) - $1000</option>
						<option value="10-page-with-sub">10-page (with sub pages) - $1500</option>
                        </select>
                        
                        <button type="submit">Submit</button>
                    </form>
                </div>
                <div class="content" id="custom">
					<form id="custom-form" action="#" method="post">

						<p class="custom-note">Choose this option if we've agreed to a price based on your unique needs. Interested in a quote? <a href="mailto:juchheim@gmail.com">Email me.</a></p>

                        <label for="custom-name">Name:</label>
                        <input type="text" id="custom-name" name="name" required>
                        
                        <label for="custom-email">Email:</label>
                        <input type="email" id="custom-email" name="email" required>
                        
                        <label for="custom-password">Password:</label>
                        <input type="password" id="custom-password" name="password" required>
                        
                        <label for="custom-price">Price:</label>
                        <input type="number" id="custom-price" name="price" required>
                        
                        <button type="submit">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
</div>

<?php
get_footer();
?>
<script src="<?php echo get_template_directory_uri(); ?>/js/script.js"></script>
<script>
    console.log('Inline script executed');
    const scriptTag = document.querySelector('script[src*="script.js"]');
    if (scriptTag) {
        console.log('script.js is enqueued:', scriptTag.src);
    } else {
        console.error('script.js is not enqueued');
    }
</script>