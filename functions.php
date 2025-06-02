<?php
// Register Custom Post Types
function blogspot_register_post_types()
{
    // Articles Post Type
    register_post_type('article', array(
        'labels' => array(
            'name' => __('Articles', 'blogspot'),
            'singular_name' => __('Article', 'blogspot'),
            'add_new' => __('Add New Article', 'blogspot'),
            'add_new_item' => __('Add New Article', 'blogspot'),
            'edit_item' => __('Edit Article', 'blogspot'),
            'all_items' => __('All Articles', 'blogspot'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-media-text',
        'supports' => array('title', 'thumbnail', 'editor', 'excerpt'),
        'rewrite' => array(
            'slug' => 'articles',
            'with_front' => false,
            'pages' => true,
            'feeds' => true,
        ),
        'show_in_rest' => true,
    ));

    // Podcasts Post Type
    register_post_type('podcast', array(
        'labels' => array(
            'name' => __('Podcasts', 'blogspot'),
            'singular_name' => __('Podcast', 'blogspot'),
            'add_new' => __('Add New Podcast', 'blogspot'),
            'add_new_item' => __('Add New Podcast', 'blogspot'),
            'edit_item' => __('Edit Podcast', 'blogspot'),
            'all_items' => __('All Podcasts', 'blogspot'),
        ),
        'public' => true,
        'has_archive' => true,
        'menu_icon' => 'dashicons-microphone',
        'supports' => array('title', 'thumbnail', 'editor', 'excerpt'),
        'rewrite' => array(
            'slug' => 'podcasts',
            'with_front' => false,
            'pages' => true,
            'feeds' => true,
        ),
        'show_in_rest' => true,
        'taxonomies' => array('category', 'post_tag'),
    ));

    // Ads Post Type
    register_post_type('ad', array(
        'labels' => array(
            'name' => __('Ads', 'blogspot'),
            'singular_name' => __('Ad', 'blogspot'),
            'add_new' => __('Add New Ad', 'blogspot'),
            'add_new_item' => __('Add New Ad', 'blogspot'),
            'edit_item' => __('Edit Ad', 'blogspot'),
            'all_items' => __('All Ads', 'blogspot'),
        ),
        'public' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array('title'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'blogspot_register_post_types');

// Register Custom Meta Boxes
function blogspot_add_meta_boxes()
{
    // Article Meta Box
    add_meta_box(
        'article_details',
        __('Article Details', 'blogspot'),
        'blogspot_article_meta_box',
        'article',
        'normal',
        'high'
    );

    // Podcast Meta Box
    add_meta_box(
        'podcast_details',
        __('Podcast Details', 'blogspot'),
        'blogspot_podcast_meta_box',
        'podcast',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'blogspot_add_meta_boxes');

// Article Meta Box Callback
function blogspot_article_meta_box($post)
{
    wp_nonce_field('blogspot_article_meta_box', 'blogspot_article_meta_box_nonce');

    $description = get_post_meta($post->ID, '_article_description', true);
    $full_text = get_post_meta($post->ID, '_article_full_text', true);
    ?>
    <div class="meta-box-content">
        <p>
            <label for="article_description"><?php _e('Description', 'blogspot'); ?></label>
            <textarea id="article_description" name="article_description" rows="3"
                style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <p>
            <label for="article_full_text"><?php _e('Full Text', 'blogspot'); ?></label>
            <?php
            wp_editor(
                $full_text,
                'article_full_text',
                array(
                    'textarea_name' => 'article_full_text',
                    'media_buttons' => true,
                    'textarea_rows' => 10,
                )
            );
            ?>
        </p>
    </div>
    <?php
}

// Podcast Meta Box Callback
function blogspot_podcast_meta_box($post)
{
    wp_nonce_field('blogspot_podcast_meta_box', 'blogspot_podcast_meta_box_nonce');

    $description = get_post_meta($post->ID, '_podcast_description', true);
    $audio_url = get_post_meta($post->ID, '_podcast_audio', true);
    $host_id = get_post_meta($post->ID, '_podcast_host_id', true);
    
    // Get all users with author role or higher
    $users = get_users(array(
        'role__in' => array('author', 'editor', 'administrator'),
        'orderby' => 'display_name'
    ));
    ?>
    <div class="meta-box-content">
        <p>
            <label for="podcast_description"><?php _e('Description', 'blogspot'); ?></label>
            <textarea id="podcast_description" name="podcast_description" rows="3"
                style="width: 100%;"><?php echo esc_textarea($description); ?></textarea>
        </p>
        <p>
            <label for="podcast_audio"><?php _e('Audio File', 'blogspot'); ?></label>
            <input type="text" id="podcast_audio" name="podcast_audio" value="<?php echo esc_url($audio_url); ?>"
                style="width: 80%;" />
            <button type="button" class="button" id="upload_audio_button"><?php _e('Upload Audio', 'blogspot'); ?></button>
        </p>
        <p>
            <label for="podcast_host_id"><?php _e('Host', 'blogspot'); ?></label>
            <select id="podcast_host_id" name="podcast_host_id" style="width: 100%;">
                <option value=""><?php _e('Select a host', 'blogspot'); ?></option>
                <?php foreach ($users as $user) : ?>
                    <option value="<?php echo esc_attr($user->ID); ?>" <?php selected($host_id, $user->ID); ?>>
                        <?php echo esc_html($user->display_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>
    </div>
    <?php
}

// Save Meta Box Data
function blogspot_save_meta_box_data($post_id)
{
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check if the user has permissions to edit this post
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Podcast Meta Box
    if (isset($_POST['blogspot_podcast_meta_box_nonce']) && 
        wp_verify_nonce($_POST['blogspot_podcast_meta_box_nonce'], 'blogspot_podcast_meta_box')) {
        
        if (isset($_POST['podcast_description'])) {
            update_post_meta($post_id, '_podcast_description', sanitize_textarea_field($_POST['podcast_description']));
        }
        if (isset($_POST['podcast_audio'])) {
            update_post_meta($post_id, '_podcast_audio', esc_url_raw($_POST['podcast_audio']));
        }
        if (isset($_POST['podcast_host_id'])) {
            update_post_meta($post_id, '_podcast_host_id', absint($_POST['podcast_host_id']));
        }
    }
}
add_action('save_post', 'blogspot_save_meta_box_data');

// Add Media Uploader Script
function blogspot_admin_scripts()
{
    wp_enqueue_media();
    wp_enqueue_script('blogspot-admin', get_template_directory_uri() . '/js/admin.js', array('jquery'), '1.0.0', true);
}
add_action('admin_enqueue_scripts', 'blogspot_admin_scripts');

// Register navigation menus
register_nav_menus(array(
    'primary' => __('Primary Menu', 'blogspot')
));

// Add theme support for post thumbnails
add_theme_support('post-thumbnails');
add_theme_support('automatic-feed-links');
add_theme_support('title-tag');

// Enqueue scripts and styles
function blogspot_enqueue_scripts()
{
    // Font Awesome
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css', array(), '6.0.0');

    // Bootstrap CSS
    wp_enqueue_style('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.rtl.min.css', array(), '5.3.2');
    
    wp_enqueue_style('animate', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css', array(), '4.1.1');

    // Theme Styles
    wp_enqueue_style('blogspot-style', get_stylesheet_uri(), array(), '1.0.0');

    // Only enqueue these if the files exist
    if (file_exists(get_template_directory() . '/styles/style.css')) {
        wp_enqueue_style('blogspot-main', get_template_directory_uri() . '/styles/style.css', array(), '1.0.0');
    }
    if (file_exists(get_template_directory() . '/styles/podcast.css')) {
        wp_enqueue_style('blogspot-podcast', get_template_directory_uri() . '/styles/podcast.css', array(), '1.0.0');
    }
    if (file_exists(get_template_directory() . '/styles/archive.css')) {
        wp_enqueue_style('blogspot-archive', get_template_directory_uri() . '/styles/archive.css', array(), '1.0.0');
    }
    if (file_exists(get_template_directory() . '/assets/css/single-templates.css')) {
        wp_enqueue_style('blogspot-single-templates', get_template_directory_uri() . '/assets/css/single-templates.css', array(), '1.0.0');
    }

    // Only enqueue fonts if they exist
    if (file_exists(get_template_directory() . '/fonts/Dana/dana.css')) {
        wp_enqueue_style('dana-font', get_template_directory_uri() . '/fonts/Dana/dana.css', array(), '1.0.0');
    }
    if (file_exists(get_template_directory() . '/fonts/Vazir/vazir-fonts.css')) {
        wp_enqueue_style('vazir-font', get_template_directory_uri() . '/fonts/Vazir/vazir-fonts.css', array(), '1.0.0');
    }
    if (file_exists(get_template_directory() . '/fonts/Kalameh/kalameh.css')) {
        wp_enqueue_style('kalameh-font', get_template_directory_uri() . '/fonts/Kalameh/kalameh.css', array(), '1.0.0');
    }

    // Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js', array('jquery'), '5.3.2', true);
}
add_action('wp_enqueue_scripts', 'blogspot_enqueue_scripts');

// Add Meta Box for Ad Details
function blogspot_add_ad_meta_box()
{
    add_meta_box(
        'ad_details',
        __('Ad Details', 'blogspot'),
        'blogspot_ad_meta_box',
        'ad',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'blogspot_add_ad_meta_box');

// Ad Meta Box Callback
function blogspot_ad_meta_box($post)
{
    wp_nonce_field('blogspot_ad_meta_box', 'blogspot_ad_meta_box_nonce');

    $ad_text = get_post_meta($post->ID, '_ad_text', true);
    $ad_link = get_post_meta($post->ID, '_ad_link', true);
    $ad_link_text = get_post_meta($post->ID, '_ad_link_text', true);
    ?>
    <div class="meta-box-content">
        <p>
            <label for="ad_text"><?php _e('Ad Text', 'blogspot'); ?></label>
            <textarea id="ad_text" name="ad_text" rows="3"
                style="width: 100%;"><?php echo esc_textarea($ad_text); ?></textarea>
        </p>
        <p>
            <label for="ad_link"><?php _e('Link URL', 'blogspot'); ?></label>
            <input type="url" id="ad_link" name="ad_link" value="<?php echo esc_url($ad_link); ?>" style="width: 100%;" />
        </p>
        <p>
            <label for="ad_link_text"><?php _e('Link Text', 'blogspot'); ?></label>
            <input type="text" id="ad_link_text" name="ad_link_text" value="<?php echo esc_attr($ad_link_text); ?>"
                style="width: 100%;" />
        </p>
    </div>
    <?php
}

// Save Ad Meta Box Data
function blogspot_save_ad_meta_box_data($post_id)
{
    if (!isset($_POST['blogspot_ad_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['blogspot_ad_meta_box_nonce'], 'blogspot_ad_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['ad_text'])) {
        update_post_meta($post_id, '_ad_text', sanitize_textarea_field($_POST['ad_text']));
    }
    if (isset($_POST['ad_link'])) {
        update_post_meta($post_id, '_ad_link', esc_url_raw($_POST['ad_link']));
    }
    if (isset($_POST['ad_link_text'])) {
        update_post_meta($post_id, '_ad_link_text', sanitize_text_field($_POST['ad_link_text']));
    }
}
add_action('save_post', 'blogspot_save_ad_meta_box_data');

// Function to get random ad
function blogspot_get_random_ad()
{
    $args = array(
        'post_type' => 'ad',
        'posts_per_page' => 1,
        'orderby' => 'rand',
        'post_status' => 'publish'
    );

    $ad_query = new WP_Query($args);

    if ($ad_query->have_posts()) {
        $ad_query->the_post();
        $ad = array(
            'title' => get_the_title(),
            'text' => get_post_meta(get_the_ID(), '_ad_text', true),
            'link' => get_post_meta(get_the_ID(), '_ad_link', true),
            'link_text' => get_post_meta(get_the_ID(), '_ad_link_text', true)
        );
        wp_reset_postdata();
        return $ad;
    }

    return false;
}

// Handle Podcast Join Form Submission
function blogspot_handle_podcast_join()
{
    if (!isset($_POST['podcast_join_nonce']) || !wp_verify_nonce($_POST['podcast_join_nonce'], 'podcast_join_nonce')) {
        return;
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    if (empty($name) || empty($email) || !is_email($email)) {
        wp_die('لطفا اطلاعات معتبر وارد کنید.');
    }

    // Create a new subscriber post
    $subscriber = array(
        'post_title' => $name,
        'post_type' => 'subscriber',
        'post_status' => 'publish'
    );

    $subscriber_id = wp_insert_post($subscriber);

    if (!is_wp_error($subscriber_id)) {
        // Save email as post meta
        update_post_meta($subscriber_id, '_subscriber_email', $email);

        // Send confirmation email
        $to = $email;
        $subject = 'خوش آمدید به پادکست ما';
        $message = "سلام {$name},\n\n";
        $message .= "از پیوستن شما به پادکست ما متشکریم. به زودی با شما در تماس خواهیم بود.\n\n";
        $message .= "با احترام,\n";
        $message .= get_bloginfo('name');

        wp_mail($to, $subject, $message);

        // Redirect with success message
        wp_redirect(add_query_arg('status', 'success', wp_get_referer()));
        exit;
    }

    // Redirect with error message
    wp_redirect(add_query_arg('status', 'error', wp_get_referer()));
    exit;
}
add_action('admin_post_podcast_join', 'blogspot_handle_podcast_join');
add_action('admin_post_nopriv_podcast_join', 'blogspot_handle_podcast_join');

// Register Subscriber Post Type
function blogspot_register_subscriber_post_type()
{
    register_post_type('subscriber', array(
        'labels' => array(
            'name' => __('Subscribers', 'blogspot'),
            'singular_name' => __('Subscriber', 'blogspot'),
        ),
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => true,
        ),
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-groups',
        'supports' => array('title'),
    ));
}
add_action('init', 'blogspot_register_subscriber_post_type');

// Add JavaScript for FAQ button
function blogspot_enqueue_podcast_scripts()
{
    if (is_page_template('page-podcast-join.php')) {
        wp_enqueue_script('blogspot-podcast', get_template_directory_uri() . '/js/podcast.js', array('jquery'), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'blogspot_enqueue_podcast_scripts');

// Handle Contact Form Submission
function blogspot_handle_contact_form()
{
    if (!isset($_POST['contact_nonce']) || !wp_verify_nonce($_POST['contact_nonce'], 'contact_form_nonce')) {
        return;
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $message = sanitize_textarea_field($_POST['message']);

    if (empty($name) || empty($email) || empty($message) || !is_email($email)) {
        wp_redirect(add_query_arg('status', 'error', wp_get_referer()));
        exit;
    }

    // Create a new contact message post
    $contact = array(
        'post_title' => sprintf('پیام از %s', $name),
        'post_type' => 'contact_message',
        'post_status' => 'private'
    );

    $contact_id = wp_insert_post($contact);

    if (!is_wp_error($contact_id)) {
        // Save contact details as post meta
        update_post_meta($contact_id, '_contact_email', $email);
        update_post_meta($contact_id, '_contact_message', $message);

        // Send notification email to admin
        $admin_email = get_option('admin_email');
        $subject = sprintf('پیام جدید از %s', $name);
        $admin_message = sprintf(
            "یک پیام جدید دریافت شد:\n\nنام: %s\nایمیل: %s\n\nپیام:\n%s",
            $name,
            $email,
            $message
        );

        wp_mail($admin_email, $subject, $admin_message);

        // Send confirmation email to user
        $user_subject = 'پیام شما دریافت شد';
        $user_message = sprintf(
            "سلام %s,\n\nاز ارسال پیام شما متشکریم. به زودی با شما تماس خواهیم گرفت.\n\nپیام شما:\n%s\n\nبا احترام,\n%s",
            $name,
            $message,
            get_bloginfo('name')
        );

        wp_mail($email, $user_subject, $user_message);

        // Redirect with success message
        wp_redirect(add_query_arg('status', 'success', wp_get_referer()));
        exit;
    }

    // Redirect with error message
    wp_redirect(add_query_arg('status', 'error', wp_get_referer()));
    exit;
}
add_action('admin_post_handle_contact_form', 'blogspot_handle_contact_form');
add_action('admin_post_nopriv_handle_contact_form', 'blogspot_handle_contact_form');

// Register Contact Message Post Type
function blogspot_register_contact_message_post_type()
{
    register_post_type('contact_message', array(
        'labels' => array(
            'name' => __('Contact Messages', 'blogspot'),
            'singular_name' => __('Contact Message', 'blogspot'),
        ),
        'public' => false,
        'show_ui' => true,
        'capability_type' => 'post',
        'capabilities' => array(
            'create_posts' => false,
        ),
        'map_meta_cap' => true,
        'menu_icon' => 'dashicons-email',
        'supports' => array('title'),
    ));
}
add_action('init', 'blogspot_register_contact_message_post_type');

// Ensure contact page template is properly loaded
function blogspot_template_include($template)
{
    global $post;

    // Handle archive pages (category, tag, date archives)
    if (is_archive()) {
        $archive_template = get_template_directory() . '/archive.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }

    // Handle blog posts page
    if (is_home() && !is_front_page()) {
        $archive_template = get_template_directory() . '/archive.php';
        if (file_exists($archive_template)) {
            return $archive_template;
        }
    }

    // Handle front page
    if (is_front_page()) {
        $index_template = get_template_directory() . '/index.php';
        if (file_exists($index_template)) {
            return $index_template;
        }
    }

    // Handle regular pages
    if (is_page() && $post) {
        $page_template = get_post_meta($post->ID, '_wp_page_template', true);

        // If this is the contact page
        if ($post->post_name === 'contact-us' || $page_template === 'page-contact.php') {
            $contact_template = get_template_directory() . '/page-contact.php';
            if (file_exists($contact_template)) {
                return $contact_template;
            }
        }

        // If this is the podcast join page
        if ($post->post_name === 'podcast-join' || $page_template === 'page-podcast-join.php') {
            $podcast_join_template = get_template_directory() . '/page-podcast-join.php';
            if (file_exists($podcast_join_template)) {
                return $podcast_join_template;
            }
        }

        // For all other pages, use page.php
        $default_template = get_template_directory() . '/page.php';
        if (file_exists($default_template)) {
            return $default_template;
        }
    }

    return $template;
}
add_filter('template_include', 'blogspot_template_include');

// Flush permalinks on theme activation
function blogspot_theme_activation()
{
    // Register post types
    blogspot_register_post_types();
    blogspot_register_contact_message_post_type();

    // Flush permalinks
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'blogspot_theme_activation');

// Add Contact Message Meta Box
function blogspot_add_contact_message_meta_box()
{
    add_meta_box(
        'contact_message_details',
        __('Message Details', 'blogspot'),
        'blogspot_contact_message_meta_box',
        'contact_message',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'blogspot_add_contact_message_meta_box');

// Contact Message Meta Box Callback
function blogspot_contact_message_meta_box($post)
{
    $email = get_post_meta($post->ID, '_contact_email', true);
    $message = get_post_meta($post->ID, '_contact_message', true);
    ?>
    <div class="meta-box-content">
        <p>
            <strong><?php _e('Email:', 'blogspot'); ?></strong><br>
            <?php echo esc_html($email); ?>
        </p>
        <p>
            <strong><?php _e('Message:', 'blogspot'); ?></strong><br>
            <?php echo nl2br(esc_html($message)); ?>
        </p>
    </div>
    <?php
}

// Create default articles on theme activation
function blogspot_create_default_articles()
{
    // Check if default articles already exist
    $existing_articles = get_posts(array(
        'post_type' => 'article',
        'posts_per_page' => 1,
        'meta_key' => '_is_default_article',
        'meta_value' => '1'
    ));

    if (!empty($existing_articles)) {
        return; // Default articles already exist
    }

    $default_articles = array(
        array(
            'title' => 'به وبلاگ ما خوش آمدید',
            'content' => 'به وبلاگ ما خوش آمدید! این اولین مقاله ماست که در آن افکار و ایده‌های خود را با شما به اشتراک می‌گذاریم. امیدواریم از خواندن محتوای ما لذت ببرید و برای شما مفید باشد.',
            'description' => 'معرفی وبلاگ ما و آنچه می‌توانید از محتوای ما انتظار داشته باشید.',
            'full_text' => 'به وبلاگ ما خوش آمدید! این اولین مقاله ماست که در آن افکار و ایده‌های خود را با شما به اشتراک می‌گذاریم. امیدواریم از خواندن محتوای ما لذت ببرید و برای شما مفید باشد. تیم ما متعهد به ارائه بهترین محتوا برای شماست و موضوعات مختلفی که برای شما مهم است را پوشش می‌دهد.'
        ),
        array(
            'title' => 'شروع کار با وردپرس',
            'content' => 'وردپرس یک سیستم مدیریت محتوای قدرتمند است که ایجاد و مدیریت وب‌سایت شما را آسان می‌کند. در این مقاله، اصول اولیه شروع کار با وردپرس را بررسی خواهیم کرد.',
            'description' => 'راهنمای مبتدیان برای وردپرس و ویژگی‌های اصلی آن.',
            'full_text' => 'وردپرس یک سیستم مدیریت محتوای قدرتمند است که ایجاد و مدیریت وب‌سایت شما را آسان می‌کند. در این مقاله، اصول اولیه شروع کار با وردپرس را بررسی خواهیم کرد. از نصب تا ایجاد اولین پست شما، همه چیزهایی که برای شروع نیاز دارید را پوشش خواهیم داد.'
        ),
        array(
            'title' => 'نکاتی برای نوشتن بهتر وبلاگ',
            'content' => 'نوشتن پست‌های جذاب وبلاگ هم هنر است و هم علم. در اینجا نکاتی برای کمک به بهبود مهارت‌های نوشتن وبلاگ و ایجاد محتوایی که با خوانندگان شما ارتباط برقرار می‌کند، ارائه می‌دهیم.',
            'description' => 'نکات و ترفندهای ضروری برای ایجاد محتوای جذاب وبلاگ.',
            'full_text' => 'نوشتن پست‌های جذاب وبلاگ هم هنر است و هم علم. در اینجا نکاتی برای کمک به بهبود مهارت‌های نوشتن وبلاگ و ایجاد محتوایی که با خوانندگان شما ارتباط برقرار می‌کند، ارائه می‌دهیم. ما موضوعاتی مانند یافتن صدای خود، ساختار محتوا و تعامل با مخاطبان را پوشش خواهیم داد.'
        )
    );

    foreach ($default_articles as $article) {
        $post_id = wp_insert_post(array(
            'post_title' => $article['title'],
            'post_content' => $article['content'],
            'post_status' => 'publish',
            'post_type' => 'article'
        ));

        if ($post_id) {
            // Add meta data
            update_post_meta($post_id, '_article_description', $article['description']);
            update_post_meta($post_id, '_article_full_text', $article['full_text']);
            update_post_meta($post_id, '_is_default_article', '1');

            // Set featured image
            $image_path = get_template_directory() . '/images/Zugpsitze_mountain.jpg';
            if (file_exists($image_path)) {
                $image_url = get_template_directory_uri() . '/images/Zugpsitze_mountain.jpg';
                $upload_dir = wp_upload_dir();
                $image_data = file_get_contents($image_url);
                $filename = 'Zugpsitze_mountain.jpg';

                if ($image_data) {
                    $file = $upload_dir['path'] . '/' . $filename;
                    file_put_contents($file, $image_data);

                    $wp_filetype = wp_check_filetype($filename, null);
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => sanitize_file_name($filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                }
            }
        }
    }
}
add_action('after_switch_theme', 'blogspot_create_default_articles');

// Create default podcasts on theme activation
function blogspot_create_default_podcasts()
{
    // Check if default podcasts already exist
    $existing_podcasts = get_posts(array(
        'post_type' => 'podcast',
        'posts_per_page' => 1,
        'meta_key' => '_is_default_podcast',
        'meta_value' => '1'
    ));

    if (!empty($existing_podcasts)) {
        return; // Default podcasts already exist
    }

    $default_podcasts = array(
        array(
            'title' => 'معرفی مجموعه پادکست ما',
            'content' => 'به مجموعه پادکست ما خوش آمدید! در این قسمت، خودمان را معرفی می‌کنیم و درباره آنچه می‌توانید از قسمت‌های آینده انتظار داشته باشید، صحبت می‌کنیم.',
            'description' => 'معرفی مجموعه پادکست ما و آنچه شنوندگان می‌توانند از قسمت‌های آینده انتظار داشته باشند.',
            'audio_url' => 'https://example.com/podcasts/intro.mp3',
            'category' => 'معرفی',
            'tags' => array('معرفی', 'شروع', 'خوش‌آمدگویی')
        ),
        array(
            'title' => 'آینده فناوری',
            'content' => 'در این قسمت، ما درباره فناوری‌های نوظهور و تأثیر بالقوه آنها بر زندگی روزمره ما بحث می‌کنیم. از هوش مصنوعی تا انرژی تجدیدپذیر، همه را پوشش می‌دهیم.',
            'description' => 'بررسی عمیق فناوری‌های نوظهور و تأثیر بالقوه آنها بر جامعه.',
            'audio_url' => 'https://example.com/podcasts/tech-future.mp3',
            'category' => 'فناوری',
            'tags' => array('فناوری', 'هوش مصنوعی', 'انرژی تجدیدپذیر')
        ),
        array(
            'title' => 'استراتژی‌های بازاریابی دیجیتال',
            'content' => 'در این قسمت درباره استراتژی‌های مؤثر بازاریابی دیجیتال بیاموزید. ما نکات و ترفندهایی برای رشد حضور آنلاین شما به اشتراک می‌گذاریم.',
            'description' => 'بینش‌های متخصص در مورد استراتژی‌های بازاریابی دیجیتال و رشد آنلاین.',
            'audio_url' => 'https://example.com/podcasts/digital-marketing.mp3',
            'category' => 'بازاریابی',
            'tags' => array('بازاریابی دیجیتال', 'استراتژی', 'رشد آنلاین')
        )
    );

    foreach ($default_podcasts as $podcast) {
        $post_id = wp_insert_post(array(
            'post_title' => $podcast['title'],
            'post_content' => $podcast['content'],
            'post_status' => 'publish',
            'post_type' => 'podcast'
        ));

        if ($post_id) {
            // Add meta data
            update_post_meta($post_id, '_podcast_description', $podcast['description']);
            update_post_meta($post_id, '_podcast_audio', $podcast['audio_url']);
            update_post_meta($post_id, '_is_default_podcast', '1');

            // Set category
            $category = get_category_by_slug(sanitize_title($podcast['category']));
            if (!$category) {
                $category_id = wp_create_category($podcast['category']);
            } else {
                $category_id = $category->term_id;
            }
            wp_set_post_categories($post_id, array($category_id));

            // Set tags
            wp_set_post_tags($post_id, $podcast['tags']);

            // Set featured image
            $image_path = get_template_directory() . '/images/Zugpsitze_mountain.jpg';
            if (file_exists($image_path)) {
                $image_url = get_template_directory_uri() . '/images/Zugpsitze_mountain.jpg';
                $upload_dir = wp_upload_dir();
                $image_data = file_get_contents($image_url);
                $filename = 'Zugpsitze_mountain.jpg';

                if ($image_data) {
                    $file = $upload_dir['path'] . '/' . $filename;
                    file_put_contents($file, $image_data);

                    $wp_filetype = wp_check_filetype($filename, null);
                    $attachment = array(
                        'post_mime_type' => $wp_filetype['type'],
                        'post_title' => sanitize_file_name($filename),
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attach_id = wp_insert_attachment($attachment, $file, $post_id);
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                }
            }
        }
    }
}
add_action('after_switch_theme', 'blogspot_create_default_podcasts');

// Create default ad on theme activation
function blogspot_create_default_ad()
{
    // Check if default ad already exists
    $existing_ad = get_posts(array(
        'post_type' => 'ad',
        'posts_per_page' => 1,
        'meta_key' => '_is_default_ad',
        'meta_value' => '1'
    ));

    if (!empty($existing_ad)) {
        return; // Default ad already exists
    }

    $default_ad = array(
        'title' => 'به وبلاگ ما خوش آمدید',
        'text' => 'از آخرین مقالات و پادکست‌های ما دیدن کنید.',
        'link' => home_url(),
        'link_text' => 'همین حالا ببینید'
    );

    $post_id = wp_insert_post(array(
        'post_title' => $default_ad['title'],
        'post_status' => 'publish',
        'post_type' => 'ad'
    ));

    if ($post_id) {
        // Add meta data
        update_post_meta($post_id, '_ad_text', $default_ad['text']);
        update_post_meta($post_id, '_ad_link', $default_ad['link']);
        update_post_meta($post_id, '_ad_link_text', $default_ad['link_text']);
        update_post_meta($post_id, '_is_default_ad', '1');
    }
}
add_action('after_switch_theme', 'blogspot_create_default_ad');

// Create default contact page on theme activation
function blogspot_create_default_contact_page()
{
    // Check if default contact page already exists
    $existing_page = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_key' => '_is_default_contact_page',
        'meta_value' => '1'
    ));

    if (!empty($existing_page)) {
        return; // Default contact page already exists
    }

    $contact_page = array(
        'post_title' => 'تماس با ما',
        'post_content' => 'برای ارتباط با ما می‌توانید از فرم زیر استفاده کنید.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'contact-us'
    );

    $page_id = wp_insert_post($contact_page);

    if ($page_id) {
        // Set the page template
        update_post_meta($page_id, '_wp_page_template', 'page-contact.php');
        // Mark as default contact page
        update_post_meta($page_id, '_is_default_contact_page', '1');
    }
}
add_action('after_switch_theme', 'blogspot_create_default_contact_page');

// Create default podcast page on theme activation
function blogspot_create_default_podcast_page()
{
    // Check if default podcast page already exists
    $existing_page = get_posts(array(
        'post_type' => 'page',
        'posts_per_page' => 1,
        'meta_key' => '_is_default_podcast_page',
        'meta_value' => '1'
    ));

    if (!empty($existing_page)) {
        return; // Default podcast page already exists
    }

    $podcast_page = array(
        'post_title' => 'پادکست',
        'post_content' => 'برای پیوستن به پادکست ما، لطفا فرم زیر را پر کنید.',
        'post_status' => 'publish',
        'post_type' => 'page',
        'post_name' => 'podcast'
    );

    $page_id = wp_insert_post($podcast_page);

    if ($page_id) {
        // Set the page template
        update_post_meta($page_id, '_wp_page_template', 'page-podcast-join.php');
        // Mark as default podcast page
        update_post_meta($page_id, '_is_default_podcast_page', '1');
    }
}
add_action('after_switch_theme', 'blogspot_create_default_podcast_page');

// Create default menu on theme activation
function blogspot_create_default_menu()
{
    // Check if menu already exists
    $menu_name = 'Default Menu';
    $menu_exists = wp_get_nav_menu_object($menu_name);

    if (!$menu_exists) {
        // Create the menu
        $menu_id = wp_create_nav_menu($menu_name);

        // Create menu items
        $menu_items = array(
            array(
                'title' => 'خانه',
                'url' => home_url(),
                'order' => 1
            ),
            array(
                'title' => 'مقالات',
                'url' => home_url('/articles/'),
                'order' => 2
            ),
            array(
                'title' => 'پادکست',
                'url' => home_url('/podcast/'),
                'order' => 3
            ),
            array(
                'title' => 'تماس با ما',
                'url' => home_url('/contact-us/'),
                'order' => 4
            ),
        );

        // Add menu items
        foreach ($menu_items as $item) {
            wp_update_nav_menu_item($menu_id, 0, array(
                'menu-item-title' => $item['title'],
                'menu-item-url' => $item['url'],
                'menu-item-status' => 'publish',
                'menu-item-type' => 'custom',
                'menu-item-position' => $item['order']
            ));
        }

        // Assign menu to primary location
        $locations = get_theme_mod('nav_menu_locations');
        $locations['primary'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);
    }
}
add_action('after_switch_theme', 'blogspot_create_default_menu');

// Custom Walker Class for Navigation Menu
class Blogspot_Nav_Walker extends Walker_Nav_Menu
{
    function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0)
    {
        $classes = empty($item->classes) ? array() : (array) $item->classes;

        // Add active class if current page matches menu item
        if (in_array('current-menu-item', $classes)) {
            $output .= '<li class="' . implode(' ', $classes) . '"><a href="' . $item->url . '" class="active">' . $item->title . '</a>';
        } else {
            $output .= '<li class="' . implode(' ', $classes) . '"><a href="' . $item->url . '">' . $item->title . '</a>';
        }
    }
}

// Add rewrite rules for archive pagination
function custom_rewrite_rules() {
    // Add rules for article archives
    add_rewrite_rule(
        '^articles/page/([0-9]+)/?$',
        'index.php?post_type=article&paged=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^articles/?$',
        'index.php?post_type=article',
        'top'
    );

    // Add rules for podcast archives
    add_rewrite_rule(
        '^podcasts/page/([0-9]+)/?$',
        'index.php?post_type=podcast&paged=$matches[1]',
        'top'
    );
    add_rewrite_rule(
        '^podcasts/?$',
        'index.php?post_type=podcast',
        'top'
    );
}
add_action('init', 'custom_rewrite_rules', 10);

// Flush rewrite rules on theme activation
function theme_activation() {
    custom_rewrite_rules();
    flush_rewrite_rules();
}
add_action('after_switch_theme', 'theme_activation');

// Register custom query vars
function blogspot_register_query_vars($vars) {
    $vars[] = 'c_page';
    return $vars;
}
add_filter('query_vars', 'blogspot_register_query_vars');

// Add Theme Customizer Settings
function blogspot_customize_register($wp_customize) {
    // Add a new section for Picks Settings
    $wp_customize->add_section('picks_section', array(
        'title'    => __('تنظیمات بخش پیکس', 'blogspot'),
        'priority' => 31,
    ));

    // Background Image Setting
    $wp_customize->add_setting('picks_background', array(
        'default'           => get_template_directory_uri() . '/images/picks.jpg',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'picks_background', array(
        'label'    => __('تصویر پس زمینه', 'blogspot'),
        'section'  => 'picks_section',
        'settings' => 'picks_background',
    )));

    // Count Type Setting
    $wp_customize->add_setting('picks_count_type', array(
        'default'           => 'all',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('picks_count_type', array(
        'label'    => __('نوع شمارنده', 'blogspot'),
        'section'  => 'picks_section',
        'type'     => 'select',
        'choices'  => array(
            'all'      => __('همه مطالب', 'blogspot'),
            'articles' => __('فقط مقالات', 'blogspot'),
            'podcasts' => __('فقط پادکست‌ها', 'blogspot'),
            'custom'   => __('عدد دلخواه', 'blogspot'),
        ),
        'settings' => 'picks_count_type',
    ));

    // Custom Count Setting
    $wp_customize->add_setting('picks_custom_count', array(
        'default'           => '0',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('picks_custom_count', array(
        'label'    => __('عدد دلخواه', 'blogspot'),
        'section'  => 'picks_section',
        'type'     => 'number',
        'settings' => 'picks_custom_count',
        'active_callback' => function() {
            return get_theme_mod('picks_count_type') === 'custom';
        }
    ));

    // Link Setting
    $wp_customize->add_setting('picks_link', array(
        'default'           => get_permalink(get_option('page_for_posts')),
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('picks_link', array(
        'label'    => __('لینک دکمه', 'blogspot'),
        'section'  => 'picks_section',
        'type'     => 'url',
        'settings' => 'picks_link',
    ));

    // Button Text Setting
    $wp_customize->add_setting('picks_button_text', array(
        'default'           => 'مشاهده همه',
        'sanitize_callback' => 'sanitize_text_field',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('picks_button_text', array(
        'label'    => __('متن دکمه', 'blogspot'),
        'section'  => 'picks_section',
        'type'     => 'text',
        'settings' => 'picks_button_text',
    ));

    // Add a new section for Radio Container Settings
    $wp_customize->add_section('radio_container_section', array(
        'title'    => __('تنظیمات کانتینر رادیو', 'blogspot'),
        'priority' => 30,
    ));

    // Contact Page Settings
    $wp_customize->add_setting('contact_container_bg', array(
        'default'           => 'https://placehold.co/1200x600/cccccc/333333?text=Contact+Us',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'contact_container_bg', array(
        'label'    => __('تصویر پس زمینه صفحه تماس', 'blogspot'),
        'section'  => 'radio_container_section',
        'settings' => 'contact_container_bg',
    )));

    $wp_customize->add_setting('contact_container_height', array(
        'default'           => '400',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('contact_container_height', array(
        'label'    => __('حداقل ارتفاع صفحه تماس (پیکسل)', 'blogspot'),
        'section'  => 'radio_container_section',
        'type'     => 'number',
        'settings' => 'contact_container_height',
    ));

    // Podcast Join Page Settings
    $wp_customize->add_setting('podcast_container_bg', array(
        'default'           => 'https://placehold.co/1200x600/cccccc/333333?text=Join+Podcast',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'podcast_container_bg', array(
        'label'    => __('تصویر پس زمینه صفحه پادکست', 'blogspot'),
        'section'  => 'radio_container_section',
        'settings' => 'podcast_container_bg',
    )));

    $wp_customize->add_setting('podcast_container_height', array(
        'default'           => '400',
        'sanitize_callback' => 'absint',
        'transport'         => 'refresh',
    ));

    $wp_customize->add_control('podcast_container_height', array(
        'label'    => __('حداقل ارتفاع صفحه پادکست (پیکسل)', 'blogspot'),
        'section'  => 'radio_container_section',
        'type'     => 'number',
        'settings' => 'podcast_container_height',
    ));
}
add_action('customize_register', 'blogspot_customize_register');

// Add custom CSS to head
function blogspot_customizer_css() {
    ?>
    <style type="text/css">
        /* Picks Section Styles */
        .card-small.picks .back-img {
            background-image: url('<?php echo esc_url(get_theme_mod('picks_background', get_template_directory_uri() . '/images/picks.jpg')); ?>');
        }

        /* Contact Page Styles */
        .page-template-page-contact .radio-container {
            background-image: url('<?php echo esc_url(get_theme_mod('contact_container_bg', 'https://placehold.co/1200x600/cccccc/333333?text=Contact+Us')); ?>');
            min-height: <?php echo esc_attr(get_theme_mod('contact_container_height', '400')); ?>px;
        }

        /* Podcast Join Page Styles */
        .page-template-page-podcast-join .radio-container {
            background-image: url('<?php echo esc_url(get_theme_mod('podcast_container_bg', 'https://placehold.co/1200x600/cccccc/333333?text=Join+Podcast')); ?>');
            min-height: <?php echo esc_attr(get_theme_mod('podcast_container_height', '400')); ?>px;
        }
    </style>
    <?php
}
add_action('wp_head', 'blogspot_customizer_css');