DROP TABLE IF EXISTS telemetry CASCADE;
CREATE TABLE telemetry (
   id                             BIGSERIAL PRIMARY KEY,
   date_creation                  TIMESTAMP DEFAULT NOW(),
   glpi_uuid                      VARCHAR(41),
   glpi_version                   VARCHAR(25),
   glpi_default_language          VARCHAR(10),
   glpi_avg_entities              VARCHAR(25),
   glpi_avg_computers             VARCHAR(25),
   glpi_avg_networkequipments     VARCHAR(25),
   glpi_avg_tickets               VARCHAR(25),
   glpi_avg_problems              VARCHAR(25),
   glpi_avg_changes               VARCHAR(25),
   glpi_avg_projects              VARCHAR(25),
   glpi_avg_users                 VARCHAR(25),
   glpi_avg_groups                VARCHAR(25),
   glpi_ldap_enabled              BOOLEAN,
   glpi_smtp_enabled              BOOLEAN,
   glpi_mailcollector_enabled     BOOLEAN,
   db_engine                      VARCHAR(25),
   db_version                     VARCHAR(25),
   db_size                        BIGINT,
   db_log_size                    BIGINT,
   db_sql_mode                    TEXT,
   php_version                    VARCHAR(25),
   php_modules                    TEXT,
   php_config_max_execution_time  INTEGER,
   php_config_memory_limit        VARCHAR(10),
   php_config_post_max_size       VARCHAR(10),
   php_config_safe_mode           BOOLEAN,
   php_config_session             TEXT,
   php_config_upload_max_filesize VARCHAR(10),
   os_family                      VARCHAR(50),
   os_distribution                VARCHAR(50),
   os_version                     VARCHAR(255)
);

DROP TABLE IF EXISTS glpi_plugin CASCADE;
CREATE TABLE glpi_plugin (
   id                             SERIAL PRIMARY KEY,
   pkey                           VARCHAR(50)
);

INSERT INTO glpi_plugin (pkey) VALUES
('room'), ('additionalalerts'), ('addressing'), ('racks'), ('manageentities'),
('manufacturersimports'), ('accounts'), ('fusioninventory'), ('appliances'), ('archires'),
('backups'), ('badges'), ('certificates'), ('databases'), ('domains'), ('ideabox'),
('financialreports'), ('eventlog'), ('environment'), ('immobilizationsheets'),
('installations'), ('network'), ('reports'), ('outlookical'), ('resources'), ('rights'),
('routetables'), ('shellcommands'), ('validation'), ('mailkb'), ('webapplications'),
('shutdowns'), ('syslogng'), ('treeview'), ('centreon'), ('dumpentity'), ('loadentity'), ('pdf'),
('datainjection'), ('genericobject'), ('order'), ('uninstall'), ('geninventorynumber'),
('removemfromocs'), ('massocsimport'), ('webservices'), ('cacti'), ('connections'),
('alerttimeline'), ('snort'), ('alias2010'), ('importbl'), ('bestmanagement'), ('22032'),
('projet'), ('morecron'), ('AdsmTape2010'), ('renamer'), ('relations'), ('catalogueservices'),
('ticketmail'), ('ticketlink'), ('behaviors'), ('mobile'), ('forward'), ('barscode'),
('monitoring'), ('formcreator'), ('themes'), ('positions'), ('helpdeskrating'), ('typology'),
('mask'), ('ocsinventoryng'), ('surveyticket'), ('utilitaires'), ('Reforme'), ('ticketcleaner'),
('escalation'), ('vip'), ('dashboard'), ('mantis'), ('reservation'), ('timezones'), ('exemple'),
('sccm'), ('talk'), ('tag'), ('news'), ('purgelogs'), ('mreporting'), ('custom'), ('customfields'),
('escalade'), ('moreticket'), ('itilcategorygroups'), ('consumables'), ('printercounters'),
('field'), ('fpsoftware'), ('fptheme'), ('fpsaml'), ('fpconsumables'), ('mhooks'), ('lock'),
('bootstraptheme'), ('webnotifications'), ('simcard'), ('processmaker'), ('seasonality'),
('moreldap'), ('tasklists'), ('mailanalyzer'), ('arsurveys'), ('glpi_ansible'), ('hidefields'),
('formvalidation'), ('mydashboard'), ('IFRAME'), ('timelineticket'), ('airwatch'),
('useditemsexport'), ('loginbyemail'), ('nebackup'), ('physicalinv'), ('openvas'),
('autologin'), ('father'), ('browsernotification'), ('armadito-glpi'), ('showloading'),
('service'), ('modifications'), ('credit'), ('myassets'), ('xivo');


DROP TABLE IF EXISTS telemetry_glpi_plugin CASCADE;
CREATE TABLE telemetry_glpi_plugin (
   telemetry_entry_id             BIGINT REFERENCES telemetry (id),
   glpi_plugin_id                 INTEGER REFERENCES glpi_plugin (id),
   version                        VARCHAR(25)
);



DROP TABLE IF EXISTS reference CASCADE;
CREATE TABLE reference (
   id                             SERIAL PRIMARY KEY,
   name                           VARCHAR(255),
   country                        VARCHAR(10),
   comment                        TEXT,
   num_assets                     INTEGER,
   num_helpdesk                   INTEGER,
   email                          VARCHAR(255),
   phone                          VARCHAR(30),
   url                            VARCHAR(255),
   referent                       VARCHAR(255),
   date_creation                  TIMESTAMP DEFAULT NOW(),
   is_displayed                   BOOLEAN
);



