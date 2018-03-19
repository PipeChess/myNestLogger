var dateFormat = require('dateformat');
var mysql = require('mysql');
var request = require("request");

var con = mysql.createConnection({
  host: "",
  user: "",
  password: "",
  database: ""
});

function drawOutput(count, date, con) {
	var hours = Math.round(count/3600 *10) / 10;

	var dateInsert = date.getFullYear() + "-" + (date.getMonth()+1) + "-" + date.getDate();

	con.query("INSERT INTO daily_hours (hours, date_time) VALUES ('"+hours+"', '" + dateInsert + "')", function(err, result) {
		if(err) throw err;
		console.log("On the "+ (date.getMonth() + 1) + "/"+ date.getDate() + "/" + date.getFullYear() + " the heat was on for " + hours  + " hours");
	});
}

con.connect(function(err) {
	if (err) throw err;

	var todayDateTime = new Date();
	todayDateTime.setDate(todayDateTime.getDate()-1);
	var min = todayDateTime.getFullYear() + "-" + (todayDateTime.getMonth()+1) + "-" + todayDateTime.getDate() + " 00:00:00";
	var max = todayDateTime.getFullYear() + "-" + (todayDateTime.getMonth()+1) + "-" + todayDateTime.getDate() + " 23:59:59";
	var heatCountInSeconds = 0;
	var countOn = false;
        var heatOnAt = null;
	var outputDay = null;
	var outputDate = null;

	con.query("SELECT * FROM nest_log WHERE date_time BETWEEN '"+min+"' AND '"+max+"' ORDER BY date_time ASC", function (err, result, fields) {
    		if (err) throw err;

		for(var i = 0; i < result.length; i++) {
			var currentDate = new Date(result[i].date_time);
                        var currentDay = currentDate.getDate();

			//Heat just switched on
			if(result[i].heating_status == "heating" && countOn === false) {
				countOn = true;
				var dateTime = new Date(result[i].date_time);
				heatOnAt = dateTime.getTime()/1000;
				//console.log("heat on, now counting");
			}

			//Heat just switched off
			if(result[i].heating_status == "off" && countOn === true) {
				if(currentDay != outputDay) {
					if(outputDay !== null) {
						drawOutput(heatCountInSeconds, outputDate, con);
						heatCountInSeconds = 0;
					}
					outputDay = currentDay;
					outputDate = currentDate;
				}

				countOn = false;
				var dateTime = new Date(result[i].date_time);
				var heatOffAt = dateTime.getTime()/1000;
				heatCountInSeconds += heatOffAt - heatOnAt;
			}
		}
		drawOutput(heatCountInSeconds, currentDate, con);
		con.end();
	});
});





