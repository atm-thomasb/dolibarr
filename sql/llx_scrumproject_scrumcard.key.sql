-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_rowid (rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_ref (ref);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_entity (entity);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_fk_user_po (fk_user_po);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_user_po FOREIGN KEY (fk_user_po) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_fk_user_dev (fk_user_dev);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_user_dev FOREIGN KEY (fk_user_dev) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_fk_task (fk_task);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_task FOREIGN KEY (fk_task) REFERENCES llx_projet_task(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_fk_scrumsprint (fk_scrumsprint);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_scrumsprint FOREIGN KEY (fk_scrumsprint) REFERENCES llx_scrumsprint(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_fk_stage (fk_stage);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_stage FOREIGN KEY (fk_stage) REFERENCES llx_c_scrum_stage(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumcard ADD INDEX idx_scrumproject_scrumcard_status (status);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_scrumproject_scrumcard ADD UNIQUE INDEX uk_scrumproject_scrumcard_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_scrumproject_scrumcard ADD CONSTRAINT llx_scrumproject_scrumcard_fk_field FOREIGN KEY (fk_field) REFERENCES llx_scrumproject_myotherobject(rowid);

