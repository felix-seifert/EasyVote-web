<?php
if(isset($_SESSION['alert'])){
	switch($_SESSION['alert']){
		case(0):
			echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
			echo "<p>Your ballot is spoiled, because you clicked 'Spoil ballot'.</p>";		
			break;
		case (1):
			echo "<style> #Header1{ background-color: #EE2D29; } </style>";
			echo "Your ballot is spoiled.";
			break;
		case(2):
			echo "<style> #Header1{ background-color: #FFA500; } </style>";
			echo "You have only casted a vote for the election district.";
			break;
		case(3):
			echo "<style> #Header1{ background-color: #FFA500; } </style>"; 
			echo "You have only casted a vote for the Landkreis.";
			break;
		case (4):
			echo "<style> #Header1{ background-color: #3DB653; } </style>"; 
			echo "Valid election.";		
			break;
		default: echo 'Error.';
			break;
	}
}else{
	echo "You did not cast any votes.";
}


	
?>