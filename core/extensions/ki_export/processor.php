<?php
/**
 * This file is part of
 * Kimai - Open Source Time Tracking // http://www.kimai.org
 * (c) 2006-2009 Kimai-Development-Team
 *
 * Kimai is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; Version 3, 29 June 2007
 *
 * Kimai is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Kimai; If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Export Processor.
 */

// insert KSPI
$isCoreProcessor = 0;
$dir_templates = "templates/";
require("../../includes/kspi.php");

require("private_func.php");

require('../../libraries/34F/export_functions.php');


// ============================
// = parse general parameters =
// ============================

if ($axAction == 'export_csv'  ||
    $axAction == 'export_pdf'  ||
    $axAction == 'export_pdf2' ||
    $axAction == 'export_html' ||
    $axAction == 'export_xls'  ||
    $axAction == 'reload') {

  if (isset($_REQUEST['axColumns'])) {
    $axColumns = explode('|',$_REQUEST['axColumns']);
    $columns = array();
    foreach ($axColumns as $column)
      $columns[$column] = true;
  }

  $timeformat = strip_tags($_REQUEST['timeformat']);
  $timeformat = preg_replace('/([A-Za-z])/','%$1',$timeformat);

  $dateformat = strip_tags($_REQUEST['dateformat']);
  $dateformat = preg_replace('/([A-Za-z])/','%$1',$dateformat);

  $default_location = strip_tags($_REQUEST['default_location']);

  $reverse_order = isset($_REQUEST['reverse_order']);
  
  $filter_cleared     = $_REQUEST['filter_cleared'];
  $filter_refundable  = $_REQUEST['filter_refundable'];
  $filter_type        = $_REQUEST['filter_type'];
  
  $filters = explode('|',$axValue);

  if ($filters[0] == "")
    $filterUsers = array();
  else
    $filterUsers = explode(':',$filters[0]);

  if ($filters[1] == "")
    $filterCustomers = array();
  else
    $filterCustomers = explode(':',$filters[1]);

  if ($filters[2] == "")
    $filterProjects = array();
  else
    $filterProjects = explode(':',$filters[2]);

  if ($filters[3] == "")
    $filterActivities = array();
  else
    $filterActivities = explode(':',$filters[3]);

  // if no userfilter is set, set it to current user
  if (isset($kga['user']) && count($filterUsers) == 0)
    array_push($filterUsers,$kga['user']['userID']);
    
  if (isset($kga['customer']))
    $filterCustomers = array($kga['customer']['customerID']);
}





// ==================
// = handle request =
// ==================
switch ($axAction) {   
    

    // ======================
    // = set status cleared =
    // ======================
    case 'set_cleared':
      if (isset($kga['customer'])) {
        echo 0;
        break;
      }
      // $axValue: 1 = cleared, 0 = not cleared
      $id = isset($_REQUEST['id']) ? strip_tags($_REQUEST['id']) : null;
      $success = false;

      if (strncmp($id,"timeSheet",9) == 0)
        $success = export_timeSheetEntry_set_cleared(substr($id,9),$axValue==1);
      else if (strncmp($id,"expense",7) == 0)
        $success = export_expense_set_cleared(substr($id,7),$axValue==1);

      echo $success?1:0;
    break;
    

    // =========================
    // = save selected columns =
    // =========================
    case 'toggle_header':
      // $axValue: header name
      $success = export_toggle_header($axValue);
      echo $success?1:0;
    break;

    // ===========================
    // = Load data and return it =
    // ===========================
    case 'reload':
        $view->exportData = export_get_data($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);

        $view->total = Format::formatDuration($database->get_duration($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,$filter_cleared));

        $ann = export_get_user_annotations($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities);
        Format::formatAnnotations($ann);
        $view->user_annotations = $ann;
        
        $ann = export_get_customer_annotations($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities);
        Format::formatAnnotations($ann);
        $view->customer_annotations = $ann;

        $ann = export_get_project_annotations($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities);
        Format::formatAnnotations($ann);
        $view->project_annotations = $ann;

        $ann = export_get_activity_annotations($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities);
        Format::formatAnnotations($ann);
        $view->activity_annotations = $ann;

        $view->timeformat = $timeformat;
        $view->dateformat = $dateformat;
        if (isset($kga['user']))
          $view->disabled_columns = export_get_disabled_headers($kga['user']['userID']);
        echo $view->render("table.php");
    break;


    /**
     * Exort as html file.
     */
    case 'export_html':   

      echo json_encode(array('errors'=>'Function not available - use Export XLS'));
      return;

        $database->user_set_preferences(array(
          'print_summary' => isset($_REQUEST['print_summary'])?1:0,
          'reverse_order' => isset($_REQUEST['reverse_order'])?1:0),
          'ki_export.print.');
          
       
        $exportData = export_get_data($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);
        $timeSum = 0;
        $wageSum = 0;
        $budgetSum = 0;
        $approvedSum = 0;
        foreach ($exportData as $data) {
          $timeSum += $data['decimalDuration'];
          $wageSum += $data['wage'];
          $budgetSum += $data['budget'];
          $approvedSum += $data['approved'];
        }
        
        $view->timespan = strftime($kga['date_format']['2'],$in).' - '.strftime($kga['date_format']['2'],$out) ;

        if (isset($_REQUEST['print_summary'])) {
          //Create the summary. Same as in PDF export
          $timeSheetSummary = array();
          $expenseSummary = array();
          foreach ($exportData as $one_entry) {

            if ($one_entry['type'] == 'timeSheet') {
              if (isset($timeSheetSummary[$one_entry['activityID']])) {
                $timeSheetSummary[$one_entry['activityID']]['time']   += $one_entry['decimalDuration']; //Sekunden
                $timeSheetSummary[$one_entry['activityID']]['wage']   += $one_entry['wage']; //Currency
                $timeSheetSummary[$one_entry['activityID']]['budget'] += $one_entry['budget']; //Currency
                $timeSheetSummary[$one_entry['activityID']]['approved']+= $one_entry['approved']; //Currency
              }
              else {
                $timeSheetSummary[$one_entry['activityID']]['name']         = html_entity_decode($one_entry['activityName']);
                $timeSheetSummary[$one_entry['activityID']]['time']         = $one_entry['decimalDuration'];
                $timeSheetSummary[$one_entry['activityID']]['wage']         = $one_entry['wage'];
                $timeSheetSummary[$one_entry['activityID']]['budget'] 	  = $one_entry['budget']; 
                $timeSheetSummary[$one_entry['activityID']]['approved']	  = $one_entry['approved'];
              }
            }
            else {
              $expenseInfo['name']   = $kga['lang']['export_extension']['expense'].': '.$one_entry['activityName'];
              $expenseInfo['time']   = -1;
              $expenseInfo['wage'] = $one_entry['wage'];
              $expenseInfo['budget'] = null;
              $expenseInfo['approved'] = null;
              
              $expenseSummary[] = $expenseInfo;
            }
          }
          
          $summary = array_merge($timeSheetSummary,$expenseSummary);
          $view->summary = $summary;
        }
        else
          $view->summary = 0;


        // Create filter descirption, Same is in PDF export
        $customers = array();
        foreach ($filterCustomers as $customerID) {
          $customer_info = $database->customer_get_data($customerID);
          $customers[] = $customer_info['name'];
        }
        $view->customersFilter = implode(', ',$customers);

        $projects = array();
        foreach ($filterProjects as $projectID) {
          $project_info = $database->project_get_data($projectID);
          $projects[] = $project_info['name'];
        }
        $view->projectsFilter = implode(', ',$projects);

        $view->exportData = count($exportData)>0?$exportData:0;

        $view->columns = $columns;
        $view->custom_timeformat = $timeformat;
        $view->custom_dateformat = $dateformat;
        $view->timeSum = $timeSum;
        $view->wageSum = $wageSum;
        $view->budgetSum = $budgetSum;
        $view->approvedSum = $approvedSum;

        header("Content-Type: text/html");
        echo $view->render("formats/html.php");
    break;


    
    /**
     * Export as excel file. - ZW ===========================================
     */
    case 'export_xls':

        $database->user_set_preferences(array(
          'decimal_separator' => $_REQUEST['decimal_separator'],
          'reverse_order' => isset($_REQUEST['reverse_order'])?1:0),
          'ki_export.xls.');      
       
        $exportData = export_get_data_34F($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);
        
        // list of employees names
        $groups = $database->get_groups();
        foreach($groups as $g) {
          if ($g['name'] == 'Design') {
            $designerGroupID = $g['groupID'];
          }
        }
        $designerUsers = $database->get_users(0, array($designerGroupID));
        
        // array of weeks in this date range 
        // -- get the query date range and enumerate all week numbers 
        $fromWeekNumber = date("W", $in); 
        $toWeekNumber = date("W", $out); 
        $numberWeeks = $toWeekNumber - $fromWeekNumber + 1;
        $view->numWeeks = $numberWeeks;
        
        // list of project data (rows)
        $projectWeeklyTimeBuckets = array();
        $weeklyTimeBuckets = array();
        $designerTimeBuckets = array();
        $projectTimeTotals = array();
        $projectExpenseTotals = array();
        $projectFeeModels = array();
        $projectIDs = array();
        $totalHours = $totalFees = 0.0;
        
        foreach($exportData as &$ed) {
          $ed['week_number'] = date("W", $ed['time_in']); 
          $projectNumbersName[$ed['projectName']] = $ed['project_number'];
          $projectFeeModels[$ed['projectName']] = $ed['fee_model'];
          $projectIDs[$ed['projectName']] = $ed['projectID'];
          
          if ($ed['type'] == 'expense') {
            // its an expense - gather expense data
            $projectExpenseTotals[$ed['projectName']] += $ed['wage']; // shitty name
            $projectTimeTotals[$ed['projectName']] += 0; // ensures value not changed - but initialized to zero if not yet set
            continue;
          }
          
          // its a timesheet entry
          $projectWeeklyTimeBuckets[$ed['username']][$ed['projectName']][$ed['week_number']] += $ed['decimalDuration'];
          $weeklyTimeBuckets[$ed['username']][$ed['week_number']] += $ed['decimalDuration'];
          $designerTimeBuckets[$ed['username']] += $ed['decimalDuration'];
          $projectTimeTotals[$ed['projectName']] += empty($ed['decimalDuration']) ? 0 : $ed['decimalDuration'];
          $totalHours += $ed['decimalDuration'];
        }
        $view->totalDisbursements = $kga['currency_sign'] . sprintf("%01.2f",  array_sum($projectExpenseTotals));
        
        $projectDataRows = array();
        foreach($projectNumbersName as $prjName => $prjNo) {
          $row = array($prjNo, $prjName);
          foreach($designerUsers as $du) {
            $currentUser = $du['name'];
            for ($w = $fromWeekNumber; $w <= $toWeekNumber; $w++) {
              if ($projectWeeklyTimeBuckets[$currentUser][$prjName][$w] > 0) {
                $row[] = $projectWeeklyTimeBuckets[$currentUser][$prjName][$w];
              } else  {
                $row[] = '&nbsp;';
              }
            }
          }            
          // Hourly Totals	
          $row[] = $projectTimeTotals[$prjName]; 
          
          // fee model
          $row[] = $projectFeeModels[$prjName];
          
          // rate (hr)
          // switch on different models here 
          $rate = $database->get_rate(null, $projectIDs[$prjName], null);
          $row[] = $kga['currency_sign'] . $rate;
          
          // Fee
          $feeText = $kga['currency_sign'] . number_format($projectTimeTotals[$prjName] * $rate, 2);
          if ($projectFeeModels[$prjName] != 'HOURLY') {
            $feeText = 'EST: ' . $feeText;
          }
          $row[] = $feeText;
          $totalFees += $projectTimeTotals[$prjName] * $rate;
          
          // Disbursements
          $row[] = $kga['currency_sign'] . sprintf("%01.2f", $projectExpenseTotals[$prjName]);
          
          $projectDataRows[] = $row;
        }
        // sort by project name
        usort($projectDataRows, 'sortProjectDataByProjectName');
        $view->totalFees = $kga['currency_sign'] .number_format($totalFees, 2);
        
        $duNames = array();
        foreach($designerUsers as $d) {
          $duNames[] = $d['name'] . ' ('.$designerTimeBuckets[$d['name']].' hr.)';
        }
        $view->duNames = $duNames;
        $view->designerTimeBuckets = $designerTimeBuckets;
        
        $weeksAllDesigners = array();
        for ($di = 0; $di < count($designerUsers); $di++) {
          for ($wn = $fromWeekNumber; $wn <= $toWeekNumber; $wn++) {
            $weeksAllDesigners[] = 'Week #'. $wn;
          }
        }
        $view->weeksAllDesigners = $weeksAllDesigners;
        $view->projectDataRows = $projectDataRows;
        
        // totals (1 row)
        $f1 = array();
        foreach($designerUsers as $d) {
          for ($wn = $fromWeekNumber; $wn <= $toWeekNumber; $wn++) {
            $f1[] = $weeklyTimeBuckets[$d['name']][$wn];
          }
        }
        $f1[] = $totalHours;
        $view->footer1 = $f1;
        
        header("Content-Disposition:attachment;filename=export.xls");
        header("Content-Type: application/vnd.ms-excel");
        
        echo $view->render("formats/excel34FTimesheet.php");
    break;
    


    /**
     * Exort as TIMESHEET DETAIL
     */
    case 'export_csv': // CHANGE THIS TO TIMESHEET DETAIL
      
        $database->user_set_preferences(array(
          'decimal_separator' => $_REQUEST['decimal_separator'],
          'reverse_order' => isset($_REQUEST['reverse_order'])?1:0),
          'ki_export.xls.');      
       
        $exportData = export_get_data_34F($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);
        
        // IF THE USER IS NOT AN ADMIN - RETRICT THE TABLE TO SELF ONLY
        // ELSE - use the selected user only
        // this report is one user
        
        $isAdmin = ($kga['user']['globalRoleID'] === '1');
        
        // list of employees names
        $groups = $database->get_groups();
        foreach($groups as $g) {
          if ($g['name'] == 'Design') {
            $designerGroupID = $g['groupID'];
          }
        }
        $designerUsers = $database->get_users(0, array($designerGroupID));
        
        $timesheetUserName = '';
        // use the user in filterUsers
        if ($isAdmin) {
          if (count($filterUsers) > 1) {
            // error single user timesheet export
            echo json_encode(array('errors'=>'Multiple Users selected - one user at a time'));
            return;
          }

          foreach($designerUsers as $u) {
            if ($u['userID'] == $filterUsers[0]) {
              $timesheetUserName = $u['name'];
              break;
            }
          }
        } else {
          $timesheetUserName = $user['name'];
        }
        $view->timesheetUserName = $timesheetUserName;
        
        // array of DAYS in this date range 
        // enumerate all DAYS AS "MON<br/>28" 
        
        // sanity check the input dates - must be less than a month and from < to
        
        $fromDayNumber = date("j", $in); 
        $toDayNumber = date("j", $out); 
        $numberDays = $toDayNumber - $fromDayNumber + 1;
        $view->numDays = $numberDays;
        $view->dateRangeStr = date('F j, Y', $in) . ' to ' . date('F j, Y', $out);
        
        // list of project data (rows)
        $projectDailyTimeBuckets = array();
        $projectDailyTimeBucketsInternal = array();
        $dailyTotals = array();
        $designerTimeBuckets = array();
        $projectTimeTotals = array();
        $projectTimeTotalsInternal = array();
        $projectExpenseTotals = array();
        $projectIDs = array();
        $projectNumbersName = array();
        $actvitiesListInternal = array();
        $totalHours = $totalHoursBillable = $totalHoursNonBillable = $totalFees = 0.0;
        
        foreach($exportData as &$ed) {
          if ($ed['type'] == 'expense') {
            continue;
          }
          $ed['day_number'] = date("j", $ed['time_in']); 
          $isInternalProject = (stristr($ed['projectName'], '34F INTERNAL') !== false);// 34F INTERNAL CUSTOMER

          if ($isInternalProject) {
            $totalHoursNonBillable += $ed['decimalDuration']; 
            $actvitiesListInternal[$ed['activityName']] = $ed['projectName'];
            $projectDailyTimeBucketsInternal[$ed['username']][$ed['activityName']][$ed['day_number']] += $ed['decimalDuration'];
            $projectTimeTotalsInternal[$ed['activityName']] += empty($ed['decimalDuration']) ? 0 : $ed['decimalDuration'];
          } else {
            $totalHoursBillable += $ed['decimalDuration'];
            $projectNumbersName[$ed['projectName']] = $ed['project_number'];
            $projectDailyTimeBuckets[$ed['username']][$ed['projectName']][$ed['day_number']] += $ed['decimalDuration'];
            $projectTimeTotals[$ed['projectName']] += empty($ed['decimalDuration']) ? 0 : $ed['decimalDuration'];
          }          
          
          $projectIDs[$ed['projectName']] = $ed['projectID'];
          $designerTimeBuckets[$ed['username']] += $ed['decimalDuration'];
          $dailyTotals[$ed['day_number']] += $ed['decimalDuration'];
          $totalHours += $ed['decimalDuration'];
        }
        
        $projectDataRows = array();
        foreach($projectNumbersName as $prjName => $prjNo) {
          $row = array($prjNo, $prjName);
          for ($d = $fromDayNumber; $d <= $toDayNumber; $d++) {
             $dailyTotals[$d] += 0;
            if ($projectDailyTimeBuckets[$timesheetUserName][$prjName][$d] > 0) {
              $row[] = $projectDailyTimeBuckets[$timesheetUserName][$prjName][$d];
            } else  {
              $row[] = '&nbsp;';
            }
          }           
          // Hourly Totals	
          $row[] = $projectTimeTotals[$prjName]; 
          $projectDataRows[] = $row; 
        }
        
        $projectDataRowsInternal = array();
        foreach($actvitiesListInternal as $activityName => $prjName) {
          $row = array($prjName, $activityName);
          for ($d = $fromDayNumber; $d <= $toDayNumber; $d++) {
            $dailyTotals[$d] += 0;
            if ($projectDailyTimeBucketsInternal[$timesheetUserName][$activityName][$d] > 0) {
              $row[] = $projectDailyTimeBucketsInternal[$timesheetUserName][$activityName][$d];
            } else  {
              $row[] = '&nbsp;';
            }
          }           
          // Hourly Totals	
          $row[] = $projectTimeTotalsInternal[$activityName]; 
          $projectDataRowsInternal[] = $row; 
        }
        
        // sort by project name
        usort($projectDataRows, 'sortProjectDataByProjectName');
        usort($projectDataRowsInternal, 'sortProjectDataByProjectName');
        ksort($dailyTotals);
        
        $view->designerTimeBuckets = $designerTimeBuckets;
        
        $daysAllDesigners = array();
        $currentDay = $in;
        for ($dn = $fromDayNumber; $dn <= $toDayNumber; $dn++) {
          $daysAllDesigners[] = strtoupper(date('D', $currentDay)) . '<br/>' . date('d', $currentDay);
          $currentDay = strtotime('+ 1 DAY', $currentDay);
        }
        $view->daysAllDesigners = $daysAllDesigners;
        
        $view->projectDataRows = $projectDataRows;
        
        $view->projectDataRowsInternal = $projectDataRowsInternal;
        
        $view->totalHours = $totalHours;
        
        
        $view->dailyTotals = $dailyTotals;
        $view->totalHoursBillable = $totalHoursBillable;
        $view->totalHoursNonBillable = $totalHoursNonBillable;
        
        
        header("Content-Disposition:attachment;filename=export.xls");
        header("Content-Type: application/vnd.ms-excel");
        
        echo $view->render("formats/excel34FTimesheetDetail.php");      
    break;


//    /**
//     * Exort as csv file.
//     */
//    case 'export_csv':
//
//      $database->user_set_preferences(array(
//          'column_delimiter' => $_REQUEST['column_delimiter'],
//          'quote_char' => $_REQUEST['quote_char'],
//          'reverse_order' => isset($_REQUEST['reverse_order'])?1:0),
//          'ki_export.csv.');      
//       
//        $exportData = export_get_data($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);
//        $column_delimiter = $_REQUEST['column_delimiter'];
//        $quote_char = $_REQUEST['quote_char'];
//
//        header("Content-Disposition:attachment;filename=export.csv");
//        header("Content-Type: text/csv ");
//
//        $row = array();
//        
//        // output of headers
//        if (isset($columns['date']))
//          $row[] = csv_prepare_field($kga['lang']['datum'],$column_delimiter,$quote_char);
//        if (isset($columns['from']))
//          $row[] = csv_prepare_field($kga['lang']['in'],$column_delimiter,$quote_char);            
//        if (isset($columns['to']))
//          $row[] = csv_prepare_field($kga['lang']['out'],$column_delimiter,$quote_char);           
//        if (isset($columns['time']))
//          $row[] = csv_prepare_field($kga['lang']['time'],$column_delimiter,$quote_char);          
//        if (isset($columns['dec_time']))
//          $row[] = csv_prepare_field($kga['lang']['timelabel'],$column_delimiter,$quote_char);     
//        if (isset($columns['rate']))
//          $row[] = csv_prepare_field($kga['lang']['rate'],$column_delimiter,$quote_char);          
//        if (isset($columns['wage']))
//          $row[] = csv_prepare_field($kga['currency_name'],$column_delimiter,$quote_char);                      
//        if (isset($columns['budget']))
//          $row[] = csv_prepare_field($kga['lang']['budget'],$column_delimiter,$quote_char);                      
//        if (isset($columns['approved']))
//          $row[] = csv_prepare_field($kga['lang']['approved'],$column_delimiter,$quote_char);                      
//        if (isset($columns['status']))
//          $row[] = csv_prepare_field($kga['lang']['status'],$column_delimiter,$quote_char);                      
//        if (isset($columns['billable']))
//          $row[] = csv_prepare_field($kga['lang']['billable'],$column_delimiter,$quote_char);                      
//        if (isset($columns['customer']))
//          $row[] = csv_prepare_field($kga['lang']['customer'],$column_delimiter,$quote_char);           
//        if (isset($columns['project']))
//          $row[] = csv_prepare_field($kga['lang']['project'],$column_delimiter,$quote_char);           
//        if (isset($columns['activity']))
//          $row[] = csv_prepare_field($kga['lang']['activity'],$column_delimiter,$quote_char);           
//        if (isset($columns['comment']))
//          $row[] = csv_prepare_field($kga['lang']['comment'],$column_delimiter,$quote_char);       
//        if (isset($columns['location']))
//          $row[] = csv_prepare_field($kga['lang']['location'],$column_delimiter,$quote_char);      
//        if (isset($columns['trackingNumber']))
//          $row[] = csv_prepare_field($kga['lang']['trackingNumber'],$column_delimiter,$quote_char);    
//        if (isset($columns['user']))
//          $row[] = csv_prepare_field($kga['lang']['username'],$column_delimiter,$quote_char);          
//        if (isset($columns['cleared']))
//          $row[] = csv_prepare_field($kga['lang']['cleared'],$column_delimiter,$quote_char);  
//
//        echo implode($column_delimiter,$row);
//        echo "\n";
//
//        // output of data
//        foreach ($exportData as $data) {
//          $row = array();
//          if (isset($columns['date']))
//            $row[] = csv_prepare_field(strftime($dateformat,$data['time_in']),$column_delimiter,$quote_char);
//          if (isset($columns['from']))
//            $row[] = csv_prepare_field(strftime($timeformat,$data['time_in']),$column_delimiter,$quote_char);            
//          if (isset($columns['to']))
//            $row[] = csv_prepare_field(strftime($timeformat,$data['time_out']),$column_delimiter,$quote_char);           
//          if (isset($columns['time']))
//            $row[] = csv_prepare_field($data['formattedDuration'],$column_delimiter,$quote_char);          
//          if (isset($columns['dec_time']))
//            $row[] = csv_prepare_field($data['decimalDuration'],$column_delimiter,$quote_char);     
//          if (isset($columns['rate']))
//            $row[] = csv_prepare_field($data['rate'],$column_delimiter,$quote_char);          
//          if (isset($columns['wage']))
//            $row[] = csv_prepare_field($data['wage'],$column_delimiter,$quote_char);                 
//          if (isset($columns['budget']))
//            $row[] = csv_prepare_field($data['budget'],$column_delimiter,$quote_char);                  
//          if (isset($columns['approved']))
//            $row[] = csv_prepare_field($data['approved'],$column_delimiter,$quote_char);                  
//          if (isset($columns['status']))
//            $row[] = csv_prepare_field($data['status'],$column_delimiter,$quote_char);                  
//          if (isset($columns['billable']))
//            $row[] = csv_prepare_field($data['billable'],$column_delimiter,$quote_char).'%';                       
//          if (isset($columns['customer']))
//            $row[] = csv_prepare_field($data['customerName'],$column_delimiter,$quote_char);           
//          if (isset($columns['project']))
//            $row[] = csv_prepare_field($data['projectName'],$column_delimiter,$quote_char);           
//          if (isset($columns['activity']))
//            $row[] = csv_prepare_field($data['activityName'],$column_delimiter,$quote_char);           
//          if (isset($columns['comment']))
//            $row[] = csv_prepare_field($data['comment'],$column_delimiter,$quote_char);       
//          if (isset($columns['location']))
//            $row[] = csv_prepare_field($data['location'],$column_delimiter,$quote_char);      
//          if (isset($columns['trackingNumber']))
//            $row[] = csv_prepare_field($data['trackingNumber'],$column_delimiter,$quote_char);    
//          if (isset($columns['user']))
//            $row[] = csv_prepare_field($data['username'],$column_delimiter,$quote_char);          
//          if (isset($columns['cleared']))
//            $row[] = csv_prepare_field($data['cleared'],$column_delimiter,$quote_char);  
//
//        echo implode($column_delimiter,$row);
//        echo "\n";
//        }     
//    break;
//
//

    /**
     * Export as tabular PDF document.
     */
    case 'export_pdf':
    
      echo json_encode(array('errors'=>'Function not available - use Export XLS'));
      return;

        $database->user_set_preferences(array(
          'print_comments'=>isset($_REQUEST['print_comments'])?1:0,
          'print_summary'=>isset($_REQUEST['print_summary'])?1:0,
          'create_bookmarks'=>isset($_REQUEST['create_bookmarks'])?1:0, 
          'download_pdf'=>isset($_REQUEST['download_pdf'])?1:0,
          'customer_new_page'=>isset($_REQUEST['customer_new_page'])?1:0, 
          'reverse_order'=>isset($_REQUEST['reverse_order'])?1:0,
          'pdf_format'=>'export_pdf'),
          'ki_export.pdf.');    

      $exportData = export_get_data($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);

      $orderedExportData = array();
      foreach ($exportData as $row) {
        $customerID = $row['customerID'];
        $projectID = $row['projectID'];

        // create key for customer, if not present
        if (!array_key_exists($customerID,$orderedExportData))
          $orderedExportData[$customerID] = array();

        // create key for project, if not present
        if (!array_key_exists($projectID,$orderedExportData[$customerID]))
          $orderedExportData[$customerID][$projectID] = array();

        // add row
        $orderedExportData[$customerID][$projectID][] = $row;

      }

      require('export_pdf.php');
    break;



    /**
     * Export as a PDF document in a list format.
     */
    case 'export_pdf2':

      echo json_encode(array('errors'=>'Function not available - use Export XLS'));
      return;

        $database->user_set_preferences(array(
          'print_comments'=>isset($_REQUEST['print_comments'])?1:0,
          'print_summary'=>isset($_REQUEST['print_summary'])?1:0,
          'create_bookmarks'=>isset($_REQUEST['create_bookmarks'])?1:0, 
          'download_pdf'=>isset($_REQUEST['download_pdf'])?1:0,
          'customer_new_page'=>isset($_REQUEST['customer_new_page'])?1:0, 
          'reverse_order'=>isset($_REQUEST['reverse_order'])?1:0,
          'pdf_format'=>'export_pdf2'),
          'ki_export.pdf.');    
       
      $exportData = export_get_data($in,$out,$filterUsers,$filterCustomers,$filterProjects,$filterActivities,false,$reverse_order,$default_location,$filter_cleared,$filter_type,false,$filter_refundable);

      // sort data into new array, where first dimension is customer and second dimension is project
      $orderedExportData = array();
      foreach ($exportData as $row) {
        $customerID = $row['customerID'];
        $projectID = $row['projectID'];

        // create key for customer, if not present
        if (!array_key_exists($customerID,$orderedExportData))
          $orderedExportData[$customerID] = array();

        // create key for project, if not present
        if (!array_key_exists($projectID,$orderedExportData[$customerID]))
          $orderedExportData[$customerID][$projectID] = array();

        // add row
        $orderedExportData[$customerID][$projectID][] = $row;

      }
      require('export_pdf2.php');
      break;

}

?>
