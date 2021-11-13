<?php
include ('MyunaAPIData.php');
include ('MyunaAPIShortcode.php');

if ( !class_exists( 'MyunaAPIPlugin' ) ) {
    class MyunaAPIPlugin
    {
        public static function init() {

            // add_action( 'init', ['MyunaAPIPlugin', 'myuna_api_register_post_type'] );

            add_action( 'admin_menu', ['MyunaAPIPlugin', 'myuna_api_settings_menu'] );

            add_action( 'admin_init', ['MyunaAPIPlugin', 'myuna_api_plugin_settings'] );

            add_action( 'admin_init', ['MyunaAPIPlugin', 'myuna_api_js_css'] );

            MyunaAPIData::init();
            MyunaAPIShortcode::init();
        }

        function myuna_api_register_post_type() {
            $labels = array( 
                'name' => __( 'Programs' , 'myuna_api' ),
                'singular_name' => __( 'Program' , 'myuna_api' ),
                'add_new' => __( 'New Program' , 'myuna_api' ),
                'add_new_item' => __( 'Add New Program' , 'myuna_api' ),
                'edit_item' => __( 'Edit Program' , 'myuna_api' ),
                'new_item' => __( 'New Program' , 'myuna_api' ),
                'view_item' => __( 'View Program' , 'myuna_api' ),
                'search_items' => __( 'Search Programs' , 'myuna_api' ),
                'not_found' =>  __( 'No Programs Found' , 'myuna_api' ),
                'not_found_in_trash' => __( 'No Programs found in Trash' , 'myuna_api' ),
            );
        
            $args = array(
                'labels' => $labels,
                'has_archive' => true,
                'public' => true,
                'hierarchical' => false,
                'supports' => array(
                    'title', 
                    'editor', 
                    'excerpt', 
                    'custom-fields', 
                    'thumbnail',
                    'page-attributes'
                ),
                'rewrite'   => array( 'slug' => 'myuna_program' ),
                'show_in_rest' => true
            );
        
            register_post_type( "myuna_program", $args );
        }

        function myuna_api_settings_menu() {
            add_options_page( 'Myuna API Settings', 'Myuna API Setting', 'manage_options', 'myuna-api-setting', ['MyunaAPIPlugin', 'myuna_api_settings_page'] );
        }

        function myuna_api_js_css() {
            wp_enqueue_style( 'myuna_api_style',  plugin_dir_url( __FILE__ ) . '/css/myuna_api.css' );
            wp_enqueue_script( 'myuna_api_script',  plugin_dir_url( __FILE__ ) . '/js/myuna_api.js' );
            wp_localize_script( 'myuna_api_script', 'ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
        }

        function myuna_api_settings_page() {
            ?>
            <form action="options.php" method="post">
                <?php 
                settings_fields( 'myuna_api_plugin_options' );
                do_settings_sections( 'myuna_api_plugin' ); ?>
                <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
            </form>
            <h2>Myuna API Manually</h2>
            <button class="button button-primary" id="myuna_api_import_manually_btn">Import Manually</button>
            <?php
            // $myunaAPI = new MyunaAPI();
            // $myunaAPI->import_programs();
        }

        function myuna_api_plugin_settings() {
            register_setting( 'myuna_api_plugin_options', 'myuna_api_plugin_options', ['MyunaAPIPlugin', 'myuna_api_plugin_options_validate'] );
            add_settings_section( 'myuna_api_settings', 'Myuna API Settings', ['MyunaAPIPlugin', 'myuna_api_plugin_section_text'], 'myuna_api_plugin' );
        
            add_settings_field( 'myuna_api_times', 'Times to run every day', ['MyunaAPIPlugin', 'myuna_api_plugin_setting_times'], 'myuna_api_plugin', 'myuna_api_settings' );
            add_settings_field( 'myuna_api_featured_programs', 'Featured Programs', ['MyunaAPIPlugin', 'myuna_api_plugin_setting_featured_programs'], 'myuna_api_plugin', 'myuna_api_settings' );
        }

        function myuna_api_plugin_options_validate( $input ) {
            // $newinput['api_key'] = trim( $input['api_key'] );
            // if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
            //     $newinput['api_key'] = '';
            // }
        
            return $input;
        }

        function myuna_api_plugin_section_text() {
            echo '<p>Here you can set all the options</p>';
        }
        
        function myuna_api_plugin_setting_times() {
            $options = get_option( 'myuna_api_plugin_options' );
            echo "<input id='myuna_api_times' name='myuna_api_plugin_options[times]' type='number' value='" . esc_attr( $options['times'] ) . "' />";
        }

        function myuna_api_plugin_setting_featured_programs() {
            $options = get_option( 'myuna_api_plugin_options' );
            echo "<textarea id='myuna_api_featured' name='myuna_api_plugin_options[featured]' >" . esc_attr( $options['featured'] ) . "</textarea>";
        }
    }
}