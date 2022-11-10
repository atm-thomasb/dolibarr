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


CREATE TABLE llx_scrumproject_scrumtask(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_scrum_user_story_sprint integer,
--     fk_user_dev integer,
    label varchar(255),
	description text, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL,
    qty_planned real NOT NULL,
    qty_consumed real,
    tms timestamp,
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	import_key varchar(14),
    last_main_doc varchar(255),
    status smallint NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
