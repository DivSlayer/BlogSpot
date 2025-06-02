<?php
/**
 * The template for displaying category archives
 */

get_header(); ?>

<style>
    body {
        overflow-y: auto;
    }
</style>

<section class="posts-holder">
    <header>
        <h1><?php single_cat_title('دسته بندی: '); ?></h1>
        <?php if (category_description()) : ?>
            <div class="category-description">
                <?php echo category_description(); ?>
            </div>
        <?php endif; ?>
    </header>

    <ul class="list">
        <?php
        // Get the current category
        $current_cat = get_queried_object();
        
        // Query both articles and podcasts in this category
        $args = array(
            'post_type' => array('article', 'podcast'),
            'posts_per_page' => 10,
            'paged' => get_query_var('paged') ? get_query_var('paged') : 1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'category',
                    'field' => 'term_id',
                    'terms' => $current_cat->term_id,
                ),
            ),
        );
        
        $category_query = new WP_Query($args);

        if ($category_query->have_posts()) :
            while ($category_query->have_posts()) : $category_query->the_post();
                $post_type = get_post_type();
                $post_type_class = $post_type === 'podcast' ? 'podcast' : 'article';
                ?>
                <li class="<?php echo $post_type_class; ?>">
                    <a href="<?php the_permalink(); ?>">
                        <div class="image" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');"></div>
                        <div class="details">
                            <h2 class="title"><?php the_title(); ?></h2>
                            <div class="meta">
                                <span class="date"><?php echo get_the_date(); ?></span>
                                <span class="type"><?php echo $post_type === 'podcast' ? 'پادکست' : 'مقاله'; ?></span>
                            </div>
                            <p><?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?></p>
                        </div>
                    </a>
                </li>
            <?php
            endwhile;

            // Pagination
            echo '<div class="pagination">';
            echo paginate_links(array(
                'total' => $category_query->max_num_pages,
                'current' => max(1, get_query_var('paged')),
                'prev_text' => 'قبلی',
                'next_text' => 'بعدی'
            ));
            echo '</div>';

            wp_reset_postdata();
        else :
            echo '<p class="no-posts">هیچ مطلبی در این دسته بندی یافت نشد.</p>';
        endif;
        ?>
    </ul>
</section>

<?php get_footer(); ?> 