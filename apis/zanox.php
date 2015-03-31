<?php
if (!defined('ABSPATH')) die; //no direct access

class Affiliate_Power_Api_Zanox {


	static public function checkLogin($connect_id, $public_key, $secret_key) {
		if (!class_exists('ApiClientException')) include_once ("zanox-api/ApiClient.php");
		
		try {
			$zx = ZanoxApiClient::factory(PROTOCOL_SOAP); //Class renamed to avoid collisions
			$zx->setConnectId($connect_id);
			$zx->setSecretKey($secret_key);
			$zx->setPublicKey($public_key);
			
			$filter_date = date("Y-m-d");
			$zx->GetLeads($filter_date, 'tracking_date', NULL, NULL, NULL, 0, 50);
		}
		catch (Exception $e) {
			return false;
		}
		return true;
	}
	
	
	static public function downloadTransactions($connect_id, $public_key, $secret_key, $filter_adspace, $fromTS, $tillTS) {
		if (!class_exists('ApiClientException')) include_once ("zanox-api/ApiClient.php");
		
		$zx = ZanoxApiClient::factory(PROTOCOL_SOAP); //Class renamed to avoid collisions
		$zx->setConnectId($connect_id);
		$zx->setSecretKey($secret_key);
		$zx->setPublicKey($public_key);
		
		$arr_filter_adspace = explode(',', $filter_adspace);
		$arr_filter_adspace = array_map('trim', $arr_filter_adspace);
		
		$filter_date_stamp = $tillTS;
		$filter_date = date("Y-m-d", $filter_date_stamp);
		
		$output_transactions = array();
		
		while($filter_date_stamp > $fromTS) 
		{
			//Leads
			$page=-1;
			do
			{
				$page++;
				try { $result = $zx->GetLeads($filter_date, 'tracking_date', NULL, NULL, NULL, $page, 50); }
				catch (Exception $e) { return $output_transactions; }
				//print_r($result); //vorrübergehend zu Testzwecken

				if ($result->items > 0)
				{
					for ($i=0;$i<count($result->leadItems->leadItem);$i++)
					{
						$adspace = $result->leadItems->leadItem[$i]->adspace->_;
						if ( $filter_adspace != '' && !in_array($adspace, $arr_filter_adspace) ) continue;
						
						$date = $result->leadItems->leadItem[$i]->trackingDate;
						$number = $result->leadItems->leadItem[$i]->id;
						$sub_id_old = isset($result->leadItems->leadItem[$i]->subPublisher->id) ? $result->leadItems->leadItem[$i]->subPublisher->id : 0;
						$gpps = isset($result->leadItems->leadItem[$i]->gpps->gpp) ? $result->leadItems->leadItem[$i]->gpps->gpp : 0;
						$shop_id = $result->leadItems->leadItem[$i]->program->id;
						$shop_name = $result->leadItems->leadItem[$i]->program->_;
						$commission = $result->leadItems->leadItem[$i]->commission;
						$status = $result->leadItems->leadItem[$i]->reviewState;
						$checkdate = $result->leadItems->leadItem[$i]->modifiedDate;
						
						$sub_id = 0;
						if ($gpps) {
							foreach ($gpps as $gpp) {
								if ($gpp->id == 'zpar4') {
									$sub_id = $gpp->_;
									break;
								}
							}
						}
						if ($sub_id == 0) $sub_id = $sub_id_old;

						if ($status == "confirmed") $confirmed = $commission;
						else $confirmed = 0;
						if ($status == "approved") $status = "open"; //keine Unterscheidung zwischen open und approved(=Partner hat Transkation bestätigt, aber noch kein Geld an Zanox überwiesen)
						if ($status == "rejected" || $status == "refused") $status = "cancelled"; //einheitliche Bezeichnungen

						$arr_date = explode("T", $date);
						$arr_date[1] = substr($arr_date[1], 0, 8); //ms und +2 abschneiden
						$datetime_db = implode(" ", $arr_date);

						$arr_checkdate = explode("T", $checkdate);
						$arr_checkdate[1] = substr($arr_checkdate[1], 0, 8);
						$checkdatetime_db = implode(" ", $arr_checkdate);

						$status = ucfirst(strtolower($status));
						
						$output_transactions[] = array(
						'network' => 'zanox', 
						'number' => $number,
						'datetime_db' => $datetime_db,
						'sub_id' => $sub_id,
						'shop_id' => $shop_id,
						'shop_name' => $shop_name,
						'transaction_type' => 'L',
						'price' => 0,
						'commission' => $commission,
						'confirmed' => $confirmed,
						'checkdatetime_db' => $checkdatetime_db,
						'status' => $status
						);
						
					}
				}
			}
			while (($page+1)*50 < $result->total);


			//Sales
			$page=-1;
			do
			{
				$page++;
				try { $result = $zx->GetSales($filter_date, 'tracking_date', NULL, NULL, NULL, $page, 50); }
				catch (Exception $e) { return $output_transactions; }
				//print_r($result); //vorrübergehend zu Testzwecken

				if ($result->items > 0)
				{
					for ($i=0;$i<count($result->saleItems->saleItem);$i++)
					{
						$adspace = $result->saleItems->saleItem[$i]->adspace->_;
						if ( $filter_adspace != '' && !in_array($adspace, $arr_filter_adspace) ) continue;
						
						$date = $result->saleItems->saleItem[$i]->trackingDate;
						$number = $result->saleItems->saleItem[$i]->id;
						$sub_id_old = isset($result->saleItems->saleItem[$i]->subPublisher->id) ? $result->saleItems->saleItem[$i]->subPublisher->id : 0;
						$gpps = isset($result->saleItems->saleItem[$i]->gpps->gpp) ? $result->saleItems->saleItem[$i]->gpps->gpp : 0;
						$shop_id = $result->saleItems->saleItem[$i]->program->id;
						$shop_name = $result->saleItems->saleItem[$i]->program->_;
						$price = $result->saleItems->saleItem[$i]->amount;
						$commission = $result->saleItems->saleItem[$i]->commission;
						$status = $result->saleItems->saleItem[$i]->reviewState;
						$checkdate = $result->saleItems->saleItem[$i]->modifiedDate;
						
						$sub_id = 0;
						if ($gpps) {
							foreach ($gpps as $gpp) {
								if ($gpp->id == 'zpar4') {
									$sub_id = $gpp->_;
									break;
								}
							}
						}
						if ($sub_id == 0) $sub_id = $sub_id_old;

						if ($status == "confirmed") $confirmed = $commission;
						else $confirmed = 0;
						if ($status == "approved") $status = "open"; //keine Unterscheidung zwischen open und approved(=Partner hat Transkation bestätigt, aber noch kein Geld an Zanox überwiesen)
						if ($status == "rejected" || $status == "refused") $status = "cancelled"; //einheitliche Bezeichnungen

						$arr_date = explode("T", $date);
						$arr_date[1] = substr($arr_date[1], 0, 8); //ms und +2 abschneiden
						$datetime_db = implode(" ", $arr_date);

						$arr_checkdate = explode("T", $checkdate);
						$arr_checkdate[1] = substr($arr_checkdate[1], 0, 8);
						$checkdatetime_db = implode(" ", $arr_checkdate);

						$status = ucfirst(strtolower($status));
						

						$output_transactions[] = array(
						'network' => 'zanox', 
						'number' => $number,
						'datetime_db' => $datetime_db,
						'sub_id' => $sub_id,
						'shop_id' => $shop_id,
						'shop_name' => $shop_name,
						'transaction_type' => 'S',
						'price' => $price,
						'commission' => $commission,
						'confirmed' => $confirmed,
						'checkdatetime_db' => $checkdatetime_db,
						'status' => $status
						);
						
					} //for ($i=0;$i<count($result->saleItems->saleItem);$i++)
				} //if ($result->items > 0)
			} //do
			while (($page+1)*50 < $result->total);
			
			$filter_date_stamp -= 60*60*24;
			$arr_filter_date = getdate($filter_date_stamp);
			if ($arr_filter_date['mon'] < 10) $arr_filter_date['mon'] = "0".$arr_filter_date['mon'];
			if ($arr_filter_date['mday'] < 10) $arr_filter_date['mday'] = "0".$arr_filter_date['mday'];
			$filter_date = $arr_filter_date['year'].'-'.$arr_filter_date['mon'].'-'.$arr_filter_date['mday'];
			//echo $select_filter['date']."<br>";
			
		} //while($filter_date_stamp > $end_stamp) 
		
		return $output_transactions;
	}
	
	


}


?>