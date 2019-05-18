<?php $wahl = new WahlNavi();
$maxstimmen = $wahl->get_maxvotes(); ?>

<div class="logo" style="padding-left:18%;">
    <a href="https://www.secuso.org/easyvote" target="blank">
        <img width="80%" src="bilder/EasyVote.png" alt="EasyVote">
    </a>
</div>
<br>&nbsp;
<br>&nbsp;
<table class="info-table">
	<tr><td>
		<strong>Entweder</strong> wählen Sie eine Liste komplett durch ein Kreuz rechts neben dem Namen der Liste. Dann
		erhalten alle Kandidierenden dieser Liste automatisch genau eine Stimme. <strong>Oder</strong> Sie können Ihre
		<?php echo $maxstimmen; ?> Stimmen an Kandidierende manuell vergeben. <br />
		&nbsp;<br />
		Dabei können Kandidierende bis zu drei Stimmen erhalten (kumulieren) und Sie können Kandidierende verschiedener
		Listen aussuchen (panaschieren). <br />
		&nbsp;<br />
		Sie können auch weniger als <?php echo $maxstimmen; ?> Stimmen vergeben. Ihre Stimme wird ungültig, wenn Sie die
		Maximalzahl der Stimmen überschreiten.
	</td></tr>
</table>
<br>
<p>Legende:</p>
<table border="0">
    <tr>
        <td>
            <img src='bilder/buttonIconCandidateVoted.gif' height='15'>
        </td>
        <td>
            In dieser Liste wurden Stimmen direkt an Kandidierende vergeben.
        </td>
    </tr>
    <tr>
        <td style="padding-top: 14px;">
            <div style="text-align: center;">
                <img height='20' src='bilder/ballotChecked.gif' />
            </div>
        </td>
        <td style="padding-top: 14px;">
            Diese Liste wurde als einzige komplett gewählt und allen zugehörigen Kandidierenden wird eine Stimme gegeben.
        </td>
    </tr>
</table>
	