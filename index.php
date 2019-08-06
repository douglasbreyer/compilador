<?php 
	// Anthony Cassol e Douglas Breyer
?>

<html>
	<head>
		<link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
		<script src=bootstrap/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
	</head>
	<body style="background-color: #a4c3d8;">
		<?php
			require __DIR__ . '/vendor/autoload.php';

			error_reporting(E_ALL);
			ini_set("display_errors", 1);

			include('funcoes.php');

			// Lê um arquivo em um array. 
			$lines = file ('entrada.txt');

			$afnd = array();
			$tabSimb = array();
			$GLOBALS['estados'] = array();
			$GLOBALS['estados']['S'] = 0;
			$GLOBALS['estadoFinal']['S'] = 0;
			$GLOBALS['reservados'] = array();
			$GLOBALS['TS'] = array();
			$GLOBALS['parserSimbolos'] = array();


			$afnd[0][0] = 0;

			$GLOBALS['estado'] = 1;


			// Percorre o array, pegando cada linha da entrada
			foreach ($lines as $line_num => $linha) {
			   $tabSimb = insereSimbolos($tabSimb, $linha);

			   $afnd =  insereAFND($afnd, $linha, $tabSimb);
			}

		 	$afnd = transicoesvazias($afnd, $tabSimb);

		 	$afd = determinizar($afnd, $tabSimb);

			$afd = eliminamortos($afd, $tabSimb);

			echo '<h2> AFD - Eliminação de Mortos</h2>';

			printaAF($afd, $tabSimb);


			$lines = file ('lexico.txt');

			$i = 1;
			$GLOBALS['id'] = 1; //Ultimo id, para criar os id do lexico
			foreach ($lines as $line_num => $linha) { 
			   lexico($linha, $i, $afd, $tabSimb);  //manda linha por linha para a função lexico
			   $i++;
			}

			foreach ($GLOBALS['TS'] as $value) {
				echo $value->linha.' '.$value->rotulo.' '.$value->tipo.' '.$value->estado.' '.$value->id.'<br>';
			}


			$xml = simplexml_load_file('gramatica.xml');
			$parsing = array();
			$parsing = getParsing($xml, $parsing);

			$htmlparsing = '<div class="row">
								<div class="col-md-10">
									<table class="table table-bordered">
								    <thead>
								      <tr>
								        ';
							      

			foreach ($GLOBALS['parserSimbolos'] as $value) {
				$htmlparsing.= '<th>'.$value.'</th>';
			}


			$htmlparsing .= '</tr>
							    </thead>

							   <tbody>';

			

			for($i = 0; $i < count($parsing); $i++){
				$htmlparsing .= '<tr>';
				for($j = 0; $j < count($GLOBALS['parserSimbolos']); $j++){
					if(isset($parsing[$i][$j])){
						$htmlparsing .= '<td>'.$parsing[$i][$j]->acao.'</td>';
					}
					else{
						$htmlparsing .= '<td> </td>';
					}
				}
				$htmlparsing .= '</tr>';
			}


			$htmlparsing .= '		
      								</tbody>
								</table>
							</div>
						</div>
						';

			echo $htmlparsing;


			$fita = array();
			foreach ($GLOBALS['TS'] as $value) {
				echo '('.$value->rotulo.' '.$value->tipo.')';
			}	

			print_r($fita);
			echo '<br>';

			sintatico($parsing, $xml);
		
		?>
	</body>
</html>