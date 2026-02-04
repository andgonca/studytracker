SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS links, subjects, subdomains, subdomain_links, domains, domain_links, certifications, study_items;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE certifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    certification_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (certification_id) REFERENCES certifications(id) ON DELETE CASCADE
);

CREATE TABLE domain_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    url TEXT NOT NULL,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE subdomains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    FOREIGN KEY (domain_id) REFERENCES domains(id) ON DELETE CASCADE
);

CREATE TABLE subdomain_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subdomain_id INT NOT NULL,
    url TEXT NOT NULL,
    FOREIGN KEY (subdomain_id) REFERENCES subdomains(id) ON DELETE CASCADE
);

CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subdomain_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    comments TEXT,
    status ENUM('Not Started', 'In Progress', 'Confident') DEFAULT 'Not Started',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (subdomain_id) REFERENCES subdomains(id) ON DELETE CASCADE
);

CREATE TABLE links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_id INT NOT NULL,
    url TEXT NOT NULL,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
);