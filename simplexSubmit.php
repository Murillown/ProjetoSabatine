<?php
ini_set('display_errors',E_ALL);
error_reporting(0);
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf8">
	</head>
<body>
<?php
  $expressao = $_GET["expression"];
  $op = $_GET["op"];
  $opmx = $_GET["opmx"];

  for($i=0; $i<(sizeof($_POST)-3); $i++){
    $restricoes[$i] = $_POST["restricao".($i+1)];
  }
  $nres = sizeof($restricoes);
  if($opmx==0)
    $mx = "MAX";
  else
    $mx = "MIN";

  echo "-------------------------- Método Simplex --------------------------";
  //echo "<br/><br/>Expressão--> $expressao";//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
?>
<?php  
  $ni = -1;
  $c = 0;
  $j=0;
  $nvd=0;
  $ncont=0;
  $neg = 0;
  $negv = 0;
  for($i=0;$i<strlen($expressao);$i++)//Extrair variáveis de decisão.
  {
    if(is_numeric($expressao[$i])==true)//É Número.
	{
	   if($c==0)
	   {
	     $ni = $i;
	     $c = 1;
	   }
	   $ncont++;
	   if($i>0)
	   {
	     if(strcmp($expressao[$i-1],'-')==0)
	       $neg = 1;
	     else
	       $neg = 0;
	   }
	   else
	     $neg = 0;
	}
     else//Não é número.É variável ou sinal.
	{	
	   if($c==1)//Tinha começado um número.
	   {
	        if($neg==1)
		  $variadec[$j][0] = ((float)substr($expressao,(int)$ni,(int)$ncont))*(-1);
		else
		  $variadec[$j][0] = (float)substr($expressao,(int)$ni,(int)$ncont);
		 $variadec[$j][1] = $expressao[$i];
		 $c = 0;
		 $j++;
		 $nvd++;
		 $ncont=0;
		 $neg=0;
	   }
	   else
	     if(strcmp($expressao[$i],'+')!=0 && strcmp($expressao[$i],'-')!=0)//Não é sinal.Provável 1.
		 {
		   if($negv==1)
		     $variadec[$j][0] = (float)-1;
		   else
		     $variadec[$j][0] = (float)1;
		   $variadec[$j][1] = $expressao[$i];
		   $j++;
		   $nvd++;
		   $negv = 0;
		 }
             else
                if(strcmp($expressao[$i],'-')==0 && is_numeric($expressao[$i+1])==false)
                  $negv = 1;
	}
       
  }
  
  if($opmx==1)
  {
    for($i=0;$i<$nvd;$i++)
      $variadec[$i][0] = ($variadec[$i][0])*-1;
  }
  /*echo "<br/>Variáveis de decisão--> ";//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
  for($i=0;$i<$nvd;$i++)
    echo "{$variadec[$i][0]}{$variadec[$i][1]} | ";*/

  for($i=0;$i<$nres;$i++)//Zerando matriz restricoes_p
     for($j=0;$j<$nvd+1;$j++)
        $restricoes_p[$i][$j] = 0;
    
  $ncont=0;  
  $negv = 0;
  $neg = 0;
  for($m=0;$m<$nres;$m++)//Extrair restrições.
  {
   //echo "<br/> Restrição $m --> $restricoes[$m]";//|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
   for($i=0,$c=0;$i<strlen($restricoes[$m]);$i++)
   {
    if(is_numeric($restricoes[$m][$i])==true)//É Número.
	{
	   if($c==0)
	   {
	     $ni = $i;
	     $c = 1;
	   }
	   $ncont++;
	   if($i>0)
	   {
	     if(strcmp($restricoes[$m][$i-1],'-')==0)
	       $neg = 1;
	     else
	       $neg = 0;
	   }
	   else
	     $neg = 0;
	   if($i==strlen($restricoes[$m])-1)
	   {
	     if($neg==1)
	       $restricoes_p[$m][$nvd] = ((float)substr($restricoes[$m],(int)$ni,(int)$ncont))*(-1);
	     else
	       $restricoes_p[$m][$nvd] = (float)substr($restricoes[$m],(int)$ni,(int)$ncont);
	     $c = 0;
	     $ncont=0;
	     $neg = 0;
	   }
	}
    else//Não é número.É variável ou sinal.
	{   
	   if($c==1)//Tinha começado um número.
	   {
	         for($u=0;$u<$nvd;$u++)
		      if(strcmp($variadec[$u][1],$restricoes[$m][$i])==0)
		         $j=$u;
		 if($neg==1)
		   $restricoes_p[$m][$j] = ((float)substr($restricoes[$m],(int)$ni,(int)$ncont))*(-1);
		 else
		   $restricoes_p[$m][$j] = (float)substr($restricoes[$m],(int)$ni,(int)$ncont);
		 $c = 0;
		 $ncont=0;
		 $neg = 0;
	   }
	   else
	     if(strcmp($restricoes[$m][$i],'+')!=0 && strcmp($restricoes[$m][$i],'-')!=0)//Não é sinal.Provável 1.
		 {
		   if(strcmp($restricoes[$m][$i],'<')==0||strcmp($restricoes[$m][$i],'>')==0)
		   {
		      if(strcmp($restricoes[$m][$i+1],'=')==0)
		        $i++;
		   }
		   else
		   {
		    for($u=0;$u<$nvd;$u++)
		      if(strcmp($variadec[$u][1],$restricoes[$m][$i])==0)
		         $j=$u;
		    if($negv==1)
		      $restricoes_p[$m][$j] = (float)-1;
		    else
		      $restricoes_p[$m][$j] = (float)1;
		    $negv = 0;
		   }
		 }
             else
               if(strcmp($restricoes[$m][$i],'-')==0 && is_numeric($restricoes[$m][$i+1])==false)
                  $negv = 1;
	}
   }}
   
  /*echo "<br/><br/> Matriz dos valores das restrições";//||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
  for($i=0;$i<$nres;$i++)
  {
    echo"<br/>";
    for($p=0;$p<=$nvd;$p++)
      print str_pad("{$restricoes_p[$i][$p]}", 10,".", STR_PAD_RIGHT) ;
  }*/
  
  echo"<br/>";
  //Preenchendo linha Z.
  $tableau[0][0] = 'Z';
  for($i=0;$i<$nvd;$i++)
   $tableau[0][$i+1] = $variadec[$i][0]*-1;
  for($i=0;$i<$nres+1;$i++)
   $tableau[0][$i+1+$nvd] = 0;
  $tableau[0][1+$nvd+$nres] = 0;
   
  //Preenchendo linhas das restrições.
  for($j=1,$i=0;$j<=$nres;$j++)
  {
   $tableau[$j][0] = $j+$nvd;
   
   for($k=0;$k<$nvd;$k++)//Variáveis de decisão.
     $tableau[$j][$k+1] = $restricoes_p[$j-1][$k];
     
   for($k=0;$k<$nres;$k++)//Zerando variáveis de folga
     $tableau[$j][$k+1+$nvd] = 0;
     
   $tableau[$j][$j+$nvd] = 1;
   
   $tableau[$j][1+$nvd+$nres] = $restricoes_p[$j-1][$nvd];
  }
  
  if($op>2){////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  echo "<br/><br/>Matriz Inicial Computada!";
  for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
		 if($p==0 && $i>0)
		   if($tableau[$i][$p]<=$nvd)
		      $print = $variadec[$tableau[$i][$p]-1][1];
		   else
		      $print = "f".strval($tableau[$i][$p]-$nvd);
		 else
           if(is_float($tableau[$i][$p])==true)
             $print = number_format($tableau[$i][$p],1,',','.');
           else
             $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }}
  
  $parada = 1;
  $it = 0;
  while($parada==1)
  {
     $it++;
	 if($op>1){/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     echo "<br/>"."<br/>";
	 echo "<br/> $it"."º"." Iteração:";
     for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
		 if($p==0 && $i>0)
		   if($tableau[$i][$p]<=$nvd)
		      $print = $variadec[$tableau[$i][$p]-1][1];
		   else
		      $print = "f".strval($tableau[$i][$p]-$nvd);
		 else
           if(is_float($tableau[$i][$p])==true)
             $print = number_format($tableau[$i][$p],1,',','.');
           else
             $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }}
     //Achar o menor coeficiente da função objetivo.
     $menorc[0] = $tableau[0][1];
     $menorc[1] = 1;
     for($i=0;$i<$nvd+$nres+1;$i++)
       if($tableau[0][$i+1] < $menorc[0])
       {
          $menorc[0] = $tableau[0][$i+1];
          $menorc[1] = $i+1;
       }
	 if($op>1){/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     echo "<br/>Menor coeficiente --> $menorc[0]"."[".$menorc[1]."]";}
       
     //Quem sai?
     $noelpo=0;
     for($k=0;$k<$nres;$k++)//Verificar se tem elemento positivo.
       if($tableau[$k+1][$menorc[1]] <= 0)
          $noelpo++;
     if($noelpo==$nres)//A solução deve parar.
     {
        $parada = 0;
        $noelpo = 0;
        break;
     }
     $noelpo = 0;
     
     for($k=0;$k<$nres;$k++)/*Dividir elementos da última coluna pelos correspondentes elementos positivos da coluna da variável a entrar na base.*/
       if($tableau[$k+1][$menorc[1]]>0)
           $quociente[$k] = ($tableau[$k+1][1+$nvd+$nres])/($tableau[$k+1][$menorc[1]]);
       else
           $quociente[$k] = -1;
       
     
     for($s=0;$s<=$nres;$s++)
        if($quociente[$s]>=0)
        {        
          $menorq[0] = $quociente[$s];//Procurar o menor quociente. Ele vai indicar a varável que sai.
          $menorq[1] = $s;
          break;
        }
     for($i=0;$i<$nres;$i++)
       if($quociente[$i] < $menorq[0] && $quociente[$i]>=0)
       {
          $menorq[0] = $quociente[$i];
          $menorq[1] = $i;//Variável que vai sair.
       }
	 if($op>1){/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     echo "<br/>Menor quociente --> $menorq[0]"."[".$menorq[1]."]";}
     $tableau[$menorq[1]+1][0] = $menorc[1];
     
     $pivo = $tableau[$menorq[1]+1][$menorc[1]];
     for($k=1;$k<=($nvd+$nres+1);$k++)//Dividindo linha($menorq[1]) pelo pivô.
       $tableau[$menorq[1]+1][$k] = ($tableau[$menorq[1]+1][$k])/$pivo;
	 if($op>2){/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     echo "<br/>"."<br/>";
	 echo "<br/> Dividindo a linha do menor quociente pelo pivô: $pivo.";
     for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
		 if($p==0 && $i>0)
		   if($tableau[$i][$p]<=$nvd)
		      $print = $variadec[$tableau[$i][$p]-1][1];
		   else
		      $print = "f".strval($tableau[$i][$p]-$nvd);
		 else
           if(is_float($tableau[$i][$p])==true)
             $print = number_format($tableau[$i][$p],1,',','.');
           else
             $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }}
       
     for($k=0;$k<$nres+1;$k++)/*Tornar a coluna [$menorc] em um vetor identidade com o elemento x na coluna($menorq[1]).*/
       if($tableau[$k][$menorc[1]]!=0 && $k!=$menorq[1]+1)//Deixar nulo todos os elementos da coluna.
       {
         $point = $tableau[$k][$menorc[1]]*-1;
         for($r=0;$r<($nvd+$nres+2);$r++)
           $tableau[$k][$r+1] = ($tableau[$menorq[1]+1][$r+1]*$point)+$tableau[$k][$r+1];
       }
      
	 if($op>2){/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     echo "<br/>"."<br/>";
	 echo "<br/> Transformando coluna do menor coeficiente em um vetor identidade.";
     for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
		 if($p==0 && $i>0)
		   if($tableau[$i][$p]<=$nvd)
		      $print = $variadec[$tableau[$i][$p]-1][1];
		   else
		      $print = "f".strval($tableau[$i][$p]-$nvd);
		 else
           if(is_float($tableau[$i][$p])==true)
             $print = number_format($tableau[$i][$p],1,',','.');
           else
             $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }}
    $contp = 0;
    for($i=0;$i<($nvd+$nres+1);$i++)
      if($tableau[0][$i+1]<0)
         $contp++;
    if($contp==0)
       $parada = 0;
		
	$inf++;
	if($inf>100)
	  $parada = 0;
  }
if($inf<100){  
  if($opmx==1)
   $tableau[0][$nvd+$nres+1] = ($tableau[0][$nvd+$nres+1])*-1;
  $it++;
  echo "<br/>"."<br/>";
  echo "-------------------------- Relatório Final --------------------------<br/>";
  echo "<br/> Matriz Final Computada!";
  for($i=0;$i<$nres+1;$i++)
      {
        echo"<br/>";
        for($p=0;$p<$nvd+$nres+2;$p++)
        {
		 if($p==0 && $i>0)
		   if($tableau[$i][$p]<=$nvd)
		      $print = $variadec[$tableau[$i][$p]-1][1];
		   else
		      $print = "f".strval($tableau[$i][$p]-$nvd);
		 else
           if(is_float($tableau[$i][$p])==true)
             $print = number_format($tableau[$i][$p],1,',','.');
           else
             $print = $tableau[$i][$p];
         print str_pad("$print", 10,".", STR_PAD_RIGHT) ;
        }
      }
  
  for($i=0;$i<$nres;$i++)
    $vfolga[$i]=0;  
  echo "<br/><br/>Variáveis Básicas:<br/>";
  for($i=0;$i<$nres;$i++)
  {
    if($tableau[$i+1][0]<=$nvd)//Variável de decisão.
	{
	  echo "<br/>Variável de decisão {$variadec[$tableau[$i+1][0]-1][1]} = ".number_format($tableau[$i+1][$nvd+$nres+1],2,',','.');
	}
	else //Variável de folga.
	{
	  $n = ($tableau[$i+1][0]-$nres);
	  $vfolga[$n-1] = 1;
	  echo "<br/>Variável de folga f$n referente a $n"."°"." restrição = ".number_format($tableau[$i+1][$nvd+$nres+1],2,',','.');
	}
  }
  echo "<br/>Função objetivo $mx Z = ".number_format($tableau[0][$nvd+$nres+1],2,',','.');
  
  echo "<br/><br/>Variáveis Não Básicas:<br/>";
  for($i=0;$i<$nres;$i++)
    if($vfolga[$i]==0)
	{
	   $n = $i+1;
	   echo "<br/>Variável de folga f$n referente a $n"."°"." restrição = 0";
	}
	
  echo "<br/>"."<br/>";
  echo "--------------------- Análise de Sensibilidade ---------------------<br/>";
  echo "<br/>Preço Sombra das restrições:";
  for($i=1;$i<=$nres;$i++)
    echo "<br/>Restrição $i --> ".number_format($tableau[0][$nvd+$i],2,',','.');
	
  echo "<br/><br/>Limites das restrições:";	
  for($i=0;$i<$nres;$i++)
  {
    $desreslim = 0;
    for($j=0;$j<$nres;$j++)
     if($tableau[$j+1][$i+$nvd+1]<0.001 && $tableau[$j+1][$i+$nvd+1]>-0.001)//......................................................................................
       {
        $desreslim = 1;//Impossível divisão por zero.
        break;
       }
     else
        $lim[$j] = $tableau[$j+1][$nvd+$nres+1]/($tableau[$j+1][$i+$nvd+1]*-1);
          
	$trocou = 1;
	while($trocou==1 && $desreslim==0)//Ordenar vetor.
	{
	 $trocou = 0;
	 for($j=0;$j<$nres-1;$j++)
	  if($lim[$j]>$lim[$j+1])
	  {
	         $auxl = $lim[$j];
		 $lim[$j] = $lim[$j+1];
		 $lim[$j+1] = $auxl;
		 $trocou = 1;
	  }
	}
	 
	//Examinando vetor.
	if($desreslim==0)
	{
	 $neg = 0;
	 $pos = 0;
	 for($j=0;$j<$nres;$j++)
	  if($lim[$j]<0)
	     $neg++;
	  else
	    if($lim[$j]>0)
	         $pos++;
	     
	 if($neg>0 && $pos>0)//Colocando os limites nos extremos do vetor.
	  {
	   for($j=0;$j<$nres-1;$j++)
	    if($lim[$j]>0)
	     {
	       $auxl = $lim[$j];
	       $lim[$j] = $lim[$nres-1];
	       $lim[$nres-1] = $auxl;
	       $auxl = $lim[$j-1];
	       $lim[$j-1] = $lim[0];
	       $lim[0] = $auxl;
	       break;
	     }
	  }
	 else//Só números positivos.
	  if($pos>0)
	  {
	       $auxl = $lim[1];
	       $lim[1] = $lim[$nres-1];
	       $lim[$nres-1] = $auxl;
	  }
	  else//Só números negativos.
	  {
	       $auxl = $lim[$nres-2];
	       $lim[$nres-2] = $lim[0];
	       $lim[0] = $auxl;
	  }
	 
	 $lim[0]+=$restricoes_p[$i][$nvd];
	 $lim[$nres-1]+=$restricoes_p[$i][$nvd]; 
	
	 echo "<br/>Restrição ".($i+1)." --> ".number_format($lim[0],2,',','.')." <= b".($i+1)." <= ".number_format($lim[$nres-1],2,',','.');
	}
  }
  
  echo "<br/><br/>Valores finais das restrições:";	
  for($i=0;$i<$nres;$i++)
  {
     echo "<br/>Restrição ".($i+1)." --> ".number_format($tableau[$i+1][$nvd+$nres+1],2,',','.');
  }
	
  printf("<br/><br/><a href='simplex.php'>Clique aqui para calcular novamente.</a><br/><br/>");
}
else
{
  echo "Parece que esse sistema é impossível de ser resolvido!";
  printf("<a href='simplex.php'>Tente novamente aqui.</a><br/><br/>");
}
?>
<!DOCTYPE html>