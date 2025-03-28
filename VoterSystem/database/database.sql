CREATE DATABASE voter_system;
USE voter_system;

-- Users table: stores voters and admins.
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('voter','admin') DEFAULT 'voter',
  verified TINYINT(1) DEFAULT 0,
  has_voted TINYINT(1) DEFAULT 0,
  verification_code VARCHAR(32)
);

-- Candidates table.
CREATE TABLE candidates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  party VARCHAR(100) NOT NULL,
  votes INT DEFAULT 0
);

-- Votes table.
CREATE TABLE votes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  voter_id INT,
  candidate_id INT,
  vote_time DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (voter_id) REFERENCES users(id),
  FOREIGN KEY (candidate_id) REFERENCES candidates(id)
);


CREATE TABLE IF NOT EXISTS settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    voting_end_time INT NOT NULL
);


