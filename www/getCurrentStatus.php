<?php

 try {
        $db = new PDO("mysql:host=; dbname=HeatingStats", "", "");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $stmt = $db->query("SELECT heating_status,current_temp_inside, set_temp, humidity FROM nest_log ORDER BY log_id DESC LIMIT 1");
        $result = $stmt->fetchAll();

	echo json_encode(["success" => true, "sensors" => $result[0]]);
} catch(PDOException $ex) {
        echo json_encode(["success" => false, "error" => $ex->getMessage()]);
}

