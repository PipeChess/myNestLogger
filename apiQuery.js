var dateFormat = require('dateformat');
var mysql = require('mysql');
var request = require("request");

var options = { method: 'GET',
  url: 'https://developer-api.nest.com/',
  headers: { 
	'postman-token': '',
	'cache-control': 'no-cache',
     	'authorization': '',
     	'content-type': 'application/json' 
	}
};

var con = mysql.createConnection({
  host: "",
  user: "",
  password: "",
  database: ""
});


request(options, function (error, response, body) {
  	if (error) throw new Error(error);
	var data = JSON.parse(body);
	var prop = data.devices.thermostats.SFm1BGJTa4lTnjmsZtlBv_XAmw3wWUnc;

	var con = mysql.createConnection({
		host: "",
		user: "",
		password: "",
		database: ""
	});

	con.connect(function(err) {
  		if (err) throw err;

		console.log("Connected!");

		var now = new Date();

		var sql = "INSERT INTO nest_log (date_time, heating_status, current_temp_inside, current_temp_outside, set_temp, humidity) " + 
			"VALUES ('" + dateFormat(now,'yyyy-mm-dd HH:MM:ss') + "', '"+prop.hvac_state+"', " + 
			"'"+prop.ambient_temperature_c+"', '-100', '" + prop.target_temperature_c +"', '"+prop.humidity+"')";

		con.query(sql, function (err, result) {
	    		if (err) throw err;
	    		con.end();
		});
	});
});




