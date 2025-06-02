<?php
/**
 * Podcast Subscription Handler
 *
 * @package Blogspot
 */

// Create database table on theme activation
function blogspot_create_subscribers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(100) NOT NULL,
        email varchar(100) NOT NULL,
        status varchar(20) NOT NULL DEFAULT 'active',
        date_subscribed datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        UNIQUE KEY email (email)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'blogspot_create_subscribers_table');

// Verify database table structure
function blogspot_verify_subscribers_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    
    // Check if table exists
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
    
    if (!$table_exists) {
        // Table doesn't exist, create it
        blogspot_create_subscribers_table();
        return;
    }
    
    // Check table structure
    $columns = $wpdb->get_results("SHOW COLUMNS FROM $table_name");
    $required_columns = array(
        'id' => 'mediumint(9)',
        'name' => 'varchar(100)',
        'email' => 'varchar(100)',
        'status' => 'varchar(20)',
        'date_subscribed' => 'datetime'
    );
    
    $missing_columns = array();
    foreach ($required_columns as $column => $type) {
        $column_exists = false;
        foreach ($columns as $col) {
            if ($col->Field === $column) {
                $column_exists = true;
                break;
            }
        }
        if (!$column_exists) {
            $missing_columns[] = $column;
        }
    }
    
    if (!empty($missing_columns)) {
        // Table structure is incorrect, recreate it
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        blogspot_create_subscribers_table();
    }
}

// Add verification on theme activation and admin init
add_action('after_switch_theme', 'blogspot_verify_subscribers_table');
add_action('admin_init', 'blogspot_verify_subscribers_table');

// Handle form submission
function blogspot_handle_podcast_subscription() {
    if (!isset($_POST['podcast_join_nonce']) || !wp_verify_nonce($_POST['podcast_join_nonce'], 'podcast_join_nonce')) {
        wp_send_json_error('توکن نامعتبر است');
        return;
    }

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    // Enhanced validation
    if (empty($name) || strlen($name) < 2) {
        wp_send_json_error('لطفا یک نام معتبر وارد کنید (حداقل ۲ کاراکتر)');
        return;
    }

    if (!is_email($email)) {
        wp_send_json_error('لطفا یک ایمیل معتبر وارد کنید');
        return;
    }

    // Check for disposable email domains
    $disposable_domains = array('tempmail.com', 'throwawaymail.com', 'mailinator.com');
    $email_domain = substr(strrchr($email, "@"), 1);
    if (in_array($email_domain, $disposable_domains)) {
        wp_send_json_error('لطفا از یک ایمیل معتبر استفاده کنید');
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';

    // Check if email already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_name WHERE email = %s",
        $email
    ));

    if ($existing) {
        wp_send_json_error('شما قبلا در خبرنامه پادکست ما عضو شده‌اید');
        return;
    }

    // Insert new subscriber with error logging
    $result = $wpdb->insert(
        $table_name,
        array(
            'name' => $name,
            'email' => $email,
            'status' => 'active'
        ),
        array('%s', '%s', '%s')
    );

    if ($result === false) {
        // Log the database error
        error_log('خطا در ثبت‌نام پادکست. خطای پایگاه داده: ' . $wpdb->last_error);
        wp_send_json_error('خطا در ثبت‌نام. لطفا دوباره تلاش کنید');
        return;
    }

    // Get the inserted ID
    $subscriber_id = $wpdb->insert_id;
    if (!$subscriber_id) {
        error_log('خطا در ثبت‌نام پادکست. شناسه ثبت نشد');
        wp_send_json_error('خطا در ثبت‌نام. لطفا دوباره تلاش کنید');
        return;
    }

    // Send welcome email with HTML formatting
    $subject = 'خوش آمدید به خبرنامه پادکست ما!';
    $message = "
    <html dir='rtl'>
    <head>
        <style>
            body { font-family: Tahoma, Arial, sans-serif; line-height: 1.6; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #f8f9fa; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>به پادکست ما خوش آمدید!</h2>
            </div>
            <div class='content'>
                <p>سلام {$name}،</p>
                <p>از پیوستن شما به خبرنامه پادکست ما متشکریم. هر زمان که یک قسمت جدید منتشر شود، یک ایمیل دریافت خواهید کرد.</p>
                <p>با احترام،<br>" . get_bloginfo('name') . "</p>
            </div>
            <div class='footer'>
                <p>این یک پیام خودکار است، لطفا به آن پاسخ ندهید.</p>
            </div>
        </div>
    </body>
    </html>";

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail_sent = wp_mail($email, $subject, $message, $headers);

    if (!$mail_sent) {
        error_log('خطا در ارسال ایمیل خوش‌آمدگویی به: ' . $email);
    }

    wp_send_json_success('از پیوستن شما به خبرنامه پادکست ما متشکریم!');
}
add_action('wp_ajax_podcast_subscription', 'blogspot_handle_podcast_subscription');
add_action('wp_ajax_nopriv_podcast_subscription', 'blogspot_handle_podcast_subscription');

// Add admin menu page
function blogspot_add_subscribers_menu() {
    add_menu_page(
        'اشتراک‌های پادکست',
        'اشتراک‌های پادکست',
        'manage_options',
        'podcast-subscribers',
        'blogspot_subscribers_page',
        'dashicons-groups',
        30
    );
}
add_action('admin_menu', 'blogspot_add_subscribers_menu');

// Add export functionality
function blogspot_export_subscribers() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    $subscribers = $wpdb->get_results("SELECT * FROM $table_name ORDER BY date_subscribed DESC");

    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=podcast-subscribers-' . date('Y-m-d') . '.csv');
    header('Pragma: no-cache');
    header('Expires: 0');

    // Create CSV file
    $output = fopen('php://output', 'w');
    
    // Add UTF-8 BOM for proper Persian character display
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add headers
    fputcsv($output, array('نام', 'ایمیل', 'وضعیت', 'تاریخ عضویت'));

    // Add data
    foreach ($subscribers as $subscriber) {
        fputcsv($output, array(
            $subscriber->name,
            $subscriber->email,
            $subscriber->status,
            $subscriber->date_subscribed
        ));
    }

    fclose($output);
    exit;
}
add_action('admin_post_export_subscribers', 'blogspot_export_subscribers');

// Enhanced admin interface
function blogspot_subscribers_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    
    // Handle bulk actions
    if (isset($_POST['action']) && isset($_POST['subscriber'])) {
        $action = $_POST['action'];
        $subscribers = array_map('intval', $_POST['subscriber']);
        
        if ($action === 'delete') {
            $wpdb->query("DELETE FROM $table_name WHERE id IN (" . implode(',', $subscribers) . ")");
            add_settings_error('podcast_subscribers', 'subscribers_deleted', 'اشتراک‌های انتخاب شده حذف شدند.', 'updated');
        } elseif ($action === 'deactivate') {
            $wpdb->query("UPDATE $table_name SET status = 'inactive' WHERE id IN (" . implode(',', $subscribers) . ")");
            add_settings_error('podcast_subscribers', 'subscribers_deactivated', 'اشتراک‌های انتخاب شده غیرفعال شدند.', 'updated');
        } elseif ($action === 'activate') {
            $wpdb->query("UPDATE $table_name SET status = 'active' WHERE id IN (" . implode(',', $subscribers) . ")");
            add_settings_error('podcast_subscribers', 'subscribers_activated', 'اشتراک‌های انتخاب شده فعال شدند.', 'updated');
        }
    }

    // Get subscribers with pagination
    $per_page = 20;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $per_page;

    $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    $subscribers = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY date_subscribed DESC LIMIT %d OFFSET %d",
        $per_page,
        $offset
    ));

    // Get status counts
    $status_counts = $wpdb->get_results("
        SELECT status, COUNT(*) as count 
        FROM $table_name 
        GROUP BY status
    ");

    // Add the test email button before the email form
    blogspot_add_test_email_button();

    ?>
    <div class="wrap">
        <h1>اشتراک‌های پادکست</h1>
        
        <?php settings_errors('podcast_subscribers'); ?>

        <div class="subscriber-stats" style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h3>آمار اشتراک‌ها</h3>
            <div style="display: flex; gap: 20px;">
                <?php foreach ($status_counts as $status): ?>
                    <div style="padding: 10px; background: #f8f9fa; border-radius: 4px;">
                        <strong><?php 
                            switch($status->status) {
                                case 'active':
                                    echo 'فعال';
                                    break;
                                case 'inactive':
                                    echo 'غیرفعال';
                                    break;
                                case 'unsubscribed':
                                    echo 'لغو شده';
                                    break;
                                default:
                                    echo $status->status;
                            }
                        ?>:</strong> <?php echo $status->count; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <form method="post" style="display: inline-block; margin-right: 10px;">
                    <select name="action">
                        <option value="-1">عملیات گروهی</option>
                        <option value="delete">حذف</option>
                        <option value="deactivate">غیرفعال کردن</option>
                        <option value="activate">فعال کردن</option>
                    </select>
                    <input type="submit" class="button action" value="اعمال">
                </form>
                <a href="<?php echo admin_url('admin-post.php?action=export_subscribers'); ?>" class="button">خروجی CSV</a>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th class="check-column"><input type="checkbox" id="cb-select-all-1"></th>
                    <th>نام</th>
                    <th>ایمیل</th>
                    <th>وضعیت</th>
                    <th>تاریخ عضویت</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $subscriber): ?>
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="subscriber[]" value="<?php echo $subscriber->id; ?>">
                    </th>
                    <td><?php echo esc_html($subscriber->name); ?></td>
                    <td><?php echo esc_html($subscriber->email); ?></td>
                    <td>
                        <span class="status-<?php echo esc_attr($subscriber->status); ?>">
                            <?php 
                                switch($subscriber->status) {
                                    case 'active':
                                        echo 'فعال';
                                        break;
                                    case 'inactive':
                                        echo 'غیرفعال';
                                        break;
                                    case 'unsubscribed':
                                        echo 'لغو شده';
                                        break;
                                    default:
                                        echo $subscriber->status;
                                }
                            ?>
                        </span>
                    </td>
                    <td><?php echo esc_html($subscriber->date_subscribed); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php
        // Add pagination
        $total_pages = ceil($total_items / $per_page);
        if ($total_pages > 1) {
            echo '<div class="tablenav bottom"><div class="tablenav-pages">';
            echo paginate_links(array(
                'base' => add_query_arg('paged', '%#%'),
                'format' => '',
                'prev_text' => __('&laquo;'),
                'next_text' => __('&raquo;'),
                'total' => $total_pages,
                'current' => $current_page
            ));
            echo '</div></div>';
        }
        ?>

        <!-- Add Email Form Section -->
        <div class="wrap" style="margin-top: 30px; padding: 20px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2>ارسال ایمیل به همه اشتراک‌ها</h2>
            <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" id="email-form">
                <input type="hidden" name="action" value="send_manual_email">
                <?php wp_nonce_field('send_manual_email_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><label for="email_subject">موضوع ایمیل</label></th>
                        <td>
                            <input type="text" name="email_subject" id="email_subject" class="regular-text" value="قسمت جدید پادکست منتشر شد!" required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="email_content">محتوا</label></th>
                        <td>
                            <?php
                            $default_content = '
<p>سلام،</p>

<p>امیدوارم حالتان خوب باشد. یک قسمت جدید از پادکست ما منتشر شده است که فکر می‌کنم برای شما جالب باشد.</p>

<p>در این قسمت، ما به موضوع [موضوع پادکست] می‌پردازیم و نکات مهمی را بررسی می‌کنیم.</p>

<p>شما می‌توانید این قسمت را از طریق لینک زیر گوش دهید:</p>

<p>امیدواریم از شنیدن این قسمت لذت ببرید.</p>

<p>با احترام،<br>
تیم ' . get_bloginfo('name') . '</p>';

                            wp_editor($default_content, 'email_content', array(
                                'textarea_name' => 'email_content',
                                'media_buttons' => false,
                                'textarea_rows' => 15,
                                'teeny' => true
                            ));
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="button_text">متن دکمه (اختیاری)</label></th>
                        <td>
                            <input type="text" name="button_text" id="button_text" class="regular-text" value="مشاهده و شنیدن پادکست">
                            <p class="description">متن دکمه‌ای که در ایمیل نمایش داده می‌شود</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="button_url">لینک دکمه (اختیاری)</label></th>
                        <td>
                            <input type="url" name="button_url" id="button_url" class="regular-text" value="<?php echo get_bloginfo('url'); ?>">
                            <p class="description">آدرس لینک دکمه</p>
                        </td>
                    </tr>
                </table>
                
                <div class="preview-section" style="margin: 20px 0; padding: 20px; background: #f8f9fa; border: 1px solid #ddd; border-radius: 4px;">
                    <h3>پیش‌نمایش ایمیل</h3>
                    <div id="email-preview" style="margin-top: 10px;"></div>
                </div>

                <p class="submit">
                    <input type="submit" name="submit" id="submit" class="button button-primary" value="ارسال ایمیل">
                </p>
            </form>
        </div>
    </div>

    <style>
        .status-active { color: #46b450; }
        .status-inactive { color: #dc3232; }
        .status-unsubscribed { color: #ffb900; }
        .subscriber-stats { margin-bottom: 20px; }
        .tablenav-pages { margin: 10px 0; }
        .form-table th { width: 200px; }
        .preview-section { margin-top: 20px; }
        #email-preview { 
            background: #fff; 
            padding: 20px; 
            border: 1px solid #ddd; 
            border-radius: 4px;
            margin-top: 10px;
        }
    </style>

    <script>
    jQuery(document).ready(function($) {
        function updatePreview() {
            var subject = $('#email_subject').val();
            var content = tinyMCE.get('email_content').getContent();
            var buttonText = $('#button_text').val();
            var buttonUrl = $('#button_url').val();
            
            var previewContent = `
                <p>سلام کاربر،</p>
                ${content}
            `;

            var preview = blogspot_get_email_template(
                subject,
                previewContent,
                buttonText,
                buttonUrl
            );

            $('#email-preview').html(preview);
        }

        // Update preview when any field changes
        $('#email_subject, #button_text, #button_url').on('input', updatePreview);
        tinyMCE.get('email_content').on('change', updatePreview);

        // Initial preview
        updatePreview();
    });
    </script>
    <?php
}

// Add unsubscribe functionality
function blogspot_handle_unsubscribe() {
    if (!isset($_GET['email']) || !isset($_GET['token'])) {
        wp_die('درخواست لغو اشتراک نامعتبر است');
    }

    $email = sanitize_email($_GET['email']);
    $token = sanitize_text_field($_GET['token']);

    // Verify token
    $expected_token = wp_hash($email . 'unsubscribe');
    if ($token !== $expected_token) {
        wp_die('توکن لغو اشتراک نامعتبر است');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';

    // Update subscriber status
    $result = $wpdb->update(
        $table_name,
        array('status' => 'unsubscribed'),
        array('email' => $email),
        array('%s'),
        array('%s')
    );

    if ($result !== false) {
        wp_redirect(home_url('/?unsubscribed=1'));
        exit;
    } else {
        wp_die('خطا در لغو اشتراک. لطفا دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.');
    }
}
add_action('admin_post_nopriv_unsubscribe', 'blogspot_handle_unsubscribe');
add_action('admin_post_unsubscribe', 'blogspot_handle_unsubscribe');

// Add default email template function
function blogspot_get_email_template($title, $content, $button_text = '', $button_url = '', $unsubscribe_url = '') {
    return "
    <!DOCTYPE html>
    <html dir='rtl' lang='fa'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            @font-face {
                font-family: 'Vazir';
                src: url('https://cdn.jsdelivr.net/gh/rastikerdar/vazir-font@v30.1.0/dist/Vazir.woff2') format('woff2');
                font-weight: normal;
                font-style: normal;
            }
            body {
                font-family: 'Vazir', Tahoma, Arial, sans-serif;
                line-height: 1.8;
                color: #333;
                background-color: #f5f5f5;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
            }
            .header {
                background: linear-gradient(135deg, #1e88e5, #1565c0);
                padding: 30px 20px;
                text-align: center;
                border-radius: 8px 8px 0 0;
            }
            .header h1 {
                color: #ffffff;
                margin: 0;
                font-size: 24px;
                font-weight: bold;
            }
            .content {
                padding: 30px 20px;
                background-color: #ffffff;
            }
            .content h2 {
                color: #1e88e5;
                margin-top: 0;
                font-size: 20px;
            }
            .content p {
                margin: 15px 0;
                font-size: 16px;
            }
            .button {
                display: inline-block;
                padding: 12px 24px;
                background: linear-gradient(135deg, #1e88e5, #1565c0);
                color: #ffffff;
                text-decoration: none;
                border-radius: 4px;
                margin: 20px 0;
                font-weight: bold;
                text-align: center;
            }
            .footer {
                text-align: center;
                padding: 20px;
                background-color: #f8f9fa;
                border-top: 1px solid #e9ecef;
                border-radius: 0 0 8px 8px;
            }
            .footer p {
                margin: 5px 0;
                color: #666;
                font-size: 14px;
            }
            .unsubscribe {
                font-size: 12px;
                color: #666;
                margin-top: 20px;
            }
            .unsubscribe a {
                color: #1e88e5;
                text-decoration: none;
            }
            .social-links {
                margin: 20px 0;
            }
            .social-links a {
                display: inline-block;
                margin: 0 10px;
                color: #1e88e5;
                text-decoration: none;
            }
            @media only screen and (max-width: 600px) {
                .container {
                    width: 100% !important;
                    padding: 10px !important;
                }
                .header {
                    padding: 20px 10px !important;
                }
                .content {
                    padding: 20px 10px !important;
                }
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>" . get_bloginfo('name') . "</h1>
            </div>
            <div class='content'>
                <h2>{$title}</h2>
                {$content}";

    if (!empty($button_text) && !empty($button_url)) {
        $template .= "
                <p style='text-align: center;'>
                    <a href='{$button_url}' class='button'>{$button_text}</a>
                </p>";
    }

    $template .= "
            </div>
            <div class='footer'>
                <div class='social-links'>
                    <a href='" . get_bloginfo('url') . "'>وب‌سایت</a>
                    <a href='https://instagram.com/" . get_bloginfo('name') . "'>اینستاگرام</a>
                    <a href='https://twitter.com/" . get_bloginfo('name') . "'>توییتر</a>
                </div>
                <p>این یک پیام خودکار است، لطفا به آن پاسخ ندهید.</p>";

    if (!empty($unsubscribe_url)) {
        $template .= "
                <p class='unsubscribe'>
                    برای لغو اشتراک، <a href='{$unsubscribe_url}'>اینجا</a> کلیک کنید.
                </p>";
    }

    $template .= "
            </div>
        </div>
    </body>
    </html>";

    return $template;
}

// Update the podcast update email function to use the new template
function blogspot_send_podcast_update_emails($post_id) {
    if (get_post_type($post_id) !== 'podcast') {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    $subscribers = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'");
    
    $post = get_post($post_id);
    $subject = 'قسمت جدید پادکست: ' . $post->post_title;
    
    foreach ($subscribers as $subscriber) {
        $unsubscribe_url = add_query_arg(
            array(
                'action' => 'unsubscribe',
                'email' => $subscriber->email,
                'token' => wp_hash($subscriber->email . 'unsubscribe')
            ),
            admin_url('admin-post.php')
        );

        $content = "
            <p>سلام {$subscriber->name}،</p>
            <p>یک قسمت جدید از پادکست ما منتشر شده است:</p>
            <h3 style='color: #1e88e5;'>{$post->post_title}</h3>
            <p>" . wp_trim_words($post->post_content, 30) . "</p>";

        $message = blogspot_get_email_template(
            'قسمت جدید پادکست منتشر شد!',
            $content,
            'مشاهده و شنیدن پادکست',
            get_permalink($post_id),
            $unsubscribe_url
        );

        $headers = array('Content-Type: text/html; charset=UTF-8');
        $mail_sent = wp_mail($subscriber->email, $subject, $message, $headers);

        if (!$mail_sent) {
            error_log('خطا در ارسال ایمیل به: ' . $subscriber->email);
        }
    }
}
add_action('publish_podcast', 'blogspot_send_podcast_update_emails');

// Add unsubscribe success message
function blogspot_unsubscribe_notice() {
    if (isset($_GET['unsubscribed']) && $_GET['unsubscribed'] == 1) {
        ?>
        <div class="notice notice-success is-dismissible">
            <p>اشتراک شما با موفقیت لغو شد.</p>
        </div>
        <?php
    }
}
add_action('wp_notices', 'blogspot_unsubscribe_notice');

// Update the manual email function to use the new template
function blogspot_send_manual_email() {
    if (!current_user_can('manage_options')) {
        wp_die('دسترسی غیرمجاز');
    }

    if (!isset($_POST['email_subject']) || !isset($_POST['email_content'])) {
        wp_die('لطفا موضوع و محتوای ایمیل را وارد کنید');
    }

    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'send_manual_email_nonce')) {
        wp_die('توکن امنیتی نامعتبر است');
    }

    $subject = sanitize_text_field($_POST['email_subject']);
    $content = wp_kses_post($_POST['email_content']);
    $button_text = isset($_POST['button_text']) ? sanitize_text_field($_POST['button_text']) : '';
    $button_url = isset($_POST['button_url']) ? esc_url_raw($_POST['button_url']) : '';

    global $wpdb;
    $table_name = $wpdb->prefix . 'podcast_subscribers';
    $subscribers = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'active'");

    if (empty($subscribers)) {
        wp_redirect(add_query_arg(
            array(
                'page' => 'podcast-subscribers',
                'error' => 'no_subscribers'
            ),
            admin_url('admin.php')
        ));
        exit;
    }

    $success_count = 0;
    $fail_count = 0;
    $errors = array();

    // Set up email headers
    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . get_bloginfo('name') . ' <' . get_bloginfo('admin_email') . '>'
    );

    foreach ($subscribers as $subscriber) {
        $personalized_content = "
            <p>سلام {$subscriber->name}،</p>
            {$content}";

        $unsubscribe_url = add_query_arg(
            array(
                'action' => 'unsubscribe',
                'email' => $subscriber->email,
                'token' => wp_hash($subscriber->email . 'unsubscribe')
            ),
            admin_url('admin-post.php')
        );

        $message = blogspot_get_email_template(
            $subject,
            $personalized_content,
            $button_text,
            $button_url,
            $unsubscribe_url
        );

        // Add error logging
        add_filter('wp_mail_failed', function($error) use (&$errors, $subscriber) {
            $errors[] = sprintf(
                'خطا در ارسال ایمیل به %s: %s',
                $subscriber->email,
                $error->get_error_message()
            );
        });

        $mail_sent = wp_mail($subscriber->email, $subject, $message, $headers);

        if ($mail_sent) {
            $success_count++;
        } else {
            $fail_count++;
            error_log(sprintf(
                'خطا در ارسال ایمیل به %s: %s',
                $subscriber->email,
                isset($wp_mail_error) ? $wp_mail_error : 'خطای نامشخص'
            ));
        }
    }

    // Log all errors
    if (!empty($errors)) {
        error_log('خطاهای ارسال ایمیل: ' . implode(', ', $errors));
    }

    // Redirect with status
    wp_redirect(add_query_arg(
        array(
            'page' => 'podcast-subscribers',
            'emails_sent' => $success_count,
            'emails_failed' => $fail_count,
            'errors' => !empty($errors) ? base64_encode(json_encode($errors)) : ''
        ),
        admin_url('admin.php')
    ));
    exit;
}

// Update the email notice function to show detailed errors
function blogspot_email_notice() {
    if (isset($_GET['emails_sent']) || isset($_GET['emails_failed'])) {
        $sent = isset($_GET['emails_sent']) ? intval($_GET['emails_sent']) : 0;
        $failed = isset($_GET['emails_failed']) ? intval($_GET['emails_failed']) : 0;
        
        if ($sent > 0) {
            add_settings_error(
                'podcast_subscribers',
                'emails_sent',
                sprintf('%d ایمیل با موفقیت ارسال شد.', $sent),
                'updated'
            );
        }
        
        if ($failed > 0) {
            add_settings_error(
                'podcast_subscribers',
                'emails_failed',
                sprintf('%d ایمیل با خطا مواجه شد.', $failed),
                'error'
            );
        }

        // Show detailed errors if any
        if (isset($_GET['errors']) && !empty($_GET['errors'])) {
            $errors = json_decode(base64_decode($_GET['errors']), true);
            if (!empty($errors)) {
                foreach ($errors as $error) {
                    add_settings_error(
                        'podcast_subscribers',
                        'email_error',
                        $error,
                        'error'
                    );
                }
            }
        }
    }

    if (isset($_GET['error']) && $_GET['error'] === 'no_subscribers') {
        add_settings_error(
            'podcast_subscribers',
            'no_subscribers',
            'هیچ اشتراک فعالی برای ارسال ایمیل وجود ندارد.',
            'error'
        );
    }
}

// Add test email button to the form
function blogspot_add_test_email_button() {
    ?>
    <div style="margin: 20px 0; padding: 15px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
        <h3>تست تنظیمات ایمیل</h3>
        <p>برای اطمینان از صحت تنظیمات ایمیل، ابتدا یک ایمیل تست ارسال کنید.</p>
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
            <input type="hidden" name="action" value="test_email_config">
            <?php wp_nonce_field('test_email_config_nonce'); ?>
            <p class="submit">
                <input type="submit" name="submit" class="button button-primary" value="ارسال ایمیل تست">
            </p>
        </form>
    </div>
    <?php
}

// Add a function to test email configuration
function blogspot_test_email_config() {
    if (!current_user_can('manage_options')) {
        wp_die('دسترسی غیرمجاز');
    }

    // Verify nonce
    if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'test_email_config_nonce')) {
        wp_die('توکن امنیتی نامعتبر است');
    }

    $admin_email = get_bloginfo('admin_email');
    $site_name = get_bloginfo('name');
    
    $subject = 'تست تنظیمات ایمیل - ' . $site_name;
    $message = blogspot_get_email_template(
        'تست تنظیمات ایمیل',
        '<p>این یک ایمیل تست برای بررسی تنظیمات ارسال ایمیل است.</p>
        <p>اگر این ایمیل را دریافت می‌کنید، تنظیمات ایمیل سایت شما به درستی کار می‌کند.</p>',
        'بازدید از سایت',
        get_bloginfo('url')
    );

    $headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $site_name . ' <' . $admin_email . '>'
    );

    // Add error logging
    $error_message = '';
    add_filter('wp_mail_failed', function($error) use (&$error_message) {
        $error_message = $error->get_error_message();
    });

    $sent = wp_mail($admin_email, $subject, $message, $headers);

    if ($sent) {
        add_settings_error(
            'podcast_subscribers',
            'test_success',
            'ایمیل تست با موفقیت ارسال شد. تنظیمات ایمیل درست است.',
            'updated'
        );
    } else {
        add_settings_error(
            'podcast_subscribers',
            'test_failed',
            'خطا در ارسال ایمیل تست: ' . $error_message,
            'error'
        );
    }

    wp_redirect(add_query_arg('page', 'podcast-subscribers', admin_url('admin.php')));
    exit;
}
add_action('admin_post_test_email_config', 'blogspot_test_email_config');

// Make sure the email notice function is hooked
add_action('admin_notices', 'blogspot_email_notice'); 