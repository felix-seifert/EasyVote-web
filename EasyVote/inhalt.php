<?php

//Action für "Ungültig wählen" - setzt die Wahl und den Header der Seite auf ungültig und sichert deren vorherigen Zustand
if(isset($_POST['ungueltig'])){
	$_SESSION['wahl'] = "invalid";
	$_SESSION['save_gueltig'] = $_SESSION['gueltig'];
	$_SESSION['gueltig'] = 0;
	$_SESSION['save_alert'] = $_SESSION['alert'];
	$_SESSION['alert'] = 0;
	header('location:print.php');
}

//Action für "Eingaben löschen" - löscht alle aktiven, relevanten Sessions
if(isset($_POST['reset'])){
	reset_session();
}

if(isset($_POST['show_rules'])) {
    require_once("lang/".$_SESSION['lang'].$_SESSION['aktive_wahl'].'/content.php');
}

//Action für "Neu starten" - löscht alle aktiven, relevanten Sessions und öffnet die Startseite
if(isset($_POST['neustart'])){
	reset_session();
	header('location:index.php');
}

//Wenn die Stimme geändert wurde, wird die Methode stimme_bearbeiten aufgerufen, um die Wahl zu aktualisieren
if(isset($_POST['vote_changed'])){
	$wahl = new WahlNavi();
	$wahl->stimme_bearbeiten();
}

//Action für "Stimme ansehen"
if(isset($_POST['wahl_submit'])){
	//Wahl abgeben -> wird in der jeweiligen Klasse definiert!
	$wahlNavi = new WahlNavi();
	$wahlNavi->stimme_abgeben();

    if(isset($_SESSION['possible_submission']) && $_SESSION['possible_submission']) {
        if(isset($_SESSION['vote'])){
            unset($_SESSION['vote']);
        }
        if(isset($_SESSION['party_elected'])){
            unset($_SESSION['party_elected']);
        }
        header('location:print.php');
    }
}

//Wenn der Button "Suche" betätigt wird, wird deren Inhalt an die entsprechende Klasse weitergegeben
if(isset($_POST['suche'])){
	$wahl = new WahlNavi();
	$wahl->suche($_POST['suche']);
}

//Wenn eine Partei geladen wurde, wird die Mitgliederliste der Partei angezeigt. Ansonsten werden die Regeln angezeigt.
if(isset($_POST['partei'])){
	$wahl = new WahlNavi();
	$wahl->mitglieder_laden($_POST['partei']);
}else{
	require_once("lang/".$_SESSION['lang'].$_SESSION['aktive_wahl'].'/content.php');
}

function reset_session() {
	if(isset($_SESSION['vote'])){
		unset($_SESSION['vote']);
	}
	if(isset($_SESSION['party_elected'])){
		unset($_SESSION['party_elected']);
	}
	if(isset($_SESSION['deleted'])){
		unset($_SESSION['deleted']);
	}
	if(isset($_SESSION['alert'])){
		unset($_SESSION['alert']);
	}
	if(isset($_SESSION['wks'])){
		unset($_SESSION['wks']);
	}
	if(isset($_SESSION['ls'])){
		unset($_SESSION['ls']);
	}
	if(isset($_SESSION['string_searching'])){
		unset($_SESSION['string_searching']);
	}
	if(isset($_SESSION['member_count'])){
		unset($_SESSION['member_count']);
	}
	if(isset($_SESSION['number_of_votes'])){
		unset($_SESSION['number_of_votes']);
	}
	if(isset($_SESSION['fehler_suche'])){
	    unset($_SESSION['fehler_suche']);
    }
    if(isset($_SESSION['possible_submission'])){
        unset($_SESSION['possible_submission']);
    }
}
