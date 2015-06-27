<?php
require '/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->response()->header('Content-Type', 'application/json;charset=utf-8');

$app->get('/', function () {
	echo "API Filmes";
});

/*
	Routes filmes
*/
$app->get('/filmes', 'getFilmes');
$app->post('/filmes', 'addFilme');
$app->get('/filmes/:id', 'getFilme');
$app->put('/filmes/:id', 'updateFilme');
$app->delete('/filmes/:id', 'deleteFilme');

/* 
	Routes diretores
*/
$app->get('/diretores','getDiretores');
$app->post('/diretores','addDiretor');
$app->get('/diretores/:id','getDiretor');
$app->put('/diretores/:id','updateDiretor');
$app->delete('/diretores/:id','deleteDiretor');

/*
	Routes produtoras
*/
$app->get('/produtoras', 'getProdutoras');
$app->post('/produtoras', 'addProdutora');
$app->put('/produtoras/:id', 'updateProdutora');
$app->delete('/produtoras/:id', 'deleteProdutora');

/*
	Routes filmes e diretores
*/
$app->get('/diretores/:id/filmes', 'getFilmesByDirectors');

$app->run();

function getConn()
{
	return new PDO('mysql:host=localhost;dbname=filmesbd',
	'root',
	'',
	array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
}
/*
	Consultar todos os filmes
*/
function getFilmes()
{
	if (isset($_GET['titulo'])){
        $titulo = $_GET['titulo'];
        getByTitle($titulo);
		return;
    }
	
	if(isset($_GET['offset']) && isset($_GET['limit']))
	{
		$offset = $_GET['offset'];
		$limit = $_GET['limit'];
		getPaginacao($offset, $limit);
		return;
	}
	
	if(isset($_GET['genero']) && isset($_GET['ano']))
	{
		$genero = $_GET['genero'];
		$ano = $_GET['ano'];
		getByGeneroEAno($genero, $ano);
		return;
	}

	$stmt = getConn()->query("SELECT f.id_filme as idFilme, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora;");

	$f = array();	
	while($filme = $stmt->fetch(PDO::FETCH_OBJ))
	{
		$f[] = jsonFormat($filme);
	}
	
	echo json_encode($f);	
}

function getFilme($id)
{
	$conn = getConn();
	$sql = "SELECT f.id_filme as id, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora WHERE id_filme=:id;";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	$filme = $stmt->fetch(PDO::FETCH_OBJ);
		
	$f = jsonFormat($filme);

	echo json_encode($f);
}

/*
	Adicionar um novo filme
*/
function addFilme()
{
	$request = \Slim\Slim::getInstance()->request();
	$filme = json_decode($request->getBody());
	$sql = "INSERT INTO filmes (titulo, ano, genero, pais, id_produtora, id_diretor) values (:titulo,:ano, :genero, :pais, :produtoraId, :diretorId)";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("titulo",$filme->titulo);
	$stmt->bindParam("ano",$filme->ano);
	$stmt->bindParam("genero",$filme->genero);
	$stmt->bindParam("pais",$filme->pais);
	$stmt->bindParam("produtoraId",$filme->produtoraId);
	$stmt->bindParam("diretorId",$filme->diretorId);
	$stmt->execute();
	$filme->id = $conn->lastInsertId();
	echo json_encode($filme);
}

/*
	Atualizar um filme
*/
function updateFilme($id)
{
	$request = \Slim\Slim::getInstance()->request();
	$filme = json_decode($request->getBody());
	$sql = "UPDATE filmes SET titulo=:titulo, ano=:ano, genero=:genero, pais=:pais, produtora_id=:produtoraId, diretor_id=:diretorId WHERE id_filme=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("titulo",$filme->titulo);
	$stmt->bindParam("ano",$filme->ano);
	$stmt->bindParam("genero",$filme->genero);
	$stmt->bindParam("pais",$filme->pais);
	$stmt->bindParam("produtoraId",$filme->produtoraId);
	$stmt->bindParam("diretorId",$filme->diretorId);
	$stmt->bindParam("id",$id);
	$stmt->execute();

	echo json_encode($filme);
}

function deleteFilme($id)
{
	$sql = "DELETE FROM filmes WHERE id_filme=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	echo json_encode(array('message' => 'Filme excluído com sucesso.'));
}

function getDiretores()
{
	$stmt = getConn()->query("SELECT * FROM diretores");
	$diretores = $stmt->fetchAll(PDO::FETCH_OBJ);
	echo json_encode($diretores);
}

function addDiretor()
{
	$request = \Slim\Slim::getInstance()->request();
	$diretor = json_decode($request->getBody());
	$sql = "INSERT INTO diretores (nome,sobrenome) values (:nome,:sobrenome)";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("nome",$diretor->nome);
	$stmt->bindParam("sobrenome",$diretor->sobrenome);
	$stmt->execute();
	$diretor->id_diretor = $conn->lastInsertId();
	echo json_encode($diretor);
}

function getDiretor($id)
{
	$conn = getConn();
	$sql = "SELECT * FROM diretores WHERE id_diretor=:id";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	$diretor = $stmt->fetchObject();

	echo json_encode($diretor);
}

function updateDiretor($id)
{
	$request = \Slim\Slim::getInstance()->request();
	$diretor = json_decode($request->getBody());
	$sql = "UPDATE diretores SET nome=:nome,sobrenome=:sobrenome WHERE id_diretor=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("nome",$diretor->nome);
	$stmt->bindParam("sobrenome",$diretor->sobrenome);
	$stmt->bindParam("id",$id);
	$stmt->execute();

	echo json_encode($diretor);
}

function deleteDiretor($id)
{
	$sql = "DELETE FROM diretores WHERE id_diretor=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	echo json_encode(array('message' => 'Diretor excluído com sucesso.'));
}

/*
	Produtoras
*/
function getProdutoras()
{
	$stmt = getConn()->query("SELECT * FROM produtoras");
	$produtoras = $stmt->fetchAll(PDO::FETCH_OBJ);
	echo json_encode($produtoras);
}

function addProdutora()
{
	$request = \Slim\Slim::getInstance()->request();
	$produtora = json_decode($request->getBody());
	$sql = "INSERT INTO produtoras (nome) values (:nome)";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("nome",$produtora->nome);
	$stmt->execute();
	$produtora->id = $conn->lastInsertId();
	echo json_encode($produtora);
}

function updateProdutora($id)
{
	$request = \Slim\Slim::getInstance()->request();
	$produtora = json_decode($request->getBody());
	$sql = "UPDATE produtoras SET nome=:nome WHERE id_produtora=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("nome",$produtora->nome);
	$stmt->bindParam("id",$id);
	$stmt->execute();

	echo json_encode($produtora);
}

function deleteProdutora($id)
{
	$sql = "DELETE FROM produtoras WHERE id_produtora=:id";
	$conn = getConn();
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	echo json_encode(array('message' => 'Produtora excluída com sucesso.'));
}

function getFilmesByDirectors($id)
{
	$conn = getConn();
	$sql = "SELECT f.id_filme as idFilme, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora WHERE dr.id_diretor=:id;";
	$stmt = $conn->prepare($sql);
	$stmt->bindParam("id",$id);
	$stmt->execute();
	
	$f = array();
	while($filme = $stmt->fetch(PDO::FETCH_OBJ))
	{
		$f[] = jsonFormat($filme);
	}	
	echo json_encode($f);		
}

function getByTitle($titulo)
{
	$request = \Slim\Slim::getInstance()->request();
	$sql = "SELECT f.id_filme as idFilme, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora WHERE f.titulo LIKE '%{$titulo}%';";
	
	$conn = getConn();
	$stmt = $conn->prepare($sql);
    $stmt->execute();
	
	$f = array();
	while($filme = $stmt->fetch(PDO::FETCH_OBJ))
	{
		$f[] = jsonFormat($filme);
	}	
	echo json_encode($f);
}

function getPaginacao($offset, $limit)
{
	$inicio = ($limit - 1) * $offset;
	
	$request = \Slim\Slim::getInstance()->request();
	
	$sqlCountRow = "SELECT COUNT(*) as count FROM filmes";
	$conn = getConn();
	$stmt = $conn->prepare($sqlCountRow);
	$stmt->execute();
	$count_row = $stmt->fetch(PDO::FETCH_OBJ);
	$total_registros = $count_row->count;
	$page_count = (int)ceil($total_registros / $offset);
	
	
	$sql = "SELECT f.id_filme as idFilme, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora LIMIT {$inicio},{$offset};";

	$stmt = $conn->prepare($sql);
	$stmt->execute();
	$f = array();
	while($filme = $stmt->fetch(PDO::FETCH_OBJ))
	{
		$f[] = jsonFormat($filme);
	}

	$paginacao = array(
		"totalRegistros" => $total_registros,
		"totalPaginas" => $page_count,
		"filmes" => $f
	);
	echo json_encode($paginacao);
}

function getByGeneroEAno($genero, $ano)
{
	$request = \Slim\Slim::getInstance()->request();
	$sql = "SELECT f.id_filme as idFilme, f.titulo, f.ano, f.genero, f.pais, 
	p.id_produtora as idProdutora, p.nome as produtora, 
	dr.id_diretor as idDiretor,dr.nome as nome_diretor, 
	dr.sobrenome as sobrenome_diretor from diretores as dr 
	INNER JOIN filmes f ON f.id_diretor = dr.id_diretor
	INNER JOIN produtoras p ON p.id_produtora = f.id_produtora WHERE f.genero LIKE '%{$genero}%' AND f.ano = $ano;";
	
	$conn = getConn();
	$stmt = $conn->prepare($sql);
    $stmt->execute();
	
	$f = array();
	while($filme = $stmt->fetch(PDO::FETCH_OBJ))
	{
		$f[] = jsonFormat($filme);
	}	
	echo json_encode($f);
}

function jsonFormat($filme)
{
	return array(
			"id" => $filme->idFilme,
			"titulo" => $filme->titulo,
			"ano" => $filme->ano, 
			"genero" => $filme->genero,
			"pais" => $filme->pais,
			"diretor" => array(
				"id" => $filme->idDiretor,
				"nome" => $filme->nome_diretor,
				"sobrenome" => $filme->sobrenome_diretor
			),
			"produtora" => array(
				"id" => $filme->idProdutora,
				"nome" => $filme->produtora
			)
		);
}

?>