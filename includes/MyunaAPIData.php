<?php
if ( !class_exists( 'MyunaAPIData' ) ) {
    class MyunaAPIData
    {
        public static function init() {
            add_action( "wp_ajax_save_settings", ['MyunaAPIData', "save_settings"] );
            add_action( "wp_ajax_nopriv_save_settings", ['MyunaAPIData', "save_settings"] );

            add_action( "wp_ajax_import_featured_programs", ['MyunaAPIData', "import_featured_programs"] );
            add_action( "wp_ajax_nopriv_import_featured_programs", ['MyunaAPIData', "import_featured_programs"] );
        }

        function savedb($name, $data) {
            $file = plugin_dir_path(__FILE__).'../db/'.$name;
            file_put_contents($file, serialize($data));
        }

        function loaddb($name) {
            $file = plugin_dir_path(__FILE__).'../db/'.$name;
            if(file_exists($file)) {
                return unserialize(file_get_contents($file));
            }
            else {
                return null;
            }
        }

        function save_settings() {
            $resp = array('success' => true);
            self::savedb('settings', array(
                'times' => isset($_POST['times']) ? $_POST['times'] : '',
                'start_at' => isset($_POST['start_at']) ? $_POST['start_at'] : '',
            ));
                        
            wp_clear_scheduled_hook( 'myuna_api_schedule_hook' );
            echo json_encode($resp);
            exit;
        }

        function get_featured_programs() {
            $file = plugin_dir_path(__FILE__).'../db/featured_programs';
            return unserialize(file_get_contents($file));
        }

        function cronjob() {
            self::import_featured_programs();
        }

        function import_featured_programs(){
            set_time_limit(0);
            $resp = array('success' => true);
            
            // ini_set('display_errors', 1);
            // ini_set('display_startup_errors', 1);
            // error_reporting(E_ALL);
            // add this line in wp-config.php
            // define( 'WP_MEMORY_LIMIT', '256M' );
            /* That's all, stop editing! Happy publishing. */

            // spring-season.php
            $data2;
            $data2['QueryString'] = "SELECT Name, SeasonStartDate FROM Custom.Season WHERE Name = 'Spring' OR Name = 'Summer';" ;
            $handle = curl_init();

            $url = "https://myuna.perfectmind.com/api/2.0/B2C/Query";

            curl_setopt_array($handle, array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data2),
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588",
                    "Content-Type: application/json"
                ),
            ));

            echo curl_error($handle);

            $output = curl_exec($handle);
            $data2 = json_decode($output,true);
            curl_close($handle);
            
            //////////////////////////////////////
            $summerStart = date("Y-m-d", strtotime($data2[1]['SeasonStartDate']));

            // <!-- Get all Spring Programs-->

            // <!-- first API Call for The first 2 Months of Spring Program-->
            $springStart = date("Y-m-d", strtotime($data2[0]['SeasonStartDate'])) ;
            $springTwoMonths = date("Y-m-d", strtotime($data2[0]['SeasonStartDate'] . '+ 60 days'));
            $springEnding = date("Y-m-d", strtotime($data2[1]['SeasonStartDate'] . '- 1 day'));

            $spring_start = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $springStart . '&endDate=' . $springTwoMonths . '&pageSize=100';

            
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$spring_start",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $results_array = $array['Result'];

            //////////////////////////////
            // <!-- second API Call for The first 2 Months of Spring Program-->
            $spring_end = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $springTwoMonths . '&endDate=' . $springEnding . '&pageSize=100';
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$spring_end",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                "X-Client-Number: 24588"
                ),
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $second_results_array = $array['Result'];
            
            $final_array_for_spring= array_merge($results_array,$second_results_array);
            
            $out = [];
            foreach ($final_array_for_spring as $key => $x) {
                $sub = $x['ID'];
                $out[$sub]['StartTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['EndTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['EndTime'],  0, 19)));
                $out[$sub]['DatesOfWeek'][] =  date('D', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['StartTimesInDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ExactTimes'][] = date('Y-m-d h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ProgramDates'][] = date('M d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['RegistrationEndDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationEndDate'],  0, 19)));
                $out[$sub]['RegistrationStartDateOriginal'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationStartDate'],  0, 19)));

                $out[$sub]['Subject'] = $x['Subject'];
                $out[$sub]['ImageOriginal'] = $x['Image'];
                $out[$sub]['CalendarName'] = $x['CalendarName'];
                $out[$sub]['Program'] = $x['Program'];
                $out[$sub]['CalendarCategory'] = $x['CalendarCategory'];
                $out[$sub]['Description'] = $x['Description'];
                $out[$sub]['LocationName'] = $x['LocationName'];
                $out[$sub]['InstructorName'] = $x['InstructorName'];
                $out[$sub]['Capacity'] = $x['Capacity'];
                $out[$sub]['Remaining'] = $x['Remaining'];
                $out[$sub]['ID'] = $x['ID'];
            }

            $final = array_values($out); 

            // <!--end of section 1-->


            // <!-- start to get the data for all calendar events-->
            $calendar;
            $calendar['QueryString'] = "SELECT * FROM Custom.CalendarEvent WHERE ShowTo = 'Public';" ;
            $handle = curl_init();

            $url = "https://myuna.perfectmind.com/api/2.0/B2C/Query";

            curl_setopt_array($handle, array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => 
                json_encode($calendar),
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588",
                    "Content-Type: application/json"
                ),
            ));

            echo curl_error($handle);

            $output = curl_exec($handle);
            $calendar = json_decode($output,true);
            curl_close($handle);
           

            // <!-- the first one is final, the second one is data, merge them and add values from second to first one-->

            $arr2 = array_column($calendar, "ID");

            $finalArray = array();
            foreach($final as $arr){
                $key = array_search($arr['ID'], $arr2);
                if($key ===false){
                    $key = array_search(0, $arr2);
                }
                $finalArray[] = array_merge($arr,$calendar[$key]);
            }

            foreach($finalArray as $v) 
            {
                $v['CourseID'] = ltrim($v['EventID'], '0');
                $spring_array[] = $v;
            };

            foreach ($spring_array as $rkey => $resource){
                if ($resource['ShowTo'] == 'Public'){
                    $results_spring[] = $resource;
                }
            }

            $newArraySpring = array();
            foreach($results_spring as $arr){
                if(!isset($newArraySpring[$arr["CourseID"]])){
                    $newArraySpring[$arr["CourseID"]] = $arr;
                }
            } 
            
            $spacePrograms = array();

            foreach($newArraySpring as $v) 
            {
                if ($v['Remaining'] == 0){
                    $v['Availability'] = 'Full';
                } else{
                    $v['Availability'] = 'Available';
                }
                $spacePrograms[] = $v;
            }
            
            $modifiedSpringPrograms = array();

            foreach($spacePrograms as $v) 
                {
                if ($v['Capacity'] == 1){
                    $v['Participants'] = 'Private';
                } else{
                    $v['Participants'] = 'Group';
                }
                $modifiedSpringPrograms[] = $v;
            }

            $springPrograms = array();

            foreach($modifiedSpringPrograms as $v) 
            {
                $v['Season'] = 'Spring';
                $springPrograms[] = $v;
            }
            
            $i=0;
            foreach($springPrograms as $element) {
            //check the property of every element
                if($element['CalendarCategory'] == 'Indoor Bookings' || $element['CalendarCategory'] == 'Parking' || $element['CalendarName'] == 'UNA Community Field' || $element['EventStatus'] == 3 || $element['CalendarCategory'] == 'Fitness Centre Access' || $element['CalendarName'] == 'Sport Bookings' || $element['Subject'] == 'One-on-One Virtual Computer Help'){
                    unset($springPrograms[$i]);
                }
                $i++;
            } 
            

            // summer-season.php
            $data2;
            $data2['QueryString'] = "SELECT Name, SeasonStartDate FROM Custom.Season WHERE Name = 'Fall' OR Name = 'Summer';" ;
            $handle = curl_init();
            
            $url = "https://myuna.perfectmind.com/api/2.0/B2C/Query";
            
            curl_setopt_array($handle,
              array(
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => 
                json_encode($data2),
                CURLOPT_HTTPHEADER => array(
                  "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                  "X-Client-Number: 24588",
                  "Content-Type: application/json"
                ),
              )
            );
            
            echo curl_error($handle);
            
            $output = curl_exec($handle);
            $data2 = json_decode($output,true);
            curl_close($handle);
                        
            // <!-- Get all Spring Programs-->
            
            // <!-- first API Call for The first 2 Months of Spring Program-->
            
            $summerStart = date("Y-m-d", strtotime($data2[1]['SeasonStartDate'])) ;
            $summerTwoMonths = date("Y-m-d", strtotime($data2[1]['SeasonStartDate'] . '+ 60 days'));
            $summerEnding = date("Y-m-d", strtotime($data2[1]['SeasonStartDate'] . '+ 90 days'));
            
            $summer_start = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $summerStart . '&endDate=' . $summerTwoMonths . '&pageSize=3000';
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$summer_start",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));
        
            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $results_array = $array['Result'];
            
            
            // <!-- second API Call for The first 2 Months of Spring Program-->
            
            $summer_end = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $summerTwoMonths . '&endDate=' . $summerEnding . '&pageSize=3000';
            
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
            CURLOPT_URL => "$summer_end",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                "X-Client-Number: 24588"
            ),
            ));
        
            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $second_results_array = $array['Result'];
            
            $final_array_for_summer= array_merge($results_array,$second_results_array);
            
            
            $out = [];
            foreach ($final_array_for_summer as $key => $x) {
                $sub = $x['ID'];
                $out[$sub]['StartTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['EndTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['EndTime'],  0, 19)));
                $out[$sub]['DatesOfWeek'][] =  date('D', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['StartTimesInDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ExactTimes'][] = date('Y-m-d h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ProgramDates'][] = date('M d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['RegistrationEndDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationEndDate'],  0, 19)));
                $out[$sub]['RegistrationStartDateOriginal'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationStartDate'],  0, 19)));
            
                $out[$sub]['Subject'] = $x['Subject'];
                $out[$sub]['ImageOriginal'] = $x['Image'];
                $out[$sub]['CalendarName'] = $x['CalendarName'];
                $out[$sub]['Program'] = $x['Program'];
                $out[$sub]['CalendarCategory'] = $x['CalendarCategory'];
                $out[$sub]['Description'] = $x['Description'];
                $out[$sub]['LocationName'] = $x['LocationName'];
                $out[$sub]['InstructorName'] = $x['InstructorName'];
                $out[$sub]['Capacity'] = $x['Capacity'];
                $out[$sub]['Remaining'] = $x['Remaining'];
                $out[$sub]['ID'] = $x['ID'];
            }
            
            $final = array_values($out);
            
            // <!--end of section 1-->
            
            // <!-- the first one is final, the second one is data, merge them and add values from second to first one-->
            
            $arr2 = array_column($calendar, "ID");
            
            $finalArray = array();
            foreach($final as $arr){
                $key = array_search($arr['ID'], $arr2);
                if($key ===false){
                    $key = array_search(0, $arr2);
                }
                $finalArray[] = array_merge($arr,$calendar[$key]);
            }
            
            foreach($finalArray as $v) 
            {
                $v['CourseID'] = ltrim($v['EventID'], '0');
                $summer_array[] = $v;
            }
            
            foreach ($summer_array as $rkey => $resource){
                if ($resource['ShowTo'] == 'Public'){
                    $results_summer[] = $resource;
                }
            }
            
            $newArraySummer = array();
            foreach($results_summer as $arr){
               if(!isset($newArraySummer[$arr["CourseID"]])){
                    $newArraySummer[$arr["CourseID"]] = $arr;
               }
            }
            
            $spacePrograms = array();
            
            foreach($newArraySummer as $v) 
            {
                if ($v['Remaining'] == 0){
                  $v['Availability'] = 'Full';
                } else{
                    $v['Availability'] = 'Available';
                }
                $spacePrograms[] = $v;
            }
            
            $modifiedSummerPrograms = array();
            
            foreach($spacePrograms as $v) 
            {
                if ($v['Capacity'] == 1){
                    $v['Participants'] = 'Private';
                } else{
                    $v['Participants'] = 'Group';
                }
                $modifiedSummerPrograms[] = $v;
            }
            
            $summerPrograms = array();
            
            foreach($modifiedSummerPrograms as $v) 
            {
                $v['Season'] = 'Summer';
                $summerPrograms[] = $v;
            }
            
            $i=0;
            foreach($summerPrograms as $element) {
               //check the property of every element
                if( $element['OnlineDisplayDate'] ==  '2021-08-06T19:00:00' || $element['CalendarCategory'] == 'Indoor Bookings' || $element['CalendarCategory'] == 'Parking' || $element['CalendarName'] == 'UNA Community Field' || $element['EventStatus'] == 3 || $element['CalendarCategory'] == 'Fitness Centre Access' || $element['CalendarName'] == 'Sport Bookings'|| $element['Subject'] == 'One-on-One Virtual Computer Help' || $element['CourseID'] == '1750' || $element['CalendarName'] == 'Parks'|| $element['CalendarName'] == 'Outdoor Bookings'|| $element['CalendarName'] == 'Sports Fields'|| $element['CalendarName'] == 'Volunteer' ){
                    unset($summerPrograms[$i]);
               }
               $i++;
            }


            // fall-season.php
            $data3;
            $data3['QueryString'] = "SELECT Name, SeasonStartDate FROM Custom.Season WHERE Name = 'Fall' OR Name = 'Summer';" ;
            $handle = curl_init();
            
            $dateUrl = "https://myuna.perfectmind.com/api/2.0/B2C/Query";
            
            curl_setopt_array($handle,
              array(
                CURLOPT_URL            => $dateUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => 
                json_encode($data3),
                CURLOPT_HTTPHEADER => array(
                  "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                  "X-Client-Number: 24588",
                  "Content-Type: application/json"
                ),
              )
            );
            
            echo curl_error($handle);
            
            $output = curl_exec($handle);
            $data3 = json_decode($output,true);
            curl_close($handle);
            
            $fallStart = date("Y-m-d", strtotime($data3[2]['SeasonStartDate']));
            
            // <!-- Get all Spring Programs-->
            
            // <!-- first API Call for The first 2 Months of Spring Program-->
            $summerStart = date("Y-m-d", strtotime($data3[2]['SeasonStartDate'])) ;
            $summerTwoMonths = date("Y-m-d", strtotime($data3[2]['SeasonStartDate'] . '+ 60 days'));
            $summerEnding = date("Y-m-d", strtotime($data3[2]['SeasonStartDate'] . '+ 120 days'));
            
            $summer_start = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $summerStart . '&endDate=' . $summerTwoMonths . '&pageSize=3500';
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$summer_start",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));
            
            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $fall_results_array = $array['Result'];


            // <!-- second API Call for The first 2 Months of Spring Program-->
            
            $summer_end = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $summerTwoMonths . '&endDate=' . $summerEnding . '&pageSize=3500';
            
            
            
            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$summer_end",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));
            
            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $fall_second_results_array = $array['Result'];
            
            
            if($fall_second_results_array !== null) { 
                $final_array_for_fall= array_merge($fall_results_array,$fall_second_results_array);
            } else{
                $final_array_for_fall= $fall_results_array;
            }

            $out = [];
            foreach ($final_array_for_fall as $key => $x) {
                $sub = $x['ID'];
                $out[$sub]['StartTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['EndTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['EndTime'],  0, 19)));
                $out[$sub]['DatesOfWeek'][] =  date('D', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['StartTimesInDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ExactTimes'][] = date('Y-m-d h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ProgramDates'][] = date('M d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['RegistrationEndDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationEndDate'],  0, 19)));
                $out[$sub]['RegistrationStartDateOriginal'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationStartDate'],  0, 19)));
            
                $out[$sub]['Subject'] = $x['Subject'];
                $out[$sub]['ImageOriginal'] = $x['Image'];
                $out[$sub]['CalendarName'] = $x['CalendarName'];
                $out[$sub]['Program'] = $x['Program'];
                $out[$sub]['CalendarCategory'] = $x['CalendarCategory'];
                $out[$sub]['Description'] = $x['Description'];
                $out[$sub]['LocationName'] = $x['LocationName'];
                $out[$sub]['InstructorName'] = $x['InstructorName'];
                $out[$sub]['Capacity'] = $x['Capacity'];
                $out[$sub]['Remaining'] = $x['Remaining'];
                $out[$sub]['ID'] = $x['ID'];
            }
            
            $final_fall = array_values($out);
            
            // <!--end of section 1-->
            
            $arr2 = array_column($calendar, "ID");
            
            $finalArray = array();
            foreach($final_fall as $arr){
                $key = array_search($arr['ID'], $arr2);
                if($key ===false){
                    $key = array_search(0, $arr2);
                }
                $finalArray[] = array_merge($arr,$calendar[$key]);
            }
            
            foreach($finalArray as $v) 
            {
                $v['CourseID'] = ltrim($v['EventID'], '0');
                $fall_array[] = $v;
            }
            
            foreach ($fall_array as $rkey => $resource){
                if ($resource['ShowTo'] == 'Public'){
                    $results_fall[] = $resource;
                }
            }
            
            $newArrayFall = array();
            foreach($results_fall as $arr){
                if(!isset($newArrayFall[$arr["CourseID"]])){
                    $newArrayFall[$arr["CourseID"]] = $arr;
                }
            }
            
            $spacePrograms = array();
            
            foreach($newArrayFall as $v) 
            {
                if ($v['Remaining'] == 0){
                    $v['Availability'] = 'Full';
                } else{
                   $v['Availability'] = 'Available';
                }
                $spacePrograms[] = $v;
            }
            
            $modifiedFallPrograms = array();
            
            foreach($spacePrograms as $v) 
            {
                if ($v['Capacity'] == 1){
                   $v['Participants'] = 'Private';
                } else{
                    $v['Participants'] = 'Group';
                }
                $modifiedFallPrograms[] = $v;
            }
            
            $fallPrograms = array();
            
            foreach($modifiedFallPrograms as $v) 
            {
                $v['Season'] = 'Fall';
                $fallPrograms[] = $v;
            }
            
            $i=0;
            foreach($fallPrograms as $element) {
               //check the property of every element
                if($element['CalendarCategory'] == 'Indoor Bookings' || $element['CalendarCategory'] == 'Parking' || $element['CalendarName'] == 'UNA Community Field' || $element['EventStatus'] == 3 || $element['CalendarCategory'] == 'Fitness Centre Access' || $element['CalendarName'] == 'Sport Bookings'|| $element['Subject'] == 'One-on-One Virtual Computer Help' || $element['CalendarName'] == 'Parks'|| $element['CalendarName'] == 'Outdoor Bookings'|| $element['CalendarName'] == 'Sports Fields'|| $element['CalendarName'] == 'Volunteer' || $element['Subject'] == 'One-on-One Computer Help | Virtual' || $element['Subject'] == "Seniors'  One-on-One Computer Help | In-person" || $element['CalendarCategory'] == 'Parks' || $element['CalendarCategory'] == 'Outdoor Bookings' ){
                    unset($fallPrograms[$i]);
               }
               $i++;
            }


            // winter-season.php
            $data3;
            $data3['QueryString'] = "SELECT Name, SeasonStartDate FROM Custom.Season WHERE Name = 'Winter';" ;
            $handle = curl_init();

            $dateUrl = "https://myuna.perfectmind.com/api/2.0/B2C/Query";

            curl_setopt_array($handle,
            array(
                CURLOPT_URL            => $dateUrl,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($data3),
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588",
                    "Content-Type: application/json"
                    ),
                )
            );

            echo curl_error($handle);

            $output = curl_exec($handle);
            $data3 = json_decode($output,true);
            curl_close($handle);
            
            $winterStart = date("Y-m-d", strtotime($data3[1]['SeasonStartDate']));

            // <!-- Get all Spring Programs-->

            // <!-- first API Call for The first 2 Months of Spring Program-->
            
            $winterTwoMonths = date("Y-m-d", strtotime($data3[1]['SeasonStartDate'] . '+ 60 days'));
            $winterEnding = date("Y-m-d", strtotime($data3[1]['SeasonStartDate'] . '+ 120 days'));
            
            $winter_start = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $winterStart . '&endDate=' . $winterTwoMonths . '&pageSize=3500';



            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$winter_start",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $winter_results_array = $array['Result'];
            
            // <!-- second API Call for The first 2 Months of Spring Program-->

            $winter_end = 'https://myuna.perfectmind.com/api/2.0/B2C/Appointments?startDate=' . $winterTwoMonths . '&endDate=' . $winterEnding . '&pageSize=3500';



            $ch = curl_init();
            curl_setopt_array($ch, array(
                CURLOPT_URL => "$winter_end",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => array(
                    "X-Access-Key: 5I8bxF2WG9nFZ0rf5zxW3tGlN0aSJDr9",
                    "X-Client-Number: 24588"
                ),
            ));

            $response = curl_exec($ch);
            curl_close($ch);
            $array = json_decode($response,true);
            $winter_second_results_array = $array['Result'];
            
            if($winter_second_results_array !== null) {
                $final_array_for_winter= array_merge($winter_results_array,$winter_second_results_array);
            } else{
                $final_array_for_winter= $winter_results_array;
            }

            $out = [];
            foreach ($final_array_for_winter as $key => $x) {
                $sub = $x['ID'];
                $out[$sub]['StartTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['EndTimes'][] = date('h:iA', strtotime(mb_strimwidth( $text= $x['EndTime'],  0, 19)));
                $out[$sub]['DatesOfWeek'][] =  date('D', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['StartTimesInDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ExactTimes'][] = date('Y-m-d h:iA', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['ProgramDates'][] = date('M d', strtotime(mb_strimwidth( $text= $x['StartTime'],  0, 19)));
                $out[$sub]['RegistrationEndDate'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationEndDate'],  0, 19)));
                $out[$sub]['RegistrationStartDateOriginal'][] = date('Y-m-d', strtotime(mb_strimwidth( $text= $x['RegistrationStartDate'],  0, 19)));

                $out[$sub]['Subject'] = $x['Subject'];
                $out[$sub]['ImageOriginal'] = $x['Image'];
                $out[$sub]['CalendarName'] = $x['CalendarName'];
                $out[$sub]['Program'] = $x['Program'];
                $out[$sub]['CalendarCategory'] = $x['CalendarCategory'];
                $out[$sub]['Description'] = $x['Description'];
                $out[$sub]['LocationName'] = $x['LocationName'];
                $out[$sub]['InstructorName'] = $x['InstructorName'];
                $out[$sub]['Capacity'] = $x['Capacity'];
                $out[$sub]['Remaining'] = $x['Remaining'];
                $out[$sub]['ID'] = $x['ID'];
            }

            $final_winter = array_values($out); 

            // <!--end of section 1-->

            $arr2 = array_column($calendar, "ID");

            $finalArray = array();
            foreach($final_winter as $arr){
                $key = array_search($arr['ID'], $arr2);
                if($key ===false){
                    $key = array_search(0, $arr2);
                }
                $finalArray[] = array_merge($arr,$calendar[$key]);
            }


            foreach($finalArray as $v) 
            {
                $v['CourseID'] = ltrim($v['EventID'], '0');
                $winter_array[] = $v;
            }

            foreach ($winter_array as $rkey => $resource){
                if ($resource['ShowTo'] == 'Public'){
                $results_winter[] = $resource;
                }
            }

            $newArrayWinter = array();
            foreach($results_winter as $arr){
                if(!isset($newArrayWinter[$arr["CourseID"]])){
                    $newArrayWinter[$arr["CourseID"]] = $arr;
                }
            }

            $spacePrograms = array();

            foreach($newArrayWinter as $v) 
            {
                if ($v['Remaining'] == 0){
                    $v['Availability'] = 'Full';
                } else{
                    $v['Availability'] = 'Available';
                }
                $spacePrograms[] = $v;
            }

            $modifiedWinterPrograms = array();

            foreach($spacePrograms as $v) 
            {
                if ($v['Capacity'] == 1){
                    $v['Participants'] = 'Private';
                } else{
                    $v['Participants'] = 'Group';
                }
                $modifiedWinterPrograms[] = $v;
            }

            $winterPrograms = array();

            foreach($modifiedWinterPrograms as $v) 
            {
                $v['Season'] = 'Winter';
                $winterPrograms[] = $v;
            }
            
            $i=0;
            foreach($winterPrograms as $element) {
            //check the property of every element
                if($element['CalendarCategory'] == 'Indoor Bookings' || $element['CalendarCategory'] == 'Parking' || $element['CalendarName'] == 'UNA Community Field' || $element['EventStatus'] == 3 || $element['CalendarCategory'] == 'Fitness Centre Access' || $element['CalendarName'] == 'Sport Bookings' || $element['Subject'] == 'One-on-One Virtual Computer Help'){
                    unset($winterPrograms[$i]);
                }
                $i++;
            }

            self::savedb('featured_programs', array(
                'spring' => $springPrograms,
                'summer' => $summerPrograms,
                'fall' => $fallPrograms,
                'winter' => $winterPrograms,
            ));

            $import_date = date('Y/m/d h:i:sa');
            self::savedb('history', ['date' => $import_date]);
            
            $resp['featured_programs'] = array(
                'spring' => count($springPrograms),
                'summer' => count($summerPrograms),
                'fall' => count($fallPrograms),
                'winter' => count($winterPrograms)                
            );

            $resp['date'] = $import_date;
            
            echo json_encode($resp);
            exit;
        }

    }
}
?>