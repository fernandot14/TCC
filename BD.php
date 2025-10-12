drop database if exists mr;
create database mr;
use mr;

drop table if exists filme;
create table filme(
id_filme int not null auto_increment primary key,
nome_filme varchar(50) not null,
data_lancamento date not null,
genero varchar(50) not null,
trailer varchar(150),
caminho_imagem varchar(150),
media_tomatoes decimal(10,2) NOT NULL,
media_imbd decimal (10,2) not null,
media_geral decimal (10,2) not null
);

drop table if exists usuario;
create table usuario(
id_usuario int not null auto_increment primary key,
nome_usuario varchar(50) not null,
foto_perfil varchar(150),
senha char(10) not null,
email varchar(50) not null unique,
tipo varchar(20)
);

drop table if exists comentario;
create table comentario(
id_comentario int not null auto_increment primary key,
conteudo varchar(200) not null,
data_comentario date not null,
id_filme int,
id_usuario int,
foreign key (id_filme) references filme(id_filme),
foreign key (id_usuario) references usuario(id_usuario)
);

select * from usuario;

insert into comentario (conteudo, data_comentario, id_usuario) values ('foi muito legal', '2007-01-02', 2)

