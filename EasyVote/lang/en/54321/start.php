 <?php 
 require_once('wahl_navigation.php');
$wahlNavi = new wahlNavi;
$maxvotes = $wahlNavi->get_maxvotes();
?>

<h1>Welcome to the local election<br>of the State Hesse 2013</h1>
<br>
<ul>
<li>You are able to cast all your <?php echo "$maxvotes" ?> votes for party members in different nominations - panachage - and cast up to three votes for every party member on the ballot card - cumulate - (<img src='bilder/init1vote.png' height='15'> oder <img src='bilder/init2vote.png' height='15'> oder <img src='bilder/init3vote.png' height='15'>).
	</li>
<br>
<li>If you do not want to cast all <?php echo "$maxvotes" ?> votes or you got votes left you are able to tag a nomination in the headline <img src='bilder/initvote.png' height='15'>.
	This will automatically cast your votes to the party members of the nomination in decending order until there are no more votes left.
	</li>
<br>
<li>You can tag a nomination in the headline <img src='bilder/initvote.png' height='15'>, without casting votes for party members. Every party member will be cast a vote in decending order until every party member has three votes or there are no more votes left.
</li>
<br>
<li>If you tag a nomination in the headline you are still able to delete party members from the ballot card. Those party members will not be cast any votes.
</li>
</ul>
<br><br><br>

<center><a href='election.php' class='button2'>Start election</a></center>
 