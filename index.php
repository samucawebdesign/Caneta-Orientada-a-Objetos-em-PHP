﻿<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Documento sem título</title>
</head>

<body>
	<pre>
	<?PHP
	require_once('Caneta.php');
	$c1 = new Caneta;
	$c1->modelo = 'Bic Cristal';
	$c1->cor = 'Azul';
	$c1->ponta = 0.5;
	$c1->carga = 99;
	$c1->tampada = true;
	
	#print_r($c1);
	$c1->rabiscar();
	$c1->tampar();
	$c1->rabiscar(); #teste 123
	print_r($c1); # olá
	# Mas e agora, fiz ajustes e não sei mais o que eu mexi
	
	# Mudei para o ambiente de testes
	?>
    </pre>
</body>
</html>
