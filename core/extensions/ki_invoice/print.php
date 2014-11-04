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

include_once('../../includes/basics.php');
require('../../libraries/34F/export_functions.php');

/**
 * returns true if activity is in the arrays
 *
 * @param $arrays
 * @param $activity
 * @return bool true if $activity is in the array
 * @author AA
 */
function array_activity_exists($arrays, $activity) {
	$index = 0;
	foreach ($arrays as $array) {
		if ( in_array($activity,$array) ) {
			return $index;
		}
		$index++;
	}
	return -1;
}

/**
 * @param $value
 * @param $precision
 * @return float
 */
function RoundValue($value, $precision) {
	// suppress division by zero error
	if ($precision == 0.0) {
		$precision = 1.0;
	}

	return floor($value / $precision + 0.5)*$precision;
}


function sortProjectDataByProjectNameInvoice($a, $b) {
  if ($a['projectName'] == $b['projectName']) {
    return 0;
  }
  return strcmp($a['projectName'], $b['projectName']);
}



function sortDisbursementsByProjectNameDate($a, $b) {
  if ($a['projectName'] == $b['projectName']) {
    if ($a['time_in'] == $b['time_in']) {
      return 0;      
    }
    return (intval($a['time_in']) > intval($b['time_in'])) ? 1 : -1;
  }
  return strcmp($a['projectName'], $b['projectName']);
}


// insert KSPI
$isCoreProcessor = 0;
$user            = checkUser();
$timeframe       = get_timeframe();
$in              = $timeframe[0];
$out             = $timeframe[1];

require_once('private_func.php');

if (count($_REQUEST['projectID']) == 0) {
    echo '<script language="javascript">alert("'.$kga['lang']['ext_invoice']['noProject'].'")</script>';
    return;
}

  $database->user_set_preferences(array(
          'decimal_separator' => $_REQUEST['decimal_separator'],
          'reverse_order' => isset($_REQUEST['reverse_order'])?1:0),
          'ki_export.xls.');      

//$exportData = invoice_get_data($in, $out, $_REQUEST['projectID'], $_REQUEST['filter_cleared'], isset($_REQUEST['short']));
//$exportData = invoice_get_data34F($in, $out, $_REQUEST['projectID'], $_REQUEST['filter_cleared'], isset($_REQUEST['short']));
$exportData = export_get_data_34F($in, $out, null, null, $_REQUEST['projectID'], null, false, false, '', $_REQUEST['filter_cleared'], -1, true, -1);        
        

if (count($exportData) == 0) {
    echo '<script language="javascript">alert("'.$kga['lang']['ext_invoice']['noData'].'")</script>';
    return;
}

// zw =========================================

$projectTimeTotals = array();
$projectExpenseTotals = array();
$projectFeeModels = array();
$projectIDs = array();
$projectNumbersName = array();
$projectDetails = array();
$travelDates = array();
$disbursementsDataRows = array();
$totalHours = $subTotal = $GSTCharged = $grandTotal = 0.0;
$totalDisbursementsText = $rateText = $subTotalText = $grandTotalText = '';

foreach($exportData as &$ed) {
  $projectNumbersName[$ed['projectName']] = $ed['project_number'];
  $projectFeeModels[$ed['projectName']] = $ed['fee_model'];
  $projectIDs[$ed['projectName']] = $ed['projectID'];

  if ($ed['type'] == 'expense') {
    // its an expense - gather expense data
    $projectExpenseTotals[$ed['projectName']] += 0; 
    $projectTimeTotals[$ed['projectName']] += 0; // ensures value not changed - but initialized to zero if not yet set
    
    $disbursementsDataRows[] = array('projectName' => $ed['projectName'], 
                                     'time_in' => $ed['time_in'], 
                                     'date' => date('M j', $ed['time_in']), 
                                     'description' => trim($ed['comment']), 
                                     'project_number' => $ed['project_number'], 
                                     'pretax_value' => $kga['currency_sign'] . number_format(floatval($ed['wage']) - floatval($ed['pst_part']) - floatval($ed['gst_part']), 2), 
                                     'pst_part' => ($ed['pst_part'] == 0) ? '-' : $kga['currency_sign'] . number_format($ed['pst_part'], 2), 
                                     'gst_part' => ($ed['gst_part'] == 0) ? '-' : $kga['currency_sign'] . number_format($ed['gst_part'], 2),
                                     'value' => $kga['currency_sign'] . number_format($ed['wage'], 2));
    
    $projectExpenseTotals[$ed['projectName']] += $ed['wage']; // shitty name

    if (stripos($ed['activityName'], 'travel') !== false) {
      $travelDates[$ed['projectName']][] = substr($ed['username'],0,2) . ': ' . date('M j', $ed['time_in']);
    }             
    continue;
  }

  // its a timesheet entry
  $projectTimeTotals[$ed['projectName']] += empty($ed['decimalDuration']) ? 0 : $ed['decimalDuration'];
  $totalHours += $ed['decimalDuration'];

  if (!empty($ed['description'])) {
    $projectDetails[$ed['projectName']][] = substr($ed['username'],0,2) . ': ' .  $ed['description'];
  }             
}
$totalDisbursementsText = $kga['currency_sign'] . sprintf("%01.2f",  array_sum($projectExpenseTotals));

usort($disbursementsDataRows, 'sortDisbursementsByProjectNameDate');

$projectDataRows = array();
foreach($projectNumbersName as $prjName => $prjNo) {

  // switch on different models here 
  $rate = $database->get_rate(null, $projectIDs[$prjName], null);
  $rateText = $kga['currency_sign'] . $rate;          

  // Fee for project (line item total)
  $feeText = $kga['currency_sign'] . number_format($projectTimeTotals[$prjName] * $rate, 2);

  if ($projectFeeModels[$prjName] != 'HOURLY') {
    $feeText = 'EST: ' . $feeText;
  }
  $subTotal += $projectTimeTotals[$prjName] * $rate;

  $projectDataRows[] = array(
           'projectName' => $prjName,
           'hours' => $projectTimeTotals[$prjName],
           'rateText' => $rateText,
           'feeText' => $feeText,
           'projectDetails' => empty($projectDetails[$prjName]) ? array('No details entered') : $projectDetails[$prjName],
           'projectTravelDates' => empty($travelDates[$prjName]) ? array('No dates entered') : $travelDates[$prjName]
        );
}

// sort by project name
usort($projectDataRows, 'sortProjectDataByProjectNameInvoice');
$subTotalText = $kga['currency_sign'] . number_format($subTotal, 2);
$GSTCharged = $kga['currency_sign'] . number_format(0.05 * $subTotal, 2);
$grandTotal = $kga['currency_sign'] . number_format($subTotal + $GSTCharged + array_sum($projectExpenseTotals), 2);

// zw =========================================


// ----------------------- FETCH ALL KIND OF DATA WE NEED WITHIN THE INVOICE TEMPLATES -----------------------

$date            = time();
$month           = $kga['lang']['months'][date("n", $out)-1];
$year            = date("Y", $out);
$projectObjects  = array();
foreach ($_REQUEST['projectID'] as $projectID)
  $projectObjects[] = $database->project_get_data($projectID);
$customer        = $database->customer_get_data($projectObjects[0]['customerID']);
$customerName    = html_entity_decode($customer['name']);
$beginDate       = $in;
$endDate         = $out;
$invoiceID       = 'XXXXXXXXX'; //$customer['name']. "-" . date("y", $in). "-" . date("m", $in);
$today           = time();
$dueDate         = mktime(0, 0, 0, date("m") + 1, date("d"), date("Y"));

// switch to create either an invoice or a disbursements detail. 
$renderType = 'INVOICE';
if (!empty($_REQUEST['invoiceBTN'])) {
  $baseFolder = dirname(__FILE__) . "/invoices/";
  $tplFilename = $_REQUEST['ivform_file']; 
  $renderType = 'INVOICE';
} else {
  $baseFolder = dirname(__FILE__) . "/disbursements/";
  $tplFilename = 'disbursement_34F.odt';
  $renderType = 'DISBURSEMENTS';
}

if (strpos($tplFilename, '/') !== false) {
  // prevent directory traversal
  header("HTTP/1.0 400 Bad Request");
  die;
}

// ---------------------------------------------------------------------------

$model = new Kimai_Invoice_PrintModel();

$model->setEntries($projectDataRows);
$model->setDisbursements($disbursementsDataRows);

//$model->setAmount($total);
//$model->setGSTRate($gst_rate); 
$model->setTotal($grandTotal);

$model->setGST($GSTCharged); 

$model->setCustomer($customer);
$model->setProjects($projectObjects);
$model->setInvoiceId($invoiceID);

$shortBeginEndDateRange = date('M d', $beginDate) . ' - ' . date('M d, Y', $endDate);
$model->setShortBeginEndDateRange($shortBeginEndDateRange);
        
$model->setBeginDate($beginDate);
$endDate = date('F j, Y', $endDate);
$model->setEndDate($endDate);

$model->setInvoiceDate(time());
$model->setDateFormat($kga['conf']['date_format_2']);
$model->setCurrencySign($kga['conf']['currency_sign']);
$model->setCurrencyName($kga['conf']['currency_name']);

//$model->setDueDate(mktime(0, 0, 0, date("m") + 1, date("d"), date("Y")));

// ---------------------------------------------------------------------------
$odtR = new Kimai_Invoice_OdtRenderer();
$odtR->renderType = $renderType;

$renderers = array(
    'odt'   => $odtR,
    'html'  => new Kimai_Invoice_HtmlRenderer(),
    'pdf'   => new Kimai_Invoice_HtmlToPdfRenderer()
);

/* @var $renderer Kimai_Invoice_AbstractRenderer */
foreach($renderers as $rendererType => $renderer)
{
    $renderer->setTemplateDir($baseFolder);
    $renderer->setTemplateFile($tplFilename);
    $renderer->setTemporaryDirectory(APPLICATION_PATH . '/temporary');
    if ($renderer->canRender()) {
        $renderer->setModel($model);
        $renderer->render();
        return;
    }
}

// no renderer could be found
die('Template does not exist or is incompatible: ' . $baseFolder . $tplFilename);
