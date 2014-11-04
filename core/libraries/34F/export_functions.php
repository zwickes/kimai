<?php

// Determine if the expenses extension is used.
$expense_ext_available = false;
if (file_exists('../ki_expenses/private_db_layer_' . $kga['server_conn'] . '.php')) {
	include ('../ki_expenses/private_db_layer_' . $kga['server_conn'] . '.php');
	$expense_ext_available = true;
}
//include ('private_db_layer_' . $kga['server_conn'] . '.php');


function sortProjectDataByProjectName($a, $b) {
  if ($a[1] == $b[1]) {
    return 0;
  }
  return strcmp($a[1], $b[1]);
}



/**
 * Get a combined array with time recordings and expenses to export.
 *
 * @param int $start Time from which to take entries into account.
 * @param int $end Time until which to take entries into account.
 * @param array $users Array of user IDs to filter by.
 * @param array $customers Array of customer IDs to filter by.
 * @param array $projects Array of project IDs to filter by.
 * @param array $activities Array of activity IDs to filter by.
 * @param bool $limit sbould the amount of entries be limited
 * @param bool $reverse_order should the entries be put out in reverse order
 * @param string $default_location use this string if no location is set for the entry
 * @param int $filter_cleared (-1: show all, 0:only cleared 1: only not cleared) entries
 * @param int $filter_type (-1 show time and expenses, 0: only show time entries, 1: only show expenses)
 * @param int $limitCommentSize should comments be cut off, when they are too long
 * @return array with time recordings and expenses chronologically sorted
 */
function export_get_data_34F($start, $end, $users = null, $customers = null, $projects = null, $activities = null, $limit = false, $reverse_order = false, $default_location = '', $filter_cleared = -1, $filter_type = -1, $limitCommentSize = true, $filter_refundable = -1) {
	global $expense_ext_available, $database;
	$timeSheetEntries = array();
	$expenses = array();
	
        if ($filter_type != 1)
          $timeSheetEntries = $database->get_timeSheet($start, $end, $users, $customers, $projects, $activities, $limit, $reverse_order, $filter_cleared);
		
	if ($filter_type != 0 && $expense_ext_available)
          $expenses = get_expenses($start, $end, $users, $customers, $projects, $limit, $reverse_order, $filter_refundable, $filter_cleared);
        
	$result_arr = array();
	$timeSheetEntries_index = 0;
	$expenses_index = 0;
        $keys = array('type', 'id', 'time_in', 'time_out', 'duration', 'formattedDuration', 'decimalDuration', 'rate',
                      'wage', 'wage_decimal', 'budget', 'approved', 'statusID', 'status', 'billable', 'customerID', 'customerName', 'projectID',
                      'projectName', 'description', 'projectComment', 'activityID', 'activityName', 'comment', 'commentType',
                      'location', 'trackingNumber', 'username', 'cleared');
        
	while ($timeSheetEntries_index < count($timeSheetEntries) && $expenses_index < count($expenses)) {
          $arr = array();
          foreach ($keys as $key)
            $arr[$key] = null;
            $arr['location'] = $default_location;
            if ((! $reverse_order && ($timeSheetEntries[$timeSheetEntries_index]['start'] > $expenses[$expenses_index]['timestamp'])) || ($reverse_order && ($timeSheetEntries[$timeSheetEntries_index]['start'] < $expenses[$expenses_index]['timestamp']))) 
            {
			if ($timeSheetEntries[$timeSheetEntries_index]['end'] != 0) {
				// active recordings will be omitted
				$arr['type'] = 'timeSheet';
				$arr['id'] = $timeSheetEntries[$timeSheetEntries_index]['timeEntryID'];
				$arr['time_in'] = $timeSheetEntries[$timeSheetEntries_index]['start'];
				$arr['time_out'] = $timeSheetEntries[$timeSheetEntries_index]['end'];
				$arr['duration'] = $timeSheetEntries[$timeSheetEntries_index]['duration'];
				$arr['formattedDuration'] = $timeSheetEntries[$timeSheetEntries_index]['formattedDuration'];
				$arr['decimalDuration'] = sprintf("%01.2f", $timeSheetEntries[$timeSheetEntries_index]['duration'] / 3600);
				$arr['rate'] = $timeSheetEntries[$timeSheetEntries_index]['rate']; //
				$arr['wage'] = $timeSheetEntries[$timeSheetEntries_index]['wage'];
				$arr['wage_decimal'] = $timeSheetEntries[$timeSheetEntries_index]['wage_decimal'];
				$arr['budget'] = $timeSheetEntries[$timeSheetEntries_index]['budget'];
				$arr['approved'] = $timeSheetEntries[$timeSheetEntries_index]['approved'];
				$arr['statusID'] = $timeSheetEntries[$timeSheetEntries_index]['statusID'];
                                $arr['status'] = $timeSheetEntries[$timeSheetEntries_index]['status'];
				$arr['billable'] = $timeSheetEntries[$timeSheetEntries_index]['billable'];
				$arr['customerID'] = $timeSheetEntries[$timeSheetEntries_index]['customerID'];
				$arr['customerName'] = $timeSheetEntries[$timeSheetEntries_index]['customerName'];
				$arr['projectID'] = $timeSheetEntries[$timeSheetEntries_index]['projectID'];
				$arr['project_number'] = $timeSheetEntries[$timeSheetEntries_index]['project_number'];
				$arr['fee_model'] = $timeSheetEntries[$timeSheetEntries_index]['fee_model'];
				$arr['projectName'] = $timeSheetEntries[$timeSheetEntries_index]['projectName'];
				$arr['description'] = $timeSheetEntries[$timeSheetEntries_index]['description'];
				$arr['projectComment'] = $timeSheetEntries[$timeSheetEntries_index]['projectComment'];
				$arr['activityID'] = $timeSheetEntries[$timeSheetEntries_index]['activityID'];
				$arr['activityName'] = $timeSheetEntries[$timeSheetEntries_index]['activityName'];
				if ($limitCommentSize)
					$arr['comment'] = Format::addEllipsis($timeSheetEntries[$timeSheetEntries_index]['comment'], 150);
				else
					$arr['comment'] = $timeSheetEntries[$timeSheetEntries_index]['comment'];
				$arr['commentType'] = $timeSheetEntries[$timeSheetEntries_index]['commentType'];
				$arr['location'] = $timeSheetEntries[$timeSheetEntries_index]['location'];
				$arr['trackingNumber'] = $timeSheetEntries[$timeSheetEntries_index]['trackingNumber'];
				$arr['username'] = $timeSheetEntries[$timeSheetEntries_index]['userName'];
				$arr['cleared'] = $timeSheetEntries[$timeSheetEntries_index]['cleared'];
        $result_arr[] = $arr;
			}
			$timeSheetEntries_index++;
		}
		else {
			$arr['type'] = 'expense';
			$arr['id'] = $expenses[$expenses_index]['expenseID'];
			$arr['time_in'] = $expenses[$expenses_index]['timestamp'];
			$arr['time_out'] = $expenses[$expenses_index]['timestamp'];
			$arr['wage'] = sprintf("%01.2f", $expenses[$expenses_index]['value'] * $expenses[$expenses_index]['multiplier']);
			$arr['pst_part'] = sprintf("%01.2f", $expenses[$expenses_index]['pst_part'] * $expenses[$expenses_index]['multiplier']);
			$arr['gst_part'] = sprintf("%01.2f", $expenses[$expenses_index]['gst_part'] * $expenses[$expenses_index]['multiplier']);
			$arr['customerID'] = $expenses[$expenses_index]['customerID'];
			$arr['customerName'] = $expenses[$expenses_index]['customerName'];
			$arr['projectID'] = $expenses[$expenses_index]['projectID'];
			$arr['project_number'] = $expenses[$expenses_index]['project_number'];
			$arr['fee_model'] = $expenses[$expenses_index]['fee_model'];
			$arr['projectName'] = $expenses[$expenses_index]['projectName'];
                        $arr['description'] = $expenses[$expenses_index]['designation'];
                        $arr['projectComment'] = $expenses[$expenses_index]['projectComment'];
			if ($limitCommentSize)
				$arr['comment'] = Format::addEllipsis($expenses[$expenses_index]['comment'], 150);
			else
				$arr['comment'] = $expenses[$expenses_index]['comment'];
			$arr['activityName'] = $expenses[$expenses_index]['designation'];
			$arr['comment'] = $expenses[$expenses_index]['comment'];
			$arr['commentType'] = $expenses[$expenses_index]['commentType'];
			$arr['username'] = $expenses[$expenses_index]['userName'];
			$arr['cleared'] = $expenses[$expenses_index]['cleared'];
      $result_arr[] = $arr;
			$expenses_index++;
		}
	}
	while ($timeSheetEntries_index < count($timeSheetEntries)) {
		if ($timeSheetEntries[$timeSheetEntries_index]['end'] != 0) {
			// active recordings will be omitted
			$arr = array();
      foreach ($keys as $key)
        $arr[$key] = null;
      $arr['location'] = $default_location;

			$arr['type'] = 'timeSheet';
			$arr['id'] = $timeSheetEntries[$timeSheetEntries_index]['timeEntryID'];
			$arr['time_in'] = $timeSheetEntries[$timeSheetEntries_index]['start'];
			$arr['time_out'] = $timeSheetEntries[$timeSheetEntries_index]['end'];
			$arr['duration'] = $timeSheetEntries[$timeSheetEntries_index]['duration'];
			$arr['formattedDuration'] = $timeSheetEntries[$timeSheetEntries_index]['formattedDuration'];
			$arr['decimalDuration'] = sprintf("%01.2f", $timeSheetEntries[$timeSheetEntries_index]['duration'] / 3600);
			$arr['rate'] = $timeSheetEntries[$timeSheetEntries_index]['rate'];
			$arr['wage'] = $timeSheetEntries[$timeSheetEntries_index]['wage'];
			$arr['wage_decimal'] = $timeSheetEntries[$timeSheetEntries_index]['wage_decimal'];
			$arr['budget'] = $timeSheetEntries[$timeSheetEntries_index]['budget'];
			$arr['approved'] = $timeSheetEntries[$timeSheetEntries_index]['approved'];
			$arr['statusID'] = $timeSheetEntries[$timeSheetEntries_index]['statusID'];
                        $arr['status'] = $timeSheetEntries[$timeSheetEntries_index]['status'];
			$arr['billable'] = $timeSheetEntries[$timeSheetEntries_index]['billable'];
			$arr['customerID'] = $timeSheetEntries[$timeSheetEntries_index]['customerID'];
			$arr['customerName'] = $timeSheetEntries[$timeSheetEntries_index]['customerName'];
			$arr['projectID'] = $timeSheetEntries[$timeSheetEntries_index]['projectID'];
			$arr['project_number'] = $timeSheetEntries[$timeSheetEntries_index]['project_number'];
			$arr['fee_model'] = $timeSheetEntries[$timeSheetEntries_index]['fee_model'];
			$arr['projectName'] = $timeSheetEntries[$timeSheetEntries_index]['projectName'];
			$arr['projectComment'] = $timeSheetEntries[$timeSheetEntries_index]['projectComment'];
			$arr['activityID'] = $timeSheetEntries[$timeSheetEntries_index]['activityID'];
			$arr['activityName'] = $timeSheetEntries[$timeSheetEntries_index]['activityName'];
			$arr['description'] = $timeSheetEntries[$timeSheetEntries_index]['description'];
			if ($limitCommentSize)
				$arr['comment'] = Format::addEllipsis($timeSheetEntries[$timeSheetEntries_index]['comment'], 150);
			else
				$arr['comment'] = $timeSheetEntries[$timeSheetEntries_index]['comment'];
			$arr['commentType'] = $timeSheetEntries[$timeSheetEntries_index]['commentType'];
			$arr['location'] = $timeSheetEntries[$timeSheetEntries_index]['location'];
			$arr['trackingNumber'] = $timeSheetEntries[$timeSheetEntries_index]['trackingNumber'];
			$arr['username'] = $timeSheetEntries[$timeSheetEntries_index]['userName'];
			$arr['cleared'] = $timeSheetEntries[$timeSheetEntries_index]['cleared'];
			$result_arr[] = $arr;
		}
		$timeSheetEntries_index++;
	}
	while ($expenses_index < count($expenses)) {
		$arr = array();
    foreach ($keys as $key)
      $arr[$key] = null;
    $arr['location'] = $default_location;

		$arr['type'] = 'expense';
		$arr['id'] = $expenses[$expenses_index]['expenseID'];
		$arr['time_in'] = $expenses[$expenses_index]['timestamp'];
		$arr['time_out'] = $expenses[$expenses_index]['timestamp'];
		$arr['wage'] = sprintf("%01.2f", $expenses[$expenses_index]['value'] * $expenses[$expenses_index]['multiplier']);
		$arr['pst_part'] = sprintf("%01.2f", $expenses[$expenses_index]['pst_part'] * $expenses[$expenses_index]['multiplier']);
		$arr['gst_part'] = sprintf("%01.2f", $expenses[$expenses_index]['gst_part'] * $expenses[$expenses_index]['multiplier']);
		$arr['customerID'] = $expenses[$expenses_index]['customerID'];
		$arr['customerName'] = $expenses[$expenses_index]['customerName'];
		$arr['projectID'] = $expenses[$expenses_index]['projectID'];
		$arr['project_number'] = $expenses[$expenses_index]['project_number'];
		$arr['fee_model'] = $expenses[$expenses_index]['fee_model'];
		$arr['projectName'] = $expenses[$expenses_index]['projectName'];
                $arr['description'] = $expenses[$expenses_index]['designation'];
                $arr['projectComment'] = $expenses[$expenses_index]['projectComment'];
		if ($limitCommentSize)
			$arr['comment'] = Format::addEllipsis($expenses[$expenses_index]['comment'], 150);
		else
			$arr['comment'] = $expenses[$expenses_index]['comment'];
		$arr['commentType'] = $expenses[$expenses_index]['commentType'];
		$arr['username'] = $expenses[$expenses_index]['userName'];
		$arr['cleared'] = $expenses[$expenses_index]['cleared'];
		$expenses_index++;
		$result_arr[] = $arr;
	}
        
        
//        for ($i=0;$i<count($result_arr);$i++) {
//          $result_arr[$i]['decimalDuration'] = str_replace(".",$_REQUEST['decimal_separator'],$result_arr[$i]['decimalDuration']);
//          $result_arr[$i]['rate'] = str_replace(".",$_REQUEST['decimal_separator'],$result_arr[$i]['rate']);
//          $result_arr[$i]['wage'] = str_replace(".",$_REQUEST['decimal_separator'],$result_arr[$i]['wage']);
//        }

	return $result_arr;
}
