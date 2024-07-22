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


<!--
        <h3>Know that you can reach me directly at <?php // echo '662-897-8747'; ?> when you have a question or to report an issue.</h3>
        <h5>I'm here to help <em>whenever</em> you need assistance.</h5> -->
		<!-- <a href="#"><img class="down-arrow" src="/wp-content/uploads/2024/06/down_arrow.png" /></a> -->


        <div class="portfolio-container">
            <h1>Portfolio</h1>
            <div class="portfolio-gallery">
                <?php
                if ( function_exists( 'pods' ) ) {
                $params = array(
                    'limit' => -1,
                    'orderby' => 'menu_order ASC', // Order by menu order in ascending order
                    'where' => 'post_status="publish"'
                );
                $portfolio = pods('portfolio', $params);

                if ($portfolio->total() > 0) {
                    while ($portfolio->fetch()) {
                    $image = $portfolio->field('image');
                    $big_images = $portfolio->field('big_image');
                    $caption = $portfolio->field('caption');
                    $link = $portfolio->field('link');
                    ?>
                    <div class="portfolio-entry" data-big-images='<?php echo json_encode($big_images); ?>' data-caption="<?php echo esc_attr($caption); ?>" data-link="<?php echo esc_url($link); ?>">
                        <img src="<?php echo esc_url($image['guid']); ?>" alt="">
                    </div>
                    <?php
                    }
                } else {
                    echo '<p>No portfolio items found.</p>';
                }
                } else {
                echo '<p>Pods is not activated or not available.</p>';
                }
                ?>
            </div>
            </div>

            <div id="image-modal" class="modal">
            <span class="close">&times;</span>
            <img class="modal-content" id="modal-image">
            <div class="navigation">
                <span class="prev">&laquo;</span>
                <span class="next">&raquo;</span>
            </div>
            <div class="caption" id="modal-caption"></div>
            <a id="view-website" class="view-website-btn" href="#" target="_blank">View Website</a>
            </div>





        <script>

document.addEventListener('DOMContentLoaded', function() {
  const portfolioGallery = document.querySelector('.portfolio-gallery');
  const modal = document.getElementById('image-modal');
  const modalImage = document.getElementById('modal-image');
  const modalCaption = document.getElementById('modal-caption');
  const viewWebsite = document.getElementById('view-website');
  const closeBtn = document.querySelector('.close');
  const prevBtn = document.querySelector('.prev');
  const nextBtn = document.querySelector('.next');

  const portfolioItems = Array.from(document.querySelectorAll('.portfolio-entry'));

  let currentIndex = 0;
  let currentImageIndex = 0;
  let currentBigImages = [];

  function openModal(index) {
    currentIndex = index;
    currentBigImages = JSON.parse(portfolioItems[index].dataset.bigImages);
    currentImageIndex = 0;
    displayImage();
    checkArrows();
    modal.style.display = 'flex';
  }

  function displayImage() {
    if (currentBigImages[currentImageIndex] && currentBigImages[currentImageIndex].guid) {
      modalImage.src = currentBigImages[currentImageIndex].guid;
    } else {
      modalImage.src = currentBigImages[currentImageIndex];
    }
    modalCaption.textContent = portfolioItems[currentIndex].dataset.caption;
    viewWebsite.href = portfolioItems[currentIndex].dataset.link;
  }

  function navigate(direction) {
    currentImageIndex += direction;
    if (currentImageIndex < 0) {
      currentImageIndex = currentBigImages.length - 1;
    } else if (currentImageIndex >= currentBigImages.length) {
      currentImageIndex = 0;
    }
    displayImage();
  }

  function checkArrows() {
    if (currentBigImages.length <= 1) {
      prevBtn.style.display = 'none';
      nextBtn.style.display = 'none';
    } else {
      prevBtn.style.display = 'block';
      nextBtn.style.display = 'block';
    }
  }

  portfolioGallery.addEventListener('click', function(e) {
    if (e.target.tagName === 'IMG') {
      const index = portfolioItems.indexOf(e.target.parentElement);
      openModal(index);
    }
  });

  closeBtn.addEventListener('click', function() {
    modal.style.display = 'none';
  });

  prevBtn.addEventListener('click', function() {
    navigate(-1);
  });

  nextBtn.addEventListener('click', function() {
    navigate(1);
  });

  window.addEventListener('click', function(e) {
    if (e.target === modal) {
      modal.style.display = 'none';
    }
  });
});






        </script>
        

            <!-- github -->
            <div class="github-section-container">
                <div class="github-section">
                    <h2>My GitHub</h2>
                    <a href="https://github.com/juchheim" target="_blank">
                        <img src="/wp-content/uploads/2024/07/github-mark-white_small.png" alt="GitHub" /><br />
                    </a>
                    <div id="repo-list"></div>
                </div>
            </div>


            <script>
            document.addEventListener('DOMContentLoaded', function() {
                fetch('https://api.github.com/users/juchheim/repos')
                    .then(response => response.json())
                    .then(data => {
                        let repoList = document.getElementById('repo-list');
                        data.forEach((repo, index) => {
                            let listItem = document.createElement('span');
                            listItem.innerHTML = `<a href="${repo.html_url}" target="_blank">${repo.name}</a>`;
                            repoList.appendChild(listItem);
                            if (index < data.length - 1) {
                                let comma = document.createElement('span');
                                comma.innerHTML = ', ';
                                comma.style.color = 'white';
                                repoList.appendChild(comma);
                            }
                        });
                    })
                    .catch(error => console.error('Error fetching repositories:', error));
            });



            </script>


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

