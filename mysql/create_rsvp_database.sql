-- SQL to create database, to have it all in one place

SET storage_engine = INNODB;

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
    PRIARY KEY (id)
);

CREATE TABLE party_emails (
    party_id INT NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE TABLE guests (
    id INT AUTO_INCREMENT,
    party_id INT NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    meal_id INT NULL,
    response BOOL DEFAULT NULL,
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

CREATE TABLE url_keys (
    id INT AUTO_INCREMENT,
    word VARCHAR(63) NOT NULL,
    party_id INT NULL,
    user_key TINYINT DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT UNIQUE INDEX (word)
);

