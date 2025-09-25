-- Backup completo de la base de datos futbol
-- Generado automáticamente para transferir a otro PC

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS futbol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE futbol;

-- Crear tabla club
CREATE TABLE IF NOT EXISTS club (
    id_club VARCHAR(5) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    fundacion INT NOT NULL,
    ciudad VARCHAR(255) NOT NULL,
    estadio VARCHAR(255) NOT NULL,
    presupuesto NUMERIC(15, 2) NOT NULL,
    PRIMARY KEY (id_club)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

-- Crear tabla player
CREATE TABLE IF NOT EXISTS player (
    id INT AUTO_INCREMENT NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    dorsal INT NOT NULL,
    salario NUMERIC(10, 0) NOT NULL,
    id_club VARCHAR(5) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_98197A6533CE2470 (id_club),
    CONSTRAINT FK_98197A6533CE2470 FOREIGN KEY (id_club) REFERENCES club (id_club)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

-- Crear tabla coach
CREATE TABLE IF NOT EXISTS coach (
    id INT AUTO_INCREMENT NOT NULL,
    dni VARCHAR(9) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    salario VARCHAR(255) NOT NULL,
    id_club VARCHAR(5) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_3B5966CC33CE2470 (id_club),
    CONSTRAINT FK_3B5966CC33CE2470 FOREIGN KEY (id_club) REFERENCES club (id_club)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

-- Insertar datos de club
INSERT INTO club (id_club, nombre, fundacion, ciudad, estadio, presupuesto) VALUES
('RM', 'Real Madrid', 1902, 'Madrid', 'Santiago Bernabéu', 800000000),
('FCB', 'FC Barcelona', 1899, 'Barcelona', 'Camp Nou', 750000000),
('ATM', 'Atlético de Madrid', 1903, 'Madrid', 'Wanda Metropolitano', 400000000),
('SEV', 'Sevilla FC', 1890, 'Sevilla', 'Ramón Sánchez-Pizjuán', 200000000),
('VAL', 'Valencia CF', 1919, 'Valencia', 'Mestalla', 300000000);

-- Insertar datos de coach
INSERT INTO coach (dni, nombre, apellidos, sueldo, id_club) VALUES
('12345678A', 'Carlo', 'Ancelotti', '15000000', 'RM'),
('87654321B', 'Xavi', 'Hernández', '12000000', 'FCB'),
('11223344C', 'Diego', 'Simeone', '10000000', 'ATM'),
('44332211D', 'José Luis', 'Mendilibar', '3000000', 'SEV'),
('55667788E', 'Rubén', 'Baraja', '2500000', 'VAL');

-- Insertar datos de player
INSERT INTO player (nombre, apellidos, dorsal, salario, id_club) VALUES
('Vinicius', 'Junior', 7, '20000000', 'RM'),
('Jude', 'Bellingham', 5, '18000000', 'RM'),
('Robert', 'Lewandowski', 9, '25000000', 'FCB'),
('Pedri', 'González', 8, '15000000', 'FCB'),
('Antoine', 'Griezmann', 7, '12000000', 'ATM'),
('Jan', 'Oblak', 13, '8000000', 'ATM'),
('Sergio', 'Ramos', 4, '5000000', 'SEV'),
('Youssef', 'En-Nesyri', 15, '4000000', 'SEV'),
('José Luis', 'Gayà', 14, '6000000', 'VAL'),
('Hugo', 'Duro', 9, '3000000', 'VAL');
