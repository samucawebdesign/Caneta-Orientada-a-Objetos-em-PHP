<?PHP
if($action == 'pedido_num_produto')
	{
		include("../config.php");
		include("header.php");
		session_start();
		include("verifica_sessao.php");
		verifica_num($id_ped);
		$sql = mysql_query("select 
							tb_pedido.id_ped
							,(SELECT COUNT(id_pro) FROM tb_itens_pedido WHERE id_ped=tb_pedido.id_ped) AS total_unico
							,(SELECT SUM(qtde_itp) FROM tb_itens_pedido WHERE id_ped=tb_pedido.id_ped) AS total_geral
							from tb_pedido where id_ped=$id_ped
							having total_unico > 0
							order by tb_pedido.data_hora_ped desc") or die("Erro");
		$linhas = mysql_num_rows($sql);
		if($linhas == '')
			{
				?>
				<script type="text/javascript">
				document.getElementById('divTotalUnico<?PHP echo($id_ped); ?>').innerHTML = '0';
				document.getElementById('divTotalGeral<?PHP echo($id_ped); ?>').innerHTML = '0';
				</script>
				<?PHP
			}
		while($dados=mysql_fetch_assoc($sql))
			{
				?>
				<script type="text/javascript">
				document.getElementById('divTotalUnico<?PHP echo($id_ped); ?>').innerHTML = '<?PHP echo($dados[total_unico]); ?>';
				document.getElementById('divTotalGeral<?PHP echo($id_ped); ?>').innerHTML = '<?PHP echo($dados[total_geral]); ?>';
				</script>
				<?PHP
			}
	}
else if($action == 'pedido_definir_filial')
	{
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				include("../config.php");
				include("header.php");
				session_start();
				include("verifica_sessao.php");
				verifica_num($id_fil2);
				$up = mysql_query("update tb_pedido set id_fil2='$id_fil2' where id_ped=$id_ped") or die("Erro");
			}
	}
else if($action == 'pedido_update')
	{
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				include("../config.php");
				include("header.php");
				session_start();
				include("verifica_sessao.php");
				verifica_num($id_itp);
				$preco_pro = addslashes($preco_pro);
				$preco_pro = str_replace(".","",$preco_pro);
				$preco_pro = str_replace(",",".",$preco_pro);
				
				$up = mysql_query("update tb_itens_pedido set preco_itp='$preco_pro' where id_itp=$id_itp") or die("Erro");
			}
	}
else if($action == 'gera_sql')
	{
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				include("../config.php");
				include("header.php");
				verifica_num($id_ped);
				
		
				$sql5 = mysql_query("select 
									 tb_pedido.id_cot
									 ,tb_cliente_fornecedor.id_cli
									 ,tb_cliente_fornecedor.razao_social_cli
									 ,tb_cliente_fornecedor.codfornec
									 ,tb_cliente_fornecedor.codcomprador
									 from tb_pedido
									 inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
									 where tb_pedido.id_cot=$id_cot
									 group by tb_cliente_fornecedor.id_cli") or die("Erro1");
				while($dados5=mysql_fetch_assoc($sql5))
					{
						$sql3 = mysql_query("select 
											 tb_pedido.id_cot
											 ,tb_filial.id_fil 
											 ,tb_filial.nome_fil
											 ,tb_pedido.id_ped
											 ,date_format(data_hora_ped, '%d/%m/%Y') as data_hora_ped_f
											 from tb_pedido
											 inner join tb_itens_pedido on tb_itens_pedido.id_ped=tb_pedido.id_ped
											 inner join tb_produto on tb_produto.id_pro=tb_itens_pedido.id_pro
											 inner join tb_filial on tb_filial.id_fil=tb_pedido.id_fil
											 where tb_pedido.id_cot=$dados5[id_cot] and tb_pedido.id_fil>0
											 group by tb_filial.id_fil
											 order by tb_filial.nome_Fil") or die(mysql_error());
						while($dados3=mysql_fetch_assoc($sql3))
							{
								$sql4 = mysql_query("select 
													 qtde_itp
													 ,cod_barras_pro
													 ,preco_pro
													 ,tb_itens_pedido.id_pro as id_pro_f
													 ,(select tb_produto.id_pro from tb_produto
													   inner join tb_itens_pedido on tb_itens_pedido.id_pro=tb_produto.id_pro
													   inner join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
													   where tb_pedido.id_cli=$dados5[id_cli] and tb_produto.id_pro=id_pro_f limit 1) as id_pro_f2
													 from tb_pedido
													 inner join tb_itens_pedido on tb_itens_pedido.id_ped=tb_pedido.id_ped
													 inner join tb_produto on tb_produto.id_pro=tb_itens_pedido.id_pro
													 where tb_pedido.id_cot=$dados3[id_cot] and tb_pedido.id_fil=$dados3[id_fil]
													 having id_pro_f=id_pro_f2
													 order by tb_itens_pedido.id_itp") or die(mysql_error());
								$linhas4 = mysql_num_rows($sql4);
								if($linhas4 != '')
									{
										$arquivo = $titulo.' - '.date("d/m/Y").'.xls';
										$html .= "<br><br># Fornecedor $dados5[razao_social_cli] | Filial: $dados3[nome_fil]<br>INSERT INTO pcsugestaocomprac (NUMSUGESTAO,CODFILIAL,CODFORNEC,CODUSUARIOSUGESTAO,DATASUGESTAO) VALUES ('$dados3[id_ped]','$dados3[id_fil]','$dados5[codfornec]','$dados5[codcomprador]','$dados3[data_hora_ped_f]');<br>";
										while($dados4=mysql_fetch_assoc($sql4))
											{
												if($dados4[qtde_itp] != '' and $dados4[qtde_itp] > 0)
													{
														$html .= "<br>INSERT INTO pcsugestaocomprai (NUMSUGESTAO, CODPROD, QTSUGERIDA,PCOMPRALIQSUGERIDO) VALUES ('$dados3[id_ped]','$dados4[cod_barras_pro]','$dados4[qtde_itp]','$dados4[preco_pro]');";
													}
											}
									}
							}
					}
							
				echo($html);
				die;
				
				// Configurações header para forçar o download
				header ("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header ("Last-Modified: " . gmdate("D,d M YH:i:s") . " GMT");
				header ("Cache-Control: no-cache, must-revalidate");
				header ("Pragma: no-cache");
				header ("Content-type: application/x-msexcel");
				header ("Content-Disposition: attachment; filename=\"{$arquivo}\"" );
				header ("Content-Description: PHP Generated Data" );
				
				// Envia o conteúdo do arquivo
				echo $html;
				exit;		
			}
	}
else if($action == 'pedido_produto_update')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				verifica_num($id_itp);
				verifica_num($id_pro);
				verifica_num($id_cli);
				
				$sql = mysql_query("select * from tb_itens_pedido
									inner join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
									where tb_itens_pedido.id_itp=$id_itp
									group by tb_pedido.id_ped") or die("Erro");
				while($dados=mysql_fetch_assoc($sql))
					{
						$id_cli_antigo = $dados[id_cli];
					}
		
				$sql = mysql_query("select * from tb_pedido where id_cli=$id_cli order by id_ped desc limit 1") or die("Erro");
				while($dados=mysql_fetch_assoc($sql))
					{
						$up = mysql_query("update tb_itens_pedido set id_cli=$id_cli_antigo,id_ped=$dados[id_ped],preco_itp='$preco_pro' where id_itp=$id_itp") or die("Erro");
						#$up = mysql_query("update tb_produto set preco_pro='$preco_pro' where id_pro=$id_pro") or die("Erro");
					}		
				?>
				<script type="text/javascript">
				mostra_esconde_div(1,'divFlutuante');
				mostra_esconde_div(1,'divFundo');
				pedido_produto_listar('','divProdutoAdicionado');
				</script>
				<?PHP
			}
	}
else if($action == 'pedido_produto_edit')
	{
		include("../config.php");
		include("header.php");
		verifica_num($id_itp);
		$sql = mysql_query("select
							 tb_produto.id_cap,tb_produto.id_pro,cod_barras_pro,nome_pro,desc_mar,preco_pro,tb_itens_pedido.id_itp,tb_itens_pedido.qtde_itp,tb_pedido.id_cli,tb_pedido.id_cot
							 from tb_produto
							 inner join tb_produto_categ on tb_produto_categ.id_cap=tb_produto.id_cap
							 inner join tb_marca on tb_marca.id_mar=tb_produto.id_mar
							 inner join tb_itens_pedido on tb_itens_pedido.id_pro=tb_produto.id_pro
							 inner join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
							 where tb_itens_pedido.id_itp=$id_itp
							 group by tb_produto.id_pro
							 order by tb_produto.nome_pro") or die("Erro1");
		$linhas = mysql_num_rows($sql);
		if($linhas != '')
			{
				?>
				<script type="text/javascript">
                document.getElementById('divFlutuanteTitulo').innerHTML = 'Alterar preço do item';
                </script>
                <div id="divCabecalho" style="height:15px;background-color:#F0F0F0;margin:1px;padding:1px;">
                    <div id="divColuna1" style="width:80px;float:left;font-weight:bold;">Qtde.</div>
                    <div id="divColuna1" style="width:100px;float:left;margin-left:5px;font-weight:bold;">C&oacute;digo barras</div>
                    <div id="divColuna2" style="width:250px;float:left;margin-left:5px;font-weight:bold;">Produto</div>
                    <div id="divColuna1" style="width:140px;float:left;margin-left:5px;font-weight:bold;">Marca</div>
                  <div id="divColuna1" style="width:100px;float:left;margin-left:5px;font-weight:bold;">R$ Pre&ccedil;o </div>
                </div>
               	<?PHP
				while($dados=mysql_fetch_assoc($sql))
					{
						$id_cli = $dados[id_cli];
						$id_pro = $dados[id_pro];
						$id_cot = $dados[id_cot];
						?>
						<div id="divCabecalho" style="display:table;height:20px;padding:1px;<?PHP echo($cor); ?>">
                            <div id="divColuna" style="width:80px;float:left;">
                              <input style="color:#999;" readonly name="qtde_itp<?PHP echo($dados[id_itp]); ?>" type="text" id="qtde_itp<?PHP echo($dados[id_itp]); ?>" value="<?PHP echo($dados[qtde_itp]); ?>" size="6" maxlength="6" onclick="alert('Desculpe, mas este pedido já está fechado, não é possível alterá-lo.');" />
  							</div>
                            <div id="divColuna" style="width:100px;float:left;margin-left:5px;"><?PHP echo($dados[cod_barras_pro]); ?></div>
                            <div id="divColuna" style="width:250px;float:left;margin-left:5px;"><?PHP echo($dados[nome_pro]); ?>&nbsp;</div>
                            <div id="divColuna" style="width:140px;float:left;margin-left:5px;"><?PHP echo($dados[desc_mar]); ?></div>
  							<div id="divColuna" style="width:100px;float:left;margin-left:5px;"><input style="color:#999;" readonly name="qtde_itp<?PHP echo($dados[id_itp]); ?>" type="text" id="qtde_itp<?PHP echo($dados[id_itp]); ?>" value="<?PHP echo(number_format($dados[preco_pro], 2, ',', '.')); ?>" size="6" maxlength="6"/></div>
						</div>
						<?PHP
					}
				?>
                <div style="clear:both;margin-top:20px;">
                	<?PHP
					# Lista a cotação que o fornecedor participou
					$sql3 = mysql_query("select *
										 ,date_format(data_inicial_cot, '%d/%m/%y') as data_inicial_cot_f
										 ,date_format(data_final_cot, '%d/%m/%y') as data_final_cot_f
										 ,(SELECT COUNT(id_pro) FROM tb_itens_cotacao WHERE id_cot=tb_cotacao.id_cot) AS total_unico
										 ,(SELECT SUM(qtde_ite) FROM tb_itens_cotacao WHERE id_cot=tb_cotacao.id_cot) AS total_geral
										 from tb_cotacao
										 inner join tb_cotacao_for on tb_cotacao_for.id_cot=tb_cotacao.id_cot
										 where tb_cotacao_for.id_cli='$id_cli' and tb_cotacao_for.id_cot='$id_cot'
										 order by tb_cotacao.data_inicial_cot") or die("Erro");
					$linhas3 = mysql_num_rows($sql3);
					if($linhas3 == '')
						{
							msg_status(1,'Nenhum resultado encontrado.');
						}
					else
						{
						?>
                            <div id="divCabecalho" style="height:15px;background-color:#F0F0F0;margin:1px;padding:1px;">
                                <div id="divColuna1" style="width:130px;float:left;font-weight:bold;">Cota&ccedil;&atilde;o</div>
                                <div id="divColuna1" style="width:550px;float:left;font-weight:bold;margin-left:5px;">Participantes</div>
                            </div>
                            <br />
                              <?PHP
							while($dados3=mysql_fetch_assoc($sql3))
								{
									$num++;
									if($num % 2 == 0)
										{
											$cor = 'background-color:#F0F0F0;';
										}
									else
										{
											$cor = '';
										}
									?>
									<div id="divCabecalho<?PHP echo($num); ?>" style="display:table;height:20px;<?PHP echo($cor); ?>padding:1px;">
										<div id="divColuna1<?PHP echo($num); ?>" style="width:130px;float:left;"><?PHP echo($dados3[data_inicial_cot_f].' à '.$dados3[data_final_cot_f]); ?></div>
										<div id="divColuna1<?PHP echo($num); ?>" style="width:550px;float:left;margin-left:5px;">
										<?PHP
										$sql2 = mysql_query("select 
															 tb_cliente_fornecedor.id_cli,tb_cliente_fornecedor.razao_social_cli
															 ,(SELECT COUNT(id_itf) FROM tb_itens_cotacao_for 
															   inner join tb_cotacao_for on tb_cotacao_for.id_cof=tb_itens_cotacao_for.id_cof
															   WHERE tb_cotacao_for.id_cli=tb_cliente_fornecedor.id_cli and tb_cotacao_for.id_cot=tb_itens_fornecedor.id_cot) AS total
															 ,(SELECT prazo_pgto_cof FROM tb_cotacao_for WHERE id_cli=tb_cliente_fornecedor.id_cli and id_cot=tb_itens_fornecedor.id_cot) AS prazo_pgto_cof
															 ,(SELECT entrega_cof FROM tb_cotacao_for WHERE id_cli=tb_cliente_fornecedor.id_cli and id_cot=tb_itens_fornecedor.id_cot) AS entrega_cof
															 ,situacao_itf
															 ,tb_itens_fornecedor.id_itf
															 ,(SELECT preco_itf FROM tb_itens_cotacao_for
															   inner join tb_cotacao_for on tb_cotacao_for.id_cof=tb_itens_cotacao_for.id_cof
															   WHERE tb_itens_cotacao_for.id_cli=tb_cliente_fornecedor.id_cli 
															   and tb_cotacao_for.id_cot=tb_itens_fornecedor.id_cot and id_pro=$id_pro) AS preco_itf
															,(SELECT min(preco_itf) FROM tb_itens_fornecedor
															   inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_itens_fornecedor.id_cli
															   inner join tb_itens_cotacao_for on tb_itens_cotacao_for.id_cli=tb_cliente_fornecedor.id_cli
															   inner join tb_cotacao_for on tb_cotacao_for.id_cof=tb_itens_cotacao_for.id_cof
															   WHERE tb_cotacao_for.id_cot=$dados3[id_cot] and tb_itens_cotacao_for.id_pro=$id_pro
															   and tb_itens_fornecedor.situacao_itf=1) AS preco_mais_baixo
															 from tb_itens_fornecedor
															 inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_itens_fornecedor.id_cli
															 where tb_itens_fornecedor.id_cot=$dados3[id_cot] and tb_itens_fornecedor.id_cli!=$id_cli
															 group by tb_cliente_fornecedor.id_cli
															 order by tb_cliente_fornecedor.razao_social_cli") or die("Erro");
										?>
                                        <div id="divCabecalho" style="height:15px;background-color:#F0F0F0;margin:1px;padding:1px;">
                                            <div id="divColuna1" style="width:290px;float:left;">Fornecedor</div>
                                            <div id="divColuna1" style="width:100px;float:left;margin-left:15px;">Pre&ccedil;o do item</div>
                                            <div id="divColuna1" style="width:100px;float:left;margin-left:5px;text-align:center;">Op&ccedil;&otilde;es</div>
                                        </div>
                                        <br />
                                        <?PHP
										while($dados2=mysql_fetch_assoc($sql2))
											{
												$preco_itf = $dados2[preco_itf];
												$id_cli_f = $dados2[id_cli];
												?>
                                                <div id="divColuna1" style="width:290px;float:left;">
                                                <?PHP
												echo('• '.$dados2[razao_social_cli].' ');
												if($dados2[prazo_pgto_cof] != '0000-00-00' and $dados2[entrega_cof] > 0)
													{
														?><font style="color:#060;"><?PHP echo( '(Concluído)');?></font><?PHP
													}
												else if($dados2[total] <= 0)
													{
														$erro++;
														?><font style="color:#F00;"><?PHP echo( '(Ainda não iniciou)');?></font><?PHP
													}
												else if($dados2[total] > 0)
													{
														$erro++;
														?><font style="color:#069;"><?PHP echo( '(Cotando...)');?></font><?PHP
													}
												?>
                                                </div>
                                                <div id="divColuna1" style="width:100px;float:left;margin-left:15px;"><?PHP if($preco_itf == ''){echo('-');}else{echo(number_format($dados2[preco_itf], 2, ',', '.'));} ?></div>
                                                <div id="divColuna1" style="width:100px;float:left;margin-left:5px;text-align:center;"><?PHP if($preco_itf == ''){echo('-');}else{?><input type="button" name="button" id="button" value="Alterar pre&ccedil;o" onclick="pedido_produto_update(<?PHP echo($id_itp); ?>,'<?PHP echo($id_cli_f); ?>','<?PHP echo($id_pro); ?>','<?PHP echo($preco_itf); ?>','divFlutuanteConteudo');" /><?PHP } ?>&nbsp;</div>
                                                <?PHP
											}
										?>
									  </div>
									</div>
									<br />
                                    <?PHP
								}
						}
					?>
                </div>
               	<?PHP
			}
	}
else if($action == 'fornecedor_envia_pedido')
	{
		include("../config.php");
		include("header.php");
		verifica_num($id_ped);
		session_start();
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				$sql = mysql_query("select * from tb_pedido 
									inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
									where tb_pedido.id_ped=$id_ped
									group by tb_pedido.id_ped") or die("Erro");
				while($dados=mysql_fetch_assoc($sql))
					{
						$id_cli = $dados[id_cli];
						$razao_social_cli = $dados[razao_social_cli];
						$email_for = $dados[email_for];
					}
				
				if($id_cli != '')
					{
						$where = " and tb_pedido.id_cli=$id_cli ";
						$group = 'group by tb_cliente_fornecedor.id_cli';
					}
				$titulo = "Relatório de pedido";
				//$arquivo = $titulo.' - '.date("d/m/Y").'.xls';
				
		
				$html .= '
				<table border="1" cellpadding="5" cellspacing="0" bordercolor="#999999">
				<tr>
				';
				$sql = mysql_query("select * 
									,if(tb_pedido.id_fil='',razao_social_cli,nome_fil) as desc_f
									from tb_pedido
									left join tb_filial on tb_filial.id_fil=tb_pedido.id_fil
									left join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
									where tb_pedido.id_ped=$id_ped $where
									$group") or die("Erro");
				while($dados=mysql_fetch_assoc($sql))
					{
						$id_cot = $dados[id_cot];
						
						$titulo .= ': '.$dados[desc_f];
						$arquivo = $titulo.' - '.date("d/m/Y").'.xls';
						$html .= '
						<td colspan="8"><strong>'.$titulo.'</strong></td>
						</tr>
						<tr>
						<td colspan="8">'.date("d/m/Y").' - '.date("H:i").'</td>
						</tr>
						';
						
						$total = 0;
						$sql3 = mysql_query("select *
											 from tb_produto
											 inner join tb_produto_categ on tb_produto_categ.id_cap=tb_produto.id_cap
											 inner join tb_marca on tb_marca.id_mar=tb_produto.id_mar
											 inner join tb_unidade_medida on tb_unidade_medida.id_uni=tb_produto.id_uni
											 inner join tb_itens_pedido on tb_itens_pedido.id_pro=tb_produto.id_pro
											 inner join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
											 where tb_pedido.id_ped=$id_ped $where
											 group by tb_produto.id_pro
											 order by tb_produto.nome_pro") or die("Erro");
						$linhas3 = mysql_num_rows($sql3);
						if($linhas3 != '')
							{
								$html .= '
								<tr>
								<td><strong>C&oacute;digo barras</strong></td>
								<td><strong>Produto</strong></td>
								<td><strong>Marca</strong></td>
								<td><strong>Unidade</strong></td>
								<td><strong>Qtde. por loja</strong></td>
								<td><strong>Qtde.Total</strong></td>
								<td><strong>R$ Pre&ccedil;o unit.</strong></td>
								<td><strong>Total</strong></td>
								</tr>
								';
								while($dados3=mysql_fetch_assoc($sql3))
									{
		
												$html .= '
												<tr>
												<td>'.$dados3[cod_barras_pro].'</td>
												<td>'.$dados3[nome_pro].'</td>
												<td>'.$dados3[desc_mar].'</td>
												<td>'.$dados3[desc_uni].'</td>';
												if($id_fil != '')
													{
														$html .= '<td>'.$dados3[qtde_itp].'</td>';
													}
												else
													{
														$html .= '<td>';
														$sql2 = mysql_query("select * 
																			 ,(select qtde_itp from tb_itens_pedido
																			   inner join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
																			   where tb_pedido.id_cot=$id_cot and tb_pedido.id_fil=tb_filial.id_fil and tb_itens_pedido.id_pro=$dados3[id_pro]) as qtde_f
																			 from tb_filial
																			 inner join tb_pedido on tb_pedido.id_fil=tb_filial.id_fil
																			 inner join tb_itens_pedido_cotacao on tb_itens_pedido_cotacao.id_ped=tb_pedido.id_ped
																			 left join tb_estado on tb_estado.id_est=tb_filial.id_est
																			 where tb_itens_pedido_cotacao.id_cot=$id_cot
																			 group by tb_filial.id_fil") or die(mysql_error());
														$linhas2 = mysql_num_rows($sql2);
														$html .= '
														<table border="1" cellpadding="5" cellspacing="0" bordercolor="#999999">
														<tr>
															';
															while($dados2=mysql_fetch_assoc($sql2))
																{
																	$total_filial[$dados2[id_fil]] = $total_filial[$dados2[id_fil]] + ($dados2[qtde_f]*$dados3[preco_itp]);
																	if($dados2[qtde_f] == '')
																		{
																			$dados2[qtde_f] = '-';
																		}
																	$html .= '<td>'.$dados2[nome_fil].'</td><td>'.$dados2[qtde_f].'</td>';
																}
															$html .= '
														</tr>
														</table>
														';
		
														$html .= '</td>';
													}
												
												$html .= '
												<td>'.$dados3[qtde_itp].'</td>
												<td>'.number_format($dados3[preco_itp], 2, ',', '.').'</td>
												<td>'.number_format($dados3[qtde_itp]*$dados3[preco_itp], 2, ',', '.').'</td>
												</tr>
												';		
												$total = $total + ($dados3[qtde_itp]*$dados3[preco_itp]);
									}
								$sql2 = mysql_query("select * 
													 from tb_filial
													 inner join tb_pedido on tb_pedido.id_fil=tb_filial.id_fil
													 inner join tb_itens_pedido_cotacao on tb_itens_pedido_cotacao.id_ped=tb_pedido.id_ped
													 left join tb_estado on tb_estado.id_est=tb_filial.id_est
													 where tb_itens_pedido_cotacao.id_cot=$id_cot
													 group by tb_filial.id_fil") or die("Erro");
								$linhas2 = mysql_num_rows($sql2);
								if($linhas2 != '')
									{
										$html .= '
										<tr>
											<td colspan="8"><strong>Totais por loja</strong></td>
										</tr>
										';
										while($dados2=mysql_fetch_assoc($sql2))
											{
												$html .= '
												<tr>																
													<td colspan="5">&nbsp;</td>
													<td colspan="2"><strong>'.$dados2[nome_fil].'</strong></td>
													<td bgcolor="#ccc">'.@number_format($total_filial[$dados2[id_fil]], 2, ',', '.').'</td>
												</tr>';
											}
									}
								$html .= '
								<tr>
									<td colspan="7"><strong>Total</strong></td>
									<td bgcolor="#D5D3EB">'.number_format($total, 2, ',', '.').'</td>
								</tr>
								<tr>
									<td colspan="8">
											Observações: '.$dados[obs_ped];
				
									$html .= '
									</td>
								</tr>
								';
							}
					}
							
				$html .= '</table>';
				
				$desc_from = $nome_config;
				$email_from = $email_config;
				$cabecalho = "MIME-Version: 1.1\n";
				$cabecalho .= "Content-type: text/html; charset=iso-8859-1\n";
				$cabecalho .= "From: $desc_from <$email_remetente>"."\n";
				$cabecalho .= "Return-Path: $desc_from <$email_from>"."\n";
				$cabecalho .= "Reply-To: $desc_from <$email_config>"."\n";
				
				$corpo = '
						<table width="100%" border="0" align="center" cellspacing="0">
						<tr> 
						<td><img src="http://'.$_SERVER['SERVER_NAME'].'/cotacao/imagens/logo-cotacao.jpg"></td>
						</tr>
						<tr> 
						<td bgcolor="#F7F7F7"> 
						  <div align="center"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif">Cota&ccedil;&atilde;o aprovada para os itens abaixo</font></div></td>
						</tr>
						<tr> 
						<td>&nbsp;</td>
						</tr>
						<tr>
						<td>
						<font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif">Este é um e-mail automático para informá-lo(a) que a sua cota&ccedil;&atilde;o para '.$desc_from.', foi aprovada com os seguintes itens:<br>
							  <br />
							  '.$html.'<br />
							  <br />
							  Em caso de dúvidas, estamos &agrave; sua disposi&ccedil;&atilde;o.<br />
						  </font>
						</td>
						</tr>
						<tr> 
						<td>&nbsp;</td>
						</tr>
						<tr> 
						<td></td>
						</tr>
						<tr> 
						<td height="13" valign="top" bgcolor="#F7F7F7"> <div align="center">  
						<a href="http://'.$site_config.'" target="_blank"><font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif">'.$nome_config.' - '.$site_config.'</font></a></div></td>
						</tr>
						</table>
						';
					#echo $corpo;
					mail("$email_for", "$razao_social_cli - Cotação aprovada para estes itens", $corpo, $cabecalho);
					
					?>
					<script type="text/javascript">
					alert('Pedido enviado com sucesso para <?PHP echo($razao_social_cli.' ('.$email_for.')'); ?>');
					</script>
					<?PHP		
			}
	}
else if($action == 'pedido_qtde_update')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				if($_SESSION['login_sessao'])
					{
						include("verifica_sessao.php");
					}
				else
					{
						include("../verifica_sessao.php");
					}
				verifica_num($id_itp);
				verifica_num($qtde_itp);
				if($qtde_itp > 0)
					{
						$up = mysql_query("update tb_itens_pedido set qtde_itp=$qtde_itp where id_itp=$id_itp") or die("Erro");
					}
			}
	}
else if($action == 'pedido_produto_del')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['id_ide_sessao'] == 2)
			{
				msg_status(2,'Nesta versão demonstrativa essa opção foi desabilitada.');
			}
		else
			{
				if($_SESSION['login_sessao'])
					{
						include("verifica_sessao.php");
					}
				else
					{
						include("../verifica_sessao.php");
					}
				verifica_num($id_ped);
				verifica_num($id_itp);
				$del = mysql_query("delete from tb_itens_pedido where id_itp=$id_itp") or die("Erro");
				?>
				<script type="text/javascript">
				pedido_produto_listar('','divProdutoAdicionado');
				</script>        
				<?PHP
			}
	}
else if($action == 'pedido_produto_cadastrar')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['login_sessao'])
			{
				include("verifica_sessao.php");
			}
		else
			{
				include("../verifica_sessao.php");
			}
		verifica_num($id_ped);
		verifica_num($id_pro);
		verifica_num($qtde_itp);
		$sql = mysql_query("select id_pro from tb_itens_pedido where id_pro=$id_pro and id_ped=$id_ped limit 1") or die("Erro");
		$linhas = mysql_num_rows($sql);
		if($linhas != '')
			{
				?>
				<script type="text/javascript">
				alert('Você já adicionou este item.');
				</script>
				<?PHP
			}
		else
			{
				$sql2 = mysql_query("select preco_pro from tb_produto where id_pro=$id_pro") or die("Erro");
				while($dados2=mysql_fetch_assoc($sql2))
					{
						$in = mysql_query("insert into tb_itens_pedido (id_ped,id_pro,qtde_itp,preco_itp) values ($id_ped,$id_pro,$qtde_itp,$dados2[preco_pro])") or die("Erro");
					}
			}
		?>
		<script type="text/javascript">
		pedido_produto_listar('','divProdutoAdicionado');
		</script>        
		<?PHP
	}
else if($action == 'pedido_produto_listar')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['login_sessao'])
			{
				include("verifica_sessao.php");
			}
		else
			{
				include("../verifica_sessao.php");
			}
		verifica_num($id_ped);
		verifica_num($page);
		verifica_num($situacao_ped);
		$consulta = "select
					 tb_produto.id_cap
					 ,tb_produto.id_pro
					 ,cod_barras_pro,nome_pro
					 ,desc_mar
					 ,preco_pro
					 ,tb_itens_pedido.id_itp
					 ,tb_itens_pedido.qtde_itp
					 ,tb_itens_pedido.preco_itp
					 ,tb_cliente_fornecedor.razao_social_cli
					 ,tb_pedido.id_cli as id_cli_f
					 from tb_produto
					 inner join tb_produto_categ on tb_produto_categ.id_cap=tb_produto.id_cap
					 left join tb_marca on tb_marca.id_mar=tb_produto.id_mar
					 inner join tb_itens_pedido on tb_itens_pedido.id_pro=tb_produto.id_pro
					 left join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
					 left join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
					 where tb_produto.id_pro!='' and tb_itens_pedido.id_ped=$id_ped
					 group by tb_produto.id_pro
					 order by tb_produto.nome_pro";	
		$sql = mysql_query("$consulta") or die(mysql_error());
		$linhas = mysql_num_rows($sql);
		if($linhas == "")
			{
				msg_status(1,'Nenhum resultado encontrado.');
				?>
                <script type="text/javascript">
				document.getElementById('produto').value = '';
				</script>
                <?PHP
			}
		else
			{
				?>
                <script type="text/javascript">
				document.getElementById('produto').value = 1;
				</script>
                <?PHP
				$busca = $consulta;
				$total_reg = "20";
				if (!$page)
					{
						$pc = "1";
					}
				else
					{
						$pc = $page;
					}
				$inicio = $pc - 1;
				$inicio = $inicio * $total_reg;
				
				$limite = mysql_query("$busca LIMIT $inicio,$total_reg");
				$todos = mysql_query("$busca");
				
				$tr = mysql_num_rows($todos); // verifica o número total de registros
				$tp = $tr / $total_reg; // verifica o número total de páginas
				
				// agora vamos criar os botões "Anterior e próximo"
				$anterior = $pc -1;
				$proximo = $pc +1;
				echo('<P align="center">');
				if ($pc>1)
					{
						echo " <a href='javascript:pedido_produto_listar($anterior,\"divProdutoAdicionado\");'>« Anterior | </a> ";
					}
				### nova paginação
				if($total_reg < $tr)
					{
						$meio = array();
						if(($page-10) < 1)
							{
								$antes = 1;
							}
						else
							{
								$antes = $page-10;
							}
						if(($page+10) > $tp)
							{
								$posterior = $tp;
							}
						else
							{
								$posterior = $page + 10;
							}
						for($i=$antes;$i <= $posterior;$i++)
							{
								if($page == $i)
									{
										$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
									}
								else
									{
										$cor = '';
									}
								$meio[] = "<a $cor href='javascript:pedido_produto_listar($i,\"divProdutoAdicionado\");'>$i</a>";
							}
						$meio_pg = join(' | ', $meio);
						echo($meio_pg);
					}
				### fim nova paginação
				if ($pc<$tp)
					{
						echo " <a href='javascript:pedido_produto_listar($proximo,\"divProdutoAdicionado\");'> | Próxima »</a>";
					}
				echo('</p>');
				?>
                <div id="divCabecalho" style="height:15px;background-color:#F0F0F0;margin:1px;padding:1px;">
                    <div id="divColuna1" style="width:80px;float:left;font-weight:bold;">Qtde.</div>
                    <div id="divColuna1" style="width:100px;float:left;margin-left:5px;font-weight:bold;">C&oacute;digo barras</div>
                    <div id="divColuna2" style="width:250px;float:left;margin-left:5px;font-weight:bold;">Produto</div>
                    <div id="divColuna1" style="width:140px;float:left;margin-left:5px;font-weight:bold;">Marca</div>
                    <div id="divColuna1" style="width:100px;float:left;margin-left:5px;font-weight:bold;">R$ Pre&ccedil;o </div>
                    <div id="divColuna1" style="width:100px;float:left;margin-left:5px;font-weight:bold;text-align:center;">Op&ccedil;&otilde;es</div>
                </div>
                <br />
                <?PHP
                $num = 0;
                while($dados=mysql_fetch_assoc($limite))
                    {
                        $num++;
                        if($num % 2 == 0)
                            {
                                $cor = 'background-color:#F0F0F0;';
                            }
                        else
                            {
                                $cor = '';
                            }
                        ?>
                        <div id="divCabecalho" style="display:table;height:20px;padding:1px;<?PHP echo($cor); ?>">
                            <div id="divColuna" style="width:80px;float:left;">
							<?PHP
                            if($situacao_ped != 2)
                                {
                                    ?>
                            		<input name="qtde_itp<?PHP echo($dados[id_itp]); ?>" type="text" id="qtde_itp<?PHP echo($dados[id_itp]); ?>" onkeyup="pedido_qtde_update(<?PHP echo($dados[id_itp]); ?>,'divPedidoUpdate');" value="<?PHP echo($dados[qtde_itp]); ?>" size="6" maxlength="6" />
                                    <?PHP
								}
							else
								{
									?>
                  <input style="color:#999;" readonly name="qtde_itp<?PHP echo($dados[id_itp]); ?>" type="text" id="qtde_itp<?PHP echo($dados[id_itp]); ?>" value="<?PHP echo($dados[qtde_itp]); ?>" size="6" maxlength="6" onclick="alert('Desculpe, mas este pedido já está fechado, não é possível alterá-lo.');" />
                                    <?PHP
								}
							?>
                          </div>
                            <div id="divColuna" style="width:100px;float:left;margin-left:5px;"><?PHP echo($dados[cod_barras_pro]); ?></div>
                            <div id="divColuna" style="width:250px;float:left;margin-left:5px;"><?PHP echo($dados[nome_pro]); ?>&nbsp;</div>
                            <div id="divColuna" style="width:140px;float:left;margin-left:5px;"><?PHP echo($dados[desc_mar]); ?></div>
                            <div id="divColuna" style="width:100px;float:left;margin-left:5px;">
							<?PHP
							if($situacao_ped != 2 and !isset($_SESSION['id_fil_sessao']))
								{
									?>
                            		<input name="preco_pro<?PHP echo($dados[id_pro]); ?>" type="text" id="preco_pro<?PHP echo($dados[id_pro]); ?>" onkeyup="pedido_update('<?PHP echo($dados[id_itp]); ?>',<?PHP echo($dados[id_pro]); ?>,'divPedidoUpdate<?PHP echo($dados[id_pro]); ?>');" onkeypress="mascara(this,mvalor);" value="<?PHP if($dados[preco_itp] != ''){echo(number_format($dados[preco_itp], 2, ',', '.'));} ?>" size="5" maxlength="12" />
                                    <div id="divPedidoUpdate<?PHP echo($dados[id_pro]); ?>"></div>
                            		<?PHP
								}
							else
								{
									?>
                            		<input readonly style="color:#999;" name="preco_pro<?PHP echo($dados[id_pro]); ?>" type="text" id="preco_pro<?PHP echo($dados[id_pro]); ?>" size="5" maxlength="12" value="<?PHP if($dados[preco_itp] != ''){echo(number_format($dados[preco_itp], 2, ',', '.'));} ?>" />
                                    <?PHP
								}
							?>
                            
							
								<?PHP
								if($dados[id_cli] > 0 and $dados[id_cli] != $dados[id_cli_f])
									{
										echo('<br>('.$dados[razao_social_cli].')');
									}
							?></div>
                            <div id="divColuna" style="width:100px;float:left;margin-left:5px;text-align:center;">
                            <?PHP
                            if($situacao_ped != 2)
                                {
                                    ?>
                            		<a href="javascript:mostra_esconde_div(0,'divFlutuante');mostra_esconde_div(0,'divFundo');pedido_produto_edit('<?PHP echo($dados[id_itp]); ?>','divFlutuanteConteudo');">Alterar</a> | <a href="javascript:pedido_produto_del(<?PHP echo($dados[id_itp]); ?>,'Tem certeza que deseja excluir este registro?','divProdutoAdicionado');">Excluir</a>
									<?PHP
                                }
                            else
                                {
                                    ?>
                                    <font style="color:#999;">Excluir</font>
                                    <?PHP
                                }
							?>
                            </div>
                        </div>
                        <br />
                        <?PHP
                    }
				
				# calculando total sem paginação
				$sql = mysql_query("select
									 tb_itens_pedido.qtde_itp
									 ,preco_itp
									 from tb_produto
									 inner join tb_produto_categ on tb_produto_categ.id_cap=tb_produto.id_cap
									 left join tb_marca on tb_marca.id_mar=tb_produto.id_mar
									 inner join tb_itens_pedido on tb_itens_pedido.id_pro=tb_produto.id_pro
									 left join tb_pedido on tb_pedido.id_ped=tb_itens_pedido.id_ped
									 left join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
									 where tb_produto.id_pro!='' and tb_itens_pedido.id_ped=$id_ped
									 group by tb_produto.id_pro
									 order by tb_produto.nome_pro") or die("Erro");
				while($dados=mysql_fetch_assoc($sql))
					{
						$total = $total + ($dados[preco_itp] * $dados[qtde_itp]);
					}
				
				echo('<P align="center">');
				if ($pc>1)
					{
						echo " <a href='javascript:pedido_produto_listar($anterior,\"divProdutoAdicionado\");'>« Anterior | </a> ";
					}
				### nova paginação
				if($total_reg < $tr)
					{
						$meio = array();
						if(($page-10) < 1)
							{
								$antes = 1;
							}
						else
							{
								$antes = $page-10;
							}
						if(($page+10) > $tp)
							{
								$posterior = $tp;
							}
						else
							{
								$posterior = $page + 10;
							}
						for($i=$antes;$i <= $posterior;$i++)
							{
								if($page == $i)
									{
										$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
									}
								else
									{
										$cor = '';
									}
								$meio[] = "<a $cor href='javascript:pedido_produto_listar($i,\"divProdutoAdicionado\");'>$i</a>";
							}
						$meio_pg = join(' | ', $meio);
						echo($meio_pg);
					}
				### fim nova paginação
				if ($pc<$tp)
					{
						echo " <a href='javascript:pedido_produto_listar($proximo,\"divProdutoAdicionado\");'> | Próxima »</a>";
					}
				echo('</p>');
			}
		?>
        <script type="text/javascript">
		if(document.getElementById('divTotal'))
			{
				document.getElementById('divTotal').innerHTML = '<strong>Total: R$ <?PHP echo(number_format($total, 2, ',', '.')); ?></strong>';
			}
		</script>
        <?PHP
	}
else if($action == 'busca_produto_pedido')
	{
		include("../config.php");
		include("header.php");
		session_start();
		if($_SESSION['login_sessao'])
			{
				include("verifica_sessao.php");
			}
		else
			{
				include("../verifica_sessao.php");
			}
		$key = addslashes($key);
		verifica_num($id_mar);
		verifica_num($situacao_ped);
		if($id_mar != '')
			{
				$where = "and tb_produto.id_mar=$id_mar";
			}
		if($key != "")
			{
				$where_key = "and tb_produto.cod_barras_pro like '%$key%' $where
							  or tb_produto.nome_pro like '%$key%' $where
							  or tb_produto.desc_pro like '%$key%' $where
							  or tb_produto_categ.desc_cap like '%$key%' $where";
			}
		$consulta = "select
					 tb_produto.id_cap,tb_produto.id_pro,cod_barras_pro,nome_pro,desc_mar,preco_pro
					 from tb_produto
					 inner join tb_produto_categ on tb_produto_categ.id_cap=tb_produto.id_cap
					 inner join tb_marca on tb_marca.id_mar=tb_produto.id_mar
					 where tb_produto.id_pro!='' $where_key $where
					 group by tb_produto.id_pro
					 order by tb_produto.nome_pro";	
		$sql = mysql_query("$consulta") or die("Erro");
		$linhas = mysql_num_rows($sql);
		if($linhas == "")
			{
				msg_status(1,'Nenhum resultado encontrado.');
			}
		else
			{
				$busca = $consulta;
				$total_reg = "20";
				if (!$page)
					{
						$pc = "1";
					}
				else
					{
						$pc = $page;
					}
				$inicio = $pc - 1;
				$inicio = $inicio * $total_reg;
				
				$limite = mysql_query("$busca LIMIT $inicio,$total_reg");
				$todos = mysql_query("$busca");
				
				$tr = mysql_num_rows($todos); // verifica o número total de registros
				$tp = $tr / $total_reg; // verifica o número total de páginas
				
				// agora vamos criar os botões "Anterior e próximo"
				$anterior = $pc -1;
				$proximo = $pc +1;
				echo('<P align="center">');
				if ($pc>1)
					{
						echo " <a href='javascript:busca_produto_pedido($anterior,\"divProduto\");'>« Anterior | </a> ";
					}
				### nova paginação
				if($total_reg < $tr)
					{
						$meio = array();
						if(($page-10) < 1)
							{
								$antes = 1;
							}
						else
							{
								$antes = $page-10;
							}
						if(($page+10) > $tp)
							{
								$posterior = $tp;
							}
						else
							{
								$posterior = $page + 10;
							}
						for($i=$antes;$i <= $posterior;$i++)
							{
								if($page == $i)
									{
										$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
									}
								else
									{
										$cor = '';
									}
								$meio[] = "<a $cor href='javascript:busca_produto_pedido($i,\"divProduto\");'>$i</a>";
							}
						$meio_pg = join(' | ', $meio);
						echo($meio_pg);
					}
				### fim nova paginação
				if ($pc<$tp)
					{
						echo " <a href='javascript:busca_produto_pedido($proximo,\"divProduto\");'> | Próxima »</a>";
					}
				echo('</p>');
				?>
				<div id="divCabecalho" style="height:15px;background-color:#F0F0F0;margin:1px;padding:1px;">
				  <div id="divColuna" style="width:150px;float:left;font-weight:bold;">Sele&ccedil;&atilde;o</div>
					<div id="divColuna" style="width:100px;float:left;margin-left:5px;font-weight:bold;">C&oacute;digo barras</div>
					<div id="divColuna" style="width:250px;float:left;margin-left:5px;font-weight:bold;">Produto</div>
					<div id="divColuna" style="width:150px;float:left;margin-left:5px;font-weight:bold;">Marca</div>
					<div id="divColuna" style="width:100px;float:left;margin-left:5px;font-weight:bold;">R$ Pre&ccedil;o base</div>
				</div>
				<br />
				<?PHP
				$num = 0;
				while($dados=mysql_fetch_assoc($limite))
					{
						$num++;
						if($num % 2 == 0)
							{
								$cor = 'background-color:#F0F0F0;';
							}
						else
							{
								$cor = '';
							}
						?>
						<div id="divCabecalho" style="display:table;height:20px;padding:1px;<?PHP echo($cor); ?>">
						  <div id="divCol<?PHP echo($dados[id_pro]); ?>" style="width:150px;float:left;">
							<?PHP
                            if($situacao_ped != 2)
								{
									?>
                                    <input type="button" name="button" id="button" value="+ Adicionar" onclick="mostra_esconde_div_geral(1,'divColMostra<?PHP echo($dados[id_pro]); ?>');mostra_esconde_div_geral(0,'divCol<?PHP echo($dados[id_pro]); ?>');document.getElementById('qtde_itp<?PHP echo($dados[id_pro]); ?>').focus();" />
									<?PHP
								}
                            else
								{
									?>
                                    <input type="button" name="button" id="button" value="+ Adicionar" style="color:#999;" onclick="alert('Desculpe, mas este pedido já está fechado, não é possível alterá-lo.');" />
                                    <?PHP
								}
                            
                            ?>
							
						  </div>
                          <div id="divColMostra<?PHP echo($dados[id_pro]); ?>" style="width:150px;float:left;display:none;">
                          Qtde.
                          <input name="qtde_itp<?PHP echo($dados[id_pro]); ?>" type="text" id="qtde_itp<?PHP echo($dados[id_pro]); ?>" value="1" size="3" maxlength="5" onkeypress="mascara(this,soNumeros);" />
                            <input type="button" name="button2" id="button2" value="Ok" onclick="pedido_produto_cadastrar(<?PHP echo($dados[id_pro]); ?>,'divProdutoAdicionado');if(document.getElementById('qtde_itp<?PHP echo($dados[id_pro]); ?>').value > 0){mostra_esconde_div_geral(0,'divColMostra<?PHP echo($dados[id_pro]); ?>');mostra_esconde_div_geral(1,'divCol<?PHP echo($dados[id_pro]); ?>');}" />
                          </div>
							<div id="divColuna" style="width:100px;float:left;margin-left:5px;"><?PHP echo($dados[cod_barras_pro]); ?></div>
							<div id="divColuna" style="width:250px;float:left;margin-left:5px;"><?PHP echo($dados[nome_pro]); ?>&nbsp;</div>
							<div id="divColuna" style="width:150px;float:left;margin-left:5px;"><?PHP echo($dados[desc_mar]); ?></div>
							<div id="divColuna" style="width:100px;float:left;margin-left:5px;"><?PHP echo(number_format($dados[preco_pro], 2, ',', '.')); ?></div>
						</div>
						<br />
						<?PHP
					}
				echo('<P align="center">');
				if ($pc>1)
					{
						echo " <a href='javascript:busca_produto_pedido($anterior,\"divProduto\");'>« Anterior | </a> ";
					}
				### nova paginação
				if($total_reg < $tr)
					{
						$meio = array();
						if(($page-10) < 1)
							{
								$antes = 1;
							}
						else
							{
								$antes = $page-10;
							}
						if(($page+10) > $tp)
							{
								$posterior = $tp;
							}
						else
							{
								$posterior = $page + 10;
							}
						for($i=$antes;$i <= $posterior;$i++)
							{
								if($page == $i)
									{
										$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
									}
								else
									{
										$cor = '';
									}
								$meio[] = "<a $cor href='javascript:busca_produto_pedido($i,\"divProduto\");'>$i</a>";
							}
						$meio_pg = join(' | ', $meio);
						echo($meio_pg);
					}
				### fim nova paginação
				if ($pc<$tp)
					{
						echo " <a href='javascript:busca_produto_pedido($proximo,\"divProduto\");'> | Próxima »</a>";
					}
				echo('</p>');
			}
	}
else
	{
		session_start();
		if(!($_SESSION['login_sessao']))
			{
				header ("Location: index.php?pagina=login");
			}
		else
			{
				if($action == "cotacao")
					{
						# cria a cotação
						$in = mysql_query("insert into tb_cotacao (data_inicial_cot,data_final_cot) values ('$data_inicial_cot','$data_final_cot')") or die("Erro");
						$sql = mysql_query("select * from tb_cotacao order by id_cot desc limit 1") or die("Erro");
						while($dados=mysql_fetch_assoc($sql))
							{
								$id_cot = $dados[id_cot];
							}
							
						$id_ped = explode(",",$pedido);
						for($i=0;$i<count($id_ped)-1;$i++)
							{
								$sql = mysql_query("select * from tb_itens_pedido
													where id_ped=$id_ped[$i]") or die("Erro");
								while($dados=mysql_fetch_assoc($sql))
									{
										# verifica se já inseriu o produto nos itens da cotação
										$sql2 = mysql_query("select id_ite,id_pro,qtde_ite from tb_itens_cotacao where id_cot=$id_cot and id_pro=$dados[id_pro]") or die("Erro");
										$linhas2 = mysql_num_rows($sql2);
										if($linhas2 == '')
											{
												$in = mysql_query("insert into tb_itens_cotacao (id_cot,id_pro,qtde_ite) values ($id_cot,$dados[id_pro],$dados[qtde_itp])") or die("Erro");
											}
										else
											{
												while($dados2=mysql_fetch_assoc($sql2))
													{
														$up = mysql_query("update tb_itens_cotacao set qtde_ite=qtde_ite+$dados[qtde_itp] where id_ite=$dados2[id_ite]") or die("Erro");
													}
											}
										# atualiza pedido para "Fechado";
										$up = mysql_query("update tb_pedido set id_cot='$id_cot',situacao_ped=2 where id_ped=$id_ped[$i]") or die("Erro");
									}
								
								# insere pedidos na cotação
								$in = mysql_query("insert into tb_itens_pedido_cotacao (id_cot,id_ped) values ($id_cot,$id_ped[$i])") or die("Erro");
							}
						?>
						<script type="text/javascript">
						window.location = 'index.php?pagina=cotacao&action=edit&id_cot=<?PHP echo($id_cot); ?>&dispara=1';
						</script>
						<?PHP
					}
				if($action == 'liberar')
					{
						$up = mysql_query("update tb_cotacao set libera_cot=1 where id_cot=$id_cot") or die("Erro");
						$up = mysql_query("update tb_pedido set situacao_ped=2 where id_ped=$id_ped") or die("Erro");
						
						# Avisando o comprador
						$sql = mysql_query("select * from tb_cotacao
											inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_cotacao.id_cli
											where tb_cotacao.id_cot=$id_cot
											group by tb_cliente_fornecedor.id_cli") or die("Erro");
						while($dados=mysql_fetch_assoc($sql))
							{
								// ENVIANDO POR E-MAIL
								$sql2 = mysql_query("select * from tb_configuracao limit 1") or die("Erro");
								while($dados2=mysql_fetch_assoc($sql2))
									{
										$razao_social_cli = $dados[razao_social_cli];
										$responsavel_cli = $dados[responsavel_cli];
										$email_cli = $dados[email_cli];
										
										$cabecalho = "MIME-Version: 1.1\n";
										$cabecalho .= "Content-type: text/html; charset=iso-8859-1\n";
										$cabecalho .= "From: ".utf8_decode($dados2[nome_loja_con])." <$dados2[email_con]>"."\n"; // remetente
										$cabecalho .= "Return-Path: ".utf8_decode($dados2[nome_loja_con])." <$dados2[email_con]>"."\n"; // return-path
										$cabecalho .= "Reply-To: ".utf8_decode($dados2[nome_loja_con])." <$dados2[email_con]>"."\n"; // reply to
														
										$corpo = '
										<img src="http://'.$_SERVER['SERVER_NAME'].'/imagens/logo.png">
										<br />
										<br />
										<font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif">
										Olá '.$dados[responsavel_cli].'. 
										<br />
										<br />
										Informamos que o fornecedor efetuou o pagamento da taxa de liberação da <strong>Cotação Nº '.$id_cot.'</strong> e o seu pedido já está em andamento. 
										Agora é possível visualizar todos os nomes dos fornecedores que participaram da sua cotação, como tambem é possível entrar em contato com eles.
										<br>
										<br>
										<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php?pagina=restrito&action=cotacao&id_cot='.$id_cot.'">Cotação '.$id_cot.' - <strong>Visualizar</strong></a>
										<br>
										<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php?pagina=restrito&action=pedido&id_ped='.$id_ped.'">Pedido '.$id_ped.' - <strong>Visualizar</strong></a>
										<br>
										<br>
										Qualquer dúvida, estamos à disposição.
										<br>
										<br />
										Atenciosamente,
										<br />
										<br />
										'.utf8_decode($dados2[nome_loja_con]).'
										<br />
										<a href="http://'.$_SERVER['SERVER_NAME'].'">'.$_SERVER['SERVER_NAME'].'</a>
										</font>
										';
										#echo $corpo.'<br><br>para '.$dados[email_cli].'<br>';
										$assunto = 'Cotação '.$id_cot.' liberada';
										mail("$dados[email_cli]", $assunto, $corpo, $cabecalho);
									}
							}
							
						# Avisando o fornecedor
						$sql = mysql_query("select * from tb_cotacao
											inner join tb_itens_fornecedor on tb_itens_fornecedor.id_cot=tb_cotacao.id_cot
											inner join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_itens_fornecedor.id_cli
											where tb_cotacao.id_cot=$id_cot and tb_itens_fornecedor.vencedor_itf=1 and tb_cliente_fornecedor.id_cli=$id_cli
											group by tb_cliente_fornecedor.id_cli") or die("Erro");
						while($dados=mysql_fetch_assoc($sql))
							{
								// ENVIANDO POR E-MAIL
								$sql2 = mysql_query("select * from tb_configuracao limit 1") or die("Erro");
								while($dados2=mysql_fetch_assoc($sql2))
									{
										$cabecalho = "MIME-Version: 1.1\n";
										$cabecalho .= "Content-type: text/html; charset=iso-8859-1\n";
										$cabecalho .= "From: ".utf8_decode($dados2[nome_loja_con])." <$dados2[email_con]>"."\n"; // remetente
										$cabecalho .= "Return-Path: ".utf8_decode($dados2[nome_loja_con])." <$dados2[email_con]>"."\n"; // return-path
										$cabecalho .= "Reply-To: ".utf8_decode($razao_social_cli)." <$email_cli>"."\n"; // reply to
														
										$corpo = '
										<img src="http://'.$_SERVER['SERVER_NAME'].'/imagens/logo.png">
										<br />
										<br />
										<font color="#666666" size="2" face="Verdana, Arial, Helvetica, sans-serif">
										Olá '.$dados[responsavel_cli].'. 
										<br />
										<br />
										Informamos que o seu pagamento foi confirmado e o pedido da <strong>Cotação Nº '.$id_cot.'</strong> foi liberado.
										Agora é possível visualizar todos itens e realizar a venda ao comprador <strong>'.$responsavel_cli.'</strong>.
										<br>
										<br>
										<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php?pagina=restrito&action=pedido_fornecedor&id_ped='.$id_ped.'">Pedido '.$id_ped.' - <strong>Visualizar</strong></a>
										<br>
										<a href="http://'.$_SERVER['SERVER_NAME'].'/index.php?pagina=restrito&action=cotacao_for&id_cot='.$id_cot.'">Cotação '.$id_cot.' - <strong>Visualizar</strong></a>
										<br>
										<br>
										A partir de agora é só aguardar o comprador entrar em contato com você para fechar a venda.
										<br>
										<br>
										Agradecemos por usar o nosso site e qualquer dúvida, estamos à disposição.
										<br>
										<br />
										Atenciosamente,
										<br />
										<br />
										'.utf8_decode($dados2[nome_loja_con]).'
										<br />
										<a href="http://'.$_SERVER['SERVER_NAME'].'">'.$_SERVER['SERVER_NAME'].'</a>
										</font>
										';
										#echo $corpo;
										$assunto = 'Cotação '.$id_cot.' liberada';
										mail("$dados[email_cli]", $assunto, $corpo, $cabecalho);
									}
							}
						msg_status(1,'Pedido liberado com sucesso!.');
					}
				if($action == "del")
					{
						$del = mysql_query("delete from tb_pedido where id_ped=$id_ped") or die("Erro");
						$del = mysql_query("delete from tb_itens_pedido_cotacao where id_ped=$id_ped") or die("Erro");
						$del = mysql_query("delete from tb_itens_pedido where id_ped=$id_ped") or die("Erro");
						$status = "Operação realizada com sucesso!";
						include("status.php");
					}
				if($action == "update")
					{
						$obs_ped = addslashes($obs_ped);
										
						$up = mysql_query("update tb_pedido set id_fil='$id_fil',id_cli='$id_cli',obs_ped='$obs_ped',situacao_ped='$situacao_ped',id_fil2='$id_fil2' where id_ped=$id_ped") or die("Erro");
						$status = "Operação realizada com sucesso!";
						include("status.php");
					}
				if($action == "cadastrar")
					{
						$obs_ped = addslashes($obs_ped);
						
						$in = mysql_query("insert into tb_pedido (id_fil,id_cli,data_hora_ped,obs_ped,situacao_ped,id_fil2) values ('$id_fil','$id_cli',now(),'$obs_ped','$situacao_ped','$id_fil2')") or die("Erro");
						$sql = mysql_query("select * from tb_pedido order by id_ped desc limit 1") or die("Erro");
						while($dados=mysql_fetch_assoc($sql))
							{
								$id_ped_banco = $dados[id_ped];
							}
						   
						$up = mysql_query("update tb_itens_pedido set id_ped=$id_ped_banco where id_ped=$id_ped") or die("Erro");
						
						$status = "Operação realizada com sucesso!";
						include("status.php");					
						if($opcao2 == 1)
							{
								$action = "add";
							}
						if($opcao == 1)
							{
								?>
								<script type="text/javascript">
								window.location = 'index.php?pagina=foto&id_ped=<?PHP echo($id_ped); ?>';
								</script>
								<?PHP
								die;
							}
					}
				?>
				<script type="text/javascript">
				function valida_campos()
					{
						if(document.getElementById("id_fil").value == "" && document.getElementById("id_cli").value == "")
							{
								alert('Por favor, preencha a filial ou o fornecedor.');
								document.getElementById("id_fil").focus();
								return false;
							}
						if(document.getElementById("produto").value == "")
							{
								alert('Por favor, adicione ao menos 1 produto.');
								return false;
							}
						if(document.getElementById("situacao_ped").value == "")
							{
								alert('Por favor, preencha os campos obrigatórios.');
								document.getElementById("situacao_ped").focus();
								return false;
							}					
					}
				</script>
				<strong>Pedidos<br />
				<br />
				</strong><a href="index.php?pagina=<?PHP echo($pagina); ?>">&raquo; Listar</a><br />
				<br />
				<?PHP
				if($action != "add" and $action != "edit")
					{
					?>
					<form name="form_busca" method="post" action="index.php?pagina=<?PHP echo($pagina); ?>">
				<div style="background-color:#FFFFFF;border:solid 1px #CCCCCC;padding:15px;display:table;width:700px;margin-bottom:15px;">
							<div style="width:40px;float:left;border:0px solid #000;padding:5px;margin-left:10px;padding-top:25px;">
					  <strong>Busca</strong></div>
				<div style="width:250px;float:left;border:0px solid #000;padding:5px;margin-left:10px;"> Filial<br />
				<select name="id_fil" id="id_fil" style="width:100%;" onChange="document.getElementById('id_cli').selectedIndex = 0;">
				<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_filial order by nome_fil") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
											<option value="<?PHP echo($dados2[id_fil]); ?>" <?PHP if($id_fil == $dados2[id_fil]){echo('selected="selected"');} ?>><?PHP echo($dados2[nome_fil]); ?></option>
											<?PHP
										}
									?>
				</select><br />
                Fornecedor<br><select name="id_cli" id="id_cli" style="width:100%;" onChange="document.getElementById('id_fil').selectedIndex = 0;">
		<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_cliente_fornecedor order by razao_social_cli") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
			  <option value="<?PHP echo($dados2[id_cli]); ?>" <?PHP if($id_cli == $dados2[id_cli]){echo('selected="selected"');} ?>><?PHP echo($dados2[razao_social_cli]); ?></option>
											<?PHP
										}
									?>
				</select>
		 
	
				</div>
				<div style="width:300px;float:right;border:0px solid #000;padding:5px;margin-right:20px;">			    Filtro por data<br />
<select name="data" id="data" style="max-width:95%;" onchange="imprimir(0);">
						  <option value="" <?PHP if($data == ""){echo('selected="selected"');} ?>>- Selecione -</option>
						  <option value="1" <?PHP if($data == 1){echo('selected="selected"');} ?>>Sim</option>
	  </select>
						<br />
	  Data inicial<br />
					  <?PHP
											if($dia == "")
												{
													$dia = date(1);
												}
											?>
<select name="dia" id="dia" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
						<?PHP
										for($num=1;$num<=31;$num++)
											{
												?>
						<option value="<?PHP echo($num); ?>" <?PHP if($num == $dia){echo('selected="selected"');} ?>><?PHP echo($num); ?></option>
						<?PHP
											}
									  ?>
	  </select>
										/
					  <?PHP
											if($mes == "")
												{
													$mes = date("m");
													$ano = date("Y");
												}
											?>
									<select name="mes" id="mes" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
										<?PHP
										for($num=1;$num<=12;$num++)
											{
												if($num == 1)
													{
														$desc_mes = "Janeiro";
													}
												if($num == 2)
													{
														$desc_mes = "Fevereiro";
													}
												if($num == 3)
													{
														$desc_mes = "Mar&ccedil;o";
													}
												if($num == 4)
													{
														$desc_mes = "Abril";
													}
												if($num == 5)
													{
														$desc_mes = "Maio";
													}
												if($num == 6)
													{
														$desc_mes = "Junho";
													}
												if($num == 7)
													{
														$desc_mes = "Julho";
													}
												if($num == 8)
													{
														$desc_mes = "Agosto";
													}
												if($num == 9)
													{
														$desc_mes = "Setembro";
													}
												if($num == 10)
													{
														$desc_mes = "Outubro";
													}
												if($num == 11)
													{
														$desc_mes = "Novembro";
													}
												if($num == 12)
													{
														$desc_mes = "Dezembro";
													}
												?>
										  <option value="<?PHP echo($num); ?>" <?PHP if($num == $mes){echo('selected="selected"');} ?>><?PHP echo($desc_mes); ?></option>
											<?PHP
											}
											?>
	  </select>
											/
<select name="ano" id="ano" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
											<?PHP
											for($num=date("Y")-5;$num<=date("Y")+5;$num++)
												{
													?>
											<option value="<?PHP echo($num); ?>" <?PHP if($num == $ano){echo('selected="selected"');} ?>><?PHP echo($num); ?></option>
											<?PHP
												}
											?>
	  </select>
											<br />
						Data final<br />
					  <?PHP
											if($dia2 == "")
												{
													$dia2 = date(31);
												}
											?>
<select name="dia2" id="dia2" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
											  <?PHP
										for($num=1;$num<=31;$num++)
											{
												?>
											  <option value="<?PHP echo($num); ?>" <?PHP if($num == $dia2){echo('selected="selected"');} ?>><?PHP echo($num); ?></option>
												  <?PHP
											}
									  ?>
	  </select>
												/
					  <?PHP
											if($mes2 == "")
												{
		
													$mes2 = date("m");
													$ano2 = date("Y");
												}
											?>
<select name="mes2" id="mes2" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
										  <?PHP
										for($num=1;$num<=12;$num++)
											{
												if($num == 1)
													{
														$desc_mes = "Janeiro";
													}
												if($num == 2)
													{
														$desc_mes = "Fevereiro";
													}
												if($num == 3)
													{
														$desc_mes = "Mar&ccedil;o";
													}
												if($num == 4)
													{
														$desc_mes = "Abril";
													}
												if($num == 5)
													{
														$desc_mes = "Maio";
													}
												if($num == 6)
													{
														$desc_mes = "Junho";
													}
												if($num == 7)
													{
														$desc_mes = "Julho";
													}
												if($num == 8)
													{
														$desc_mes = "Agosto";
													}
												if($num == 9)
													{
														$desc_mes = "Setembro";
													}
												if($num == 10)
													{
														$desc_mes = "Outubro";
													}
												if($num == 11)
													{
														$desc_mes = "Novembro";
													}
												if($num == 12)
													{
														$desc_mes = "Dezembro";
													}
												?>
										  <option value="<?PHP echo($num); ?>" <?PHP if($num == $mes2){echo('selected="selected"');} ?>><?PHP echo($desc_mes); ?></option>
												  <?PHP
											}
									  ?>
	  </select>
										/
<select name="ano2" id="ano2" onchange="document.getElementById('data').selectedIndex = 1;imprimir(0);">
										  <?PHP
										for($num=date("Y")-5;$num<=date("Y")+5;$num++)
											{
												?>
										  <option value="<?PHP echo($num); ?>" <?PHP if($num == $ano2){echo('selected="selected"');} ?>><?PHP echo($num); ?></option>
											  <?PHP
											}
									  ?>
	  </select>
						<br />
				  Descri&ccedil;&atilde;o
				  <br />
						<input name="key" type="text" id="key" value="<?PHP echo($key); ?>" size="25" maxlength="100" />
				  <input type="submit" name="Submit3" value="Buscar" />
				</div>
					<script type="text/javascript">
					  document.getElementById("key").focus();
					  document.getElementById("key").select();
					  </script>
					</div>
					</form>
					<br />
					<?PHP
					}
				if($action == "edit")
					{
						$sql = mysql_query("select * 
											,date_format(id_fil, '%d/%m/%Y') as id_fil_f
											,date_format(data_hora_ped, '%d/%m/%Y') as data_hora_ped_f
											from tb_pedido where id_ped=$id_ped") or die("Erro");
						while($dados=mysql_fetch_assoc($sql))
							{
								?>
                                <div style="text-align:right;height:30px;line-height:30px;"><a href="../<?PHP echo($pagina); ?>.php?action=relatorio_xls<?PHP echo("&amp;id_ped=$id_ped&amp;id_fil=$dados[id_fil]"); ?>" target="_blank"><img src="imagens/ico_xls.png" width="20" height="20" border="0" /> Exportar para excel</a></div>
								<form name="form1" id="form1" method="post" action="index.php?pagina=<?PHP echo($pagina); ?>&action=update" onSubmit="return valida_campos();">
								<div id="divDados" style="border: solid 1px #CCCCCC;background-color:#F9F9F9;padding:5px;"> <strong>Alterar dados </strong><br />
													<br />
													<input name="id_ped" type="hidden" id="id_ped" value="<?PHP echo($dados[id_ped]); ?>" />
												  <br />
								   Filial <br />
		<select name="id_fil" id="id_fil" onChange="document.getElementById('id_cli').selectedIndex = 0;">
		<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_filial order by nome_fil") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
			  <option value="<?PHP echo($dados2[id_fil]); ?>" <?PHP if($dados[id_fil] == $dados2[id_fil]){echo('selected="selected"');} ?>><?PHP echo($dados2[nome_fil]); ?></option>
											<?PHP
										}
									?>
				</select>
		<br>
		 Fornecedor<br><select name="id_cli" id="id_cli" onChange="document.getElementById('id_fil').selectedIndex = 0;">
		<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_cliente_fornecedor order by razao_social_cli") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
			  <option value="<?PHP echo($dados2[id_cli]); ?>" <?PHP if($dados[id_cli] == $dados2[id_cli]){echo('selected="selected"');} ?>><?PHP echo($dados2[razao_social_cli]); ?></option>
											<?PHP
										}
									?>
				</select>
		 <br />
		Adicione os produtos a serem cotados
						  <input type="hidden" name="produto" id="produto" />
						  <input name="id_ped" type="hidden" id="id_ped" value="<?PHP echo($dados[id_ped]); ?>" />
		  &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<img src="imagens/ico-lupa.png" width="25" height="25" border="0" style="margin-bottom:-8px;" />
<input name="chave" type="text" id="chave" onClick="if(this.value == 'Pesquisar...'){this.value='';}else{this.select();}" value="Pesquisar..." size="20" maxlength="100" onkeyup="busca_produto_pedido('','divProduto');"> 
<select name="id_mar" id="id_mar" onchange="busca_produto_pedido('','divProduto')">
      <option value="">- Marca -</option>
						  <?PHP
										$sql2 = mysql_query("select * from tb_marca order by desc_mar") or die("Erro");
										while($dados2=mysql_fetch_assoc($sql2))
											{
												?>
											  <option value="<?PHP echo($dados2[id_mar]); ?>" <?PHP if($id_mar == $dados2[id_mar]){echo('selected="selected"');} ?>><?PHP echo($dados2[desc_mar]); ?></option>
											  <?PHP
											}
										?>
						</select>
<div id="divProduto" style="min-height:150px;max-height:250px;overflow:auto;border:1px solid #CCC;padding:10px;width:90%;margin-bottom:5px;margin-top:5px;">
	  <script type="text/javascript">
		busca_produto_pedido('','divProduto');
		</script>
		</div>
		<br />
		<strong>		Produtos adicionados</strong>
<div id="divProdutoAdicionado" style="min-height:150px;max-height:250px;overflow:auto;border:1px solid #CCC;padding:10px;width:90%;margin-bottom:5px;margin-top:5px;">
			<script type="text/javascript">
            pedido_produto_listar('','divProdutoAdicionado');
            </script>
              </div>
              <div id="divTotal" style="height:20px;line-height:20px;margin-bottom:5px;margin-top:10px;margin-left:520px;"></div>
				Observa&ccedil;&otilde;es<br />
				  <textarea name="obs_ped" id="obs_ped" cols="45" rows="6" style="width:90%;"><?PHP echo($dados[obs_ped]); ?></textarea>
				<br /> 
				* Situa&ccedil;&atilde;o
		<br />
		<select name="situacao_ped" id="situacao_ped">
				  <option value="1" <?PHP if($dados[situacao_ped] == 1){echo('selected="selected"');} ?>>Em aberto</option>
				  <option value="2" <?PHP if($dados[situacao_ped] == 2){echo('selected="selected"');} ?>>Fechado</option>
				  </select>
		<br />
		<br />
        <div id="divPedidoUpdate"></div>
						  <input type="submit" name="Submit" value="Atualizar" />
								<input type="button" name="Submit2" value="Cancelar" onclick="javascript:window.location='index.php?pagina=<?PHP echo($pagina); ?>';" />
								</div>
								</form>
								<script type="text/javascript">
								document.getElementById("id_fil").focus();
								</script>
								<?PHP
							}
					}
				if($action == "add")
					{
						?>
						<form name="form1" id="form1" method="post" action="index.php?pagina=<?PHP echo($pagina); ?>&action=cadastrar" onSubmit="return valida_campos();">
						<div id="divDados" style="border: solid 1px #CCCCCC;background-color:#F9F9F9;padding:5px;"> <strong>Cadastro</strong><br />
						  <br />
						   Filial <br />
		<select name="id_fil" id="id_fil" onChange="document.getElementById('id_cli').selectedIndex = 0;">
		<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_filial order by nome_fil") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
											<option value="<?PHP echo($dados2[id_fil]); ?>" <?PHP if($id_fil == $dados2[id_fil]){echo('selected="selected"');} ?>><?PHP echo($dados2[nome_fil]); ?></option>
											<?PHP
										}
									?>
				</select>
		<br>
		Fornecedor<br><select name="id_cli" id="id_cli" onChange="document.getElementById('id_fil').selectedIndex = 0;">
		<option value="">- Selecione -</option>
						<?PHP
									$sql2 = mysql_query("select * from tb_cliente_fornecedor order by razao_social_cli") or die("Erro");
									while($dados2=mysql_fetch_assoc($sql2))
										{
											?>
			  <option value="<?PHP echo($dados2[id_cli]); ?>" <?PHP if($id_cli == $dados2[id_cli]){echo('selected="selected"');} ?>><?PHP echo($dados2[razao_social_cli]); ?></option>
											<?PHP
										}
									?>
				</select>
		 
		<br />
						  Adicione os produtos a serem cotados
						  <input type="hidden" name="produto" id="produto" />
						  <input name="id_ped" type="hidden" id="id_ped" value="<?PHP echo(time()); ?>" />
		  &nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;<img src="imagens/ico-lupa.png" width="25" height="25" border="0" style="margin-bottom:-8px;" />
<input name="chave" type="text" id="chave" onClick="if(this.value == 'Pesquisar...'){this.value='';}else{this.select();}" value="Pesquisar..." size="20" maxlength="100" onkeyup="busca_produto_pedido('','divProduto');">
&nbsp;
<select name="id_mar" id="id_mar" onchange="busca_produto_pedido('','divProduto')">
      <option value="">- Marca -</option>
						  <?PHP
										$sql2 = mysql_query("select * from tb_marca order by desc_mar") or die("Erro");
										while($dados2=mysql_fetch_assoc($sql2))
											{
												?>
											  <option value="<?PHP echo($dados2[id_mar]); ?>" <?PHP if($id_mar == $dados2[id_mar]){echo('selected="selected"');} ?>><?PHP echo($dados2[desc_mar]); ?></option>
											  <?PHP
											}
										?>
						</select>
<div id="divProduto" style="min-height:150px;max-height:250px;overflow:auto;border:1px solid #CCC;padding:10px;width:90%;margin-bottom:5px;margin-top:5px;">
	  <script type="text/javascript">
		busca_produto_pedido('','divProduto');
		</script>
		</div>
		<br />
		<strong>		Produtos adicionados</strong>
<div id="divProdutoAdicionado" style="min-height:150px;max-height:250px;overflow:auto;border:1px solid #CCC;padding:10px;width:90%;margin-bottom:5px;margin-top:5px;">
			<script type="text/javascript">
            pedido_produto_listar('','divProdutoAdicionado');
            </script>
              </div>
              <div id="divTotal" style="height:20px;line-height:20px;margin-bottom:5px;margin-top:10px;margin-left:520px;"></div>
				Observa&ccedil;&otilde;es<br />
				  <textarea name="obs_ped" id="obs_ped" cols="45" rows="6" style="width:90%;"></textarea>
				<br /> 
				* Situa&ccedil;&atilde;o
		<br />
						  <select name="situacao_ped" id="situacao_ped">
							<option value="1">Em aberto</option>
							<option value="2">Fechado</option>
						  </select>
						  <br />
						  <br />
                          <div id="divPedidoUpdate"></div>
						  Ap&oacute;s este cadastrar novo?<br />
						  <input name="opcao2" type="checkbox" id="opcao2" value="1" <?PHP if($opcao == 1){echo('checked="checked"');} ?> />
		Sim <br />
						  <br />
						<input type="submit" name="Submit" value="Cadastrar" />
						<input type="button" name="Submit2" value="Cancelar" onclick="javascript:window.location='index.php?pagina=<?PHP echo($pagina); ?>';" />
						</div>
						</form>
						<script type="text/javascript">
						document.getElementById("id_fil").focus();
						</script>
						<?PHP
					}
				if($action != "add" and $action != "edit" and $action != "ordenacao")
					{
						if($data == 1)
							{
								$data_inicio  = "$ano-$mes-$dia";
								$data_fim = "$ano2-$mes2-$dia2";
								$where .= "and tb_pedido.data_hora_ped between '$data_inicio 00:00:00' and '$data_fim 23:59:59'";
							}
						if($id_fil != '')
							{
								$where .= " and tb_pedido.id_fil='$id_fil' ";
							}
						if($id_cli != '')
							{
								$where .= " and tb_pedido.id_cli='$id_cli' ";
							}
						if($key != "")
							{
								$where .= "and tb_filial.nome_fil like '%$key%'";
							}
						$consulta = "select *
									 ,date_format(data_hora_ped, '%d/%m/%y - %H:%i') as data_hora_ped_f
									 ,if(tb_pedido.situacao_ped=1,'Em aberto','Fechado') as situacao_ped_f
									 ,(SELECT COUNT(id_pro) FROM tb_itens_pedido WHERE id_ped=tb_pedido.id_ped) AS total_unico
									 #,(SELECT SUM(qtde_itp) FROM tb_itens_pedido WHERE id_ped=tb_pedido.id_ped) AS total_geral
									 from tb_pedido
									 left join tb_filial on tb_filial.id_fil=tb_pedido.id_fil
									 left join tb_cliente_fornecedor on tb_cliente_fornecedor.id_cli=tb_pedido.id_cli
									 where tb_pedido.id_ped!='' $where
									 group by tb_pedido.id_ped
									 having total_unico > 0
									 order by tb_pedido.data_hora_ped desc";
						$sql = mysql_query("$consulta") or die("Erro");
						$linhas = mysql_num_rows($sql);
						echo('<strong>'.$linhas.'</strong> registro(s) encontrado(s).'); ?>
						<br />
						<br />
						<?PHP
						if($linhas == "")
							{
								$status = 'Nenhum resultado encontrado.';
								include("status.php");
							}
						else
							{
								$busca = $consulta;
								$total_reg = "50";
								if (!$page)
									{
										$pc = "1";
									}
								else
									{
										$pc = $page;
									}
								$inicio = $pc - 1;
								$inicio = $inicio * $total_reg;
								
								$limite = mysql_query("$busca LIMIT $inicio,$total_reg");
								$todos = mysql_query("$busca");
								
								$tr = mysql_num_rows($todos); // verifica o número total de registros
								$tp = $tr / $total_reg; // verifica o número total de páginas
								
								// agora vamos criar os botões "Anterior e próximo"
								$anterior = $pc -1;
								$proximo = $pc +1;
								echo('<P align="center">');
								if ($pc>1)
									{
										echo " <a href='index.php?pagina=$pagina&amp;page=$anterior&amp;key=$key&amp;id_fil=$id_fil&amp;data=$data&amp;dia=$dia&amp;mes=$mes&amp;ano=$ano&amp;dia2=$dia2&amp;mes2=$mes2&amp;ano2=$ano2&id_cli=$id_cli'>« Anterior | </a> ";
									}
								if($total_reg < $tr)
									{
										$meio = array();
										for ($i = 1; $i <= $tp+1; $i++) 
											{
												if($page == "")
													{
														$page = 1;
													}
												if($page == $i)
													{
														$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
													}
												else
													{
														$cor = '';
													}
												$meio[] = '<a '.$cor.' href="index.php?pagina='.$pagina.'&amp;page='.$i.'&amp;key='.$key.'&amp;id_fil='.$id_fil.'&amp;data='.$data.'&amp;dia='.$dia.'&amp;mes='.$mes.'&amp;ano='.$ano.'&amp;dia2='.$dia2.'&amp;mes2='.$mes2.'&amp;ano2='.$ano2.'&id_cli='.$id_cli.'">'.$i.'</a>';
											}
										$meio_pg = join(' | ', $meio);
										echo(''.$meio_pg.'');
									}
								if ($pc<$tp)
									{
										echo " <a href='index.php?pagina=$pagina&amp;page=$proximo&amp;key=$key&amp;id_fil=$id_fil&amp;data=$data&amp;dia=$dia&amp;mes=$mes&amp;ano=$ano&amp;dia2=$dia2&amp;mes2=$mes2&amp;ano2=$ano2&id_cli=$id_cli'> | Próxima »</a>";
									}
								echo('</p>');
								?>
						<input type="hidden" name="pedido" id="pedido" />
						
                        <select class="but_comando" name="id_cot" id="id_cot" onChange="if(this.value > 0){window.open('pedido.php?action=gera_sql&id_cot='+this.value);this.selectedIndex=0;}" style="display:none;">
                        <option value="">- Gerar SQL da Cota&ccedil;&atilde;o -</option>
                        	<?PHP
							$sql = mysql_query("select *
											   ,date_format(data_inicial_cot, '%d/%m/%y') as data_inicial_cot_f
											   ,date_format(data_final_cot, '%d/%m/%y') as data_final_cot_f
											   ,(SELECT COUNT(id_pro) FROM tb_itens_cotacao WHERE id_cot=tb_cotacao.id_cot limit 1) AS total_unico
											   ,(SELECT SUM(qtde_ite) FROM tb_itens_cotacao WHERE id_cot=tb_cotacao.id_cot limit 1) AS total_geral
											   from tb_cotacao
											   where tb_cotacao.id_cot!=''
											   order by tb_cotacao.data_inicial_cot") or die("Erro");
							while($dados=mysql_fetch_assoc($sql))
								{
									?>
                                    <option value="<?PHP echo($dados[id_cot]); ?>"><?PHP echo($dados[data_inicial_cot_f].' à '.$dados[data_final_cot_f]); ?></option>
                                    <?PHP
								}
							?>
                          </select>
								<div id="divPedidoEnvia"></div>
                                <form id="form1" name="form1" method="post" action="" onsubmit="return false();">
								<div id="divCabecalho" style="height:auto;display:table;background-color:#F0F0F0;margin:1px;padding:1px;">
                                    <div id="divColuna1" style="width:90px;float:left;font-weight:bold;">Cod. Pedido</div>
                                    <div id="divColuna1" style="width:90px;float:left;font-weight:bold;margin-left:5px;">C&oacute;d. Cota&ccedil;&atilde;o</div>
                                    <div id="divColuna1" style="width:120px;float:left;font-weight:bold;margin-left:5px;">Fornecedor</div>
                                    <div id="divColuna1" style="width:120px;float:left;font-weight:bold;margin-left:5px;">Data</div>
									<div id="divColuna1" style="width:80px;float:left;font-weight:bold;margin-left:5px;">N&ordm; produtos &uacute;nicos</div>
									<div id="divColuna1" style="width:80px;float:left;font-weight:bold;margin-left:5px;">N&ordm; total produtos</div>
									<div id="divColuna1" style="width:100px;float:left;font-weight:bold;margin-left:5px;">Situa&ccedil;&atilde;o</div>
                                    <div id="divColuna1" style="width:100px;float:left;font-weight:bold;margin-left:5px;">Taxa de libera&ccedil;&atilde;o</div>
									<div id="divColuna3" style="width:250px;float:left;margin-left:5px;font-weight:bold;text-align:center;">Op&ccedil;&otilde;es</div>
								</div>
								<br />
								  <?PHP
								while($dados=mysql_fetch_assoc($limite))
									{
										$num++;
										if($num % 2 == 0)
											{
												$cor = 'background-color:#F0F0F0;';
											}
										else
											{
												$cor = '';
											}
											?>
											<div id="divCabecalho<?PHP echo($num); ?>" style="display:table;height:20px;<?PHP echo($cor); ?>padding:1px;">
                                              <div id="divColuna1<?PHP echo($num); ?>" style="width:90px;float:left;"><?PHP echo($dados[id_ped]); ?>&nbsp;</div>
                                              <div id="divColuna1<?PHP echo($num); ?>" style="width:90px;float:left;margin-left:5px;"><?PHP echo($dados[id_cot]); ?>&nbsp;</div>
                                              <div id="divColuna1<?PHP echo($num); ?>" style="width:120px;float:left;margin-left:5px;"><?PHP echo($dados[razao_social_cli]); ?>&nbsp;</div>
                                              <div id="divColuna1<?PHP echo($num); ?>" style="width:120px;float:left;margin-left:5px;"><?PHP echo($dados[data_hora_ped_f]); ?></div>
											  <div id="divTotalUnico<?PHP echo($dados[id_ped]); ?>" style="width:80px;float:left;margin-left:5px;">&nbsp;</div>
											  <div id="divTotalGeral<?PHP echo($dados[id_ped]); ?>" style="width:80px;float:left;margin-left:5px;">&nbsp;</div>
                                              <script type="text/javascript">
											  pedido_num_produto(<?PHP echo($dados[id_ped]); ?>,'divTotalUnico<?PHP echo($dados[id_ped]); ?>');
											  </script>
											  <div id="divColuna1<?PHP echo($num); ?>" style="width:100px;float:left;margin-left:5px;"><?PHP echo($dados[situacao_ped_f]); ?></div>
											  <div id="divColuna1<?PHP echo($num); ?>" style="width:100px;float:left;margin-left:5px;"><?PHP echo(number_format($dados[comissao_ped], 2, ',', '.')); ?></div>
                                              <div id="divColuna3<?PHP echo($num); ?>" style="width:250px;float:left;margin-left:5px;text-align:center;line-height:20px;">
                                              <?PHP
											  if($dados[situacao_ped] != 2){ ?><a href="javascript:question('<?PHP echo(utf8_encode('Tem certeza que deseja liberar este pedido?\n\nAo clicar em [Ok] você está afirmando que recebeu o pagamento da taxa de liberação.\n\n O pedido será liberado ao fornecedor e um e-mail será disparado avisando')); ?>.','index.php?pagina=<?PHP echo($pagina); ?>&action=liberar&id_ped=<?PHP echo($dados[id_ped]); ?>&id_cot=<?PHP echo($dados[id_cot]); ?>&id_cli=<?PHP echo($dados[id_cli]); ?>&key=<?PHP echo($key); ?>');">Liberar Pedido</a> | <?PHP } ?><a href="javascript:question('Tem certeza que deseja excluir este registro?','index.php?pagina=<?PHP echo($pagina); ?>&amp;action=del&amp;id_ped=<?PHP echo($dados[id_ped]); ?>&key=<?PHP echo($key); ?>');">Excluir</a>
                                                <br>
                                                <a href="../restrito.php?action=relatorio_pedido<?PHP echo("&amp;id_ped=$dados[id_ped]&id_cli=$dados[id_cli]&xls=0"); ?>" target="_blank">Ver Pedido</a><br>
                                                <a href="../restrito.php?action=relatorio_pedido<?PHP echo("&amp;id_ped=$dados[id_ped]&id_cli=$dados[id_cli]&xls=1"); ?>" target="_blank">Exportar p/ excel</a><br>
                                              </div>
											</div>
											<br />
										  <?PHP
									}
								?>
								</form>
								<?PHP
								// agora vamos criar os botões "Anterior e próximo"
								$anterior = $pc -1;
								$proximo = $pc +1;
								echo('<P align="center">');
								if ($pc>1)
									{
										echo " <a href='index.php?pagina=$pagina&amp;page=$anterior&amp;key=$key&amp;id_fil=$id_fil&amp;data=$data&amp;dia=$dia&amp;mes=$mes&amp;ano=$ano&amp;dia2=$dia2&amp;mes2=$mes2&amp;ano2=$ano2&id_cli=$id_cli'>« Anterior | </a> ";
									}
								if($total_reg < $tr)
									{
										$meio = array();
										for ($i = 1; $i <= $tp+1; $i++) 
											{
												if($page == "")
													{
														$page = 1;
													}
												if($page == $i)
													{
														$cor = 'style="color:#FFFFFF;font:bold;background-color:#666666"';
													}
												else
													{
														$cor = '';
													}
												$meio[] = '<a '.$cor.' href="index.php?pagina='.$pagina.'&amp;page='.$i.'&amp;key='.$key.'&amp;id_fil='.$id_fil.'&amp;data='.$data.'&amp;dia='.$dia.'&amp;mes='.$mes.'&amp;ano='.$ano.'&amp;dia2='.$dia2.'&amp;mes2='.$mes2.'&amp;ano2='.$ano2.'&id_cli='.$id_cli.'">'.$i.'</a>';
											}
										$meio_pg = join(' | ', $meio);
										echo(''.$meio_pg.'');
									}
								if ($pc<$tp)
									{
										echo " <a href='index.php?pagina=$pagina&amp;page=$proximo&amp;key=$key&amp;id_fil=$id_fil&amp;data=$data&amp;dia=$dia&amp;mes=$mes&amp;ano=$ano&amp;dia2=$dia2&amp;mes2=$mes2&amp;ano2=$ano2&id_cli=$id_cli'> | Próxima »</a>";
									}
								echo('</p>');
							}
					}
			}
		?>
		<div style="clear:both"></div>
<?PHP
	}
?>