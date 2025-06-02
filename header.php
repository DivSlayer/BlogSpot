<!DOCTYPE html>
<html <?php language_attributes(); ?> dir="rtl">

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>
    <div class="sections-holder">
        <header class="header animate__animated animate__fadeInDown">
            <a class="logo" href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a>
            <nav class="nav-links">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => '',
                    'items_wrap' => '%3$s',
                    'walker' => new Blogspot_Nav_Walker()
                ));
                ?>
            </nav>
            <div class="actions">
                <button class="search-btn"><i class="fas fa-search"></i></button>
                <button class="c-btn curve dark menu-btn" id="mobile-nav-btn">منو</button>
            </div>
        </header>
        <div class="mobile-menu" id="mobile-nav">
            <button class="c-btn curve dark close-btn" id="mobile-nav-close"><i class="fas fa-times"></i></button>
            <ul class="holder">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => '',
                    'items_wrap' => '%3$s',
                    'walker' => new Blogspot_Nav_Walker()
                ));
                ?>
            </ul>
        </div>