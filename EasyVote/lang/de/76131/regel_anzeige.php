<?php

$wahl = new WahlNavi();
$maxstimmen = $wahl->get_maxvotes();

//Ausgabe der Regeln im Header

if(isset($_SESSION['alert'])){
    switch($_SESSION['alert']){
        case(0):
            echo "<p>Sie haben bisher noch keine Stimme vergeben.</p>";
            break;
        case(1):
            $votes_left = $maxstimmen - $_SESSION['number_of_votes'];
            echo "<style> #Header1{ background-color: #3DB653; } </style>";
            echo "<p>Sie haben ".$_SESSION['number_of_votes']." Stimmen direkt an Kandidierende vergeben. Es stehen noch $votes_left Stimmen zur Verf&uuml;gung.</p>";
            break;
        case(2):
            $votes_left = $maxstimmen - $_SESSION['number_of_votes'];
            echo "<style> #Header1{ background-color: #3DB653; } </style>";
            echo "<p>Sie haben ".$_SESSION['number_of_votes']." Stimmen direkt vergeben. Es stehen noch $votes_left Stimmen zur Verf&uuml;gung. Ihre Parteienauswahl hat keinerlei Bedeutung.</p>";
            break;
        case(3):
            echo "<style> #Header1{ background-color: #3DB653; } </style>";
            $votes_left = $_SESSION['number_of_votes'] - $maxstimmen;
            if(isset($_SESSION['party_elected']) && !empty($_SESSION['party_elected'])) {
                $party_id = explode('_', $_SESSION['party_elected'][0])[1];
                $party = $wahl->get_wahl()->get_partei($party_id);
                echo "<p>Sie geben den unmarkierten Stimmzettel der \"$party\" ab. Dadurch werden automatisch ".$_SESSION['number_of_votes']." von $maxstimmen Stimmen vergeben.</p>";
            }
            else {
                echo "<p>Sie haben genau eine Liste ausgew&auml;hlt. Dadurch werden automatisch " . $_SESSION['number_of_votes'] . " von $maxstimmen Stimmen vergeben.</p>";
            }
            break;
        case (4):
            echo "<style> #Header1{ background-color: #EE2D29; } </style>";
            echo "<p>Ihre Stimme ist ung&uuml;ltig, da Sie mehr als $maxstimmen Kandidierende ausgew&auml;hlt haben.</p>";
            break;
        case (5):
            echo "<style> #Header1{ background-color: #FFA500; } </style>";
            echo "<p>Sie geben mehrere unmarkierte Stimmzettel ab. Dadurch hat Ihre Stimmabgabe keinerlei Bedeutung.</p>";
            break;
        case (6):
            echo "<style> #Header1{ background-color: #EE2D29; } </style>";
            echo "<p>F&uuml;r eine ung&uuml;ltige bzw. leere Auswahl wird kein m&ouml;glicher Stimmzettel generiert.</p>";
            break;
        default: echo '<p>FEHLER</p>';
            break;
    }
}else{
    echo "<p>Sie haben bisher noch keine Stimme vergeben.</p>";
}
?>