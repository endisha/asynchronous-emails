<?php
/**
 * App file
 * 
 * @package AsynchronousEmails
 * @version 1.0.0
 */

defined('ABSPATH') or exit;

// Enqueue scripts
if (!empty($scripts)) {
    foreach ($scripts as $script) {
        wp_enqueue_script($script);
    }
}
// Enqueue styles
if (!empty($styles)) {
    foreach ($styles as $style) {
        wp_enqueue_style($style);
    }
}
// Enqueue media
if (isset($enqueue_media) && $enqueue_media && !did_action('wp_enqueue_media')) {
    wp_enqueue_media();
}
?>
<style>[v-cloak] { display: none; }</style>
<div id="<?php echo esc_attr($app_file); ?>" class="wrap">
    <transition name="bounce">
        <router-view></router-view>
    </transition>
</div>
<?php
// App Components
foreach( glob( __DIR__ . '/components/' . $app_file . "/*.php" ) as $file ) {
    if( file_exists( $file ) ) {
        $component = basename($file , '.vue.php');
        ?>
        <script type="text/x-template" id="<?php echo esc_attr($component); ?>">
            <?php require $file; ?>
        </script>
        <?php
    }
}
// Shared components
foreach( glob( __DIR__ . '/components/shared/*.php' ) as $file ) {
    if( file_exists( $file ) ) {
        $component = basename($file , '.vue.php');
        ?>
        <script type="text/x-template" id="<?php echo esc_attr($component); ?>">
            <?php require $file; ?>
        </script>
        <?php
    }
}