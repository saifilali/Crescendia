<!DOCTYPE html>
<?php
//ini_set('display_errors', 'On');
//error_reporting(E_ALL | E_STRICT);
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
        <h2>Search Results for: "<?php echo $_GET["query"]; ?>"</h2>
      </div>
      </br><?php


        $guilds_list = json_decode(file_get_contents("http://crescendiagame.com:8080/guild/search?query=".urlencode($_GET["query"])));
        echo $guilds_list;
        foreach($guilds_list AS $guild)
        {
            ?>
            <div class="row panel panel-primary">
              <div class="panel-heading">Guild name:<?php echo $guild->name;?>
              </div>
              <div class="panel-body">Guild description:<?php echo $guild->description;?>
                <a class="btn btn-warning pull-right" href="http://crescendiagame.com:8080/user/join_guild?user_id=<?php echo $user_id.'&guild_id='.$guild->guild_id.'&auth='.$authcode;?>">Join Guild</a>
              </div>
             </div>
           </br><?php
        }
        ?>
</div>
</div>
</body>
</html>
