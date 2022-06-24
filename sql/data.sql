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

INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042151,'scrumproject_scrumsprint','internal','PO','ScrumProjectUserRolePO','1',null);
INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042152,'scrumproject_scrumsprint','internal','DEV','ScrumProjectUserRoleDEV','1',null);

INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042153,'scrumproject_scrumcard','internal','PO','ScrumProjectUserRolePO','1',null);
INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042154,'scrumproject_scrumcard','internal','DEV','ScrumProjectUserRoleDEV','1',null);

INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042155,'scrumproject_scrumtask','internal','PO','ScrumProjectUserRolePO','1',null);
INSERT INTO llx_c_type_contact(rowid, element, source, code, libelle, active, module) VALUES (1042156,'scrumproject_scrumtask','internal','DEV','ScrumProjectUserRoleDEV','1',null);

INSERT INTO llx_c_scrumcard_stage(rowid, code, label, position, picto, active) VALUES (1, 'BKLO','Backlog',10,'fa fa-lightbulb',1);
INSERT INTO llx_c_scrumcard_stage(rowid, code, label, position, picto, active) VALUES (2, 'TODO','Todo',20,'fas fa-list',1);
INSERT INTO llx_c_scrumcard_stage(rowid, code, label, position, picto, active) VALUES (3, 'INPR','In progress',30,'fa fa-spinner',1);
INSERT INTO llx_c_scrumcard_stage(rowid, code, label, position, picto, active) VALUES (4, 'TEST','Testing',40,'fas fa-check',1);
INSERT INTO llx_c_scrumcard_stage(rowid, code, label, position, picto, active) VALUES (5, 'DONE','Done',50,'fas fa-check-double',1);
