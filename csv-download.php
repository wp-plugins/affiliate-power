<?php

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="'.date('Y-m-d').'-affiliate-provisionen.csv"');


if (!session_id()) session_start();
echo $_SESSION['affiliate-power-csv'];
//session_destroy();

?>