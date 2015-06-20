<?php
require '/Slim/Slim/Slim.php';
\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();
$app->response()->header('Content-Type', 'application/json;charset=utf-8');
$app->get('/', function () {
echo "SlimProdutos";
});

$app->get('/diretores','getDiretores');

$app->post('/diretores','addDiretor');

$app->get('/diretores/:id','getDiretor');

$app->put('/diretores/:id','updateDiretor');

$app->delete('/diretores/:id','deleteDiretor');

$app->run();

function getConn()
{
return new PDO('mysql:host=localhost;dbname=filmesbd',
'root',
'160622Al',
array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
);

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
echo "{'message':'Diretor excluido com sucesso.'}";
}

?>