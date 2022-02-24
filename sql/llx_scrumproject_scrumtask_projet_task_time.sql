
CREATE TABLE llx_scrumproject_scrumtask_projet_task_time(
    rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
    fk_projet_task_time integer NOT NULL,
    fk_scrumproject_scrumtask integer NOT NULL
) ENGINE=innodb COMMENT='table de liason avec les saisie de temps';
