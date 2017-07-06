<?PHP
# Classe Caneta
class Caneta
	{
		public $modelo;
		private $ponta;
		
		protected $carga;
		protected $tampada;
		
		public function getModelo()
			{
				return $this->modelo;
			}
			
		public function setModelo($m)
			{
				$this->modelo = $m;
			}
			
		public function getPonta()
			{
				return $this->ponta;
			}
		
		public function setPonta($p)
			{
				$this->ponta = $p;
			}
		
		public function rabiscar()
			{
				if($this->tampada == true)
					{
						echo '<br>Erro! Tá tampado, não dá pra rabiscar<br>';
					}
				else
					{
						echo '<br>Estou rabiscando<br>';
					}
			}
			
		public function tampar()
			{
				$this->tampada = true;
			}
			
		public function destampar()
			{
				$this->tampada = false;
			}
	}
?>