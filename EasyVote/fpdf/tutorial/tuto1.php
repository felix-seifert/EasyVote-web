<?php
require('../fpdf.php');

	$test = $_POST['pdf'];

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
//$pdf->Cell(40,10,'Hello World!');
$pdf->Cell(40,10,$test);
$pdf->Output();
?>
