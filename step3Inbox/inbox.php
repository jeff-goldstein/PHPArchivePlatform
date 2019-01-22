<!-- Copyright 2016 Jeff Goldstein

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

File: inbox
Purpose: Show currently scheduled campaigns and allow user to cancel them

 -->
<!DOCTYPE html>
<html>
<head>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!--<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>-->
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<meta content="width=device-width, initial-scale=1" name="viewport">
<title>SparkPost Archive Inbox</title>
<link rel="shotcut icon" type="image/png" href="http://www.geekswithapersonality.com/email.png" />
<link href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css" rel="stylesheet">
<!--<link href="//code.jquery.com/ui/3.2.1/themes/base/jquery-ui.css" rel="stylesheet">-->
<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
<!--<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>-->
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />


<style>

#myInput {
  background-image: url('/css/searchicon.png');
  background-position: 10px 10px;
  background-repeat: no-repeat;
  width: 100%;
  font-size: 16px;
  padding: 12px 20px 12px 40px;
  border: 1px solid #ddd;
  margin-bottom: 12px;
}

</style>
</head>

<body style="margin-left: 20px; margin-right: 20px">
<table style="width:1420px; outline:red solid 1px">
<tr><td style="width:1420px;" colspan="3">
	<center><h1>Archived Email Event Inbox</h1></center>
</td></tr>
<tr>
<td>What email address would you like to trace?
<input id="emailaddress" name="emailaddress" style="width:200px">
&nbsp;&nbsp;&nbsp;<select id="tabletypeSelector"><option value="wide">Wide</option><option value="collapsed">Collapsed</option></select>
&nbsp;&nbsp;&nbsp;<input hidden id="currentTableDisplayType"</input>
&nbsp;&nbsp;&nbsp;Start Date: <input type="date" id="sDate">
&nbsp;&nbsp;&nbsp;End Date: <input type="date" id="eDate">
&nbsp;&nbsp;&nbsp;<input type="button" id="getSelected" name="getSelected" onclick="getEmailEvents('email','')" value="Search Archive" style="color: #FFFFFF; font-family: Helvetica, Arial; font-weight: bold; font-size: 12px; background-color: #72A4D2;" size="10" >
<br><br>
<textarea id="serverstatus" name="serverstatus" readonly rows=1 type="textnormal" style="border:none; width:100px; resize: none;">Server Results:</textarea>
<textarea id="servererror" name="servererror" readonly rows=1 type="textnormal" style="background-color: #f5f5f5; border:none; width: 1000px; resize: none;"></textarea>
	<table id="EventTable" style="width:1420px; border: 0px">
		<tr style="width=1420px">
			<td colspan="5" style="font-size: 20px;"><strong><center>Filters</center></strong></td>
		</tr>
		<tr >
			<td><input style="width:275px; border:0px; background-color: #f5f5f5;" type="text" id="subjectInput" onkeyup="FilterFunction()" placeholder="Filter by Subject.." title="Type in a Subject"></td>
      <td><input style="width:275px; border:0px; background-color: #f5f5f5;" type="text" id="timeInput" onkeyup="FilterFunction()" placeholder="Filter by Email Sent at Time/Date.." title="Type in a Time/Date"></td>			
      <td><input style="width:275px; border:0px; background-color: #f5f5f5;" type="text" id="campaignInput" onkeyup="FilterFunction()" placeholder="Filter by Campaign.." title="Type in a Campaign Name"></td>
			<td><input style="width:275px; border:0px; background-color: #f5f5f5;" type="text" id="eventTypeInput" onkeyup="FilterFunction()" placeholder="Filter by Event Type.." title="Enter in an Event Type"></td>
      <td><input style="width:275px; border:0px; background-color: #f5f5f5;" type="text" id="UIDInput" onkeyup="FilterFunction()" placeholder="Filter by UID.." title="Type in UID"></td>
		</tr>
	</table>
	<br><br>
<table id='EventtableDetails' border=1 style='width:1420px;'></table>

<br><br>

</table>
<p>&nbsp;&nbsp;&nbsp;* Times are showing GMT Time</p>
<div id="dialog-modal-full" title="Full Message Events Details!"></div>

<script>

function AdjustIframeHeightOnLoad() { document.getElementById("form-iframe").style.height = document.getElementById("form-iframe").contentWindow.document.body.scrollHeight + "px"; }
function AdjustIframeHeight(i) { document.getElementById("form-iframe").style.height = parseInt(i) + "px"; }

function getEmailEvents(searchType, searchId)
{
	var emailaddress = document.getElementById("emailaddress").value;
  var theme = document.getElementById("tabletypeSelector").value;
  var sdate = document.getElementById("sDate").value;
  var edate = document.getElementById("eDate").value; 

  if (!emailaddress || !sdate || !edate)
  { 
    alert("Email Address, Start Date and End Date must all be entered");	
  }
  else
  {
    $.ajax({
      url:'getEventsFromDatabase.php',
    	type: "POST",
    	dataType : 'json',
    	data: {"emailaddress" : emailaddress, "sdate" : sdate, "edate" : edate, "theme" : theme, "searchId" : searchId},
    	beforeSend:function()
    	{
     		$('#servererror').html("Calling SparkPost server for data...");
        document.getElementById("EventtableDetails").innerHTML = "";
        document.getElementById("UIDInput").value = "";
     		document.getElementById("subjectInput").value = "";
     		document.getElementById("campaignInput").value = "";
     		document.getElementById("eventTypeInput").value = "";
     		document.getElementById("timeInput").value = "";
    	},
    	complete: function (response) 
    	{
        	document.getElementById("EventtableDetails").innerHTML = response.responseJSON.details;
          document.getElementById("currentTableDisplayType").value = theme;
        	if (response.responseJSON.details != null) $('#servererror').html(response.responseJSON.notes); else $('#servererror').html(response.responseJSON.error);
    	},
    	error: function (response) {
        	alert("Problem getting data from SparkPost Server!");
        	$('#servererror').html(response);
    	}
    	});
  }
}


function FilterFunction() 
/*

If you look closely, I had to seperate for both the 'wide' and 'collapsed' tables because the data is located in different td cells

The collapsed table was more of a challenge to filter because of how I am displaying the data.  
The FilterFunction searches the tables and looks for td entries in the table.  
I then match the data in the filters to the appropriate td entry in the table.  
In the wide table, that was very easy.  I had ‘x’ number of columns (td), and my data was easily found in it’s column. 
In the collaped table….well, let’s just say it was a tad more challenging trying to figure which td entry has the data I needed.  
The first 4 entries for each row in the collapsed table are all in a single tr/td combination.  
But to get the items in the orange (event type, email type and UID) to align correctly, they needed to be in a table.  

*/
{
	var theme = document.getElementById("currentTableDisplayType").value;
  var input, filter, table, tr, td, i;
	var showNOshow = $('#showNOshow').data('show');
	var input1 = document.getElementById("subjectInput");
  var input2 = document.getElementById("timeInput");
	var input3 = document.getElementById("campaignInput");
	var input4 = document.getElementById("eventTypeInput");
  var input5 = document.getElementById("UIDInput");
	filter1 = input1.value.toUpperCase();
	filter2 = input2.value.toUpperCase();
	filter3 = input3.value.toUpperCase();
	filter4 = input4.value.toUpperCase();
	filter5 = input5.value.toUpperCase();
	table = document.getElementById("EventtableDetails");
	tr = table.getElementsByTagName("tr");	
	for (i = 0; i < tr.length; i++)
	{
		if (theme == "wide")
    {
      td1 = tr[i].getElementsByTagName("td")[1];
      td2 = tr[i].getElementsByTagName("td")[2];
      td3 = tr[i].getElementsByTagName("td")[4];
      td4 = tr[i].getElementsByTagName("td")[5];
      td5 = tr[i].getElementsByTagName("td")[9];
      if (td1 || td2 || td3 || td4 || td5 ) 
      {
        if ((td1.innerHTML.toUpperCase().indexOf(filter1) > -1) &&
            (td2.innerHTML.toUpperCase().indexOf(filter2) > -1) &&
            (td3.innerHTML.toUpperCase().indexOf(filter3) > -1) &&
            (td4.innerHTML.toUpperCase().indexOf(filter4) > -1) &&
            (td5.innerHTML.toUpperCase().indexOf(filter5) > -1)) 
        {
          tr[i].style.display = "";
        }
        else
        {
          tr[i].style.display = "none";
        }
      }       
    }
    else
    {
      if ((i % 2) != 0)
        {
          td1 = tr[i].getElementsByTagName("td")[5];
          td2 = tr[i].getElementsByTagName("td")[6];
          td3 = tr[i].getElementsByTagName("td")[7];
          td4 = tr[i].getElementsByTagName("td")[8]; 
          td5 = tr[i].getElementsByTagName("td")[4];
          if (td1 || td2 || td3 || td4 || td5 ) 
          {
            if ((td1.innerHTML.toUpperCase().indexOf(filter1) > -1) &&
      			    (td2.innerHTML.toUpperCase().indexOf(filter2) > -1) &&
      			    (td3.innerHTML.toUpperCase().indexOf(filter3) > -1) &&
      			    (td4.innerHTML.toUpperCase().indexOf(filter4) > -1) &&
      			    (td5.innerHTML.toUpperCase().indexOf(filter5) > -1)) 
      		  {
        		  tr[i].style.display = "";
      		  }
      		  else
      		  {
        		  tr[i].style.display = "none";
      		  }
          }
        }
    	}       
  	}
}

function show_details_local_html( row )
{

	var theme = document.getElementById("currentTableDisplayType").value;
  if (theme=="wide")
  {
    var raw = document.getElementById("EventtableDetails").rows[row].cells.item(10).innerHTML;
  }
  else
  {
    row = row - 1;
    var raw = document.getElementById("rows").rows[row].cells.item(8).innerHTML;
  }
	var rawArray = [];

	rawArray = JSON.parse(raw);
  rawArray = rawArray.msys;
  var secondKey = Object.keys(rawArray); //fetched the key at second index
  
  rawArray = rawArray[Object.keys(rawArray)[0]];
 
	
    var alertText = "<center><strong>Email for " + rawArray.rcpt_to + "</center></strong><br>";
    alertText = alertText + "Subject: " + rawArray.subject + "<br>";
    alertText = alertText + "Campaign: " + rawArray.campaign_id + "<br>";
    alertText = alertText + "From: " + rawArray.friendly_from + "<br>";
    alertText = alertText + "To: " + rawArray.rcpt_to + "<br>";
    if(rawArray.rcpt_type) alertText = alertText + "RCPT Type: "  + rawArray.rcpt_type + "<br>";
    if(rawArray.ip_address) alertText = alertText + "IP Address: " + rawArray.ip_address + "<br>";
    if(rawArray.target_link_url) 
    {
      alertText = alertText + "Clicked Link: ";
      if(rawArray.target_link_name) alertText = alertText + "<strong>"  + rawArray.target_link_name + "</strong> / ";
      alertText = alertText + rawArray.target_link_url + "<br>";
    }
    if (rawArray.type == "open" ||  rawArray.type == "click" || rawArray.type == "initial_open")
    {
        if (rawArray.geo_ip.city != "" || rawArray.geo_ip.state != "" || rawArray.geo_ip.region != "")
        {
        	alertText = alertText + "Geo IP Location Estimate<br>";
        	if (rawArray.geo_ip.city != "")
        		alertText = alertText + "&nbsp;&nbsp;&nbspCity: " + rawArray.geo_ip.city + "<br>";
        	if (rawArray.geo_ip.country != "")
        		alertText = alertText + "&nbsp;&nbsp;&nbspCountry:  " + rawArray.geo_ip.country + "<br>";
        	if (rawArray.geo_ip.region != "")
        		alertText = alertText + "&nbsp;&nbsp;&nbspRegion: " + rawArray.geo_ip.region + "<br>";
        }
    }
    alertText = alertText + "Template: " + rawArray.template_id + "<br>";
    alertText = alertText + "Record Type: <strong>" + rawArray.type + "</strong><br>";
    alertText = alertText + "IP Pool: " + rawArray.ip_pool + "<br>";
    alertText = alertText + "Message Size: " + rawArray.msg_size + "<br>";
    if(rawArray.num_retries) alertText = alertText + "Number of Retries: " + rawArray.num_retries + "<br>";
    if(rawArray.queue_time) alertText = alertText + "Queue Time: " + rawArray.queue_time + "<br>";
    if(rawArray.rcpt_meta) 
    {
    	var metastring = JSON.stringify(rawArray.rcpt_meta);
    	alertText = alertText + "Meta Data: " + metastring + "<br>";
    }
    if (rawArray.subaccount_id) alertText = alertText + "Sub Account: " +  rawArray.subaccount_id + "<br>";
    var UID = rawArray.rcpt_meta.uid;
    alertText = alertText + "UID: " + UID + "<br>";
    alertText = alertText + "<br><strong>Email</strong><p>";
    $.ajax({
    url:'getBodyFromS3.php',
    	type: "POST",
    	dataType : 'text',
    	data: {"UID" : UID},
    	complete: function (response) 
    	{

        alertText = alertText + "<div style='border:2px solid #666; border-radius:11px; padding:20px;'><iframe readonly id='form-iframe' width='100%' style='background-color:#FFFFFF; margin:0; width:100%; height:150px; border:none; overflow:hidden;' scrolling='no' onload='AdjustIframeHeightOnLoad()' src='" + response.responseText + "'</iframe></div>";
        if (theme=="wide")
        {
          showDialogFull (alertText);
        }
        else
        {
          var holder = document.getElementById('emailbodyanddetails');
          holder.innerHTML = alertText;
          $('html, body').animate({ scrollTop: 0 }, 'fast');
        }
		}
    }); 
    uncheck(row);
}

function uncheck(row) 
{    
    
    //document.getElementById("EventtableDetails").rows[row].cells.item(0).checked = false;
    var checks = document.getElementsByName('detailcheck');
	for(var i = 0; i < checks.length; ++i)
	{
    	checks[i].checked = false;
	}
}


function showDialogFull(text)
{
    $("#dialog-modal-full").html(text);
      $("#dialog-modal-full").dialog(
      {
        width: 900,
        height: 600,
        open: function(event, ui)
        {
          var textarea = $("<textarea style='background-color:blue'></textarea>");
          $(textarea).html({
              focus: true,
              autoresize: false,
              initCallback: function()
              {
                  this.set('<p>finders keepers</p>');
              }
          });
        }
      });
}

</script>

</body>
</html>
