$(document).ready(function() {
   // defines options for carts
   var donut_options = {
      donut: true,
      plugins: [
         Chartist.plugins.tooltip()
      ]
  };

  var simple_bar_options = {
      // horizontalBars: true,
      distributeSeries: true,
      axisX: {
         showGrid: false,
      },
      axisY: {
         showGrid: false,
         showLabel: false,
      },
      plugins: [
         Chartist.plugins.tooltip()
      ]
  }

   // render charts
   var php_versions = new Chartist.Pie(
      '#php_versions',
      $('#php_versions').data("id"),
      donut_options
   );
   animateDonut(php_versions);

   var glpi_versions = new Chartist.Pie(
      '#glpi_versions',
      $('#glpi_versions').data("id"),
      donut_options
   );
   animateDonut(glpi_versions);

   var top_plugins = new Chartist.Bar(
      '#top_plugins',
      $('#top_plugins').data("id"),
      simple_bar_options
   )
   .on('draw', function(data) {
      if(data.type === 'bar') {
         data.element.attr({
            style: 'stroke-width: 40px'
         });
      }
   });
   animateSimpleBar(top_plugins);

   var os_family = new Chartist.Pie(
      '#os_family',
      $('#os_family').data("id"),
      donut_options
   );
   animateDonut(os_family);


   // permits to expand chart cards
   $(".chart .expand").click(function() {
      var that = $(this);

      // toggle columm class
      that
         .parents(".chart")
            .toggleClass('col-sm-4')
            .toggleClass('col-sm-12');

      // redraw chart
      setTimeout(function() {
         that
            .parents(".chart")
            .find(".ct-chart")
            .get(0).__chartist__.update();
      }, 400);
   });

});


var animateDonut = function (chart) {
   chart.on('draw', function(data) {
      if(data.type === 'slice') {
         // Get the total path length in order to use for dash array animation
         var pathLength = data.element._node.getTotalLength();

         // Set a dasharray that matches the path length as prerequisite to animate dashoffset
         data.element.attr({
            'stroke-dasharray': pathLength + 'px ' + pathLength + 'px'
         });

         // Create animation definition while also assigning an ID to the animation for later sync usage
         var animationDefinition = {
            'stroke-dashoffset': {
               id: 'anim' + data.index,
               dur: 500,
               from: -pathLength + 'px',
               to:  '0px',
               easing: Chartist.Svg.Easing.easeOutQuint,
               // We need to use `fill: 'freeze'` otherwise our animation will fall back to initial (not visible)
               fill: 'freeze'
            }
         };

         // If this was not the first slice, we need to time the animation so that it uses the end sync event of the previous animation
         if(data.index !== 0) {
            animationDefinition['stroke-dashoffset'].begin = 'anim' + (data.index - 1) + '.end';
         }

         // We need to set an initial value before the animation starts as we are not in guided mode which would do that for us
         data.element.attr({
            'stroke-dashoffset': -pathLength + 'px'
         });

         // We can't use guided mode as the animations need to rely on setting begin manually
         // See http://gionkunz.github.io/chartist-js/api-documentation.html#chartistsvg-function-animate
         data.element.animate(animationDefinition, false);
     }
   });
};

var animateSimpleBar = function(chart) {
   chart.on('draw', function(data) {
      if(data.type === 'bar') {
         data.element.animate({
            y2: {
            dur: 1000,
            from: data.y1,
            to: data.y2,
            easing: Chartist.Svg.Easing.easeOutQuint
         },
         opacity: {
            dur: 1000,
            from: 0,
            to: 1,
            easing: Chartist.Svg.Easing.easeOutQuint
         }
       });
     }
   });
}