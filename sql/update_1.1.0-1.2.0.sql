ALTER TABLE llx_scrumproject_scrumuserstory CHANGE point qty DOUBLE NULL DEFAULT NULL;

ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD label varchar(255) DEFAULT '';
