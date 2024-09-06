-- Eliminar la base de datos si existe
DROP DATABASE IF EXISTS DesireCloset;

-- Crear la base de datos DesireCloset
CREATE DATABASE DesireCloset;

-- Usar la base de datos DesireCloset
USE DesireCloset;

-- Tabla Usuarios
CREATE TABLE Usuarios (
    idUsuario INT AUTO_INCREMENT PRIMARY KEY,
    nombreUsuario VARCHAR(25),
    nombre VARCHAR(25),
    apellidos1 VARCHAR(25),
    apellidos2 VARCHAR(25),
    email VARCHAR(50),
    password VARCHAR(255),
    sexo VARCHAR(10),
    descripcion VARCHAR(255),
    fechaNacimiento DATE,
    fechaRegistro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fechaBaja TIMESTAMP NULL,
    calificacion INT(5),
    foto VARCHAR(255) DEFAULT NULL,
    pagado BOOLEAN DEFAULT FALSE
);

-- Tabla Roles
CREATE TABLE Roles (
    idRol INT AUTO_INCREMENT PRIMARY KEY,
    nombreRol VARCHAR(25)
);

-- Tabla Usuarios_Roles
CREATE TABLE Usuarios_Roles (
    idUsuarioRol INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario INT,
    idRol INT,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idRol) REFERENCES Roles(idRol)
);

-- Tabla Categorias
CREATE TABLE Categorias (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nombreCategoria VARCHAR(25)
);

-- Tabla Productos
CREATE TABLE Productos (
    idProducto INT AUTO_INCREMENT PRIMARY KEY,
    nombreProducto VARCHAR(25),
    talla VARCHAR(25),
    descripcion VARCHAR(100),
    precio DECIMAL(10, 2),
    condicion VARCHAR(50),
    idUsuario INT,
    idCategoria INT,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idCategoria) REFERENCES Categorias(idCategoria)
);

-- Tabla Fotos
CREATE TABLE Fotos (
    idFoto INT AUTO_INCREMENT PRIMARY KEY,
    nombreFoto VARCHAR(255),
    idProducto INT,
    idUsuario INT,
    FOREIGN KEY (idProducto) REFERENCES Productos(idProducto),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

-- Tabla MeGusta
CREATE TABLE MeGusta (
    idMeGusta INT AUTO_INCREMENT PRIMARY KEY,
    idProducto INT,
    idUsuario INT,
    FOREIGN KEY (idProducto) REFERENCES Productos(idProducto),
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

-- Tabla Mensajes
CREATE TABLE Mensajes (
    idMensaje INT AUTO_INCREMENT PRIMARY KEY,
    idEmisor INT,
    idReceptor INT,
    idProducto INT,
    contenido VARCHAR(255),
    FOREIGN KEY (idEmisor) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idReceptor) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idProducto) REFERENCES Productos(idProducto)
);

-- Tabla Valoraciones
CREATE TABLE Valoraciones (
    idValoracion INT AUTO_INCREMENT PRIMARY KEY,
    idValorado INT,
    idValorador INT,
    valoracion INT,
    comentario VARCHAR(255),
    FOREIGN KEY (idValorado) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idValorador) REFERENCES Usuarios(idUsuario)
);

-- Tabla Transacciones
CREATE TABLE Transacciones (
    idTransaccion INT AUTO_INCREMENT PRIMARY KEY,
    idComprador INT,
    idVendedor INT,
    idProducto INT,
    fechaTransaccion DATE,
    hora TIME,
    cantidad DECIMAL(10, 2),
    estado ENUM('vendido', 'comprado', 'reservado','enventa'),
    FOREIGN KEY (idComprador) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idVendedor) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idProducto) REFERENCES Productos(idProducto)
);

-- Tabla ValidacionDNI
CREATE TABLE ValidacionDNI (
    idValidacion INT AUTO_INCREMENT PRIMARY KEY,
    dni VARCHAR(255) NOT NULL,
    estado ENUM('pendiente', 'validado', 'rechazado') NOT NULL,
    idUsuario INT,
    fechaValidacion DATE,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario)
);

-- Insertar los roles
INSERT INTO Roles (nombreRol) VALUES ('admin'), ('usuario'), ('invitado');

-- Insertar las categorías en la tabla Categorias
INSERT INTO Categorias (nombreCategoria) VALUES 
('Bragas y Tangas'), 
('Sujetadores'), 
('Fotos de pies'), 
('Juguetes sexuales');
