CREATE SCHEMA rsvp;

CREATE TABLE admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password ???
);

CREATE TABLE guests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    meal_id INT NULL,
    plus_one_count INT NOT NULL
);

CREATE TABLE plus ones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    guest_id INT NOT NULL,
    name VARCHAR(255) NOT NULL
);

CREATE TABLE meals (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL
);

CREATE INDEX idx_plus_ones_guest_id ON plus_ones.guest_id;

