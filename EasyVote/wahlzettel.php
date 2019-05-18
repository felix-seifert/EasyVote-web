<div id="Inhalt">
    	
	<?php		
		
		//Wenn der Button "Zurück" angeklickt wurde, werden die verschiedenen Sessions wiederhergestellt (vote, alert und gueltig) und die vorherige Seite wird geladen
		if(isset($_POST['back'])){	 
			if(isset($_SESSION['wahl'])){
				if($_SESSION['wahl'] != 'invalid'){
                    if($_SESSION['aktive_wahl'] == '76131' && count($_SESSION['wahl']) == 1
                                && count(explode('_', $_SESSION['wahl'][0])) == 2) {
                        $_SESSION['party_elected'] = $_SESSION['wahl'];
                    }
                    else {
                        $_SESSION['vote'] = $_SESSION['wahl'];
                    }
				}
                unset($_SESSION['wahl']);
			}
			if(isset($_SESSION['save_alert'])){
				$_SESSION['alert'] = $_SESSION['save_alert'];
				unset($_SESSION['save_alert']);
			}
			if(isset($_SESSION['save_gueltig'])){
				$_SESSION['gueltig'] = $_SESSION['save_gueltig'];
				unset($_SESSION['save_gueltig']);
			}
			header('location:election.php');
		}

		$wahl = new WahlNavi();
		$test = $wahl->wahl_auswerten();
		
		//Wenn der Button "Stimme hochladen" angeklickt wurde, wird der Abgabescreen angezeigt
		if(isset($_POST['onlineAbgabe'])){ 
			$_SESSION['ausgabe'] = 0;
			require_once("lang/".$_SESSION['lang']."abgabeformular.php");
		}
		
		/*	Wenn der Button "Wahl hochladen" gedrückt wurde, wird die Benutzerdaten (Benutzername und Passwort) auf ihre Korrektheit untersucht.
			Die Wahl kann nur abgegeben werden, wenn noch keine Wahl hochgeladen wurde */
		if(isset($_POST['submit'])){
			
			$abgabe = new WahlNavi();
			$login = trim($_POST['login']);
			$passwort = hash('sha256', trim($_POST['passwortlog']).$login);	
			
			
			if(strlen($login) == 8){
				if($abgabe->benutzerdaten_korrekt($login, $passwort)){	
					if($abgabe->wahl_is_null($login)){
						$abgabe->abgabe_wahl($login, $passwort, $test);
						$_SESSION['ausgabe'] = 1;
					}else{
						$_SESSION['ausgabe'] = 2;
					}
				}else{
					$_SESSION['ausgabe'] = 3;
				}
			}else{
				$_SESSION['ausgabe'] = 4;
			}

			require_once("lang/".$_SESSION['lang']."abgabeformular.php");
			
		}else{
			if(!isset($_POST['onlineAbgabe'])){
				$wahlNavi = new WahlNavi();
				$wahlNavi->showWahl($test);
			}
		}
	
	?>
  </div>