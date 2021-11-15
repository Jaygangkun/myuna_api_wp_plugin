<?php
include ('MyunaAPIData.php');
if ( !class_exists( 'MyunaAPIShortcode' ) ) {
    class MyunaAPIShortcode
    {
        public static function init() {
            add_shortcode('myuna-featured-programs', ['MyunaAPIShortcode', 'featured_programs']);
            add_shortcode('myuna-all-programs', ['MyunaAPIShortcode', 'all_programs']);
        }

        function featured_programs($atts = array(), $content = null) {
            extract(shortcode_atts(array(
                'rating' => '5'
            ), $atts));
            ob_start();

            $apiData = new MyunaAPIData();
            $featured_programs = $apiData->loaddb('featured_programs');

            if($featured_programs == null) {
                return "No Programs";
            }

            $springPrograms = $featured_programs['spring'];
            $summerPrograms = $featured_programs['summer'];
            $fallPrograms = $featured_programs['fall'];
            $winterPrograms = $featured_programs['winter'];


            $dt = new DateTime();
            $tz = new DateTimeZone('America/Vancouver'); // or whatever zone you're after
            $dt->setTimezone($tz);
            $dateTime= $dt->format('Y-m-d'); 

            $gmtNow = date('Y-m-d H:i:s');

            $fallStart = '2021-09-01 08:00:00';

            $summerStart = '2021-06-28 08:00:00';

            $fallDisplayTime = '2021-08-06 08:00:00';

            if ($gmtNow >= $fallDisplayTime && $gmtNow >= $summerStart  && $gmtNow < $fallStart) {
                $springAndSummer= array_merge( $fallPrograms, $summerPrograms);
            } elseif ( $gmtNow >= $fallDisplayTime && $gmtNow >= $fallStart){ 
                $springAndSummer= array_merge( $fallPrograms);
            } elseif( $gmtNow < $fallDisplayTime) {
                $springAndSummer= array_merge( $summerPrograms);
            };

            foreach ($springAndSummer as $rkey => $resource){
                if ($resource['Featureonwebsite'] == true){
                    $featured_results_fall[] = $resource;
                }
            }

            foreach ( $featured_results_fall as $rkey => $resource){
                $brandNewArray[] = $resource;
            }
            ?>

            <h2 class="frontpage-section-headline news-section">Featured Programs</h2>
            <?php 
            if($brandNewArray == null){
                $alertText = get_field('no_program_alert_title_programs', 'option');
                $alertLink = get_field('no_program_alert_content_programs', 'option'); 
            ?>

                <div class="container">
                    <div class="alert-container">
                        <div class="alert-icon">
                            <img src="<?php custom_url(); ?>/images/attention-icon-plain.svg" class="attention-icon">
                        </div>
                        <div class="alert-box">
                            <?php 
                            if($alertText){ 
                            ?>
                                <h4><?php  echo $alertText; ?></h4>
                            <?php
                            } 
                            ?>
                            <?php 
                            if($alertLink){ 
                                echo $alertLink; 
                            } 
                            ?>
                        </div>
                    </div>
                </div>
                <div class="top_label view-news-button view-events-calendar">
                    <div class="center-link paddingbox">
                        <a class="button-link" href="/programs/"><i class="fa fa-angle-right" aria-hidden="true"></i>See All Programs</a>
                    </div>
                </div>
            <?php 
            } else{ 
            ?>
                <div class="activities-time-loop events-section featured-programs">    
                    <div class="flexslider">
                        <ul class="slides">    
                        <?php 
                        for ($i=0; $i < count($brandNewArray); $i++):
                            $uniqueDatesOfWeek = array_unique($brandNewArray[$i]['DatesOfWeek']);
                            $dayClasses = "";

                            // Days of The Week
                            foreach ( $uniqueDatesOfWeek as $key => $val) {
                                $lowerDay =  strtolower(date('D', strtotime($val)));
                                $dayClasses .= "day-" .$lowerDay . " ";
                            }
                            // Work Out Season
                            $startDate = $brandNewArray[$i]['ProgramDates'][0];
                            $endDate = end($brandNewArray[$i]['ProgramDates']);
                            $startSeason = strtolower(plugin_get_season("$startDate", "northern"));
                            $endSeason = strtolower(plugin_get_season("$endDate", "northern"));

                            // Age Range
                            $ageMax = $brandNewArray[$i]['MaximumAge'];
                            $ageMin = $brandNewArray[$i]['MinimumAge'];

                            if ($startSeason == $endSeason) {
                                $programSeason = $startSeason;
                            } else {
                                $programSeason = $startSeason . " " . $endSeason;
                            }

                            if( $ageMax || $ageMin ){
                                if( is_numeric($ageMax) && $ageMax <= 6 ) {
                                    $lowerRange = "early-years";
                                } elseif (is_numeric($ageMax) && $ageMax <= 17 ) {
                                    $lowerRange = "children-youth";
                                } elseif ($ageMin <= 17 ) {
                                    $lowerRange = "children-youth";
                                } elseif ($ageMin >= 55 ) {
                                    $lowerRange = "seniors adults-seniors";
                                } else {
                                    $lowerRange = "adults-seniors";
                                }
                            }

                            $differentCategories = plugin_slugify($brandNewArray[$i]['CalendarCategory']);

                            $lowerProgram = plugin_slugify($brandNewArray[$i]['CalendarName']);

                            $seasons = plugin_slugify($brandNewArray[$i]['Season']);

                            $groupCapacity = plugin_slugify($brandNewArray[$i]['Participants']);
                            $classSpace = plugin_slugify($brandNewArray[$i]['Availability']);
                            $classLocation = plugin_slugify($brandNewArray[$i]['LocationName']);

                        ?>

                            <li class="carousel-cell program <?php echo $dayClasses . " " .$differentCategories . " " .$seasons . " " .$groupCapacity . " " .$classSpace . " " .$classLocation . " " .$lowerProgram; ?>">
                                <div class="activities-loop event-column">
                                    <div class="frontpage-card">
                                        <?php 
                                        $defaultURL = get_site_url() . "/assets/media/custom_images/una-default-image.png";
                                        if( $brandNewArray[$i]['ImageOriginal'] ): ?>
                                            <div class="post_image" style="background-image: url('<?php echo $brandNewArray[$i]['ImageOriginal']; ?>');"></div>
                                        <?php else: ?>
                                            <div class="post_image" style="background-image: url('<?php echo $defaultURL; ?>');"></div>
                                        <?php endif; ?>

                                        <div class="events-content">
                                            <div class="events-subject">
                                                <h3 class="frontpage-card-title"><?php echo mb_strimwidth( $text= $brandNewArray[$i]['Subject'],  0, 32, '...'); ?></h3>
                                                <h4 class="frontpage-card-category">
                                                    <div class="row">
                                                        <?php 
                                                        if( $brandNewArray[$i]['MinimumAge'] && $brandNewArray[$i]['MaximumAge'] && $brandNewArray[$i]['CourseID'] ){ 
                                                        ?>
                                                            <div class="col-6">
                                                                <span class="program-number">Ages <?php echo $brandNewArray[$i]['MinimumAge'];?> - <?php echo $brandNewArray[$i]['MaximumAge'];?> </span>
                                                            </div>
                                                            <div class="col-6 program-course-number">
                                                                <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
                                                            </div>
                                                        <?php 
                                                        } elseif( $brandNewArray[$i]['MinimumAge'] && $brandNewArray[$i]['CourseID']) { ?>
                                                            <div class="col-6">
                                                                <span class="program-number">Ages <?php echo $brandNewArray[$i]['MinimumAge'];?> + </span>
                                                            </div>
                                                            <div class="col-6 program-course-number">
                                                                <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
                                                            </div>
                                                        <?php 
                                                        } elseif( $brandNewArray[$i]['MaximumAge'] && $brandNewArray[$i]['CourseID']) { ?>
                                                            <div class="col-6">
                                                                <span class="program-number">Ages Under <?php echo $brandNewArray[$i]['MaximumAge'];?> </span>
                                                            </div>
                                                            <div class="col-6 program-course-number">
                                                                <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
                                                            </div>
                                                        <?php 
                                                        } elseif( $brandNewArray[$i]['CourseID']){ ?>
                                                            <div class="col-12 program-course-number course-id-only">
                                                                <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
                                                            </div>
                                                        <?php 
                                                        } ?>
                                                    </div>
                                                </h4>

                                                <div class="row program-date">
                                                    <div class="col-1">
                                                        <i class="fa fa-clock-o" aria-hidden="true"></i>
                                                    </div>
                                                    <div class="col-10">
                                                        <?php  
                                                        $prefix = $fruitList = ''; ?>
                                                        <span class="program-date">
                                                            <?php  
                                                            foreach ( $uniqueDatesOfWeek as $key => $val) {
                                                                $fruitList .= $prefix . date('D', strtotime($val));
                                                                $prefix = ', ';
                                                            } 
                                                            ?>
                                                            <?php echo $fruitList; ?>
                                                            <?php echo $brandNewArray[$i]['StartTimes'][0]; ?> - <?php echo $brandNewArray[$i]['EndTimes'][0]; ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div><!-- .events-subject -->

                                            <div class="container">
                                                <div class="activities-button row">
                                                    <button class="col-6 details-button selector" rel="details-content-<?php echo $i ?>">Details</button>
                                                    <button class="col-6 description-button selector" rel="description-content-<?php echo $i ?>">Description</button>
                                                </div>
                                            </div><!-- .container -->

                                            <div class="description-registration-wrapper">
                                                <div class="description" id="description-content-<?php echo $i ?>" style="display: none">
                                                <?php 
                                                if ($brandNewArray[$i]['Description']){ 
                                                ?>
                                                    <div class="single-activity-description row">
                                                        <div class="col-1 single-activity-icon info-icon">
                                                            <i class="fa fa-info" aria-hidden="true"></i>
                                                        </div>
                                                        <div class="col-10 info-detail">
                                                            <?php echo wp_trim_words( $text= $brandNewArray[$i]['Description'], $num_words = 20, $more = "..."); ?>
                                                            <a class="learn-more-pm" target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">Learn More</a>
                                                        </div>
                                                    </div>
                                                <?php 
                                                }else{ ?>
                                                    <div class="single-activity-description row">
                                                        <div class="col-1 single-activity-icon info-icon">
                                                            <i class="fa fa-info" aria-hidden="true"></i>
                                                        </div>
                                                        <div class="col-10 info-detail">
                                                                No Description Available
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                                </div><!-- .description -->

                                                <div class="registration" id="details-content-<?php echo $i ?>">
                                                    <?php 
                                                    if ($brandNewArray[$i]['LocationName']): ?>
                                                        <div class="location row">
                                                            <div class="col-1 single-activity-icon location-icon">
                                                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                                                            </div>
                                                            <div class="col-10 location-detail">
                                                                <?php echo $brandNewArray[$i]['LocationName']; ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php 
                                                    if ($brandNewArray[$i]['InstructorName']){ ?>
                                                        <div class="instructor row">
                                                            <div class="col-1 single-activity-icon instructor-icon">
                                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                            </div>
                                                            <div class="col-10 instructor-detail">
                                                                <strong><?php echo $brandNewArray[$i]['InstructorName']; ?></strong>
                                                            </div>
                                                        </div>
                                                    <?php 
                                                    }else{ ?>
                                                        <div class="instructor row">
                                                            <div class="col-1 single-activity-icon instructor-icon">
                                                                <i class="fa fa-user" aria-hidden="true"></i>
                                                            </div>
                                                            <div class="col-10 instructor-detail">
                                                                <strong> No Instructor</strong>
                                                            </div>
                                                        </div>
                                                    <?php 
                                                    } 
                                                    
                                                    if ($brandNewArray[$i]['Capacity']): ?>
                                                        <div class="capacity row">
                                                            <div class="col-1 single-activity-icon capacity-icon">
                                                                <i class="fa fa-users" aria-hidden="true"></i>
                                                            </div>
                                                            <div class="col-10 capacity-detail">
                                                                <?php echo $brandNewArray[$i]['Remaining']; ?> spaces available (<?php echo $brandNewArray[$i]['Capacity']; ?> total)
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php echo $brandNewArray[$i]['RegistrationEndDateOriginal'][0]; ?>
                                                    <?php 
                                                    if ($brandNewArray[$i]['ProgramDates']): ?>
                                                        <div class="registration-start row">
                                                            <div class="col-1 single-activity-icon start-date-icon">
                                                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                                            </div>
                                                            <div class="col-10 start-date-detail">
                                                            <?php 
                                                            $numberOfDates =  count($brandNewArray[$i]['ProgramDates']);
                                                            if ( $numberOfDates !== 1 ) { 
                                                                echo $brandNewArray[$i]['ProgramDates'][0];  ?> - <?php echo end($brandNewArray[$i]['ProgramDates']) . ',';
                                                                echo count($brandNewArray[$i]['ProgramDates']) . ' sessions';
                                                            } else{ 
                                                                $brandNewArray[$i]['ProgramDates'][0] . ',';
                                                                echo count($brandNewArray[$i]['ProgramDates']) . ' session';
                                                            } 
                                                            ?>
                                                            </div>
                                                        </div>
                                                    <?php 
                                                    endif; 
                                                    ?>
                                                </div><!-- .registration -->
                                            </div><!-- details and registration wrapper-->
                                            <?php 
                                            if($dateTime < $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0   ){ 
                                            ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Register on <?php echo date("M d", strtotime( $brandNewArray[$i]['RegistrationStartDateOriginal'][0])) ;?></button>
                                                </a>
                                            </div>
                                            <?php 
                                            } elseif($dateTime >= $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0 ){ ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
                                                </a>
                                            </div>
                                            <?php 
                                            } elseif ($brandNewArray[$i]['Remaining'] == 0 && $brandNewArray[$i]['WaitListCapacity'] > 0 ){ ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Join Waitlist</button>
                                                </a>
                                            </div>
                                            <?php 
                                            } elseif ($brandNewArray[$i]['Remaining'] == 0 && $brandNewArray[$i]['Remaining'] !== null && $brandNewArray[$i]['WaitListCapacity'] == 0 ){ ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Registration Full </button>
                                                </a>
                                            </div>
                                            <?php 
                                            }elseif ($dateTime < $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null  ){ ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Register on <?php echo date("M d", strtotime( $brandNewArray[$i]['RegistrationStartDateOriginal'][0])) ;?></button>
                                                </a>
                                            </div>
                                            <?php 
                                            } elseif ($dateTime >= $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null ){ ?>
                                            <div class="button-wrapper-for-program-loop">
                                                <?php $eventID = $brandNewArray[$i]['ID']; ?>
                                                <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                                                    <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
                                                </a>
                                            </div>
                                            <?php 
                                            } ?>
                                        </div><!-- .events.content -->
                                    </div><!-- .frontpage-card -->
                                </div><!-- .activities-loop -->
                            </li>
                        <?php endfor; ?>
                        </ul>
                        <div class="number-results">
                            <p><?php echo count($brandNewArray); ?> Results</p>
                        </div>
                    </div>
                </div>

                <div class="top_label view-news-button view-events-calendar">
                    <div class="center-link paddingbox">
                        <a class="button-link" href="/programs/"><i class="fa fa-angle-right" aria-hidden="true"></i>See All Programs</a>
                    </div>
                </div>

            <?php 
            } 
            
            return ob_get_clean();
        }

        function all_programs($atts = array(), $content = null) {
            extract(shortcode_atts(array(
                'rating' => '5'
            ), $atts));
            ob_start();

            $apiData = new MyunaAPIData();
            $featured_programs = $apiData->loaddb('featured_programs');

            if($featured_programs == null) {
                return "No Programs";
            }

            $springPrograms = $featured_programs['spring'];
            $summerPrograms = $featured_programs['summer'];
            $fallPrograms = $featured_programs['fall'];
            $winterPrograms = $featured_programs['winter'];

            $gmtNow = date('Y-m-d H:i:s'); 
            $fallStart = '2021-09-01 07:00:00';
            $summerStart = '2021-06-28 08:00:00';
            $fallDisplayTime = '2021-08-06 19:00:00';
            $newFallDisplayTime = '2021-11-08 20:00:00';

            if ($gmtNow < $newFallDisplayTime ) { 
                $springAndSummer= array_merge( $fallPrograms);
            } else {
                $springAndSummer= array_merge( $fallPrograms, $winterPrograms);
            }

            $dt = new DateTime();
            $tz = new DateTimeZone('America/Vancouver'); // or whatever zone you're after
            $dt->setTimezone($tz);
            $dateTime= $dt->format('Y-m-d');

            $allPrograms = array();
            $allSeasons = array();
            $allGroups = array();
            $allAvailability = array();
            $allLocations = array();

            foreach ($springAndSummer as $rkey => $resource){
                $brandNewArray[] = $resource;
            }


            // Programs
            for ($i=0; $i < count($brandNewArray); $i++) {
                if(!empty($brandNewArray[$i]['CalendarName'])){
                    array_push($allPrograms,$brandNewArray[$i]['CalendarName']);
                }
            }
            $uniquePrograms = array_unique($allPrograms);

            //Seasons
            for ($i=0; $i < count($brandNewArray); $i++) {
                if(!empty($brandNewArray[$i]['Season'])){
                    array_push($allSeasons,$brandNewArray[$i]['Season']);
                }
            }
            $uniqueSeasons = array_unique($allSeasons);

            //Capacity
            for ($i=0; $i < count($brandNewArray); $i++) {
                if(!empty($brandNewArray[$i]['Participants'])){
                array_push($allGroups,$brandNewArray[$i]['Participants']);
                }
            }
            $uniqueGroups = array_unique($allGroups);

            //Availability
            for ($i=0; $i < count($brandNewArray); $i++) {
                if(!empty($brandNewArray[$i]['Availability'])){
                    array_push($allAvailability,$brandNewArray[$i]['Availability']);
                }
            }
            $uniqueAvailability = array_unique($allAvailability);

            //Location
            for ($i=0; $i < count($brandNewArray); $i++) {
                if(!empty($brandNewArray[$i]['LocationName'])){
                    array_push($allLocations,$brandNewArray[$i]['LocationName']);
                }
            }
            $uniqueLocations = array_unique($allLocations);
            ?>

            <!-- Filter -->
            <div class="programs-filter">
                <div class="container">
                    <div class="row">
                        <div class="col-12 programs-filter-title">
                            <h2>Our Programs</h2>
                            <div>
                                <p><input type="text" id="quicksearch" placeholder="Search" /></p>
                                <p><span style="cursor: pointer" id="btn_show_filter" status="hide">Show Filter</span></p>
                            </div>
                        </div>
                    </div>
                    <form action="#" id="filter_form" style="display: none">
                        <div class="row dropdowns">
                            <p class="filter-by">Filter By</p>
                            <select name="season" id="filter-season">
                                <option value="*">All Seasons</option>
                                <?php
                                foreach ($uniqueSeasons as $season) {
                                    echo '<option value=".'.plugin_slugify($season).'">'. $season. '</option>';
                                }
                                ?>
                            </select>

                            <i class="fas fa-plus"></i>
                            <select name="season" id="filter-age">
                                <option value="*">All Ages</option>
                                <option value=".early-years">Early Years</option>
                                <option value=".children">Children</option>
                                <option value=".youth">Youth</option>
                                <option value=".adults-seniors">Adults & Seniors</option>
                            </select>

                            <i class="fas fa-plus"></i>
                            <select name="activity" id="filter-activity">
                                <option value="*">All Activities</option>
                                <?php
                                foreach ($uniquePrograms as $program) {
                                    echo '<option value=".'.plugin_slugify($program).'">'. $program. '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="row dropdowns">
                            <i class="fas fa-plus"></i>
                            <select name="groups" id="filter-groups">
                                <option value="*">All Groups</option>
                                <?php
                                foreach ($uniqueGroups as $group) {
                                    echo '<option value=".'.plugin_slugify($group).'">'. $group. '</option>';
                                }
                                ?>
                            </select>

                            <i class="fas fa-plus"></i>
                            <select name="space" id="filter-space">
                                <option value="*">All Availabilies</option>
                                <?php
                                foreach ($uniqueAvailability as $availability) {
                                    echo '<option value=".'.plugin_slugify($availability).'">'. $availability. '</option>';
                                }
                                ?>
                            </select>

                            <i class="fas fa-plus"></i>
                            <select name="location" id="filter-location">
                            <option value="*">All Locations</option>
                                <?php
                                foreach ($uniqueLocations as $location) {
                                    echo '<option value=".'.plugin_slugify($location).'">'. $location. '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <p class="choose-days"><i class="fas fa-plus"></i> choose your days</p>
                                <div class="day-checks multi-selects">
                                    <input type="checkbox" id="weekdays" name="weekdays" value=".day-mon .day-tue .day-wed .day-thu .day-fri"><label for="weekdays">Weekdays</label>
                                    <input type="checkbox" id="weekends" name="weekends" value=".day-sat .day-sun"><label for="weekends">Weekends</label>
                                </div>
                                <div class="day-checks weekdays">
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekday" id="monday" name="monday" value=".day-mon">
                                        <label for="monday">Monday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekday" id="tuesday" name="tuesday" value=".day-tue">
                                        <label for="tuesday">Tuesday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekday" id="wednesday" name="wednesday" value=".day-wed">
                                        <label for="wednesday">Wednesday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekday" id="thursday" name="thursday" value=".day-thu">
                                        <label for="thursday">Thursday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekday" id="friday" name="friday" value=".day-fri">
                                        <label for="friday">Friday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekend" id="saturday" name="saturday" value=".day-sat">
                                        <label for="saturday">Saturday</label>
                                    </div>
                                    <div class="day-checks-wrapper">
                                        <input type="checkbox" class="weekend" id="sunday" name="sunday" value=".day-sun">
                                        <label for="sunday">Sunday</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="filtered-programs-section" id="activities_loop_1" style="display: none">
                <div class="toggle-button-container">
                    <li class="toggle-program-button" id="grid-button">Grid View</button>
                    <li class="toggle-program-button" id="list-button">List View</button>
                </div>

                <!-- Flickity View -->
                <div class="main-carousel-wrap" style="position: relative;">
                    <div class="number-results">
                        <p><?php echo count($brandNewArray); ?> Results</p>
                    </div>
                    <ul class="main-carousel filtered-programs-container">
                    <?php 
                    for ($i=0; $i < count($brandNewArray); $i++):
                        include('templates/single-activities-in-loop.php');
                    endfor; 
                    ?>
                    </ul>
                </div>

                <!-- List View -->
                <ul class="filtered-programs-container filtered-programs-list-container" style="opacity: 1;">
                    <?php 
                    for ($i=0; $i < count($brandNewArray); $i++):
                        include('templates/single-activities-in-loop-list.php');
                    endfor; 
                    ?>
                </ul>

                <div class="no-programs">
                    <div class="container">
                        <div class="alert-container">
                            <div class="alert-icon">
                                <img src="<?php custom_url(); ?>/images/attention-icon-plain.svg" class="attention-icon">
                            </div>
                            <?php    
                            $alertText = get_field('no_program_alert_title_programs', 'option');
                            $alertLink = get_field('no_program_alert_content_programs', 'option'); 
                            ?>

                            <div class="alert-box">
                            <?php 
                            if($alertText){ 
                            ?>
                                <h4><?php echo $alertText;?></h4>
                            <?php 
                            }
                            if($alertLink){
                                echo $alertLink; 
                            } 
                            ?>
                            </div>
                        </div>
                    </div>
                </div><!-- end of no programs-->
            </div>
            <?php

            return ob_get_clean();
        }
    }
}
?>