/*================================================================================*/
/* DDL SCRIPT                                                                     */
/*================================================================================*/
/*  Title    :                                                                    */
/*  FileName : modelagem_relacional.ecm                                           */
/*  Platform : MySQL 5                                                            */
/*  Version  : Concept                                                            */
/*  Date     : sábado, 20 de junho de 2015                                        */
/*================================================================================*/
/*================================================================================*/
/* CREATE TABLES                                                                  */
/*================================================================================*/
CREATE DATABASE  IF NOT EXISTS `filmesbd` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `filmesbd`;

CREATE TABLE `diretores` (
  `id_diretor` INT(11) AUTO_INCREMENT NOT NULL,
  `nome` VARCHAR(40) NOT NULL,
  `sobrenome` VARCHAR(40) NOT NULL,
  CONSTRAINT `PK_diretores` PRIMARY KEY (`id_diretor`),
	KEY `idx_nome` (`nome`(15)),
	KEY `idx_sobrenome` (`sobrenome`(15))
  );

CREATE TABLE `produtoras` (
  `id_produtora` INT(11) AUTO_INCREMENT NOT NULL,
  `nome` VARCHAR(80) NOT NULL,
  CONSTRAINT `PK_produtoras` PRIMARY KEY (`id_produtora`)
);

CREATE TABLE `filmes` (
  `id_filme` INT(11) AUTO_INCREMENT NOT NULL,
  `titulo` VARCHAR(100) NOT NULL,
  `ano` INT(4) NOT NULL,
  `genero` VARCHAR(40) NOT NULL,
  `pais` VARCHAR(40) NOT NULL,
  `id_produtora` INT(11) NOT NULL,
  `id_diretor` INT(11) NOT NULL,
  CONSTRAINT `PK_filmes` PRIMARY KEY (`id_filme`)
);

/*================================================================================*/
/* CREATE FOREIGN KEYS                                                            */
/*================================================================================*/

ALTER TABLE `filmes`
  ADD CONSTRAINT `FK_filmes_diretores`
  FOREIGN KEY (`id_diretor`) REFERENCES `diretores` (`id_diretor`);

ALTER TABLE `filmes`
  ADD CONSTRAINT `FK_filmes_produtoras`
  FOREIGN KEY (`id_produtora`) REFERENCES `produtoras` (`id_produtora`);

/*================================================================================*/
/* CREATE INDEXES                                                            	  */
/*================================================================================*/

CREATE UNIQUE INDEX ak_nome_produtora ON produtoras (nome);
CREATE UNIQUE INDEX ak_titulo_filme ON filmes (titulo);
CREATE UNIQUE INDEX ak_diretor_nome_sobrenome ON diretores (nome, sobrenome);