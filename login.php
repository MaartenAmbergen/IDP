<?php

session_start();

if(!empty($_GET['gebruikersnaam']) && !empty($_GET['wachtwoord']))
{

    if ($_GET['gebruikersnaam'] == "log" && $_GET['wachtwoord'] == "log")
    {
        $_SESSION['authenticated'] = 1; 
        header("Location: log.php");
        exit();
    }
    
$servername = "localhost"; #Database servernaam
$username = "root"; #Database gebruikersnaam
$password = "raspberry"; #Database wachtwoord
$dbname = "IDP"; #Database naam

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} 



$sql = "SELECT `HuisID` FROM login WHERE Gebruikersnaam='".$_GET['gebruikersnaam']."' AND Wachtwoord='".$_GET['wachtwoord']."'";
$result = $conn->query($sql);
    

if ($result->num_rows > 0){
    $row = $result->fetch_assoc();
    $_SESSION['authenticated'] = 1; 
    $_SESSION['HID'] = $row['HuisID'];
    print_r($row['HuisID']);
    header("Location: index.php");
    }
else{
    header("Location: login.php?false=1");
}

//header( "Location: index.php" ) ; #stuurt je door naar de index
//exit();
}

?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- haalt alle css op -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
                        <!-- Additional css code -->
        <link rel="stylesheet" href="css/custom.css" >
        <title>Login</title>
    </head>
    
<body background="images/background.jpg">
    <div class="col-lg-4 col-xs-2 col-md-4"></div>
    <div class = "col-xs-8 col-lg-4 col-md-3 login">
        <br>
        <?php
        if(isset($_GET['false']) && $_GET['false'] == 1)
        {
            ?>
            <div class="alert alert-danger" role="alert">Verkeerde gebruikersnaam/wachtwoord combinatie.</div>
            <?php
        }
        ?>
      <form action="login.php" class="col-xs-12">
            <br>
            <span class="login">Gebruikersnaam: </span>
            <input class="form-control tekstveld" type="text" id="username" name="gebruikersnaam" />
            <br>
            <span>Wachtwoord: </span>
            <input class="form-control tekstveld" type="password" id="password" name="wachtwoord" />
            <br>
            <button class="btn btn-success">Login</button>
        </form>
    </div>
</body>

