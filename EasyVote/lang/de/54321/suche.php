<form method="post" action='election.php'>
	<input type="search" list="Namen" name="search" class='suchfenster'/> 
		<datalist id = "Namen"> 
			<?php
				if(isset($_SESSION['suche'])){
					foreach($_SESSION['suche'] as $name){
						echo "<option value = $name>";
					}
				}
			?>
		</datalist> 
		<input type='submit' value='Suchen' name='suche' class='suchbutton'>
</form>