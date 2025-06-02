<?php
$post_type = get_post_type();
$description = '';

if ($post_type === 'article') {
    $description = get_post_meta(get_the_ID(), '_article_description', true);
} elseif ($post_type === 'podcast') {
    $description = get_post_meta(get_the_ID(), '_podcast_description', true);
}

if (empty($description)) {
    $description = get_the_excerpt();
}
?>
<li>
    <a href="<?php the_permalink(); ?>">
        <div class="image" style="background-image: url('<?php echo get_the_post_thumbnail_url(); ?>');"></div>
        <div class="details">
            <h2 class="title"><?php the_title(); ?></h2>
            <p><?php echo wp_trim_words($description, 50, '...'); ?></p>
        </div>
    </a>
</li> 