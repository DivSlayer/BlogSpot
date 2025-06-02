<?php
$ad = blogspot_get_random_ad();
if ($ad) : ?>
    <div class="card-small ads">
        <div class="label">تبلیغات</div>
        <span class="title"><?php echo esc_html($ad['title']); ?></span>
        <div class="text"><?php echo esc_html($ad['text']); ?></div>
        <a href="<?php echo esc_url($ad['link']); ?>" class="c-btn underlined"><?php echo esc_html($ad['link_text']); ?></a>
    </div>
<?php endif; ?> 