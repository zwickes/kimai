
<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:x="urn:schemas-microsoft-com:office:excel"
xmlns="http://www.w3.org/TR/REC-html40">

<head>
<meta http-equiv=Content-Type content="text/html; charset=utf-8">
<meta name=ProgId content=Excel.Sheet>
</head>
  <style id="Timesheet Detail"><!--table
  .c_name {
    background:#FFC000;
  }
--></style>
  
<body>
  
<table border=0 cellpadding=0 cellspacing=0 width=1858 style='border-collapse: collapse;table-layout:fixed;width:1399pt'>
 <tr height=21 style='height:15.75pt'>
   <td height=21 width=65 style='height:15.75pt;width:49pt;background-color:#cccccc;' colspan="<?php echo 3 + $this->numDays; ?>"><h2>WEEKLY TIME SHEET DETAIL</h2></td>
  </tr>
  <tr>
    <td width='100'><strong>NAME:</strong></td>
    <td><strong><?php echo $this->timesheetUserName; ?></strong></td>
  </tr>    
  <tr>
    <td width='100'><strong>PERIOD:</strong></td>
    <td><strong><?php echo $this->dateRangeStr; ?></strong></td>
  </tr>
  <tr>
    <td width='100'><strong>TOTAL HOURS:</strong></td>
    <td style='text-align: left;'><strong><?php echo $this->totalHours; ?></strong></td>
  </tr>
  

  <tr><td>&nbsp;</td></tr>

 <tr>
   <td COLSPAN='<?php echo $this->numDays + 3; ?>' style='height:30.75pt;width:49pt; background: #FFC000; border:solid;text-align: left;'><h3>CHARGEABLE (Projects)</h3></td>
 </tr>
  
 <tr height=41 style='height:30.75pt'>
  <td height=41 width=65 style='height:30.75pt;width:49pt; background: #FFC000; border:solid; font-weight: bold;'>Project Number</td>
  <td width=214 style='border-left:none;width:220pt; background: #FFC000; border:solid;font-weight: bold;'>Project Name</td>

  <?php
    foreach($this->daysAllDesigners as $dayCell){
      echo "<td width=41 style='border:solid; width:31pt; background: #FFC000;font-weight: bold;text-align:right;'>$dayCell</td>\n";
    }
  ?>
  <td width=55 style='border:solid; width:41pt; background: #FFC000;font-weight: bold;text-align: right;'>Total</td>
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
          echo "<td style='border-top:solid; border-bottom:solid; border-left:solid;border-right:solid; text-align: center;'>$row[$cell]&nbsp;</td>\n";
        }
      ?>
      
     </tr>
     <?php
   }
   ?>
 
 <tr height=20 style='height:15.0pt'>
  <td height=20  colspan='<?php echo $this->numDays + 2; ?>'  style='height:15.0pt'>BILLABLE TOTAL</td>
  <?php 
    echo "<td align=right style='border-top:solid;border-bottom:double;'><strong>{$this->totalHoursBillable}</strong></td>";
  ?>
 </tr>

  
  <tr><td>&nbsp;</td></tr>

  
 <tr>
   <td COLSPAN='<?php echo $this->numDays + 3; ?>' style='height:30.75pt;width:49pt; background: #FFC000; border:solid;text-align: left;'><h3>NON-CHARGEABLE (34F Internal)</h3></td>
 </tr>
  
 <tr height=41 style='height:30.75pt'>
  <td height=41 width=65 style='height:30.75pt;width:49pt; background: #FFC000; border:solid; font-weight: bold;'>Project</td>
  <td width=214 style='border-left:none;width:220pt; background: #FFC000; border:solid;font-weight: bold;'>Activity</td>

  <?php
    foreach($this->daysAllDesigners as $dayCell){
      echo "<td width=41 style='border:solid; width:31pt; background: #FFC000;font-weight: bold;text-align:right;'>$dayCell</td>\n";
    }
  ?>
  <td width=55 style='border:solid; width:41pt; background: #FFC000;font-weight: bold;text-align: right;'>Total</td>
 </tr>
   
   <?php
   
   foreach($this->projectDataRowsInternal as $row) {
     ?>
      <tr height=20 style='height:15.0pt'>
      <td style='border-top:solid; border:solid;'><?php echo $row[0]; ?>&nbsp;</td>
      <td style='border-top:solid; border:solid;'><?php echo $row[1]; ?>&nbsp;</td>
      
      <?php 
        $count = 0;
        for($cell = 2; $cell < count($row); $cell++, $count++) {
          echo "<td style='border-top:solid; border-bottom:solid; border-left:solid;border-right:solid; text-align: center;'>$row[$cell]&nbsp;</td>\n";
        }
      ?>
      
     </tr>
     <?php
   }
   ?>
 
 <tr height=20 style='height:15.0pt'>
   <td height=20 colspan='<?php echo $this->numDays + 2; ?>' style='height:15.0pt'>NON-BILLABLE TOTAL</td>
  <?php 
    echo "<td align=right style='border-top:solid;border-bottom:double;'><strong>{$this->totalHoursNonBillable}</strong></td>";
  ?>
 </tr>
  

  <tr><td>&nbsp;</td></tr>

  
 <tr>
   <td COLSPAN='<?php echo $this->numDays + 3; ?>' style='height:30.75pt;width:49pt; background: #FFC000; border:solid;text-align: left;'><STRONG>DAILY TOTALS</STRONG></td>
 </tr>

  <TR>
  <td colspan='2'>TOTAL</td>
  
  <?php 
    foreach($this->dailyTotals as $d) {
      echo "<td align=center style='border-top:solid;border-bottom:double;'><strong>{$d}</strong></td>";
    }
  
  ?>
  </tr>
  
  
  
  
</table>

</div>


<!----------------------------->
<!--END OF OUTPUT FROM EXCEL PUBLISH AS WEB PAGE WIZARD-->
<!----------------------------->
</body>

</html>




