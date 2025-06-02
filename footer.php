</div><!-- .sections-holder -->
<?php wp_footer(); ?>
<div class="search-page">
    <form action="<?php echo esc_url(home_url('/')); ?>" method="get" class="search-form">
        <div class="c-input-group">
            <input type="text" name="s" placeholder="نام مقاله، پادکست و..." class="input-field" required
                autocomplete="off" value="<?php echo get_search_query(); ?>" />
        </div>
        <button type="submit" class="c-btn curve">جستجو</button>
    </form>

    <button class="c-btn curve dark close-btn" id="page-close"><i class="fas fa-times"></i></button>
</div>
<script>
    window.onload = () => {
        const mobileNavBtn = document.getElementById('mobile-nav-btn');
        const mobileNav = document.getElementById('mobile-nav');
        const closeBtn = document.getElementById('mobile-nav-close');
        const closePageBtn = document.getElementById('page-close');
        const searchBtn = document.querySelector('.search-btn');
        const searchPage = document.querySelector('.search-page');
        const innerWidth = window.innerWidth;

        if (innerWidth <= 750) {
            mobileNavBtn.style.display = "unset";
        }

        mobileNavBtn.onclick = () => {
            mobileNav.classList.toggle('active');
        }

        closeBtn.onclick = () => {
            mobileNav.classList.toggle('active');
        }

        // Search page functionality
        searchBtn.onclick = () => {
            searchPage.classList.add('active');
        }

        closeBtn.onclick = () => {
            searchPage.classList.remove('active');
            document.body.style.overflow = 'auto';
            document.body.style.pointerEvents = 'auto';
        }

        // Close search page when clicking outside the search form
        closePageBtn.addEventListener('click', (e) => {
console.log('hi');

            searchPage.classList.remove('active');
        });
    }
</script>
</body>

</html>