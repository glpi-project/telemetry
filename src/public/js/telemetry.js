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

});
