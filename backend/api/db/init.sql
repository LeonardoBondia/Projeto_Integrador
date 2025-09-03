-- ADMINISTRADOR
CREATE TABLE IF NOT EXISTS administrador (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email        VARCHAR(150) NOT NULL UNIQUE,
  senha        VARCHAR(255) NOT NULL,
  criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ALUNO
CREATE TABLE IF NOT EXISTS aluno (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  matricula         VARCHAR(16) NOT NULL UNIQUE,
  curso             VARCHAR(120) NOT NULL,
  nome              VARCHAR(150) NOT NULL,
  cpf               CHAR(11)     NOT NULL UNIQUE, 
  email             VARCHAR(150) NOT NULL UNIQUE,
  contato           VARCHAR(20)  NOT NULL,        
  administrador_id  INT UNSIGNED NOT NULL,
  criado_em         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_aluno_admin
    FOREIGN KEY (administrador_id) REFERENCES administrador(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- PROFESSOR
CREATE TABLE IF NOT EXISTS professor (
  id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nome              VARCHAR(150) NOT NULL,
  cpf               CHAR(11)     NOT NULL UNIQUE,
  email             VARCHAR(150) NOT NULL UNIQUE,
  contato           VARCHAR(20)  NOT NULL,
  administrador_id  INT UNSIGNED NOT NULL,
  criado_em         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_prof_admin
    FOREIGN KEY (administrador_id) REFERENCES administrador(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ENDERECO (compartilhado)
CREATE TABLE IF NOT EXISTS endereco (
  id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cep          CHAR(8)      NOT NULL,       -- só dígitos
  rua          VARCHAR(150) NOT NULL,
  bairro       VARCHAR(120) NOT NULL,
  numero       VARCHAR(10)  NOT NULL,
  uf           CHAR(2)      NOT NULL,
  complemento  VARCHAR(150) NULL,
  criado_em    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RELAÇÃO: ALUNO ↔ ENDERECO (1:1)
CREATE TABLE IF NOT EXISTS aluno_endereco (
  aluno_id    INT UNSIGNED NOT NULL,
  endereco_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (aluno_id, endereco_id),
  CONSTRAINT fk_aluno_endereco_aluno
    FOREIGN KEY (aluno_id) REFERENCES aluno(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_aluno_endereco_endereco
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- RELAÇÃO: PROFESSOR ↔ ENDERECO (1:1)
CREATE TABLE IF NOT EXISTS professor_endereco (
  professor_id INT UNSIGNED NOT NULL,
  endereco_id  INT UNSIGNED NOT NULL,
  PRIMARY KEY (professor_id, endereco_id),
  CONSTRAINT fk_prof_endereco_prof
    FOREIGN KEY (professor_id) REFERENCES professor(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_prof_endereco_endereco
    FOREIGN KEY (endereco_id) REFERENCES endereco(id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Índices úteis 
CREATE INDEX idx_aluno_nome   ON aluno(nome);
CREATE INDEX idx_prof_nome    ON professor(nome);
CREATE INDEX idx_end_cep      ON endereco(cep);
