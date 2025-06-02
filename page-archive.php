<?php
/**
 * Template Name: Archive Page
 * Description: A page template that displays both articles and podcasts
 */

get_header(); ?>

<div class="archive-container">
    <h1 class="archive-title">همه مطالب</h1>
    
    <div class="archive-filters">
        <button class="filter-btn active" data-filter="all">همه</button>
        <button class="filter-btn" data-filter="article">مقالات</button>
        <button class="filter-btn" data-filter="podcast">پادکست‌ها</button>
    </div>

    <div class="archive-grid">
        <?php
        $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
        $args = array(
            'post_type' => array('article', 'podcast'),
            'posts_per_page' => 12,
            'paged' => $paged,
            'orderby' => 'date',
            'order' => 'DESC'
        );

        $query = new WP_Query($args);

        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $post_type = get_post_type();
                $post_type_class = $post_type === 'podcast' ? 'podcast' : 'article';
                ?>
                <div class="archive-item <?php echo $post_type_class; ?>">
                    <div class="item-image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('medium'); ?>
                        <?php endif; ?>
                    </div>
                    <div class="item-content">
                        <div class="item-meta">
                            <span class="date"><?php echo get_the_date('M d, Y'); ?></span>
                            <span class="type"><?php echo $post_type === 'podcast' ? 'پادکست' : 'مقاله'; ?></span>
                        </div>
                        <h2 class="item-title">
                            <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                        </h2>
                        <div class="item-excerpt">
                            <?php the_excerpt(); ?>
                        </div>
                    </div>
                </div>
            <?php
            endwhile;

            // Pagination
            echo '<div class="pagination">';
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'prev_text' => 'قبلی',
                'next_text' => 'بعدی'
            ));
            echo '</div>';

            wp_reset_postdata();
        else :
            echo '<p class="no-posts">هیچ مطلبی یافت نشد.</p>';
        endif;
        ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterButtons = document.querySelectorAll('.filter-btn');
    const archiveItems = document.querySelectorAll('.archive-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove active class from all buttons
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Add active class to clicked button
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');

            archiveItems.forEach(item => {
                if (filter === 'all' || item.classList.contains(filter)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });
});
</script>

<?php get_footer(); ?> 