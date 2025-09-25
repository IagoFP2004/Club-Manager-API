-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS futbol CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE futbol;

-- Crear tabla club con id_club como PK e id como campo único
CREATE TABLE IF NOT EXISTS club (
    id INT AUTO_INCREMENT NOT NULL,
    id_club VARCHAR(5) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    fundacion INT NOT NULL,
    ciudad VARCHAR(255) NOT NULL,
    estadio VARCHAR(255) NOT NULL,
    presupuesto NUMERIC(15, 2) NOT NULL,
    PRIMARY KEY (id_club),
    UNIQUE KEY (id)  -- id es único y se usa en parámetros de rutas
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
    INDEX IDX_player_club (id_club),
    CONSTRAINT FK_player_club FOREIGN KEY (id_club) REFERENCES club (id_club)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

-- Crear tabla coach
CREATE TABLE IF NOT EXISTS coach (
    id INT AUTO_INCREMENT NOT NULL,
    dni VARCHAR(9) NOT NULL,
    nombre VARCHAR(255) NOT NULL,
    apellidos VARCHAR(255) NOT NULL,
    salario NUMERIC(10,0) NOT NULL,
    id_club VARCHAR(5) DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX IDX_coach_club (id_club),
    CONSTRAINT FK_coach_club FOREIGN KEY (id_club) REFERENCES club (id_club)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`;

-- Insertar datos de club (id se autoincrementa, id_club es la PK)
INSERT INTO club (id_club, nombre, fundacion, ciudad, estadio, presupuesto) VALUES
('RM', 'Real Madrid', 1902, 'Madrid', 'Santiago Bernabéu', 800000000),
('FCB', 'FC Barcelona', 1899, 'Barcelona', 'Camp Nou', 750000000),
('ATM', 'Atlético de Madrid', 1903, 'Madrid', 'Wanda Metropolitano', 400000000),
('SEV', 'Sevilla FC', 1890, 'Sevilla', 'Ramón Sánchez-Pizjuán', 200000000),
('VAL', 'Valencia CF', 1919, 'Valencia', 'Mestalla', 300000000),
('CEL', 'Celta de Vigo', 1923, 'Vigo', 'Balaídos', 150000000),
('MAN', 'Manchester United', 1878, 'Manchester', 'Old Trafford', 900000000),
('OFC', 'Olympique de Marseille', 1899, 'Marsella', 'Vélodrome', 250000000);

-- Insertar datos de coach
INSERT INTO coach (dni, nombre, apellidos, salario, id_club) VALUES
('11223344C', 'Diego', 'Simeone', 30000000, 'ATM'),
('12345678A', 'Carlo', 'Ancelotti', 15000000, 'RM'),
('87654321B', 'Xavi', 'Hernández', 12000000, 'FCB'),
('44332211D', 'José Luis', 'Mendilibar', 3000000, 'SEV'),
('55667788E', 'Rubén', 'Baraja', 2500000, 'VAL'),
('99887766F', 'Claudio', 'Giraldez', 2000000, 'CEL'),
('11223344G', 'Erik', 'ten Hag', 8000000, 'MAN'),
('55443322H', 'Igor', 'Tudor', 4000000, 'OFC');

-- Insertar datos de player
INSERT INTO player (nombre, apellidos, dorsal, salario, id_club) VALUES
('Iago', 'Aspas Juncal', 10, 2500, 'CEL'),
('Vinicius', 'Junior', 7, 20000000, 'RM'),
('Jude', 'Bellingham', 5, 18000000, 'RM'),
('Karim', 'Benzema', 9, 25000000, 'RM'),
('Luka', 'Modric', 10, 15000000, 'RM'),
('Robert', 'Lewandowski', 9, 25000000, 'FCB'),
('Pedri', 'González', 8, 15000000, 'FCB'),
('Gavi', 'Paez', 6, 12000000, 'FCB'),
('Frenkie', 'de Jong', 21, 18000000, 'FCB'),
('Antoine', 'Griezmann', 7, 12000000, 'ATM'),
('Jan', 'Oblak', 13, 8000000, 'ATM'),
('Sergio', 'Ramos', 4, 5000000, 'SEV'),
('Youssef', 'En-Nesyri', 15, 4000000, 'SEV'),
('José Luis', 'Gayà', 14, 6000000, 'VAL'),
('Hugo', 'Duro', 9, 3000000, 'VAL'),
('Marcus', 'Rashford', 10, 20000000, 'MAN');

-- NOTAS IMPORTANTES:
-- - id_club es la clave primaria de la tabla club (VARCHAR(5))
-- - id es un campo único autoincremental que se usa en los parámetros de las rutas
-- - Las claves foráneas de player y coach referencian club.id_club
-- - Para acceder a un club: GET /clubs/1 (usa el campo id numérico)
-- - Las respuestas JSON incluyen tanto id como id_club para mayor flexibilidad
