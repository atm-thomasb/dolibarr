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
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD INDEX idx_scrumproject_scrumuserstorysprint_rowid (rowid);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD INDEX idx_scrumproject_scrumuserstorysprint_fk_scrum_user_story (fk_scrum_user_story);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD CONSTRAINT llx_scrumproject_scrumuserstorysprint_fk_scrum_user_story FOREIGN KEY (fk_scrum_user_story) REFERENCES llx_scrumproject_scrumuserstory(rowid);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD INDEX idx_scrumproject_scrumuserstorysprint_fk_scrum_sprint (fk_scrum_sprint);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD CONSTRAINT llx_scrumproject_scrumuserstorysprint_fk_scrum_sprint FOREIGN KEY (fk_scrum_sprint) REFERENCES llx_scrumproject_scrumsprint(rowid);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD INDEX idx_scrumproject_scrumuserstorysprint_business_value (business_value);
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD CONSTRAINT llx_scrumproject_scrumuserstorysprint_fk_user_creat FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD UNIQUE INDEX uk_scrumproject_scrumuserstorysprint_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD CONSTRAINT llx_scrumproject_scrumuserstorysprint_fk_field FOREIGN KEY (fk_field) REFERENCES llx_scrumproject_myotherobject(rowid);

-- ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD UNIQUE unique_plannif_for_sprint ( fk_scrum_user_story, fk_scrum_sprint);
