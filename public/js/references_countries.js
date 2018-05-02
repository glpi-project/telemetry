var references_map;

$(document).ready(function() {
   // leaflet map for references countries
   references_map = L.map('references_countries').setView([0.0, 0.0], 2);
   L.tileLayer.provider(map_provider, map_provider_conf).addTo(references_map);
   _loadMapRefs(references_map);
});

var _loadMapRefs = function(references_map) {
   //retrieve geojson data
   references_map.spin(true);
   $.getJSON(geojson_path).done(function(countries_geo) {
      // add a popup for country hover
      var references_info = L.control();
      references_info.onAdd = function (map) {
         this._div = L.DomUtil.create('div', 'country_info');
         this.update();
         return this._div;
      };
      references_info.update = function (country) {
         this._div.innerHTML =
            (country
                ? "<b>" + country.name + "<b>:" + country.total
                : "hover a country");
      };
      references_info.addTo(references_map);

      // add geo json for each country
      $.each($('#references_countries').data("id"), function(index, value) {
         var current_geojson = countries_geo[value['cca3']];
         for (var attr in value) {
            current_geojson.features[0].properties[attr] = value[attr];
         }

         var geojson = L.geoJson(current_geojson, {
            style: function (country) {
               return {
                  weight: 1,
                  opacity: 1,
                  color: 'white',
                  dashArray: '',
                  fillOpacity: 0.6,
                  fillColor: getColor(country.properties.total)
               }
            },
            onEachFeature: function (feature, layer) {
               layer.on({
                  mouseover: function(e) {
                     var layer = e.target;
                     layer.setStyle({
                        weight: 1,
                        color: '#666',
                        dashArray: '',
                        fillOpacity: 0.6
                     });
                     if (!L.Browser.ie && !L.Browser.opera) {
                        layer.bringToFront();
                     }
                     references_info.update(layer.feature.properties);
                  },
                  mouseout: function(e) {
                     geojson.resetStyle(e.target);
                     references_info.update();
                  },
               });
               layer.bindPopup(feature.properties.name);
            }
         }).addTo(references_map);
      });
   }).fail(function () {
      // add a popup for country hover
      var fail_info = L.control();
      fail_info.onAdd = function (map) {
         this._div = L.DomUtil.create('div', 'fail_info');
         this._div.innerHTML = 'An error occured loading data :('
            + '<br/><span id="reload_data"><i class="fa fa-refresh"></i> Reload</span>';
         return this._div;
      };
      fail_info.addTo(references_map);
      $('#reload_data').on('click', function() {
          $('.fail_info').remove();
         _loadMapRefs(references_map);
      });
   }).always(function() {
      //hide spinner
      references_map.spin(false);
   });
}

// leaflet controls
var getColor = function(d) {
   return d > 1000 ? '#800026' :
          d > 500  ? '#BD0026' :
          d > 200  ? '#E31A1C' :
          d > 100  ? '#FC4E2A' :
          d > 50   ? '#FD8D3C' :
          d > 20   ? '#FEB24C' :
          d > 10   ? '#FED976' :
                     '#FFEDA0';
};
