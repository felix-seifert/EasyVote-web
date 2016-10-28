<?php

$wahl = new WahlNavi();
$maxstimmen = $wahl->get_maxvotes();

if(isset($_SESSION['alert'])){
		switch($_SESSION['alert']){
			case(0):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Your ballot is spoiled, because you clicked 'Spoil ballot'.</p>";		
				break;
			case (1):
				$left_votes = $_SESSION['implicit'];
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>Out of $maxstimmen votes you casted  ".$_SESSION['member_count']." votes on candidates, $left_votes will be divided between the remaining candidates of the chosen party.</p>";
				break;
			case(2):
				$left_votes = $maxstimmen - $_SESSION['member_count'];
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>Out of $maxstimmen votes you casted ".$_SESSION['member_count']." votes on candidates. You have $left_votes votes left.</p>";
				break;
			case(3):
				$left_votes = $_SESSION['member_count'] - $maxstimmen;
				echo "<style> #Header1{ background-color: #FFA500; } </style>";  
				echo "<p>You casted ".$_SESSION['member_count']." votes for a party out of $maxstimmen. You will lose $left_votes votes if you print the ballot card.</p>";
				break;
			case (4):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Your ballot is spoiled, you casted more than  $maxstimmen votes on candidates.</p>";		
				break;
			case (5):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Your ballot is spoiled, you chose neither a candidate nor a party.</p>";		
				break;	
			case (6):
				echo "<style> #Header1{ background-color: EE2D29; } </style>";  
				echo "<p>Your ballot is spoiled, you chose too many parties.</p>";
				break;
				
			case (7):
				echo "<style> #Header1{ background-color: #FFA500; } </style>";  
				echo "<p>According to electoral law only your candidate votes will be used, because you chose too many parties.</p>";
				break;
			default: echo '<p>Error!';
					break;
			}
	}else{
		echo "<p>You did not cast any votes.</p>";
	}
?>