//
// Interactive multi-line graph
// Based on example here: http://www.d3noob.org/2014/07/d3js-multi-line-graph-with-automatic.html
//

var LineChart = function (type) {

	// Set the dimensions of the canvas / graph
	var margin = {top: 30, right: 20, bottom: 70, left: 50},
	    width = 1200 - margin.left - margin.right,
	    height = 400 - margin.top - margin.bottom;

	// Parse the date / time
	var parseDate = d3.time.format("%Y%m%d%H%M%S").parse;

	// Set the ranges
	var x = d3.time.scale().range([0, width]);
	var y = d3.scale.linear().range([height, 0]);

	// Define the axes
	var xAxis = d3.svg.axis().scale(x)
	    .orient("bottom").ticks(24).tickFormat(d3.time.format("%H:%M"));

	var yAxis = d3.svg.axis().scale(y)
	    .orient("left").ticks(5).tickSize(-width,0,0);

	// Define the line
	var priceline = d3.svg.line()
	    .x(function(d) { return x(d.time); })
	    .y(function(d) { return y(d.value); });

	// Adds the svg canvas
	var svg = d3.select("#"+type)
	    .append("svg")
		.attr("width", width + margin.left + margin.right)
		.attr("height", height + margin.top + margin.bottom)
	    .append("g")
		.attr("transform",
		      "translate(" + margin.left + "," + margin.top + ")");

	var dataSource = "";

	if(type == "temperature") dataSource = "../gettempdata.php";
	else if(type == "humidity") dataSource = "../gethumdata.php";

	// Get the data
	d3.csv(dataSource, function(error, data) {
	    data.forEach(function(d) {
		        d.time = parseDate(d.time);
		        d.value = +d.value;
	    });
	    // Scale the range of the data
	    x.domain(d3.extent(data, function(d) { return d.time; }));
	    y.domain([d3.min(data, function(d) {return d.value; }), d3.max(data, function(d) { return d.value; })]);

	    // Nest the entries by device
	    var dataNest = d3.nest()
		.key(function(d) {return d.device;})
		.entries(data);

	    var color = d3.scale.category10();   // set the colour scale

	    legendSpace = width/dataNest.length; // spacing for the legend

	    // Loop through each device / key
	    dataNest.forEach(function(d,i) {

		svg.append("path")
		    .attr("class", "line")
		    .style("stroke", function() { // Add the colours dynamically
		        return d.color = color(d.key); })
		    .style("stroke-dasharray", (function() { var retval = "1, 0"; if(type=="humidity") retval="8, 3"; return retval; }))	// dotted line, remove to solid line
		    .attr("id", 'tag'+d.key.replace(/\s+/g, '')) // assign ID
		    .attr("d", priceline(d.values));

		// Add the Legend
		svg.append("text")
		    .attr("x", (legendSpace/2)+i*legendSpace)  // space legend
		    .attr("y", height + (margin.bottom/2)+ 5)
		    .attr("class", "legend")    // style the legend
		    .style("fill", function() { // Add the colours dynamically
		        return d.color = color(d.key); })
		    .on("click", function(){
		        // Determine if current line is visible
		        var active   = d.active ? false : true,
		        newOpacity = active ? 0 : 1;
		        // Hide or show the elements based on the ID
		        d3.select("#tag"+d.key.replace(/\s+/g, ''))
		            .transition().duration(100)
		            .style("opacity", newOpacity);
		        // Update whether or not the elements are active
		        d.active = active;
		        })
		    .text(d.key);
	    });

	    // Add the X Axis
	    svg.append("g")
		.attr("class", "x axis")
		.attr("transform", "translate(0," + height + ")")
		.call(xAxis);

	    // Add the Y Axis
	    svg.append("g")
		.attr("class", "y axis")
		.call(yAxis);

	});
}
