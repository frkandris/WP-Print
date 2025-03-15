<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?> <?php bloginfo('name'); ?> - <?php esc_html_e('Print Version', 'Print Button'); ?></title>
    <?php wp_head(); ?>
    <style>
        /* Hide all unnecessary elements when actually printing */
        @media print {
            .print-button-controls {
                display: none;
            }
        }
    </style>
</head>
<body <?php body_class('print-button-page'); ?>>
    <div class="print-button-controls">
        <button onclick="window.print();" style="padding: 10px 15px; margin: 20px 0; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer;"><?php esc_html_e('Print This Page', 'Print Button'); ?></button>
        <button onclick="window.close();" style="padding: 10px 15px; margin: 20px 0 20px 10px; background: #f7f7f7; color: #333; border: 1px solid #ddd; border-radius: 4px; cursor: pointer;"><?php esc_html_e('Close', 'Print Button'); ?></button>
    </div>

    <?php while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
            <header class="entry-header">
                <h1 class="entry-title"><?php the_title(); ?></h1>
                <div class="entry-meta">
                    <?php echo esc_html(get_the_time(get_option('date_format'))); ?> | <?php the_author(); ?>
                </div>
            </header>

            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
    <?php endwhile; ?>

    <?php wp_footer(); ?>
</body>
</html>
