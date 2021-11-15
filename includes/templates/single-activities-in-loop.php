<?php
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
  $startSeason = strtolower(get_season("$startDate", "northern"));
  $endSeason = strtolower(get_season("$endDate", "northern"));

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

  $differentCategories = slugify($brandNewArray[$i]['CalendarCategory']);


  $lowerProgram = slugify($brandNewArray[$i]['CalendarName']);

  $seasons = slugify($brandNewArray[$i]['Season']);


  $groupCapacity = slugify($brandNewArray[$i]['Participants']);
  $classSpace = slugify($brandNewArray[$i]['Availability']);
  $classLocation = slugify($brandNewArray[$i]['LocationName']);

?>

<li class="carousel-cell program <?php echo $dayClasses . " " .$differentCategories . " " .$seasons . " " .$groupCapacity . " " .$classSpace . " " .$classLocation . " " .$lowerProgram; ?>">
  <div class="activities-loop event-column">
    <div class="frontpage-card">

      <?php $defaultURL = get_site_url() . "/assets/media/custom_images/una-default-image.png"; ?>
      <?php if( $brandNewArray[$i]['ImageOriginal'] ): ?>
        <div class="post_image" style="background-image: url('<?php echo $brandNewArray[$i]['ImageOriginal']; ?>');"></div>
      <?php else: ?>
        <div class="post_image" style="background-image: url('<?php echo $defaultURL; ?>');"></div>
      <?php endif; ?>


      <div class="events-content">

        <div class="events-subject">
        <h3 class="frontpage-card-title"><?php echo mb_strimwidth( $text= $brandNewArray[$i]['Subject'],  0, 32, '...'); ?></h3>

          <h4 class="frontpage-card-category">
          <div class="row">
          <?php if( $brandNewArray[$i]['MinimumAge'] && $brandNewArray[$i]['MaximumAge'] && $brandNewArray[$i]['CourseID'] ){ ?>
            <div class="col-6">
            <span class="program-number">Ages <?php echo $brandNewArray[$i]['MinimumAge'];?> - <?php echo $brandNewArray[$i]['MaximumAge'];?> </span>
          </div>
          <div class="col-6 program-course-number">
            <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
          </div>
          <?php } elseif( $brandNewArray[$i]['MinimumAge'] && $brandNewArray[$i]['CourseID']) { ?>

            <div class="col-6">
            <span class="program-number">Ages <?php echo $brandNewArray[$i]['MinimumAge'];?> + </span>
          </div>
          <div class="col-6 program-course-number">
            <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
          </div>

          <?php } elseif( $brandNewArray[$i]['MaximumAge'] && $brandNewArray[$i]['CourseID']) { ?>
            <div class="col-6">
            <span class="program-number">Ages Under <?php echo $brandNewArray[$i]['MaximumAge'];?> </span>
          </div>
          <div class="col-6 program-course-number">
            <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
          </div>
          <?php } elseif( $brandNewArray[$i]['CourseID']){ ?>

          <div class="col-12 program-course-number course-id-only">
            <span class="program-number"> #<?php echo $brandNewArray[$i]['CourseID'];?></span>
          </div>
          <?php } ?>
        </div>
          </h4>


      <div class="row program-date">
      <div class="col-1">
          <i class="fa fa-clock-o" aria-hidden="true"></i>
      </div>
      <div class="col-10">

  <?php  $prefix = $fruitList = ''; ?>

        <span class="program-date">
          <?php  foreach ( $uniqueDatesOfWeek as $key => $val) {
                $fruitList .= $prefix . date('D', strtotime($val));
                $prefix = ', ';
          } ?>
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
          <?php if ($brandNewArray[$i]['Description']){ ?>
            <div class="single-activity-description row">
              <div class="col-1 single-activity-icon info-icon">
                <i class="fa fa-info" aria-hidden="true"></i>
              </div>
              <div class="col-10 info-detail">
                <?php echo wp_trim_words( $text= $brandNewArray[$i]['Description'], $num_words = 20, $more = "..."); ?>
        <a class="learn-more-pm" target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
                Learn More
          </a>
              </div>
            </div>
          <?php }else{ ?>
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

          <?php if ($brandNewArray[$i]['LocationName']): ?>
            <div class="location row">
              <div class="col-1 single-activity-icon location-icon">
                <i class="fa fa-map-marker" aria-hidden="true"></i>
              </div>
              <div class="col-10 location-detail">
                <?php echo $brandNewArray[$i]['LocationName']; ?>
              </div>
            </div>
          <?php endif; ?>

        <?php if ($brandNewArray[$i]['InstructorName']){ ?>
        <div class="instructor row">
              <div class="col-1 single-activity-icon instructor-icon">
              <i class="fa fa-user" aria-hidden="true"></i>
              </div>
              <div class="col-10 instructor-detail">
                <strong><?php echo $brandNewArray[$i]['InstructorName']; ?></strong>
              </div>
            </div>
        <?php }else{ ?>
          <div class="instructor row">
              <div class="col-1 single-activity-icon instructor-icon">
              <i class="fa fa-user" aria-hidden="true"></i>
              </div>
              <div class="col-10 instructor-detail">
              <strong> No Instructor</strong>
              </div>
            </div>

        <?php } ?>

          <?php if ($brandNewArray[$i]['Capacity']): ?>
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


          <?php if ($brandNewArray[$i]['ProgramDates']): ?>
            <div class="registration-start row">
              <div class="col-1 single-activity-icon start-date-icon">
                <i class="fa fa-calendar" aria-hidden="true"></i>
              </div>
              <div class="col-10 start-date-detail">
            <?php $numberOfDates =  count($brandNewArray[$i]['ProgramDates']); ?>
            <?php if ( $numberOfDates !== 1 ) { ?>
                  <?php echo $brandNewArray[$i]['ProgramDates'][0];  ?> - <?php echo end($brandNewArray[$i]['ProgramDates']) . ',';  ?>
                  <?php echo count($brandNewArray[$i]['ProgramDates']) . ' sessions';  ?>
            <?php } else{ ?>
              <?php echo $brandNewArray[$i]['ProgramDates'][0] . ',';?>
                  <?php echo count($brandNewArray[$i]['ProgramDates']) . ' session';  ?>
            <?php } ?>
              </div>
            </div>
          <?php endif; ?>


        </div><!-- .registration -->
        </div><!-- details and registration wrapper-->
        
         <!-- when daylight saving starts make adjustment -->
        


<?php //Get the current timestamp.
$currentTime = time();

//The number of hours that you want
//to subtract from the date and time.
$hoursToSubtract = 7;

//Convert those hours into seconds so
//that we can subtract them from our timestamp.
$timeToSubtract = ($hoursToSubtract * 60 * 60);

//Subtract the hours from our Unix timestamp.
$timeInPast = $currentTime - $timeToSubtract;

//Print it out in a human-readable format.
$timeRegister = date("Hi", $timeInPast); ?>

  <?php if($dateTime < $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0   ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register on <?php echo date("M d", strtotime( $brandNewArray[$i]['RegistrationStartDateOriginal'][0])) ;?></button>
        </a>
        </div>
        <?php } elseif($dateTime == $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0 && $timeRegister < 1200 ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register at 12PM</button>
        </a>
        </div>
        <?php } elseif($dateTime == $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0 && $timeRegister >= 1200 ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
        </a>
        </div>
        <?php } elseif($dateTime > $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] > 0 ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
        </a>
        </div>
        <?php } elseif ($brandNewArray[$i]['Remaining'] == 0 && $brandNewArray[$i]['WaitListCapacity'] > 0 ){ ?>
          <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Join Waitlist</button>
        </a>
        </div>
        <?php } elseif ($brandNewArray[$i]['Remaining'] == 0 && $brandNewArray[$i]['Remaining'] !== null && $brandNewArray[$i]['WaitListCapacity'] == 0 ){ ?>
          <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Registration Full </button>
        </a>
        </div>
        <?php }elseif ($dateTime < $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null  ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register on <?php echo date("M d", strtotime( $brandNewArray[$i]['RegistrationStartDateOriginal'][0])) ;?></button>
        </a>
        </div>
        <?php } elseif ($dateTime == $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null && $timeRegister < 1200  ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register at 12PM</button>
        </a>
        </div>
        <?php } elseif ($dateTime == $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null && $timeRegister >= 1200  ){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
        </a>
        </div>
        <?php }elseif ($dateTime > $brandNewArray[$i]['RegistrationStartDateOriginal'][0] && $brandNewArray[$i]['Remaining'] == null && $brandNewArray[$i]['WaitListCapacity'] == null){ ?>
        <div class="button-wrapper-for-program-loop">
        <?php $eventID = $brandNewArray[$i]['ID']; ?>
          <a target="_blank" rel="noopener noreferrer" href="https://myuna.perfectmind.com/SocialSite/BookMe4LandingPages/CoursesLandingPage?courseId=<?php echo $brandNewArray[$i]['ID']; ?>">
          <button class="register-button" id="button <?php echo $i; ?>">Register Now</button>
        </a>
        </div>
        <?php }?>


       
      </div><!-- .events.content -->

    </div><!-- .frontpage-card -->
  </div><!-- .activities-loop -->
        </li>