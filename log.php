<?php

session_start(); #Een sessie wordt gestart en je wordt doorgelinkt naar de loginpagina, tenzij je in dezelfde sessie al ingelogd bent
if(!isset($_SESSION['authenticated']))
{
    header("Location: login.php");
    exit();
}

if(isset($_GET['end']) && $_GET['end'] == 1) #controleerd of er op de logout button is geklikt.
    {
        session_destroy();
        header("location: index.php");
    
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

$sql = "SELECT * FROM `log` ORDER BY `ID` ASC";
$result = $conn->query($sql);

$log = "";

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) 
    {
        $ID = $row["ID"];
        $actie = $row["Actie"];
        switch($actie)
        {
            case 1:
                $actie = "Licht aan gezet";
                break;
            case 2:
                $actie = "Licht uit gezet";
                break;
            case 3:
                $actie = "Camera aan gezet";
                break;
            case 4:
                $actie = "Camera uit gezet";
                break;
            case 5:
                $actie = "Alarm is uit gezet";
                break;
            case 6:
                $actie = "Alarm is aan gezet";
                break;
                
        }
        $huisnummer = $row["Huisnummer"];
        $tijd = $row["Tijd"];
        
        $log .= "<tr><td>$ID</td><td>$actie</td><td>$huisnummer</td><td>$tijd</td></tr>";
    }
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
        <title>Log</title>
    </head>

    <body background="images/black_background.jpg">
        <div><button type="button" class="btn btn-danger logoutbutton" id="logoutbutton">Logout</button></div>
        <div class="main-content log login">
            <table class="table"> 
                <thead> 
                    <tr> 
                        <th>ID</th> 
                        <th>Actie</th> 
                        <th>Huisnummer</th> 
                        <th>Tijd</th>   
                    </tr> 
                </thead> 
                <tbody> 
                    <?php print($log); ?>
                    
                </tbody> 
            </table>
            
        </div>
    </body>

    
   <script type="text/javascript">
        $("#logoutbutton").on('click', function(){
        window.location.replace("log.php?end=1");
    });
    
        $(document).ready(function(){
        $('.table').DataTable({
            paging: false,
            pageLength: 999999
        });
    });
    </script>
    
</html>