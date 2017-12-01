{
   "data": {
      "glpi": {
         "uuid": "xxxxxxxx-xxxxxxxx-xxxxxxxxxxxxxx.xxxxxxxx",
         "version": "9.2",
         "plugins": [
            {
               "key": "plugin_1",
               "version": "x.x.x"
            },
            {
               "key": "plugin_2",
               "version": "x.x.x"
            },
            {
               "key": "plugin_3",
               "version": "x.x.x"
            }
         ],
         "default_language": "fr_FR",
         "install_mode": "TARBALL",
         "usage": {
            "avg_entities": "0-500",
            "avg_computers": "1000-2500",
            "avg_networkequipments": "1000-2500",
            "avg_tickets": "1000-2500",
            "avg_problems": "0",
            "avg_changes": "0",
            "avg_projects": "0",
            "avg_users": "500-1000",
            "avg_groups": "0-500",
            "ldap_enabled": true,
            "notifications_modes": [],
            "mailcollector_enabled": true
         }
      },
      "system": {
         "db": {
            "engine": "MariaDB",
            "version": "15.1",
            "size": "95105843",
            "log_size": "1677721",
            "sql_mode": ""
         },
         "web_server": {
            "engine": "nginx",
            "version": "1.10"
         },
         "php": {
            "version": "7.1.5",
            "modules": [
               "apc",
               "apcu",
               "calendar",
               "Core",
               "ctype",
               "curl",
               "date",
               "dom",
               "..."
            ],
            "setup": {
               "max_execution_time": "30",
               "memory_limit": "128M",
               "post_max_size": "20M",
               "safe_mode": true,
               "session": "save_handler=\"files\"",
               "upload_max_filesize": "20M"
            }
         },
         "os": {
            "family": "linux",
            "distribution": "Debian",
            "version": "8.8"
         }
      }
   }
}
