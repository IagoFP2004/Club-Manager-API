-- Crear base de datos
CREATE DATABASE IF NOT EXISTS futbol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE futbol;

-- Resetear tablas si existen
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS coach;
DROP TABLE IF EXISTS player;
DROP TABLE IF EXISTS club;
DROP TABLE IF EXISTS user;
SET FOREIGN_KEY_CHECKS = 1;

-- Tabla club (PK = id INT, id_club como código único)
CREATE TABLE club
(
    id          INT AUTO_INCREMENT NOT NULL,
    id_club     VARCHAR(5)         NOT NULL,
    nombre      VARCHAR(255)       NOT NULL,
    fundacion   INT                NOT NULL,
    ciudad      VARCHAR(255)       NOT NULL,
    estadio     VARCHAR(255)       NOT NULL,
    presupuesto DECIMAL(15, 2)     NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY UNIQ_club_id_club (id_club)
) DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Tabla player (FK -> club.id)
CREATE TABLE player
(
    id        INT AUTO_INCREMENT NOT NULL,
    nombre    VARCHAR(255)       NOT NULL,
    apellidos VARCHAR(255)       NOT NULL,
    dorsal    INT                NOT NULL,
    salario   DECIMAL(10, 2)     NOT NULL,
    id_club   INT,
    PRIMARY KEY (id),
    KEY IDX_player_club (id_club),
    CONSTRAINT FK_player_club FOREIGN KEY (id_club) REFERENCES club (id)
) DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Tabla coach (FK -> club.id)
CREATE TABLE coach
(
    id        INT AUTO_INCREMENT NOT NULL,
    dni       VARCHAR(9)         NOT NULL,
    nombre    VARCHAR(255)       NOT NULL,
    apellidos VARCHAR(255)       NOT NULL,
    salario   DECIMAL(10, 2)     NOT NULL,
    id_club   INT,
    PRIMARY KEY (id),
    KEY IDX_coach_club (id_club),
    CONSTRAINT FK_coach_club FOREIGN KEY (id_club) REFERENCES club (id)
) DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Tabla user (sin relaciones externas)
CREATE TABLE user
(
    id     INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255)       NOT NULL,
    email  VARCHAR(255)       NOT NULL UNIQUE,
    pass   VARCHAR(255)       NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Insertar clubes
INSERT INTO club (id_club, nombre, fundacion, ciudad, estadio, presupuesto)
VALUES ('RM', 'Real Madrid', 1902, 'Madrid', 'Santiago Bernabéu', 800000000),
       ('FCB', 'FC Barcelona', 1899, 'Barcelona', 'Camp Nou', 750000000),
       ('ATM', 'Atlético de Madrid', 1903, 'Madrid', 'Civitas Metropolitano', 400000000),
       ('SEV', 'Sevilla FC', 1890, 'Sevilla', 'Ramón Sánchez-Pizjuán', 200000000),
       ('VAL', 'Valencia CF', 1919, 'Valencia', 'Mestalla', 300000000),
       ('CEL', 'Celta de Vigo', 1923, 'Vigo', 'Balaídos', 150000000),
       ('MAN', 'Manchester United', 1878, 'Manchester', 'Old Trafford', 900000000),
       ('OFC', 'Olympique de Marseille', 1899, 'Marsella', 'Vélodrome', 250000000);

-- Insertar jugadores
INSERT INTO player (nombre, apellidos, dorsal, salario, id_club)
VALUES ('Iago', 'Aspas Juncal', 10, 2500, (SELECT id FROM club WHERE id_club = 'CEL')),
       ('Vinicius', 'Junior', 7, 20000000, (SELECT id FROM club WHERE id_club = 'RM')),
       ('Jude', 'Bellingham', 5, 18000000, (SELECT id FROM club WHERE id_club = 'RM')),
       ('Karim', 'Benzema', 9, 25000000, (SELECT id FROM club WHERE id_club = 'RM')),
       ('Luka', 'Modric', 10, 15000000, (SELECT id FROM club WHERE id_club = 'RM')),
       ('Robert', 'Lewandowski', 9, 25000000, (SELECT id FROM club WHERE id_club = 'FCB')),
       ('Pedri', 'González', 8, 15000000, (SELECT id FROM club WHERE id_club = 'FCB')),
       ('Gavi', 'Paez', 6, 12000000, (SELECT id FROM club WHERE id_club = 'FCB')),
       ('Frenkie', 'de Jong', 21, 18000000, (SELECT id FROM club WHERE id_club = 'FCB')),
       ('Antoine', 'Griezmann', 7, 12000000, (SELECT id FROM club WHERE id_club = 'ATM')),
       ('Jan', 'Oblak', 13, 8000000, (SELECT id FROM club WHERE id_club = 'ATM')),
       ('Sergio', 'Ramos', 4, 5000000, (SELECT id FROM club WHERE id_club = 'SEV')),
       ('Youssef', 'En-Nesyri', 15, 4000000, (SELECT id FROM club WHERE id_club = 'SEV')),
       ('José Luis', 'Gayà', 14, 6000000, (SELECT id FROM club WHERE id_club = 'VAL')),
       ('Hugo', 'Duro', 9, 3000000, (SELECT id FROM club WHERE id_club = 'VAL')),
       ('Marcus', 'Rashford', 10, 20000000, (SELECT id FROM club WHERE id_club = 'MAN'));

-- Insertar entrenadores
INSERT INTO coach (dni, nombre, apellidos, salario, id_club)
VALUES ('11223344C', 'Diego', 'Simeone', 30000000, (SELECT id FROM club WHERE id_club = 'ATM')),
       ('12345678A', 'Carlo', 'Ancelotti', 15000000, (SELECT id FROM club WHERE id_club = 'RM')),
       ('87654321B', 'Xavi', 'Hernández', 12000000, (SELECT id FROM club WHERE id_club = 'FCB')),
       ('44332211D', 'José Luis', 'Mendilibar', 3000000, (SELECT id FROM club WHERE id_club = 'SEV')),
       ('55667788E', 'Rubén', 'Baraja', 2500000, (SELECT id FROM club WHERE id_club = 'VAL')),
       ('99887766F', 'Claudio', 'Giraldez', 2000000, (SELECT id FROM club WHERE id_club = 'CEL')),
       ('11223344G', 'Erik', 'ten Hag', 8000000, (SELECT id FROM club WHERE id_club = 'MAN')),
       ('55443322H', 'Igor', 'Tudor', 4000000, (SELECT id FROM club WHERE id_club = 'OFC'));

-- Insertar usuarios de ejemplo
INSERT INTO user (nombre, email, pass)
VALUES('Administrador', 'admin@futbol.com', 'pbkdf2_sha256$200000$4ba32d43b20ffadebff7f3ba55c6b884$ca304b59c58b0d0353fdcf0b0decb0515d7875be0b866846bc2023cd059849f0'),
      ('test', 'test@futbol.com', 'pbkdf2_sha256$200000$fc03e47b0439cf216b0784cf32a6bd85$202622edfb7128a3da105df4398982acfea38d57008a5068f6eeb4fbc61399b4');

