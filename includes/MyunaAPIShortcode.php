<?php
if ( !class_exists( 'MyunaAPIShortcode' ) ) {
    class MyunaAPIShortcode
    {
        public static function init() {
            add_shortcode('myuna-featured-programs', ['MyunaAPIShortcode', 'featured_programs']);
        }

        function featured_programs($atts = array(), $content = null) {
            extract(shortcode_atts(array(
                'rating' => '5'
            ), $atts));
            $options = get_option( 'myuna_api_plugin_options' );
            ob_start();
            ?>
            <h3>myuna featured programs</h3>
            <div>
            <?php echo $options['featured']?>
            </div>
            <?php
            return ob_get_clean();
        }
    }
}
?>