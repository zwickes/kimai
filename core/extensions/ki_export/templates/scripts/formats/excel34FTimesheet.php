
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
</head>
  <style id="Billing Timesheet"><!--table
  .c_name {
    background:#FFC000;
  }
--></style>
  
<body>
  
<table border=0 cellpadding=0 cellspacing=0 width=1858 style='border-collapse: collapse;table-layout:fixed;width:1399pt'>
 <tr height=21 style='height:15.75pt'>
  <td height=21 width=65 style='height:15.75pt;width:49pt'></td>
  <td width=214 style='width:161pt'></td>
  
  <?php 
    foreach($this->duNames as $name) { 
      echo "<td colspan={$this->numWeeks} width=205 style='width:155pt; background:#00B050; border:solid;'>{$name}</td>";
    }
  ?>
  
  <td width=55 style='width:41pt'></td>
  <td width=60 style='width:45pt'></td>
  <td width=76 style='width:57pt'></td>
  <td width=99 style='width:74pt'></td>
  <td width=103 style='width:77pt'></td>
  <td width=268 style='width:201pt'></td>
  <td width=98 style='width:74pt'></td>
 </tr>
   
 <tr height=41 style='height:30.75pt'>
  <td height=41 width=65 style='height:30.75pt;width:49pt; background: #FFC000; border:solid;'>Project Number</td>
  <td width=214 style='border-left:none;width:220pt; background: #FFC000; border:solid;'>Project Name</td>

  <?php
    foreach($this->weeksAllDesigners as $weekCell){
      echo "<td width=41 style='border:solid; width:31pt; background: #FFC000;'>$weekCell</td>\n";
    }
  ?>
  
  <td width=55 style='border:solid; width:41pt; background: #FFC000;'>Totals</td>
  <td width=60 style='border:solid; width:60pt; background: #FFC000;'>Fee MODEL</td>
  <td width=76 style='border:solid; width:57pt; background: #FFC000;'>Rate</td>
  <td width=99 style='border:solid; width:90pt; background: #FFC000;'>Total Fees</td>
  <td width=103 style='border:solid; ;width:77pt; background: #FFC000;'>Total Disbursements</td>
  <td width=268 style='border:solid; width:201pt; background: #FFC000;'>Notes</td>
  <td width=98 style='border:solid; width:74pt; background: #FFC000;'>INVOICE #</td>
 </tr>
   
   <?php
   
   foreach($this->projectDataRows as $row) {
     ?>
      <tr height=20 style='height:15.0pt'>
      <td style='border-top:solid; border:solid;'><?php echo $row[0]; ?>&nbsp;</td>
      <td style='border-top:solid; border:solid;'><?php echo $row[1]; ?>&nbsp;</td>
      
      <?php 
        $count = 0;
        for($cell = 2; $cell < count($row); $cell++, $count++) {
          
          $el = empty($row[$cell]) ? '&nbsp;' : $row[$cell];
          
          if ($count < count($this->duNames)*$this->numWeeks) {
            if ($count % $this->numWeeks == 0) {
              echo "<td style='border-top:solid; border-bottom:solid; border-left:solid; text-align: right;'>$el</td>\n";
            } else {
              echo "<td style='border-top:solid; border-bottom:solid; text-align: right;'>$el</td>\n";
            }
          } else if ($row[$cell][0] == '$' || strpos($row[$cell], 'EST') !== false) {
            echo "<td style='border-top:solid; border-bottom:solid; border-left:solid; text-align: right;'>$el</td>\n";
          } else {
            echo "<td style='border-top:solid; border-bottom:solid; border-left:solid;  text-align: center;'>$el</td>\n";
          }
        } 
      ?>
      
      <td style='border:solid;'></td>
      <td style='border:solid;'></td>
      
     </tr>
     <?php
   }
   ?>
 
 <tr height=20 style='height:15.0pt'>
  <td height=20 style='height:15.0pt'>TOTALS</td>
  <td >&nbsp;</td>
  
  <?php 
    foreach($this->footer1 as $footercell) {
      echo " <td align=right style='border-top:solid;border-bottom:double;'><strong>$footercell</strong></td>";
    }
  ?>
  <td ></td>
  <td ></td>
  <td style='border-top:solid;border-bottom:double;'><strong><?php echo $this->totalFees; ?></strong></td>
  <td style='border-top:solid;border-bottom:double;'><strong><?php echo $this->totalDisbursements; ?></strong></td>
  <td >&nbsp;</td>
  <td >&nbsp;</td>
 </tr>
 
</table>

</div>


<!----------------------------->
<!--END OF OUTPUT FROM EXCEL PUBLISH AS WEB PAGE WIZARD-->
<!----------------------------->
</body>

</html>




