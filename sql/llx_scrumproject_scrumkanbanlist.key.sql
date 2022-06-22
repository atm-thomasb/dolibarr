-- Copyright (C) 2022 John Botella <john.botella@atm-consulting.fr>
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
ALTER TABLE llx_scrumproject_scrumkanbanlist ADD INDEX idx_scrumproject_scrumkanbanlist_rowid (rowid);
ALTER TABLE llx_scrumproject_scrumkanbanlist ADD INDEX idx_scrumproject_scrumkanbanlist_fk_scrum_kanban (fk_scrum_kanban);
ALTER TABLE llx_scrumproject_scrumkanbanlist ADD CONSTRAINT llx_scrumproject_scrumkanbanlist_fk_scrum_kanban FOREIGN KEY (fk_scrum_kanban) REFERENCES llx_scrumproject_scrumkanban(rowid);
ALTER TABLE llx_scrumproject_scrumkanbanlist ADD INDEX idx_scrumproject_scrumkanbanlist_fk_rank (fk_rank);
ALTER TABLE llx_scrumproject_scrumkanbanlist ADD CONSTRAINT llx_scrumproject_scrumkanbanlist_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
-- END MODULEBUILDER INDEXES

ALTER TABLE llx_scrumproject_scrumkanbanlist ADD UNIQUE INDEX uk_scrumproject_scrumkanbanlist_code(fk_scrum_kanban, ref_code);

--ALTER TABLE llx_scrumproject_scrumkanbanlist ADD CONSTRAINT llx_scrumproject_scrumkanbanlist_fk_field FOREIGN KEY (fk_field) REFERENCES llx_scrumproject_myotherobject(rowid);

