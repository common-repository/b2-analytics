export function drawMap(geodata) {

    console.log("Drawing map...")

    var svg = d3.select("#d3Div"),
        width = 800,
        height = 600;

    // Map and projection
    var path = d3.geoPath();
    var projection = d3.geoMercator()
        .scale(85)
        .center([70, -30])
        .translate([width / 2, height / 2]);

    // Data and color scale
    var data = d3.map();
    var geoJson = JSON.parse(geodata);
    geoJson.forEach((item) => {
        data.set(item.alpha3, (item.domainCssAdBlockCount + item.domainAdBlockCount + item.cssAdBlockCount));
    });

    var maxValue = Math.max.apply(Math, geoJson.map(function (o) { return o.domainCssAdBlockCount + o.domainAdBlockCount + o.cssAdBlockCount; }))
    var scaleThreshold = maxValue / 5;

    var colorScale = d3.scaleThreshold()
        .domain([0, scaleThreshold * 1, scaleThreshold * 2, scaleThreshold * 3, scaleThreshold*4])
        .range(['#f5c7c4', '#f1b4b1', '#eea29e', '#eb8f8a', '#e46a63', '#DE453D']);

    // Load external data and boot
    d3.queue()
        .defer(d3.json, jsonLocation)
        .await(ready);

    function ready(error, topo) {

        // Draw the map
        svg.append("g")
            .selectAll("path")
            .data(topo.features)
            .enter()
            .append("path")
            // draw each country
            .attr("d", d3.geoPath()
                .projection(projection)
            )
            // set the color of each country
            .attr("fill", function (d) {

                if (data.get(d.id) == null) {
                    return '#d3d3d3';
                }

                d.total = data.get(d.id) || 0;
                return colorScale(d.total);
            })
            .on('mouseover', function (d, i) {
                //console.log(d);
            })
            .on('mouseout', function (d, i) {
               
            });
    }
}