<?php
  session_start();

  include($_SERVER["DOCUMENT_ROOT"]."/code/php/AC.php");
  $user_name = check_logged(); /// function checks if visitor is logged.
  $admin = false;

  if ($user_name == "") {
    // user is not logged in
    return;
  } else {
    if ($user_name == "admin")
      $admin = true;
    echo('<script type="text/javascript"> user_name = "'.$user_name.'"; </script>'."\n");
    echo('<script type="text/javascript"> admin = '.($admin?"true":"false").'; </script>'."\n");
  }
  
  $permissions = list_permissions_for_user( $user_name );

  // find the first permission that corresponds to a site
  // Assumption here is that a user can only add assessment for the first site he has permissions for!
  $site = "";
  foreach ($permissions as $per) {
     $a = explode("Site", $per); // permissions should be structured as "Site<site name>"

     if (count($a) > 0) {
        $site = $a[1];
	break;
     }
  }
  if ($site == "") {
     echo (json_encode ( array( "message" => "Error: no site assigned to this user" ) ) );
     return;
  }

  // if there is a running session it would have the follow information
  $subjid = "";
  $sessionid = "";
  $run = "";
  if( isset($_SESSION['ABCD']) && isset($_SESSION['ABCD']['stroop']) ) {
     if (isset($_SESSION['ABCD']['stroop']['subjid'])) {
        $subjid  = $_SESSION['ABCD']['stroop']['subjid'];
     }
     if (isset($_SESSION['ABCD']['stroop']['sessionid'])) {
        $sessionid  = $_SESSION['ABCD']['stroop']['sessionid'];
     }
     if (isset($_SESSION['ABCD']['stroop']['run'])) {
        $run  = $_SESSION['ABCD']['stroop']['run'];
     }
  }

  echo('<script type="text/javascript"> subjid  = "'.$subjid.'"; </script>'."\n");
  echo('<script type="text/javascript"> session = "'.$sessionid.'"; </script>'."\n");
  echo('<script type="text/javascript"> run     = "'.$run.'"; </script>'."\n");
  echo('<script type="text/javascript"> site    = "'.$site.'"; </script>'."\n");
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>ABCD's Stroop Task</title>

  <!-- Bootstrap Core CSS -->
  <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

  <!-- Custom CSS -->
  <!-- required for the date and time pickers -->
  <link href="css/bootstrap-datetimepicker.css" rel="stylesheet" type="text/css">

  <!-- <link rel='stylesheet' href='//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.min.css' /> -->
  <!-- media="print" is required to display the fullcalendar header buttons -->
  <!-- <link rel='stylesheet' media='print' href='//cdnjs.cloudflare.com/ajax/libs/fullcalendar/2.6.0/fullcalendar.print.css' /> -->

  <link rel="stylesheet" href="css/style.css">

</head>

<body>

  <nav class="navbar navbar-default">
  <div class="container-fluid">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1" aria-expanded="false">
        <span class="sr-only">Toggle navigation</span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="#">Stroop Task</a>
    </div>

    <!-- Collect the nav links, forms, and other content for toggling -->
    <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
      <ul class="nav navbar-nav">
        <li class="active"><a href="/index.php" title="Back to report page">Report</a></li>
      </ul>
      <ul class="nav navbar-nav navbar-right">
        <li><a href="#" class="connection-status" id="connection-status">Connection Status</a></li>
        <li class="dropdown">
          <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span id="session-active">User</span> <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="#" id="user_name"></a></li>
            <li><a href="#" class="subject-id"></a></li>
            <li><a href="#" class="session-id"></a></li>
            <li><a href="#" class="run-id"></a></li>
            <li role="separator" class="divider"></li>
            <li><a href="#" onclick="closeSession();">Close Session</a></li>
            <li><a href="#" onclick="logout();">Logout</a></li>
          </ul>
        </li>
      </ul>
    </div><!-- /.navbar-collapse -->
  </div><!-- /.container-fluid -->
</nav>

  <!-- start session button -->
  <section id="admin-top" class="bg-light-gray">
    <div class="container">
      <div class="row" style="margin-bottom: 20px;"></div>
      <div class="row start-page">
        <div class="col-md-12">
          <div class="date">Adolescent Brain Cognitive Development</div>
	  <div style='position: relative;'>
	    <h1>ABCD's Stroop Test</h1>
	    <div class='date2'>March 2016</div>
	  </div>
	  <p>It is difficult to name the color of a word printed in different inks if the word itself denotes a color. The word "<span style='color: green;'>red</span>" written with green ink can be more easily read as 'red' than its color can be named as 'green'. This effect creates a reaction time delay that can be measured using the Stroop test. After an initial training phase this application will try to measure the delay between naming correctly and incorrectly colored words.</p><br/><p>Source code for this assessment has been created using jsPsych and can be viewed on <a href='https://github.com/ABCD-STUDY/stroop'>github</a>.
	  </p>
        </div>
      </div>
      <div class="row" style="margin-bottom: 20px;"></div>
      <div class="row">
        <div class="col-md-12">
          <center>
            <button class="btn btn-primary btn-lg" data-toggle="modal" data-target="#defineSession" title="Create new session and redirect to the Stroop Task page.">New Session</button>
          </center>
        </div>
      </div>
    </div>
  </section>

  <div class="portfolio-modal modal fade" id="saveSession" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-content">
      <div class="close-modal" data-dismiss="modal">
        <div class="lr">
          <button class="close">x</button>
        </div>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="modal-body">
              <h3>Finish and upload the current session?</h3>
              <form name="sentMessage" id="sessionInfoForm" novalidate>
                <div class="col-md-6">

                  <div class="form-group">
                    <label for="session-participant" class="control-label">Confirm Participant ID</label>
                    <input type="text" class="form-control" placeholder="NDAR-#####" id="session-participant-again" required data-validation-required-message="Please enter the participant NDAR ID.">
                    <p class="help-block text-danger"></p>
                  </div>

                  <button id="save-session-button" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-save"></i> Save Session</button> &nbsp;
                  <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Back</button>&nbsp;
                </div>
              </form>
            </div><!-- /.modal-body -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
      </div><!-- /.container -->
    </div><!-- /.modal-content -->
  </div><!-- /.portfolio-modal -->
  
  <!-- define session -->
  <div class="portfolio-modal modal fade" id="defineSession" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-content">
      <div class="close-modal" data-dismiss="modal">
        <div class="lr">
          <button class="close" style="font-size: 52px;margin-right: 20px;">x</button>
        </div>
      </div>
      <div class="container">
        <div class="row">
          <div class="col-lg-12">
            <div class="modal-body">
              <h3>Assessment Setup</h3>
              <form name="sentMessage" id="sessionInfoForm" novalidate>
                <div class="col-md-6">

                  <div class="form-group">
                    <label for="session-participant" class="control-label">Participant</label>
                    <!-- <input type="text" class="form-control" placeholder="NDAR-#####" id="session-participant" required data-validation-required-message="Please enter the participant NDAR ID." autofocus> -->
		    <select class="form-control" id="session-participant"></select>
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-name" class="control-label">Session name</label>
                    <!-- <input type="text" class="form-control" placeholder="Baseline-01" id="session-name" required data-validation-required-message="Please enter the session ID."> -->
		    <select class="form-control" id="session-name"></select>
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-run" class="control-label">Session run</label>
		    <select class="form-control" id="session-run">
		      <option value="01">01</option>
		      <option value="02">02</option>
		      <option value="03">03</option>
		    </select>		  
                    <p class="help-block text-danger"></p>
                  </div>

                  <div class="form-group">
                    <label for="session-date" class="control-label">Session Date</label>
                    <div class='input-group date' id='session-date-picker'>
                      <input type='text' data-format="MM/dd/yyyy HH:mm:ss PP" id="session-date" class="form-control" placeholder="(TODO: Fill in with the current date)" />
                      <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                      </span>
                    </div>
                  </div>

                  <div class="clearfix"></div>
                </div>
              </form>
	    </div>
          </div>
	</div>
        <div class="row">
          <div class="col-lg-12">
            <div class="modal-body">
              <div>
                <button id="open-stroop1-button" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-save"></i> Start Stroop-Task (touch)</button> &nbsp;
<?php if ($admin): ?>
    <button id="open-stroop2-button" type="button" class="btn btn-success" data-dismiss="modal"><i class="fa fa-save"></i> Start Stroop-Task (keyboard)</button> &nbsp;
<?php endif; ?>
                <button type="button" class="btn btn-default" data-dismiss="modal"><i class="fa fa-times"></i> Back</button>&nbsp;
              </div>
            </div><!-- /.modal-body -->
          </div><!-- /.col-lg-12 -->
        </div><!-- /.row -->
      </div><!-- /.container -->
    </div><!-- /.modal-content -->
  </div><!-- /.portfolio-modal -->

  <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  <script src='js/moment.min.js'></script>

  <!-- Bootstrap Core JavaScript -->
  <script src="js/bootstrap.min.js"></script>

  <script src="js/bootstrap-datetimepicker.js"></script>
  <script src="js/bootstrap-datepicker.js"></script>

  <script type="text/javascript" src="js/all.js"></script>

</body>

</html>
