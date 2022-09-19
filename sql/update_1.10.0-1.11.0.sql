CREATE TABLE llx_categorie_scrumcard (fk_categorie integer, fk_scrumcard integer, import_key integer);
ALTER TABLE llx_categorie_scrumcard ADD PRIMARY KEY (fk_categorie, fk_scrumcard);
ALTER TABLE llx_categorie_scrumcard ADD KEY idx_categorie_scrumcard_fk_categorie (fk_categorie);
ALTER TABLE llx_categorie_scrumcard ADD KEY idx_categorie_scrumcard_fk_product (fk_scrumcard);
ALTER TABLE llx_categorie_scrumcard ADD CONSTRAINT llx_categorie_scrumcard_categorie_rowid FOREIGN KEY (fk_categorie) REFERENCES llx_categorie (rowid);