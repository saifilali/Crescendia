<?php
session_start();
ob_start();
ini_set('display_errors', 'On');
error_reporting(E_ALL | E_STRICT);

if ( isset($_SESSION['user'])!="" && isset($_SESSION['authcode'])!="" ) {
 header("Location: user/home.php");
 exit;
}
 $error = false;

if( isset($_GET['comment']) ) {
 if($_GET['comment']=="registration_success")
 {
   $succMSG = "You have successfully registered! Now log in!";
}
 if($_GET['comment']=="wrong_code")
 {
   $errMSG = "Alpha Key is wrong";
 }
 if($_GET['comment']=="wrong")
 {
   $errMSG = "Username or Password was wrong";
 }
 if($_GET['comment']=="missing_field")
 {
 $errMSG = "One of more fields was missing";
}
 if($_GET['comment']=="broke")
 {
$errMSG =" Something broke.";
 }
 if($_GET['comment']=="taken")
{
   $errMSG = "That email address or username has already been registered.";
 }
 }

 if( isset($_POST['btn-login']) ) {

  $username = trim($_POST['username']);
  $username = strip_tags($username);
  $username = htmlspecialchars($username);


  $pass = trim($_POST['password']);
  $pass = strip_tags($pass);
  $pass = htmlspecialchars($pass);
$passwordmd5 = md5($pass);
    $passwordsha = hash('sha256', $pass);
  //Try to log in I guess and if it works, redirect nyaa
  $logintry = json_decode(file_get_contents("http://crescendiagame.com:8080/user/login?auth=".$passwordsha."&username=".$username));
  $errMSG = $logintry;
  if($logintry[0]->username == $username)
  {
    $_SESSION['authcode'] = $passwordsha;
    $_SESSION['user'] = $logintry[0]->user_id;
      $errMSG = "login worked but it should've redirected you".$_SESSION['authcode']." ".$_SESSION['user'];

  header("Location: /user/home.php");
  exit;
  }
  else {
    $logintry = json_decode(file_get_contents("http://crescendiagame.com:8080/user/login?auth=".$passwordmd5."&username=".$username));
    $errMSG = $logintry;
    if($logintry[0]->username == $username)
    {
      $_SESSION['authcode'] = $passwordmd5;
      $_SESSION['user'] = $logintry[0]->user_id;
        $errMSG = "login worked but it should've redirected you".$_SESSION['authcode']." ".$_SESSION['user'];

    header("Location: /user/home.php");
    exit;
    }
    else {
      $error = true;
    $errMSG = "Username or Password was wrong";
    }
  }

}

if( isset($_POST['btn-register']) ) {

 $username = trim($_POST['username']);
 $username = strip_tags($username);
 $username = htmlspecialchars($username);


 $pass = trim($_POST['password']);
 $pass = strip_tags($pass);
 $pass = htmlspecialchars($pass);

 $passconf = trim($_POST['passconf']);
 $passconf = strip_tags($passconf);
 $passconf = htmlspecialchars($passconf);

 $email = trim($_POST['email']);
 $email = strip_tags($email);
 $email = htmlspecialchars($email);

 $alphakey = trim($_POST['regcode']);
 $alphakey = strip_tags($alphakey);
 $alphakey = htmlspecialchars($alphakey);

 if ($pass != $passconf){
   $error = true;
   $errMSG = "Password confirmation does not match.";
  }
  if (empty($pass)){
     $error = true;
     $errMSG = "Please enter password.";
    } else if(strlen($pass) < 6) {
     $error = true;
     $errMSG = "Password must have atleast 6 characters.";
    }
    if (empty($passconf)){
       $error = true;
       $errMSG = "Please enter password confirmation.";
      }
  if ( !filter_var($email,FILTER_VALIDATE_EMAIL) ) {
   $error = true;
   $errMSG = "Please enter valid email address.";
  }

    $password = hash('sha256', $pass);

    if($error == false)
    {
    $registertry = file_get_contents("http://crescendiagame.com:8080/user/register?auth=".$password."&username=".$username."&alphakey=".$alphakey."&email=".$email);
    $errMSG = $registertry;
    if($registertry == "200")
    {
      $_SESSION['authcode'] = $password;
      $_SESSION['user'] = $registertry[0]->user_id;
      $errMSG = "login worked but it should've redirected you".$_SESSION['authcode']." ".$_SESSION['user'];

      $logintry = json_decode(file_get_contents("http://crescendiagame.com:8080/user/login?auth=".$password."&username=".$username));
      $errMSG = $logintry;
      if($logintry[0]->username == $username)
      {
        $_SESSION['authcode'] = $password;
        $_SESSION['user'] = $logintry[0]->user_id;
          $errMSG = "login worked but it should've redirected you".$_SESSION['authcode']." ".$_SESSION['user'];

      header("Location: /user/home.php");
      exit;
      }
    exit;
    }
    else {
      $error = true;
    $errMSG = "Something in registration broke:".$registertry;
    }
  }
}

?>
<!DOCTYPE html>
<html lang="en">
   <head>
      <style type="text/css">
         html,body {
         background: url("bg.png") repeat center center fixed;
         -webkit-background-size: cover; /* For WebKit*/
         -moz-background-size: cover;    /* Mozilla*/
         -o-background-size: cover;      /* Opera*/
         background-size: cover;         /* Generic*/
         }
      </style>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <link rel="stylesheet" href="css/font-awesome.min.css">
      <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
      <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
      <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
   </head>
   <body>
      <nav class="navbar navbar-inverse">
         <center>
            <h2 style="color:white">Welcome to the Crescendia Prototype</h2>
         </center>
      </nav>
      <div class="container-fluid center-block" style="width:100%">
         <div class="row center-block" style="max-width:1200px">
            <div class="col-sm-6 col-sm-offset-3">


              <?php
               if ( isset($errMSG) ) {

                ?>
                <div class="form-group">
                         <div class="alert alert-danger">
                <span class="glyphicon glyphicon-info-sign"></span> <?php echo $errMSG; ?>
                            </div>
                         </div>
                            <?php
               }

                if ( isset($succMSG) ) {

                 ?>
                 <div class="form-group">
                          <div class="alert alert-success">
                 <span class="glyphicon glyphicon-info-sign"></span> <?php echo $succMSG; ?>
                             </div>
                          </div>
                             <?php
                }
                ?>
               <div class="panel panel-primary">
                  <div class="panel-heading">
                     <center>
                        <h3>Log in
                     </center>
                     </h3>
                  </div>
                  <div class="panel-body">
                     <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <center>Username:</center>
                        <input type="text" class="form-control" name="username"></br>
                        <center>Password:</center>
                        <input type="password" class="form-control" name="password"></br>
                        <input type="submit" class="btn btn-primary btn-block form-control" name="btn-login"  value="Log In">
                     </form>
                  </div>
               </div>
             </br>
               <div class="panel panel-primary">
                  <div class="panel-heading">
                     <center>
                        <h3>Register</h3>
                     </center>
                  </div>
                  <div class="panel-body">
                     <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                        <center>Email Address:</center>
                        <input type="text" class="form-control" name="email"></br>
                        <center>Username:</center>
                        <input type="text" class="form-control" name="username"></br>
                        <center>Password:</center>
                        <input type="password" class="form-control" name="password"></br>
                        <center>Password Confirmation:</center>
                        <input type="password" class="form-control" name="passconf"></br>
                        <center>Alpha Key:</center>
                        <input type="regcode" class="form-control" name="regcode"></br>
                        <input type="submit" class="btn btn-primary btn-block form-control" name="btn-register" value="Register">
                     </form>
                  </div>
               </div>
            </div>
         </div>
         <!--
            If you don't already have an account, please register</br></br>
            <form action="http://crescendiagame.com:8000/results.php" method="post">
                Email Address: <input type="text" class="form-control" name="username">
                Username: <input type="text" class="form-control" name="username">
                Password: <input type="text" class="form-control" name="password">
                Access Code: <input type="text" class="form-control" name="access_code"></br>
                <input type="submit" class="btn btn-primary btn-block form-control"  value="Register">
              </h3></center>
            </form>
            -->
      </div>
      </div>
   </body>
</html>
