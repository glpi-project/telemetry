var references_map;

$(document).ready(function() {

   // leaflet map for references countries
   references_map = L.map('references_countries').setView([0.0, 0.0], 2);

   // get tile
   L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token={accessToken}', {
      id: 'mapbox.light',
      accessToken: 'pk.eyJ1Ijoib3J0aGFnaCIsImEiOiJjajRoNjMybXkwMDFxMzJtN3AwNzJtMWh3In0.3rhNEAzgXke91gHLvlW3Vg'
   })
   .addTo(references_map);

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

   //retrieve geojson data
   $.getJSON('./telemetry/geojson').done(function(countries_geo) {

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
   });
});

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