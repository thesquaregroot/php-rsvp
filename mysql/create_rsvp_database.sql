CREATE SCHEMA rsvp;

CREATE TABLE admin_users (
    id INT AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL,
    password CHAR(60) NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE parties (
    id INT AUTO_INCREMENT,
    nickname VARCHAR(255) NULL,
    plus_ones INT DEFAULT 0;
    PRIARY KEY (id)
);

CREATE TABLE party_emails (
    party_id INT NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE TABLE guests (
    id INT AUTO_INCREMENT,
    party_id INT NOT NULL,
    name VARCHAR(255) NULL,
    meal_id INT NULL,
    is_plus_one BOOL NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE INDEX idx_emails_guest_id ON emails (guest_id);

CREATE TABLE meals (
    id INT AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (id)
);

