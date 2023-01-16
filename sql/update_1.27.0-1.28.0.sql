ALTER TABLE llx_scrumproject_scrumuserstory ADD default_prod_calc SET('count','notcount','onlyspent') NOT NULL DEFAULT 'count'
ALTER TABLE llx_scrumproject_scrumuserstorysprint ADD default_prod_calc SET('count','notcount','onlyspent') NOT NULL DEFAULT 'count'
ALTER TABLE llx_scrumproject_scrumtask ADD prod_calc SET('count','notcount','onlyspent') NOT NULL DEFAULT 'count'
