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



ALTER TABLE llx_scrumproject_scrumsprintuser ADD INDEX idx_scrumproject_scrumsprintuser_rowid (rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD INDEX idx_scrumproject_scrumsprintuser_fk_scrum_sprint (fk_scrum_sprint);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_scrum_sprint FOREIGN KEY (fk_scrum_sprint) REFERENCES llx_scrumproject_scrumsprint(rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD INDEX idx_scrumproject_scrumsprintuser_fk_user_role (fk_user_role);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_user_role FOREIGN KEY (fk_user_role) REFERENCES llx_c_type_contact(rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_user FOREIGN KEY (fk_user) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_user_modif FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD INDEX idx_scrumproject_scrumsprintuser_status (status);
ALTER TABLE llx_scrumproject_scrumsprintuser ADD UNIQUE INDEX unique_sprint_user_key ( fk_scrum_sprint, fk_user);

--ALTER TABLE llx_scrumproject_scrumsprintuser ADD UNIQUE INDEX uk_scrumproject_scrumsprintuser_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_scrumproject_scrumsprintuser ADD CONSTRAINT llx_scrumproject_scrumsprintuser_fk_field FOREIGN KEY (fk_field) REFERENCES llx_scrumproject_myotherobject(rowid);

