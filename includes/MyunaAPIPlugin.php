<?php
include ('MyunaAPIData.php');
include ('MyunaAPIShortcode.php');

function myuna_api_schedule_hook() {
    $myunaAPIData = new MyunaAPIData();
    $myunaAPIData->import_featured_programs();
}

function myuna_api_cron_schedules($schedules){
    $myunaAPIData = new MyunaAPIData();
    $settings = $myunaAPIData->loaddb('settings');
    if($settings) {
        $interval = 24;
        if(isset($settings['times'])) {
            $interval = intval($settings['times']);
        }

        if(isset($settings['start_at'])) {
            $start_at = $settings['start_at'];
            $start_at_datetime = new DateTime($start_at, new DateTimeZone('America/Vancouver'));
            // $start_at_datetime->setTimezone(new DateTimeZone('UTC'));

            if(!isset($schedules["myuna_cron_schedule"])){
                $schedules["myuna_cron_schedule"] = array(
                    'interval' => $interval * 3600,
                    'display' => __('Once every '.$interval.' hours from '.$start_at_datetime->format('Y-m-d H:i:s'))
                );
            }
        }
    }
    
    return $schedules;
}

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

            add_action( 'init', ['MyunaAPIPlugin', 'myuna_api_register_cronjob'] );
            add_action( 'admin_init', ['MyunaAPIPlugin', 'myuna_api_register_cronjob'] );
            add_action( 'myuna_api_schedule_hook', 'myuna_api_schedule_hook' );

            // /wp-json/myuna-api/import
            add_action( 'rest_api_init', function () {
                register_rest_route( 'myuna-api', 'import', array(
                    'methods' => 'GET',
                    'callback' => 'myuna_api_schedule_hook',
                ) );
            });
        }

        function myuna_api_register_cronjob() {
            add_filter('cron_schedules', 'myuna_api_cron_schedules');
            // wp_clear_scheduled_hook( 'myuna_api_schedule_hook' );
            if ( ! wp_next_scheduled( 'myuna_api_schedule_hook' ) ) {
                $myunaAPIData = new MyunaAPIData();
                $settings = $myunaAPIData->loaddb('settings');
                $start_at = time();
                if($settings) {
                    if(isset($settings['start_at'])) {
                        $start_at_datetime = new DateTime($settings['start_at'], new DateTimeZone('America/Vancouver'));
                        // $start_at_datetime->setTimezone(new DateTimeZone('UTC'));
                        wp_schedule_event( $start_at_datetime->getTimestamp(), 'myuna_cron_schedule', 'myuna_api_schedule_hook', array(), true);
                    }
                }
            }
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
            wp_enqueue_style( 'myuna_api_style',  plugin_dir_url( __FILE__ ) . '../css/myuna_api.css' );
            wp_enqueue_script( 'myuna_api_script',  plugin_dir_url( __FILE__ ) . '../js/myuna_api.js' );
            wp_localize_script( 'myuna_api_script', 'ajax', array( 'url' => admin_url( 'admin-ajax.php' ) ) );
        }

        function myuna_api_settings_page() {
            ?>
            <div class="myuna-api-form">
                <?php 
                settings_fields( 'myuna_api_plugin_options' );
                do_settings_sections( 'myuna_api_plugin' );
                ?>
                <button class="button button-primary" id="myuna_api_settings_save_btn" style="display: none">
                    <div class="processing-btn-wrap">
                        <div><?php esc_attr_e( 'Save' ); ?></div>
                        <div class="icon"></div>
                    </div>
                </button>
            </div>
            <?php
            // $myunaAPI = new MyunaAPIData();
            // $myunaAPI->save_settings();
        }

        function myuna_api_plugin_settings() {
            register_setting( 'myuna_api_plugin_options', 'myuna_api_plugin_options', ['MyunaAPIPlugin', 'myuna_api_plugin_options_validate'] );
            add_settings_section( 'myuna_api_settings', 'Myuna API Settings', ['MyunaAPIPlugin', 'myuna_api_plugin_section_text'], 'myuna_api_plugin' );
        
            // add_settings_field( 'myuna_api_start_at', 'Start at (Vancouver, Canada Timezone)', ['MyunaAPIPlugin', 'myuna_api_plugin_setting_start_at'], 'myuna_api_plugin', 'myuna_api_settings' );
            // add_settings_field( 'myuna_api_times', 'Interval', ['MyunaAPIPlugin', 'myuna_api_plugin_setting_times'], 'myuna_api_plugin', 'myuna_api_settings' );
        }

        function myuna_api_plugin_options_validate( $input ) {
            // $newinput['api_key'] = trim( $input['api_key'] );
            // if ( ! preg_match( '/^[a-z0-9]{32}$/i', $newinput['api_key'] ) ) {
            //     $newinput['api_key'] = '';
            // }
        
            return $input;
        }

        function myuna_api_plugin_section_text() {
            $myunaAPI = new MyunaAPIData();
            
            $history = $myunaAPI->loaddb('history');
            $last_import_date = '';
            if($history && isset($history['date'])) {
                $last_import_date = $history['date'];
            }
            ?>
            <div class="">
                <p>Last imported: <span id="last_import_date"><?php echo $last_import_date?></span></p>
                <button class="button button-primary" id="myuna_api_import_manually_btn">
                    <div class="processing-btn-wrap">
                        <div>Import Manually</div>
                        <div class="icon"></div>
                    </div>
                </button>
            </div>
            <?php
        }
        
        function myuna_api_plugin_setting_start_at() {
            $myunaAPI = new MyunaAPIData();
            
            $settings = $myunaAPI->loaddb('settings');
            $value = '';
            if($settings && isset($settings['start_at'])) {
                $value = $settings['start_at'];
            }
            
            echo "<input id='myuna_api_start_at' type='datetime-local' value='" . $value . "' />";
            echo "<div style='display: none'><div id='pst_time'></div><div id='utc_time'></div></div>";
        }

        function myuna_api_plugin_setting_times() {
            $myunaAPI = new MyunaAPIData();
            
            $settings = $myunaAPI->loaddb('settings');
            $value = '';
            if($settings && isset($settings['times'])) {
                $value = $settings['times'];
            }
            
            echo "<input id='myuna_api_times' type='number' value='" . $value . "' />(hrs)";
        }
    }
}