<?php
	class TS { 
	    public $linha;
	    public $rotulo;
	    public $tipo;
	    public $estado;
	    public $id;
	} 

	class LALRState{
		public $estado;
		public $acao;
	}
	//Insere simbolos na tabela de simbolos
	function insereSimbolos($tabSimb, $linha){
		if($linha[0] == '<'){
			$tabSimb = insereGR($tabSimb, $linha);
		}
		else{
			$tabSimb = insereToken($tabSimb, $linha);
		}
		return $tabSimb;
		//echo $linha.'<br>';
	}
	//Inserio simbolos na tabela de simbolos
	function insereToken($tabSimb, $linha){
		$length = strlen($linha);
		$reservado = "";

		for ($i=0; $i<$length-1; $i++) {
			$simbolo = $linha[$i];
			$reservado .= $simbolo;

			if(!in_array($simbolo, $tabSimb)){
				array_push($tabSimb, $simbolo); //iunsere na tabela de simbolos o simbolo
			}
				
		}

		array_push($GLOBALS['reservados'], $reservado);
		return $tabSimb;
	}
	//Insere simbolos na tabela de simbolos
	function insereGR($tabSimb, $linha){
		$length = strlen($linha);
		//varro todos os simbolos da linha
		for ($i=0; $i<$length; $i++) {
			$simbolo = $linha[$i];
			//se o simbolo for '<' e nao for o primeiro
			if($simbolo == '<' && $i != 0){
				if(!in_array($linha[$i-1], $tabSimb)){
					array_push($tabSimb, $linha[$i-1]);
				}
			}	
		}

		return $tabSimb;
	}
	function insereAFND($afnd, $linha, $tabSimb){
		if($linha[0] == '<'){
			$afnd = lerGR($afnd, $linha, $tabSimb);
		}
		else{
			$afnd = lerToken($afnd, $linha, $tabSimb);
		}
		return $afnd;
	}
	function lerToken($afnd, $linha, $tabSimb){
		$length = strlen($linha); // Comprimento da linha 
		$anterior = 0; //linha na matriz em questão
		for ($i=0; $i<$length-1; $i++) {  //vare toda a linha
			$simbolo = $linha[$i];  //simbolo recebe caractere
			$chave = (array_search($simbolo, $tabSimb)+1); //verifica se existe na tabela de simbolos e retorna a posicao

			if(isset($afnd[$anterior][$chave])){ //se ja existe nessa linha um simbolo
				array_push($afnd[$anterior][$chave], $GLOBALS['estado']);
			}
			else{ //se nao existe, cria com o proximo estado
				$vetor[0] = $GLOBALS['estado'];
				$afnd[$anterior][$chave] = $vetor;
			}

			//na coluna 0 (Coluna de estados), cria o novo estado com o estado em questao
			$afnd[$GLOBALS['estado']][0] = $GLOBALS['estado']; 
			$GLOBALS['estados'][$GLOBALS['estado']] = $GLOBALS['estado'];
			$anterior = $GLOBALS['estado'];  //anterior aponta para a linha desse estado
			$GLOBALS['estadoFinal'][$GLOBALS['estado']] = 0;
			$GLOBALS['estado']++;  //proxima estado ++
			
		}
		$final = $GLOBALS['estado']-1; //no token, ultimo é o estado final, o estado em que se leu aquele token
		$GLOBALS['estadoFinal'][$final] = 1;

		return $afnd;
	}

	function lerGR($afnd, $linha, $tabSimb){
		$length = strlen($linha); //comprimento da linha
		for ($i=0; $i<$length-1; $i++) {  //para cada simbolo(char) da linha
			$simbolo = $linha[$i]; //simbolo em questao

			if($simbolo == '<' && $i == 0){ //se o simbolo for '<' e estiver no primeiro simbolo da linha
				$producao = $linha[$i+1];       //producao recebe o proximo simbolo. EX: <A>, producao = A da linha em questao

				//Cria o estado da producao
				if(!isset($GLOBALS['estados'][$producao])){
					$GLOBALS['estados'][$producao] = $GLOBALS['estado'];
					$GLOBALS['estadoFinal'][$producao] = 0;
					$afnd[$GLOBALS['estado']][0] = $GLOBALS['estado'];
					$GLOBALS['estado']++;
				}
			}


			else if($simbolo == '<' && $i != 0){ //se o simbolo for '<' e nao for o primeir simbolo da linha
				$terminal = $linha[$i-1];  //terminal recebe o anterior , a<A>. terminal = a
				$chave = (array_search($terminal, $tabSimb)+1);      //verifica se existe esse terminal na tabela de simbolos
				$naoterminal = $linha[$i+1];  //nao terminal,   a<A>, naoterminal = A


				//$GLOBALS['estados'] USADO PARA GUARDAR OS ESTADOS CRIADOS NAS GR, GUARDA O ESTADO E A POSICAO NA AFND

				//offset é o qual a linha no afnd da producao atual
				$offset = $GLOBALS['estados'][$producao]; 

				//se o nao terminal ja estiver inserido no estados GR
				if(isset($GLOBALS['estados'][$naoterminal])){			
					if(isset($afnd[$offset][$chave])){
						if(!in_array($GLOBALS['estados'][$naoterminal], $afnd[$offset][$chave])){
							array_push($afnd[$offset][$chave], $GLOBALS['estados'][$naoterminal]);
						}	
					}
					else{
						$vetor[0] = $GLOBALS['estados'][$naoterminal];;
						$afnd[$offset][$chave] = $vetor;
					}
				}
				else{
					//se nao tiver o estado desse nao terminal o vetor de estados GR
					$GLOBALS['estados'][$naoterminal] = $GLOBALS['estado'];
					$GLOBALS['estadoFinal'][$naoterminal] = 0;
					$afnd[$GLOBALS['estado']][0] = $GLOBALS['estado'];
					$GLOBALS['estado']++;

					$offset = $GLOBALS['estados'][$producao]; 

					if(isset($afnd[$offset][$chave])){
						if(!in_array($GLOBALS['estados'][$naoterminal], $afnd[$offset][$chave])){
							array_push($afnd[$offset][$chave], $GLOBALS['estados'][$naoterminal]);
						}	
					}
					else{
						$vetor[0] = $GLOBALS['estados'][$naoterminal];;
						$afnd[$offset][$chave] = $vetor;
					}
				}
			}
			else if($simbolo == '#' && $linha[$i+1]!='>' && $linha[$i+1]!='<'){ //Verifica se o final é o ultimo
				$GLOBALS['estadoFinal'][$producao] = 1;
			}	

		}

		return $afnd;
	}

	function transicoesvazias($afnd, $tabSimb){

		$transicaovazia = 0;
		//se tem transicao vazia na tabela de simbolos
		if(in_array('#', $tabSimb)){
			//Coluna da transicao na tabela de simbolos, ou seja, na afnd
			$coluna = array_search('#', $tabSimb)+1;
			//varre a coluna do vazio
			for($i=0; $i<=count($afnd); $i++){
				//verifica se tem alguma transição vazia
				if(isset($afnd[$i][$coluna])){
					//achou uma transicao vazia
					$transicaovazia = 1;

					//qi recebe a linha, estado que esta esta apontando
					$qi = $i;

					//qj recebe o que estado que esta recebendo a transicao
					$qj = $afnd[$i][$coluna];   // VETOR, QUE TEM OS ESTADOS QUE A TRANSICAO VAIZA APONTA

					//Finalqi = Simbolo do QI
					$finalqi = array_search($qi, $GLOBALS['estados']);

					//pega todos as transicoes que estao em qj
					for($j=0; $j<count($qj); $j++){

						//Finalqj = Simbolo do QJ na tabela de simbolos
						$finalqj = (array_search($qj[$j], $GLOBALS['estados']));
							
						//Se o QJ é estado final
						if($GLOBALS['estadoFinal'][$finalqj] == 1){

							//Qi também é estado final
						 	$GLOBALS['estadoFinal'][$finalqi] = 1;
						}

						//varre a linha da transicao de qj em questao
						for($k = 1; $k<=count($tabSimb); $k++){
							if(isset($afnd[$qj[$j]][$k])){
								//se tiver transicao na linha
								if(!isset($afnd[$qi][$k])){
									$afnd[$qi][$k] = array();
								}
								//aqui verifica se a transicao da linha de qj esta em qi, se nao esta adiciona;
								$afnd[$i][$k] = verificasetem($afnd[$qj[$j]][$k], $afnd[$qi][$k]);
									
							}				
						}
					}
					
				$afnd[$i][$coluna] = NULL;
				}
			}
		}
		if($transicaovazia == 1){
			transicoesvazias($afnd, $tabSimb);
		}

		return $afnd;
	}

	function determinizar($afnd, $tabSimb){

		$determinizar = 0;

		//percorre toda a matriz afnd
		for($i=0;$i<count($afnd);$i++){
		   for($j=1;$j<=count($tabSimb);$j++){
		   		//se tiver algum vetor na posicao
		   		if(isset($afnd[$i][$j])){
		   			//verifica se esse vetor tem mais de um valor
		   			$count = count($afnd[$i][$j]);
		   			if($count>1){
		   				//concat usado para dar nome ao novo estado
		   				$concat = "";

		   				//ordenado, vetor da posicao ordenado
		   				$ordenado = $afnd[$i][$j];

		   				sort($ordenado);

		   				//faz a concatenacao das posicoes do vetor que esta o indeterminismo
		   				for($k = 0; $k < $count; $k++){
		   					if($k!=$count-1)	$concat.= $ordenado[$k].'-';
		   					else $concat.=$ordenado[$k];
		   				}

		   				//se esse stado nao estiver criado, cria
		   				if(!isset($GLOBALS['estados'][$concat])){

		   					$determinizar = 1;

		   					//cria o estado
		   					$GLOBALS['estados'][$concat] = $GLOBALS['estado'];
		   					$GLOBALS['estadoFinal'][$concat] = 0;
		   					$afnd[$GLOBALS['estado']][0] = $GLOBALS['estado'];
		   					$qi = $GLOBALS['estado'];
		   					$GLOBALS['estado']++;	


		   					//Finalqi = Simbolo do QI
		   					$finalqi = array_search($qi, $GLOBALS['estados']);

		   					//posicao atual faz a transicao para o novo estado
		   					$afnd[$i][$j] = array();
		   					array_push($afnd[$i][$j], $qi);

		   					//for para varrer o vetor que esta o indeterminismo
			   				for($l = 0; $l < $count; $l++){
			   					$qj = $ordenado[$l];

			   					//Finalqj = Simbolo do QJ na tabela de simbolos
			   					$finalqj = (array_search($qj, $GLOBALS['estados']));
			   					//Se o QJ é estado final
			   					if($GLOBALS['estadoFinal'][$finalqj] == 1){



			   						//Qi também é estado final
			   					 	$GLOBALS['estadoFinal'][$finalqi] = 1;
			   					}

			   					//varre as linhas das transicoes (invederminismo)
			   					for($k = 1; $k<=count($tabSimb); $k++){

			   						//se tiver algo na linha
									if(isset($afnd[$qj][$k])){
				   						//se tiver transicao na linha
										if(!isset($afnd[$qi][$k])){
											$afnd[$qi][$k] = array();
										}
										//aqui verifica se a transicao da linha de qj esta em qi, se nao esta adiciona;
										$afnd[$qi][$k] = verificasetem($afnd[$qj][$k], $afnd[$qi][$k]);
											
									}			
			   					}
			   				}
		   				}
		   				else{
		   					$afnd[$i][$j] = array();
		   					array_push($afnd[$i][$j], $GLOBALS['estados'][$concat]);
		   				}

		   			}
		   		}
		   		
		   }

		}	

		if($determinizar == 1){
			determinizar($afnd, $tabSimb);
		}

		return $afnd;
	}

	function verificasetem($vetor, $afnd){	
		//varre as posicoes do vetor passado
		for($k = 0; $k<count($vetor); $k++){
			//se estiver no array passado adiciona no final;
			
		
			if(!in_array($vetor[$k], $afnd)){
				array_push($afnd, $vetor[$k]);
			}	
			//retorna o array
		}
		return $afnd;
	}


	function eliminamortos($afd, $tabSimb){
		$alcancaveis = array(); //vetor para guardar os estados alcancaveis
		$alcancaveis[0] = 0; //alcancavel = estado inicial
		//percorre toda a matriz afd
		for($i=0;$i<count($afd);$i++){
		   for($j=1;$j<=count($tabSimb);$j++){
		   		//se tiver algum vetor na posicao
		   		if(isset($afd[$i][$j])){
		   			//verifica se esse vetor tem mais de um valor
		   			$count = count($afd[$i][$j]);
		   			//verifica qual é a letra do GR
		   			$simbolo = array_search($afd[$i][$j][0], $GLOBALS['estados']);

		   			if(!in_array($afd[$i][$j][0], $alcancaveis)){
		   			array_push($alcancaveis, $afd[$i][$j][0]);
		   			}
		   		}
		   		
		   }
		}

		//verifica para todos os estados se algum deles não esta no vetor de algancaveis
		foreach ($GLOBALS['estados'] as $estado) {
			if(!in_array($estado, $alcancaveis)){
				unset($afd[$estado]);
				unset($GLOBALS['estados'][$estado]);
			}
		}
		return $afd;
	}

	function equivalencia($afd, $tabSimb){
		$verificacoes = array();

		//percorre todos os estados na linha e coluna
		foreach($GLOBALS['estados'] as $key => $i){
		 	foreach($GLOBALS['estados'] as $keys => $j){

		 		//verificou recebe os dois ordenado
		 		if($i>$j){
		 			$verificou = $j.' - '.$i;
		 		}
		 		else{
		 			$verificou = $i.' - '.$j;
		 		}

		 		//Verifica se o I == J, nao quero verificar equivalencia de 0 com 0.
		 		if($i == $j){
		 			echo '  ';
		 		}
		 		//so verifica o par de estados que nao foi ainda
		 		else if(!in_array($verificou, $verificacoes)){
		 			array_push($verificacoes, $verificou);

		 			//Envia para verificar a igualdade entre os estados; estado i e estado j
		 			$flag = verificaigualdade($i, $j, $tabSimb, $afd);

		 			if($flag == 1){
		 				$simboloi = array_search($i, $GLOBALS['estados']);
						$simboloj = array_search($j, $GLOBALS['estados']);

						echo '<h2>'.$simboloi.' e '.$simboloj.'</h2><br>';
		 			}
		 		}
			} 	
		}
	}

	function verificaigualdade($i, $j, $tabSimb, $afd){
		$flag = 1;
	
		//percorre as duas linhas verificando se os estados são iguais para a mesma produção
		for($k = 1; $k<=count($tabSimb); $k++){
			//Se nenhum deles for vazio, verifica se o valor contido no array é o mesmo;
			if(isset($afd[$i][$k]) && isset($afd[$j][$k])){
				if($afd[$i][$k][0] != $afd[$j][$k][0]){
					$flag = 0;
				}
			}
			//se um deles for vazio e o outro nao, não sao equivalentes
			else if(isset($afd[$i][$k])){
				$flag = 0;
			}
			else if(isset($afd[$j][$k])){
				$flag = 0;
			}
		}	

		$simboloi = array_search($i, $GLOBALS['estados']);
		$simboloj = array_search($j, $GLOBALS['estados']);

		if($GLOBALS['estadoFinal'][$simboloi] != $GLOBALS['estadoFinal'][$simboloj]){
			$flag = 0;
		}
		return $flag;
	}

	function printaAF($afnd, $tabSimb){
	echo'	
				<div class="col-md-12">
				    <table class="table table-striped table-bordered">
				        <tbody>
				            <tr>
				                <th>δ</th>';
				                $head = '';
				                $body = '';

				                    foreach ($tabSimb as $key => $value) {			                        
				                        $head .= "<th> " . $value . " </th>";			                        
				                    }
				                    $head.= "</tr>";
				                    echo $head;

				                    $count = count($afnd);
				                    $i = 0;
				                    while($i<$count){
				                    	if(isset($afnd[$i])){
				                    		echo '<tr>';
				                    	   for($j=0;$j<=count($tabSimb);$j++)
				                    	   {
				                    	   	echo '<td>';	
				                    	   		//Se existe afnd nessa posicao, se nao printa espaco (Para printar algo nos quue nao tem)
				                    	   		if(isset($afnd[$i][$j])){
				                    	   			
				                    	   			//verifica qual é a letra do GR
				                    	   			$letra = array_search($afnd[$i][$j], $GLOBALS['estados']);


				                    	   			//se é um GR, printar a letra ($posicao)
				                    	   			if(isset($GLOBALS['estados'][$letra])){
				                    	   				//se for estado final e estiver na coluna 1
				                    	   				if($GLOBALS['estadoFinal'][$letra] == 1 && $j == 0){
				                    	   					echo $letra.'#';
				                    	   				}
				                    	   				//se nao printa normal
				                    	   				else{
				                    	   					echo $letra;
				                    	   				}
				                    	   			}
				                    	   			else{
				                    	   				for($print=0; $print<count($afnd[$i][$j]); $print++){
				                    	   					$estado = array_search($afnd[$i][$j][$print], $GLOBALS['estados']);
				                    	   					echo $estado.' ';
				                    	   				}
				                    	   			}
				                    	   			
				                    	   		}
				                    	   		else{
				                    	   			echo ' ';
				                    	   		}
				                    	   		
				                    	   	echo '</td>';
				                  			}
				                    	
				                         
				                    }
				                    else{
				                       echo '</tr>';
				                       $count+=1;
				                    }
				                    $i++;
				                 }
				                
		echo '
				        </tbody>
				    </table>
				</div>
				';
	}

	function is_array_empty($arr){
	   if(is_array($arr)){
	      foreach($arr as $value){
	         if(!empty($value)){
	            return false;
	         }
	      }
	   }
	   return true;
	}

	function in_array_field($needle, $needle_field, $haystack, $strict = false) { 
	    if ($strict) { 
	        foreach ($haystack as $item) 
	            if (isset($item->$needle_field) && $item->$needle_field === $needle) 
	                return $item->id; 
	    } 
	    else { 
	        foreach ($haystack as $item) 
	            if (isset($item->$needle_field) && $item->$needle_field == $needle) 
	                return $item->id; 
	    } 
	    return false; 
	} 

	function lexico($linha, $valorlinha, $afd, $tabSimb){
		$linha = str_replace("&nbsp;"," ",$linha);
		$linha = str_replace("\n", "", $linha);
		$linha = str_replace("\r", "", $linha);
		$linha = preg_replace('/\s/',' ',$linha);   //separa a linha em simbolos/tokens/palavras/slah

		

		$length = strlen($linha);   //percorre todos os simbolos da linha
		$vetor = array();
		$palavra = "";
		for ($i=0; $i<$length; $i++) {//letra por letra
			$simbolo = $linha[$i];
			
			//se encontra um espaco
			if($simbolo == ' '){
				if(!is_array_empty($vetor)){
					verificalexico($vetor, $valorlinha, $afd, $tabSimb, $palavra);
					$vetor = array();
					$palavra = "";
				}
			}
			//final da linha
			else if($i==$length-1){
				array_push($vetor, $simbolo);
				$palavra.=$simbolo;
				verificalexico($vetor, $valorlinha, $afd, $tabSimb, $palavra);
				$vetor = array();
				$palavra = "";
			}
			else{
				$palavra.=$simbolo;
				array_push($vetor, $simbolo);
			}
			
		}
		//print_r($vetor);
	}

	function verificalexico($vetor, $valorlinha, $afd, $tabSimb, $palavra){
		$estado = 0;
		$erro = 0;
		$lexico = new TS; //criacao da fita
		$id = 0;

		foreach ($vetor as $vet => $letra) {
			
			//verifica se existe o simbolo
			if(in_array($letra, $tabSimb)){  //verifica as letras da palavra
				$chave = array_search($letra, $tabSimb)+1;  //posição table simbolos
			}
			else{
				echo 'Erro Lexico. Na linha '.$valorlinha.' "'.$palavra.'" nao foi reconhecido <br>';
				$erro = 1;
				break;
			}
			
			//verifica se faz parte da afd
			if(!isset($afd[$estado][$chave][0])){
				echo 'Erro Lexico. Na linha '.$valorlinha.' "'.$palavra.'" nao foi reconhecido <br>';
				$erro = 1;
				break;
			}
			else{
				$estado = $afd[$estado][$chave][0];
			}

			
		}

		if($erro == 0){
			$estado = array_search($estado, $GLOBALS['estados']);

			//verifica se é estado final
			if($GLOBALS['estadoFinal'][$estado] == 0) {
				echo 'Erro Lexico. Na linha '.$valorlinha.' "'.$palavra.'" nao foi reconhecido (faltam caracteres)';
			}

			// é estado final, carrega informaçoes fita
			else{
				$lexico->linha = $valorlinha;
				$lexico->rotulo = $palavra;

				//verifica se é token
				if(in_array($palavra, $GLOBALS['reservados'])){
					$lexico->tipo = $palavra;
				}
				//verifica se é numero
				else if(is_numeric($palavra)) { 
					$lexico->tipo = 'digit'; 
					//verifica aqui 
				}
				//se nao é id
				else{
					$lexico->tipo = 'Id';
				}
				$lexico->estado = $estado;


				//criacao de id
				$verificaid = in_array_field($palavra, 'rotulo', $GLOBALS['TS']);

				if($verificaid){
					$lexico->id = $verificaid;
				}
				else{
					$lexico->id = $GLOBALS['id'];
					$GLOBALS['id']++;
				}
				
				
				array_push($GLOBALS['TS'], $lexico);
			}
		}

		//print_r($afd[$estado][$simbolo]);
		
	}


	function xml_attribute($object, $attribute){
	    if(isset($object[$attribute]))
	        return (string) $object[$attribute];
	}

	function getParsing($xml, $parsing){
		

		foreach($xml->m_Symbol->Symbol as $value) {  //busca na tabela m_Symbol o index e o name
			$index = xml_attribute($value, 'Index');
			$simbolo = xml_attribute($value, 'Name');
			$GLOBALS['parserSimbolos'][$index] = $simbolo;  //recebe os simbolos da gramatica
		}

		foreach($xml->LALRTable->LALRState as $i) { //cria a tabela de parsing, coloca numa matriz

			$linha = xml_attribute($i, 'Index');
			foreach($i->LALRAction as $j){
				$aux = new LALRState; 
				$aux->estado = xml_attribute($j, 'Value'); //qual estado ir
				$aux->acao = xml_attribute($j, 'Action');  //qual acao fazer
				$coluna = xml_attribute($j, 'SymbolIndex'); 
				$parsing[$linha][$coluna] = $aux;
			}
		}

		return $parsing;
	}

	function sintatico($parsing, $xml){
		$acao = 0;  
		$pilha = array(); //inicia a pilha
		$posicao = 0;   //posicao inicial é 0
		$i=0;        //coluna
		$reduziu = 0;    //verifica se reduziu
		array_push($pilha, 0);   //coloca o 0 no inicio da pilha
		$erro = 0;

		while($erro != 1 AND $acao != 4){ //se foi encontrado erro ou aceitou finaliza o while
			
			//verificações se reduziu ou nao
			if($reduziu == 0){
				$ultimo = end($pilha); //recebe ultimo elemento da pilha
				$simbolo = array_search($GLOBALS['TS'][$posicao]->tipo, $GLOBALS['parserSimbolos']);   //busca na tabela de simbolos qual a coluna
			}
			else{
				$simbolo = end($pilha); //ultimo da pilha coluna
				$ultimo = $auxreducao;  //penultimo linha
				$reduziu = 0; //reduziu
			}

			/*echo 'Linha:'.$ultimo.' Coluna:'.$simbolo.'       Pilha: ';
			print_r($pilha); echo ' '.$acao;
			echo '<br>';*/

			if(isset($parsing[$ultimo][$simbolo])){    //se nao é vazio
				$aux = $parsing[$ultimo][$simbolo];
				$acao = $aux->acao;
				$estado = $aux->estado;
			}
			else{
				$erro = 1;
			}

			if($acao == 1){                                  //Empilha
				array_push($pilha, $simbolo, $estado);
				$i+=1;              //move a fita
				$posicao = $i;
				$reduziu = 0;
			}
			else if($acao ==  2){     //redução 
				foreach($xml->m_Production->Production as $prod) {
					if(xml_attribute($prod, 'Index') == $estado){						
						$size = xml_attribute($prod, 'SymbolCount');
						$size = $size * 2;						//desempilha o dobro de produçoes empilhadas
						$naoterminal = xml_attribute($prod, 'NonTerminalIndex');   //da nome a regra
					}
				}

				if($size<=count($pilha)){  //para controlar e n reduzir alem do que ja esta na pilha
					while ($size!=0) {
						array_pop($pilha); //remove da pilha 
						$size--; //diminui tamanho de controle
					}
					array_push($pilha, $naoterminal);  //empilha o não terminal na pilha
					$auxreducao = $pilha[count($pilha)-2]; 

					$reduziu = 1;
				}
				else{
					$erro = 1;
				}

				
			}
			else if($acao == 3){   				//salto
				array_push($pilha, $estado);  //adiciono o estado na pilha, n movo a fita
				$reduziu = 0;
			}

			
		}
	
		if($acao == 4){  //ACEITA 
			echo 'Aceita';
		}

		if($erro == 1){
			echo 'Erro sintático na linha '.$GLOBALS['TS'][$posicao-1]->linha;   //
		}
		return 0;
	}
	
?>