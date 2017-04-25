<!DOCTYPE html>
<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);
session_start();
ob_start();

if( !isset($_SESSION['user']) ) {
 header("Location: index.php");
 exit;
}
if( !isset($_SESSION['authcode']) ) {
 header("Location: index.php");
 exit;
}
$authcode = $_SESSION["authcode"];
$user_id = $_SESSION['user'];
$clientid = "79b15454f84bc616792fc99d6b9104e8";
$sc_results_raw = file_get_contents("http://api.soundcloud.com/tracks.json?q=".urlencode($_GET["query"])."&client_id=".$clientid);
$sc_results = json_decode($sc_results_raw);
 ?>

<html lang="en">
    <head>
    <style type="text/css">
    html,body {
    background: url("http://crescendiagame.com/bg.png") repeat center center fixed;
    -webkit-background-size: cover; /* For WebKit*/
    -moz-background-size: cover;    /* Mozilla*/
    -o-background-size: cover;      /* Opera*/
    background-size: cover;         /* Generic*/
    }
    </style>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="http://crescendiagame.com/css/style.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <link rel="stylesheet" href="./css/font-awesome.min.css">
    </head>
    <body>
      <nav class="navbar navbar-inverse">
  <div class="container-fluid">
    <div class="navbar-header">
      <a class="navbar-brand" href="#">Crescendia Prototype</a>
    </div>
    <ul class="nav navbar-nav">
      <li><a href="http://crescendiagame.com/user/home.php">User Home</a></li>
      <li class="active"><a href="#">Song Search</a></li>
    </ul>
  </div>
</nav>

      <div class="container-fluid center-block" style="width:100%">
        <div class="row center-block" style="max-width:1200px">
          <div class="alert alert-warning">
        <h2>Soundcloud Results for: "<?php echo $_GET["query"]; ?>"</h2>
        </div>
        <?php
        foreach($sc_results AS $song)
        {
        ?>
        <div class="row panel panel-primary">
        <div class="panel-heading">
        <h1><?php echo $song->title; ?></h1>
        </div>
        <div class="panel-body">
          <div class="col-sm-10">
            <iframe width="100%" height="200" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=https%3A//api.soundcloud.com/tracks/<?php echo $song->id; ?>&amp;auto_play=false&amp;hide_related=false&amp;show_comments=true&amp;show_user=true&amp;show_reposts=false&amp;visual=true"></iframe>
        </div>
        <div class="col-sm-2 fill">
          <a class="btn btn-block btn-info" style="min-height: 100%;height: 100%;" href="http://crescendiagame.com:8080/units/get_soundcloud?sc_id=<?php echo $song->id;?>&user_id=<?php echo $user_id;?>&auth=<?php echo $authcode;?>">Analyze</a>
      </div>
        </div>
      </div>
        <?php
        }
        ?>

      </div>
</div>
</body>
</html>
