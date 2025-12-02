DROP DATABASE IF EXISTS bd_pokefullStack;
CREATE DATABASE bd_pokefullStack;

use bd_pokefullStack;

create table camarero(
    idCamarero int primary key auto_increment not null,
    nombre varchar(60) not null,
    apellidos varchar(80) not null,
    nombreUsu varchar(60) not null,
    dni varchar(10) not null,
    telefono varchar(10) not null,
    email varchar(60) not null,
    fechaContratacion date not null,
    password varchar(60) not null
);

create table sala(
    idSala int primary key auto_increment not null,
    nombre varchar(60) not null
);

create table mesa(
    idMesa int primary key auto_increment not null,
    nombre varchar(60) not null,
    numSillas int not null,
    estado enum('libre','ocupada') not null,
    idSala int not null
);

create table historico(
    idHistorico int primary key auto_increment not null,
    idMesa int not null,
    idSala int not null,
    idCamarero int not null,
    horaOcupacion datetime not null,
    -- horaDesocupacion datetime not null
    horaDesocupacion datetime DEFAULT null
);

alter table historico
add constraint fk_mesa_historico
foreign key (idMesa) references mesa(idMesa);

alter table historico
add constraint fk_camarero_historico
foreign key (idCamarero) references camarero(idCamarero);

alter table historico
add constraint fk_sala_historico
foreign key (idSala) references sala(idSala);

alter table mesa
add constraint fk_sala_mesa 
foreign key (idSala) references sala(idSala);



insert into sala (nombre) values("kanto");
insert into sala (nombre) values("jotho");
insert into sala (nombre) values("hoenn");
insert into sala (nombre) values("sinnoh");
insert into sala (nombre) values("unova");
insert into sala (nombre) values("kalos");
insert into sala (nombre) values("alola");
insert into sala (nombre) values("galar");
insert into sala (nombre) values("paldea");

-- Kanto
insert into mesa (nombre, numSillas, estado, idSala) values("PLATEADA", 2, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("CELESTE", 2, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("CARMÍN", 3, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("AZULONA", 4, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("FUCSIA", 4, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("AZAFRÁN", 3, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("CANELA", 3, "libre", 1);
insert into mesa (nombre, numSillas, estado, idSala) values("VERDE", 4, "libre", 1);

-- Johto
insert into mesa (nombre, numSillas, estado, idSala) values("VIOLETA", 2, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("PRIMAVERA", 3, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("TRIGAL", 4, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("MALVA", 3, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("IRIS", 2, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("OLIVO", 2, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("ORQUÍDEA", 3, "libre", 2);
insert into mesa (nombre, numSillas, estado, idSala) values("ENDRINO", 4, "libre", 2);

-- Hoenn
insert into mesa (nombre, numSillas, estado, idSala) values("FÉRRICA", 2, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("AZULIZA", 3, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("MALVALONA", 3, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("LAVACALDA", 4, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("PETALIA", 3, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("ARBOLEDA", 4, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("CALIGULA", 4, "libre", 3);
insert into mesa (nombre, numSillas, estado, idSala) values("ALGARIA", 3, "libre", 3);

-- Sinnoh
insert into mesa (nombre, numSillas, estado, idSala) values("ROCAVELO", 3, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("VETUSTA", 3, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("MARINA", 4, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("CORAZÓN", 3, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("PUNTANEVA", 4, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("MINERA", 3, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("SOLAR", 4, "libre", 4);
insert into mesa (nombre, numSillas, estado, idSala) values("ALBA", 4, "libre", 4);

-- Unova
insert into mesa (nombre, numSillas, estado, idSala) values("GRES", 2, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("ESMALTE", 3, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("PORCELANA", 4, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("MAYÓLICA", 4, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("FAYENZA", 5, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("LOZA", 5, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("TEJA", 6, "libre", 5);
insert into mesa (nombre, numSillas, estado, idSala) values("CAOLÍN", 8, "libre", 5);

-- Kalos
insert into mesa (nombre, numSillas, estado, idSala) values("NOVARTE", 2, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("RELIEVE", 3, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("LUMINALIA", 5, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("YANTRA", 6, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("ROMANTIS", 3, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("TEMPERA", 4, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("FLUXUS", 4, "libre", 6);
insert into mesa (nombre, numSillas, estado, idSala) values("FRACTAL", 4, "libre", 6);

-- Alola
insert into mesa (nombre, numSillas, estado, idSala) values("MELEMELE-A", 2, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("MELEMELE-B", 4, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("AKALA-A", 2, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("AKALA-B", 4, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("ULA-ULA-A", 2, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("ULA-ULA-B", 4, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("PONI-A", 2, "libre", 7);
insert into mesa (nombre, numSillas, estado, idSala) values("PONI-B", 4, "libre", 7);

-- Galar
insert into mesa (nombre, numSillas, estado, idSala) values("PISTON-A", 2, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("PISTON-B", 4, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("ARTEJO-A", 2, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("ARTEJO-B", 4, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("LADERA-A", 2, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("LADERA-B", 4, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("AURIGA", 3, "libre", 8);
insert into mesa (nombre, numSillas, estado, idSala) values("PUNTERA", 4, "libre", 8);

-- Paldea
insert into mesa (nombre, numSillas, estado, idSala) values("LEUDAL", 2, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("MEZCLORA", 2, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("ALFORNO", 3, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("CANTARA", 2, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("FRAGUA", 3, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("NAPADA", 4, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("MEDALI", 4, "libre", 9);
insert into mesa (nombre, numSillas, estado, idSala) values("ALMIZCLE", 2, "libre", 9);
