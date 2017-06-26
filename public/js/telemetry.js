$(document).ready(function() {
   // masonry on dashboard
   $('.dashboard').masonry({
     itemSelector: '.chart',
     columnWidth: 350,
     fitWidth: true
   });

   // defines options for carts
   var pie_options = {
      // donutWidth: 60,
      // donutSolid: true,
      height: '100%',
      plugins: [
         Chartist.plugins.tooltip()
      ]
  };
  var donut_options = pie_options;
  donut_options.donut = true;

  var simple_bar_options = {
      horizontalBars: true,
      distributeSeries: true,
      reverseData: true,
      // height: 200,
      axisX: {
         showGrid: false,
         showLabel: false,
      },
      axisY: {
         offset: 100,
         showGrid: false,
      },
      plugins: [
         Chartist.plugins.tooltip()
      ]
  }

   // render charts
   // var php_versions = new Chartist.Line(
   var php_versions = new Chartist.Bar(
      '#php_versions',
      $('#php_versions').data("id"),
      {
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
   );

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
   /*.on('draw', function(data) {
      if(data.type === 'bar') {
         data.element.attr({
            style: 'stroke-width: 40px'
         });
      }
   })*/;
   animateSimpleBar(top_plugins);

   var os_family = new Chartist.Pie(
      '#os_family',
      $('#os_family').data("id"),
      pie_options
   );
   animateDonut(os_family);

   var default_languages = new Chartist.Pie(
      '#default_languages',
      $('#default_languages').data("id"),
      donut_options
   );
   animateDonut(default_languages);

   var web_engines = new Chartist.Pie(
      '#web_engines',
      $('#web_engines').data("id"),
      donut_options
   );
   animateDonut(web_engines);

   var db_engines = new Chartist.Pie(
      '#db_engines',
      $('#db_engines').data("id"),
      donut_options
   );
   animateDonut(db_engines);


   // permits to expand chart cards
   $(".chart .expand").click(function() {
      var e_button = $(this);
      var chart    = e_button.parents(".chart")
      var chartist = chart.find(".ct-chart").get(0).__chartist__;

      // set fullscreen on chart
      if (!chart.hasClass('chart-max')) {
         chart
            .toggleClass("chart-max")
            .width($(window).width() * .95)
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

      // redraw chart
      $('.dashboard')
         .masonry()
         .one( 'layoutComplete', function() {
            chartist.update();

            // scroll to the chart
            $('html, body').animate({
               scrollTop: $(chart).offset().top - 30
            }, 200);
         });
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