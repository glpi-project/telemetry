$(document).ready(function() {

   new Chartist.Pie('#php_versions',
                    $('#php_versions').data("id"),
                    {
                        plugins: [
                           Chartist.plugins.tooltip()
                        ]
                    });

   new Chartist.Pie('#glpi_versions',
                    $('#glpi_versions').data("id"),
                    {
                        plugins: [
                           Chartist.plugins.tooltip()
                        ]
                    });

   new Chartist.Bar('#top_plugins',
                    $('#top_plugins').data("id"),
                    {
                        // horizontalBars: true,
                        distributeSeries: true,
                        plugins: [
                           Chartist.plugins.tooltip()
                        ]
                    });

   new Chartist.Pie('#os_family',
                    $('#os_family').data("id"),
                    {
                        plugins: [
                           Chartist.plugins.tooltip()
                        ]
                    });

});
