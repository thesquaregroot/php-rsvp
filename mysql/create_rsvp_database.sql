--
-- SQL to create database as "rsvp"
--
-- The database is intended to be created by rsvp_admin.php (via include/db_setup_functions.php#create_tables)
--  This file included largely for documentation purposes.
--

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
    plus_ones INT DEFAULT 0,
    url_key VARCHAR(255) NULL,
    rsvp_comment TEXT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE party_emails (
    party_id INT NOT NULL,
    email VARCHAR(255) NOT NULL
);

CREATE INDEX idx_party_emails_party_id ON party_emails (party_id);

CREATE TABLE guests (
    id INT AUTO_INCREMENT,
    party_id INT NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    meal_id INT NULL,
    response BOOL DEFAULT NULL,
    is_plus_one BOOL NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
);

CREATE TABLE meals (
    id INT AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    PRIMARY KEY (id)
);

CREATE TABLE url_keys (
    id INT AUTO_INCREMENT,
    value VARCHAR(63) NOT NULL,
    party_id INT NULL,
    user_key TINYINT DEFAULT 0,
    PRIMARY KEY (id),
    CONSTRAINT UNIQUE INDEX (value)
);
