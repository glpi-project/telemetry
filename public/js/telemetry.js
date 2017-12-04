// # OPTIONS FOR CHARTS #
var palettes = {
    'belize11': [
        "#5cbae6","#b6d957","#fac364","#8cd3ff","#d998cb","#f2d249",
        "#93b9c6","#ccc5a8","#52bacc","#dbdb46","#98aafb"
    ],
    'test6': [
        "#063951", "#0d95bc", "#a2b969", "#ebcb38", "#f36f13", "#c13018"
    ],
    'fall6': [
        "#7C5B37", "#B37A3B", "#E29138", "#DBA365",
    ],
    'bluestone': [
        "#29384B", "#2B486D", "#2E5D98", "#276ABE", "#277EEB"
    ],
    'combo': [
        "#006495", "#004C70", "#0093D1", "#F2635F", "#F4D00C", "#E0A025"
    ],
    'icecream': [
        "#FFEC94", "#FFAEAE", "#FFF0AA", "#B0E57C", "#B4D8E7", "#56BAEC"
    ],
    'nivo': [
        "#E8C1A0", "#F47560", "#F1E15B", "#E8A838", "#61CDBB", "#97E3D5"
    ]
};

var plotly_pie_layout = {
    showlegend: false,
    margin: {
        l: 0,
        r: 0,
        b: 10,
        t: 10,
        pad: 0
    }
};

var plotly_bar_layout = {
    showlegend: true,
    margin: {
        l: 40,
        r: 10,
        b: 85,
        t: 10,
        pad: 0
    },
    xaxis: {
        tickangle: 35
    },
};

var plotly_config = {
    displayModeBar: false,
};

var plotlyData = function(raw_data) {
    $.each(raw_data, function(index, current) {
        current.textinfo  = 'label';
        current.hoverinfo = 'label+value';
        current.insidetextfont = {
        color: "#FEFEFE"
        };
        if (typeof current.marker == "undefined") {
        current.marker    = {};
        }

        if (typeof current.palette != 'undefined') {
        current.marker = {
            colors: palettes[current.palette],
        };
        }

        if (current.type == 'pie') {
            if (typeof current.hole == "undefined" ) {
                current.pull = .05;
            }
        }
    });

    return raw_data;
}



var _plugins_contents;
var _plugins_title;

var pluginsExpanded = function(chart_id, chart) {
    var _chart = $('#' + chart_id);
    var _title = chart.find('.card-text');

    if (typeof _plugins_contents == 'undefined') {
        _plugins_contents = _chart.html();
        _plugins_title = _title.text();
    }

    if (chart.hasClass('chart-max')) {
        $.ajax(
            _allPluginsURL, {
                success: function(data) {
                    _title.text(_plugins_title.replace(/5/, data[0]['x'].length));
                    new Plotly.newPlot(
                        'top_plugins',
                        plotlyData(data),
                        $.extend(
                            {},
                            plotly_bar_layout, {
                                paper_bgcolor: '#529AA5',
                                plot_bgcolor: '#529AA5',
                                showlegend: false
                            }
                        ),
                        plotly_config
                    );
                }
            }
        );
    } else {
        _chart.html(_plugins_contents);
        _title.html(_plugins_title);
    }
};

$(document).ready(function() {
    // # CHARTS DEFINITION #
    var php_versions = $('#php_versions');
    if (php_versions.length > 0) {
        Plotly.newPlot(
            "php_versions",
            plotlyData(php_versions.data("id")),
            plotly_bar_layout,
            plotly_config
        );
    }

    var nb_telemetry_entries = $('#nb_telemetry_entries');
    if (nb_telemetry_entries.length > 0) {
        data_nb_telemetry_entries = nb_telemetry_entries.data("id")
        nb_telemetry_entries.html(
            "<div class='big-number' title='"+ data_nb_telemetry_entries.raw +"'>" +
            data_nb_telemetry_entries.nb + "</div>"
        );
    }

    var nb_reference_entries = $('#nb_reference_entries');
    if (nb_reference_entries.length > 0) {
        data_nb_reference_entries = nb_reference_entries.data("id")
        nb_reference_entries.html(
            "<div class='big-number' title='"+ data_nb_reference_entries.raw +"'>" +
            data_nb_reference_entries.nb + "</div>"
        );
    }

    var glpi_versions = $('#glpi_versions');
    if (glpi_versions.length > 0) {
        Plotly.newPlot(
            "glpi_versions",
            plotlyData(glpi_versions.data("id")),
            plotly_pie_layout,
            plotly_config
        );
    }

    var top_plugins = $('#top_plugins');
    if (top_plugins.length > 0) {
        new Plotly.newPlot(
            'top_plugins',
            plotlyData(top_plugins.data("id")),
            $.extend(
                {},
                plotly_bar_layout, {
                    paper_bgcolor: '#529AA5',
                    plot_bgcolor: '#529AA5',
                    showlegend: false
                }
            ),
            plotly_config
        );
    }

    var os_family = $('#os_family');
    if (os_family.length > 0) {
        Plotly.newPlot(
            "os_family",
            plotlyData(os_family.data("id")),
            $.extend({}, plotly_pie_layout, {paper_bgcolor: '#E9AA63'}),
            plotly_config
        );
    }

    var default_languages = $('#default_languages');
    if (default_languages.length > 0) {
        Plotly.newPlot(
            "default_languages",
            plotlyData(default_languages.data("id")),
            plotly_pie_layout,
            plotly_config
        );
    }

    var web_engines = $('#web_engines');
    if (web_engines.length > 0) {
        Plotly.newPlot(
            "web_engines",
            plotlyData(web_engines.data("id")),
            $.extend({}, plotly_pie_layout, {paper_bgcolor: '#1A5197'}),
            plotly_config
        );
    }

    var db_engines = $('#db_engines');
    if (db_engines.length > 0) {
        Plotly.newPlot(
            "db_engines",
            plotlyData(db_engines.data("id")),
            plotly_pie_layout,
            plotly_config
        );
    }

    var install_modes = $('#install_modes');
    if (install_modes.length > 0) {
        Plotly.newPlot(
            "install_modes",
            plotlyData(install_modes.data("id")),
            $.extend({}, plotly_pie_layout, {paper_bgcolor: '#1A5197'}),
            plotly_config
        );
    }

    // # MISC INTERACTIONS #

    // masonry on dashboard
    $('.dashboard').masonry({
        itemSelector: '.chart',
        columnWidth: 350,
        fitWidth: true
    });

   // permits to expand chart cards
   $(".chart .expand").click(function() {
      var e_button = $(this);
      var card = e_button.parents('.card');
      var chart_id = card.find(".ct-chart").attr('id');
      var chart    = e_button.parents(".chart");
      var plotly = chart.find(".plotly");

      // set fullscreen on chart
      if (!chart.hasClass('chart-max')) {
         chart
            .toggleClass("chart-max")
            .width($(window).width() * .8)
            .height('80vh')
            .find(".card-block:not(.description)")
               .height('calc(80vh - 78px)');
      } else {
         // disable full screen
         chart
            .toggleClass("chart-max")
            .width("")
            .height("")
            .find(".card-block:not(.description)")
               .height("");
      }

      //execute callback if defined
      var callback = chart.data('expand-callback');
      if (typeof callback != 'undefined') {
        window[callback](chart_id, chart);
      }

      // redraw chart
      $('.dashboard')
         .masonry()
         .one( 'layoutComplete', function() {
            if (plotly.length != 0) {
               Plotly.Plots.resize(Plotly.d3.select("#"+chart_id).node())
            } else {
               references_map.invalidateSize();
            }

            // scroll to the chart
            $('html, body').animate({
               scrollTop: $(chart).offset().top - 30
            }, 200);
         });
   });

});
