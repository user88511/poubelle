CREATE DATABASE instruments_db;
USE instruments_db;
CREATE TABLE instruments (ID INT PRIMARY KEY AUTO_INCREMENT, NOM VARCHAR(100),CATEGORIE VARCHAR(50),PRIX DECIMAL(10,2),ETAT VARCHAR(20),DISPONIBILITE BOOLEAN,VENDEUR VARCHAR(100));
INSERT INTO instruments VALUES (1,"Guitare acoustique","Corde","150.00", "Bon", "Disponible", "John Doe");
INSERT INTO instruments VALUES (2,"Piano électrique", "Clavier", "300.00", "Très bon", "Disponible", "Jane Smith");