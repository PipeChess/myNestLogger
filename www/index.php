<?php
	$displayLimit = 5;
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Lierzer Nest API</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js" integrity="sha384-vFJXuSJphROIrBnz7yo7oB41mKfc8JzQZiCq4NCceLEaO4IHwicKwpJf9c9IpFgh" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js" integrity="sha384-alpBpkh1PFOepccYVYDB4do5UnbKysX5WZXm3XxPqe5iKTfUKjNkCk9SaVuEZflJ" crossorigin="anonymous"></script>
		<script src="dable.min.js"></script>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
		<style type="text/css">
			body {
				background-color: #6BD3ED;
				font-family: "Arial";
			}
		</style>
	</head>

	<body>
		<div class="container">
			<div class="row">
				<div class="col-lg-2">
					<img src="nestLogo_off.png" class="logoIndicator" style="height: 150px;" />
				</div>
				<div class="col-lg-10">
					<div class="float-right">
						<b>Current Temperature:</b> <span class="tempSensor">Temp</span>
					</div>
					<br />
					<div class="float-right">
						<b>Current Humidity:</b> <span class="humiditySensor">Humidity</span>
					</div>
				</div>
			</div>
			<div class="row" id="TableNest">
				<table class="table table-striped table-dark">
					<thead class="thead-dark">
						<tr>
							<th>Last <?=$displayLimit?> Days</th>
							<th>Hours of Heating</th>
						</tr>
					</thead>
				<?php
				try {
					$db = new PDO("mysql:host=; dbname=", "nest_log", "");
					$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

					$stmt = $db->query("SELECT * FROM refill_log ORDER BY date DESC LIMIT 1");
					$result = $stmt->fetchAll();
					$refillInfo = ["date" => $result[0]["date"], "gallons" => $result[0]["gallons"], "multiplier" => $result[0]["multiplier"]];

					$stmt = $db->query("SELECT * FROM daily_hours WHERE date_time >='".$refillInfo["date"]."' ORDER BY date_time DESC");
					$result = $stmt->fetchAll();
					$total = 0;
					$rowCount = 1;
					foreach($result as $row) {
						if($rowCount <= $displayLimit) {
						?>
							<tr>
								<td><?=date("m/d/Y", strtotime($row["date_time"]))?></td>
								<td><?=$row["hours"]?>h</td>
							</tr>
						<?php
						}
						$rowCount++;
						$total += $row["hours"];
					}
				} catch(PDOException $ex) {
					echo "Error: " . $ex->getMessage();
				}

				$gallonsLastRefill = $refillInfo["gallons"];
				$burnerMultiplier = $refillInfo["multiplier"];
				$gallonsUsed = ($total * $burnerMultiplier);
				$gallonsLeft = $gallonsLastRefill - $gallonsUsed;

				$daysSinceLastRefill = $rowCount ?:1;
				$averagePerDay = ($gallonsUsed / $daysSinceLastRefill) ?: 1;
				$daysLeft = $gallonsLeft / $averagePerDay;
				$dateRunEmpty = date("m/d/Y", (time() + $daysLeft * 86400));
				$dateSafe = date("m/d/Y", (time() + ($daysLeft * 86400 * 0.9)));
				?>
				<tfoot>
					<tr>
						<td colspan='2'></td>
					</tr>
					<tr>
						<th>Hours since refill</th>
						<th><?=$total?> hours</th>
					</tr>
					<tr>
						<th>Gallons used since refill</th>
						<th>~<?=$gallonsUsed?> gallons</th>
					</tr>
					<tr>
						<th>Fuel left</th>
						<th>~<?= $gallonsLeft ?> gallons</th>
					</tr>
					<tr>
						<th>Average/Day</th>
						<th>~<?= round($averagePerDay,1) ?> gallons</th>
					</tr>
					<tr>
						<th>Refill by</th>
						<th><?= $dateSafe ?> (empty by <?= ($dateRunEmpty)?>) </th>
					</tr>
				</tfoot>
				</table>
			</div>
		</div>
	</body>
	<script type="text/javascript">
		$(function() {
			getStatus();
			window.setInterval(getStatus, 60000);
		});

		function getStatus () {
			$.ajax({
				url: "getCurrentStatus.php",
				dataType: "json",
				success: function (data) {
					if(data.success) {
						if(data.sensors.heating_status == "heating") {
							$(".logoIndicator").prop("src", "nestLogo_on.png");
						}else{
							$(".logoIndicator").prop("src", "nestLogo_off.png");
						}
						$(".tempSensor").html(data.sensors.current_temp_inside + "C | " + (Math.round((data.sensors.current_temp_inside*1.8 + 32)*100)/100) + "F");
						$(".humiditySensor").html(data.sensors.humidity);
					}else{
						$(".logoIndicator").prop("src", "nestLogo_off.png");
						console.log("Error with sensor request");
					}
				}
			});
		}
	</script>
</html>

