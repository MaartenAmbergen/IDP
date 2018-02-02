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

//controle of alles in de url is doorgestuurd
if(isset($_GET['action']) && isset($_GET['lid']) &&isset($_GET['hid']))
{
    //HID = HuisID
    //LID = LampID
    //action 0 -> lamp uitzetten
    //action 1 -> lamp aanzetten
    $action = $_GET['action']; #haalt de status (aan/uit) op vanuit de link
    $lid = $_GET['lid']; #haalt het ID van de lamp op vanuit de link
    $hid = $_GET['hid']; #haalt het ID van het huis op vanuit de link

    //selecteer de juiste database en haal de waardes op
    $sql = "SELECT * FROM `huizen` WHERE `huis`=$hid";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $output_array = json_decode($row["lampen"]); #decode de json die in de database staat
    $output_array->$lid = $action; #de array met de lamp die wordt geswitched
    $result_json = json_encode($output_array); #de array wordt opnieuw geëncode
    $update_sql = "UPDATE `huizen` SET `lampen`='$result_json' WHERE `huis`=$hid"; #de json string wordt geupdate in de database
    $conn->query($update_sql);
    $result->free();
    $sql = "UPDATE `huizen` SET `applamp`='$action' WHERE `huis`=$hid";
    $conn->query($sql);

    $result->free();

    if(isset($_GET['actie']) && isset($_GET['hid']))
    {
        $actie = $_GET['actie'];
        $huis = $_GET['hid'];
        $sql = "INSERT INTO `log` (`ID`, `Huisnummer`, `Actie`, `Tijd`) VALUES (NULL, '$huis', '$actie', CURRENT_TIMESTAMP)";
        $conn->query($sql);
    }
    
    
    header("Location: index.php"); #pagina wordt opnieuw geladen
}

   if(isset($_GET['hid']) && isset($_GET['camera']) &&isset($_GET['actie']))
    {
        $huis = $_GET['hid'];
        $camera_status = $_GET['camera'];
        $actie = $_GET['actie'];
        $sql = "UPDATE `huizen` SET `Camera`=$camera_status WHERE `huis`=$huis";
        $conn->query($sql);
        $sql = "INSERT INTO `log` (`ID`, `Huisnummer`, `Actie`, `Tijd`) VALUES (NULL, '$huis', '$actie', CURRENT_TIMESTAMP)";
        $conn->query($sql);

    header("Location: index.php"); #pagina wordt opnieuw geladen
    }


    if(isset($_GET['hid']) && isset($_GET['alarm']) && isset($_GET['actie']))
    {
        $huis = $_GET['hid'];
        $alarm_status = $_GET['alarm'];
        $actie = $_GET['actie'];
        $sql = "UPDATE `huizen` SET `Alarm`=$alarm_status WHERE `huis`=$huis";
        $conn->query($sql);
                $sql = "INSERT INTO `log` (`ID`, `Huisnummer`, `Actie`, `Tijd`) VALUES (NULL, '$huis', '$actie', CURRENT_TIMESTAMP)";
        $conn->query($sql);
        
    header("Location: index.php"); #pagina wordt opnieuw geladen
    }

//aanmaken buttons voor de verschillende lampen
function displayHouse($conn, $hid)
{
    $hid = $_SESSION['HID'];
    $sql = "SELECT * FROM `huizen` WHERE `huis`=$hid"; #haal de string van de lampen op van het bijbehorende huis
    $result = $conn->query($sql);

    $output_array = array();
    if ($result->num_rows > 0) { #zorg ervoor dat er op z'n minst 1 lamp is
        // output data of each row
        $output_array = array();
        $row = $result->fetch_assoc();
        $lampen_raw_json = $row["lampen"];
        $output_array = json_decode($lampen_raw_json); #zet de json string om naar een array
        }
        $output_lampen = ""; #lege string waar de lampen/buttons aan worden toegevoegd

        foreach($output_array as $lampID=>$value) #loopt alle verschillende lampen door
        {
            if($value==0) #als de lamp als value 0 heeft, oftewel uit staat
            {
                $output_lampen .= "<a href=?action=1&lid=$lampID&hid=$hid&actie=1 class='btn btn-danger lamp_uit'>Lamp $lampID</a><br><br>"; #button wordt aangemaakt met een redirect waarin het ID van de lamp, het ID van het huis en de nieuwe status (1) wordt doorgegeven voor de $_GET
            }
            else if($value==1) #als de lamp als value 1 heeft, oftewel aan staat
            {
                $output_lampen .= "<a href=?action=0&lid=$lampID&hid=$hid&actie=2 class='btn btn-success lamp_aan'>Lamp $lampID</a><br><br>"; #button wordt aangemaakt met een redirect waarin het ID van de lamp, het ID van het huis en de nieuwe status (0) wordt doorgegeven voor de $_GET
            }
        }
  
        return $output_lampen;
}
$hid = $_SESSION['HID'];
$sql = "SELECT * FROM `huizen` WHERE `huis`=$hid";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$camera = $row['camera'];
$alarm = $row['Alarm'];
$camip = $row['camip'];

if($alarm == 0 || $alarm == 3)
{
	if($camera == 2)
	{
    		$camera_button = "<a href=?&hid=$hid&actie=3&camera=1 class='btn btn-danger lamp_uit'>Camera</a><br><br>";
	}
	else if($camera == 3)
	{
    		$camera_button = "<a href=?&hid=$hid&actie=4&camera=0 class='btn btn-success lamp_uit'>Camera</a><br><br>";
	}
}
else
{
    $camera_button = "<a href=?&hid=$hid&actie=4&camera=2 class='btn btn-success lamp_uit' disabled>Camera</a><br><br>";
}

//0 = uit
//1 = aan
//2 = aan
//3 = uit
if($alarm == 0)
{
    $alarm_button = "<a href=?&hid=$hid&actie=5&alarm=1 class='btn btn-danger lamp_uit'>Alarm</a><br><br>";
}
else if($alarm == 1)
{
    $alarm_button = "<a href=?&hid=$hid&actie=6&alarm=0 class='btn btn-success lamp_uit'>Alarm</a><br><br>";
}
else if($alarm == 3)
{
    $alarm_button = "<a href=?&hid=$hid&actie=5&alarm=1 class='btn btn-danger lamp_uit'>Alarm</a><br><br>";
}
else if($alarm == 2)
{
    $alarm_button = "<a href=?&hid=$hid&actie=6&alarm=0 class='btn btn-success lamp_uit'>Alarm</a><br><br>";
}

$cam_button = "<a href='$camip' target='_blank' class='btn btn-info'>Camera feed</a>";

$houseHTML = displayHouse($conn,1); #de variabele met daarin alle buttons

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
        <title>Lampen</title>
    </head>
    
<body background="images/background.jpg"> <!-- achtergrond -->
    <div><button type="button" class="btn btn-danger logoutbutton" id="logoutbutton">Logout</button>
    <?php print($cam_button); ?>
    </div>
    <h3 class="House_text">Huis <?php print($_SESSION['HID']) ?></h3> <!-- titel + huisnummer -->
    <br>
    <?php print($houseHTML); ?> <!-- print alle buttons -->
    <?php print($camera_button); ?> <!-- print de camera button -->
    <?php print($alarm_button); ?> <!-- print de alarm button -->
    </body>
</html>

<script type="text/javascript">
    $("#logoutbutton").on('click', function(){
        window.location.replace("index.php?end=1");
    });
    setTimeout(function(){
   window.location.reload(1);
}, 30000);
</script>
