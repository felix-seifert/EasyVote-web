<?php

if(isset($_SESSION['alert'])){
		switch($_SESSION['alert']){
			case(0):
				echo "<style> #Header1{ background-color: EE2D29; } </style>"; 
				echo "<p>Your ballot is spoiled, because you clicked 'Spoil ballot'.</p>";		
				break;
			case (1): 
				echo "<p>You did not cast any votes.</p>";
				break;
			case(2):
				echo "<style> #Header1{ background-color: EE2D29; } </style>";  
				echo "<p>Your ballot is spoiled, you chose no party.</p>";
				break;
			case(3):
				echo "<style> #Header1{ background-color: #3DB653; } </style>";  
				echo "<p>Valid election!</p>";
				break;
			default: echo 'Error!';
				break;
			}
	}else{
		echo "<p>You did not cast any votes.</p>";
	}
?>