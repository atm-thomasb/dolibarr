ALTER TABLE llx_scrumproject_scrumuserstory CHANGE point qty DOUBLE NULL DEFAULT NULL;

ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD label varchar(255) DEFAULT '';
ALTER TABLE llx_scrumproject_scrumcard ADD fk_rank integer NOT NULL;
ALTER TABLE llx_scrumproject_scrumcard ADD fk_scrum_kanbanlist integer NOT NULL;
ALTER TABLE llx_scrumproject_scrumcard ADD fk_element int(11) DEFAULT '0';
ALTER TABLE llx_scrumproject_scrumcard ADD element_type varchar(255)  DEFAULT NULL;
