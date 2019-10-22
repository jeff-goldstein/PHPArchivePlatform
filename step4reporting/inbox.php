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
<meta content="text/html; charset=utf-8" http-equiv="Content-Type">
<meta content="width=device-width, initial-scale=1" name="viewport">
<title>SparkPost Archive Inbox</title>
<link rel="shotcut icon" type="image/png" href="http://www.geekswithapersonality.com/email.png" />
<link href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.2.1.js"></script>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="http://code.jquery.com/ui/1.11.1/jquery-ui.min.js"></script>
<!-- From jo-geek https://github.com/Jo-Geek/jQuery-ResizableColumns -->
<script src="resizableColumns.min.js"></script>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.11.1/themes/smoothness/jquery-ui.css" />
<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" />


<style>

.hoverrow:hover {
  background-color: lightblue;
}

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

<!-- Toggle code found -->
<!-- https://stackoverflow.com/questions/48095300/toggle-arrow-right-and-up-on-click-of-a-div/48095370 -->
.koh-faqs-page-title {
  font-family: Nexa W01 Heavy;
  font-size: 30px;
  color: #04202E;
  font-weight: 700;
}

.koh-faq-question-span {
  font-family: Helvetica Neue LT Pro Roman !important;
  font-size: 16px !important;
  color: #000 !important;
  font-weight: 700 !important;
  display: inline-block;
}

.koh-faq-answer {
  font-family: Helvetica Neue LT Pro Roman;
  color: #000;
  font-weight: 400;
  display: none;
}

.icon {
  font-size: 10px;
  padding-right: 5px;
}

.fa {
  transition: transform .2s;
}

.fa.active {
  transform: rotateZ(90deg);
}

.button {
    border: 0;
    line-height: 2.5;
    padding: 0 20px;
    font-size: 10px;
    text-align: center;
    color: #fff;
    text-shadow: 1px 1px 1px #000;
    border-radius: 10px;
    background-color: rgba(220, 0, 0, 1);
    background-image: linear-gradient(to top left,
                                      rgba(0, 0, 0, .2),
                                      rgba(0, 0, 0, .2) 30%,
                                      rgba(0, 0, 0, 0));
    box-shadow: inset 2px 2px 3px rgba(255, 255, 255, .6),
                inset -2px -2px 3px rgba(0, 0, 0, .6);
}

.button:hover {
    background-color: rgba(255, 0, 0, 1);
}

.button:active {
    box-shadow: inset -2px -2px 3px rgba(255, 255, 255, .6),
                inset 2px 2px 3px rgba(0, 0, 0, .6);
}

.btn {
  border: none;
  background-color: inherit;
  padding: 7px 14px;
  font-size: 14px;
  cursor: pointer;
  display: inline-block;}

/* On mouse-over */
.btn:hover {
  background: #eee;}

.press {color: green;}

</style>
</head>

<body style="margin-left: 20px; margin-right: 20px">
<table style="width:1420px; outline:red solid 1px">
<tr><td style="width:1420px;" colspan="3">
  <center><h1>Archive Email Event Inbox</h1></center>
</td></tr>
<tr>
<td>What recipient email address would you like to trace?
<input id="emailaddress" name="emailaddress" style="width:200px">
&nbsp;&nbsp;&nbsp;<select id="tabletypeSelector"><option value="wide">Wide</option><option value="collapsed">Collapsed</option></select>
&nbsp;&nbsp;&nbsp;<input hidden id="currentTableDisplayType"</input>
&nbsp;&nbsp;&nbsp;Start Date: <input type="date" id="sDate">
&nbsp;&nbsp;&nbsp;End Date: <input type="date" id="eDate">
&nbsp;&nbsp;&nbsp;<input type="button" class="button" id="getSelected" name="getSelected" onclick="getEmailEvents('email','')" value="Search Archive" style="color: #FFFFFF; font-family: Helvetica, Arial;">
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
  <div class="koh-tab-content">
    <div class="koh-tab-content-body">
      <div class="koh-faq">
        <div class="koh-faq-question">
          <i class="fa fa-chevron-right" aria-hidden="true"></i>
          <span class="koh-faq-question-span"> Reports/Export </span>
        </div>
        <div class="koh-faq-answer">
          <table id='reportingTable' border=0 style='width:300px;'>
            <tr>
              <td><button class="button" onclick="displayDetailandEmailOnly()">Print Email with Details</button></td>
              <td><button class="button" onclick="createCSV()">Export to CSV</button></td>
            </tr>
          </table>
        </div>
      </div>
    </div>
  </div>
<br><br>
<table class='resizable' id='widedetailtable' border=1 style='width:1420px; border-collapse:collapse; border-color:black' hidden>
<thead>
  <tr>
  <th style='width:55px; height:75px;' title='Select to see the actual email and further details on this event'><center>Display Email</center></th>
  <th style="background-color: #E8F0FE; width:365px;"><center>Subject</center></th>
  <th style="background-color: #E8F0FE; width:160px"><center>Sent At<sup>*</sup></center></th>
  <th style="background-color: #E8F0FE; width:160px"><center>Event Happened At<sup>*</sup></center></th>
  <th style="background-color: #E8F0FE; width:150px"><center>Campaign ID</center></th>
  <th style="background-color: #E8F0FE; width:100px"><center>Event Type</center></th>
  <th style="background-color: #E8F0FE; width:100px"title='This indicates if this record represents an action on the original email, archive duplicate email, cc or bcc'><center>Rcpt Type</center></th>
  <th style="background-color: #E8F0FE; width:230px"><center>Rcpt To</center></th>
  <th style="background-color: #E8F0FE; width:100px"title='Select to retrieve all data on this UID' ><center>UID</center></th>
  <th hidden><center>UID</center></th> 
  <th hidden><center>Raw</center></th>
  </tr></thead>
  <tbody id='EventtableDetails'></tbody>
</table>
<table id='collapseddetailtable' border=1 style='width:1420px; border-collapse:collapse;' hidden></table>
<p>&nbsp;&nbsp;&nbsp;* Times are showing GMT Time</p>
<textarea id="hiddenAlertText" style="display:none" value="this is my way"></textarea>
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
        if (theme=="wide")
        {
          var x = document.getElementById("widedetailtable");
          x.style.display = "block";
          var y = document.getElementById("collapseddetailtable");
          y.style.display = "none";
          document.getElementById("EventtableDetails").innerHTML = response.responseJSON.details;
        }
        else
        {
          var x = document.getElementById("widedetailtable");
          x.style.display = "none";
          var y = document.getElementById("collapseddetailtable");
          y.style.display = "block";
          document.getElementById("collapseddetailtable").innerHTML = response.responseJSON.details;
        } 
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


function createCSV()
{
  var emailaddress = document.getElementById("emailaddress").value;
  var sdate = document.getElementById("sDate").value;
  var edate = document.getElementById("eDate").value; 
  var subjectFilter = document.getElementById("subjectInput").value;
  var sentDateFilter = document.getElementById("timeInput").value;
  var campaignFilter = document.getElementById("campaignInput").value;
  var eventTypeFilter = document.getElementById("eventTypeInput").value;
  var UIDFilter = document.getElementById("UIDInput").value;
  var onlyUID = document.getElementById("drilleddownUID").value;

  if (!emailaddress || !sdate || !edate)
  { 
    alert("Email Address, Start Date and End Date must all be entered");  
  }
  else
  {
    $.ajax({
      url:'csvoutput.php',
      type: "POST",
      dataType : 'json',
      data: {"emailaddress" : emailaddress, "sdate" : sdate, "edate" : edate, "searchId" : UIDFilter, "subject" : subjectFilter, "sentDate" : sentDateFilter, "eventType" : eventTypeFilter, "campaign" : campaignFilter, "drilleddownUID" : onlyUID},
      beforeSend:function()
      {
        $('#servererror').html("Calling database...");
      },
      complete: function (response) 
      {
        if (response.responseJSON.details != null) $('#servererror').html(response.responseJSON.notes); else $('#servererror').html(response.responseJSON.error);

        var blob = new Blob([response.responseJSON.csvstring]);
        var a = window.document.createElement("a");
        a.href = window.URL.createObjectURL(blob, {type: "text/plain"});
        a.download = response.responseJSON.filename;
        document.body.appendChild(a);
        a.click();  // IE: "Access is denied"; see: https://connect.microsoft.com/IE/feedback/details/797361/ie-10-treats-blob-url-as-cross-origin-and-denies-access
        document.body.removeChild(a);
        $('#servererror').html(response.responseJSON.deletemessage);
        alert(response.responseJSON.deletemessage);
      },
      error: function (response) {
          alert("Problem getting data from database!");
          $('#servererror').html(response);
      }
      });
  }
}


function FilterFunction() 
{
  var theme = document.getElementById("currentTableDisplayType").value;
  var input, filter, table, tr, td, i;
  //var showNOshow = $('#showNOshow').data('show');
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
  if (theme =="wide") table = document.getElementById("EventtableDetails"); else table = document.getElementById("collapseddetailtable");
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

function timeConverter(ts){
  var a = new Date(ts * 1000);
  var year = a.getFullYear();
  var month = a.getMonth()+1;
  if (month<9) month= "0" + month;
  var date = a.getDate();
   if (date<9) date = "0" + date;
  var hour = a.getHours();
    if (hour<9) hour = "0" + hour;
  var min = a.getMinutes();
      if (min<9) min = "0" + min;
  var sec = a.getSeconds();
      if (sec<9) sec = "0" + sec;
  var time = year + '-' + month + '-' + date + ' ' + hour + ':' + min + ':' + sec ;
  return time;
}

function showhide() 
{
  var e = document.getElementById('headertable');
  var f = document.getElementById('displaybutton');
  if (e.style.display == 'none') 
  {
    e.style.display = 'block';
    f.innerHTML='Headers(hide)';
  } 
  else 
  {
    e.style.display = 'none';
    f.innerHTML='Headers(display)';
  }
};

function show_details( row )
{
  
  $.ajax({
      url:'obtainOutputSizing.php',
      type: "GET",
      dataType : 'json',
      complete:function(parameters)
      {
        var width = parameters.responseJSON.width;
        var height = parameters.responseJSON.height;
      },
      error: function (parameters) 
      {
        if (response.responseJSON.details != null) $('#servererror').html(response.responseJSON.notes); else $('#servererror').html(response.responseJSON.error);
      }
  });

  var buttonstyle = ".btn {border: none;background-color: inherit;padding: 7px 14px;font-size: 14px;cursor: pointer;display: inline-block;}/* On mouse-over */.btn:hover {background: #eee;}.press {color: green;}"
  var resizefuncs = "function AdjustIframeHeightOnLoad() { document.getElementById('form-iframe').style.height = document.getElementById('form-iframe').contentWindow.document.body.scrollHeight + 'px'; }function AdjustIframeHeight(i) { document.getElementById('form-iframe').style.height = parseInt(i) + 'px'; }";
  resizefuncs = resizefuncs + "function showhide() {var e = document.getElementById('headertable');var f = document.getElementById('displaybutton');if (e.style.display == 'none') {e.style.display = 'block';f.innerHTML='Headers(hide)'} else {e.style.display = 'none';f.innerHTML='Headers(display)'}}";

  var d = new Date();
  var n = d.getTimezoneOffset();
  var offsetInSeconds = n * 60;
  var theme = document.getElementById("currentTableDisplayType").value;
  if (theme=="wide")
  {
    var raw = document.getElementById("EventtableDetails").rows[row].cells.item(10).innerHTML;
  }
  else
  {
    //row = row - 1;
    var raw = document.getElementById("rows").rows[row].cells.item(8).innerHTML;
  }
  var rawArray = [];

  rawArray = JSON.parse(raw);
  rawArray = rawArray.msys;
  var secondKey = Object.keys(rawArray); //fetched the key at second index
  
  rawArray = rawArray[Object.keys(rawArray)[0]];
 
  // I agree, this next line looks funky, and in my opinion is a kludge.
  // adding the html ending tag for '/script' in the quoted text, kills something in the editor that I'm using on my hosted system; which then does something during runtime to not allow that code. 
  // The only way I could get this to work was to break the word into two pieces.  
  // For some reason, that allows me to inject scripting into the alert frame for the resizing function(s)
     
  var alertText = "<html><head><title>Print Page for - " + rawArray.rcpt_to + "/" + rawArray.subject + "</title>";
  alertText = alertText + "<style>" + buttonstyle + "</style>";
  alertText = alertText + "<script>" + resizefuncs + "</scrip" + "t></head>";
    
  alterText = alertText + "<center><strong>Email for " + rawArray.rcpt_to + "</center></strong><br>";
  alertText = alertText + "Subject: " + rawArray.subject + "<br>";
  alertText = alertText + "Campaign: " + rawArray.campaign_id + "<br>";
  alertText = alertText + "From: " + rawArray.friendly_from + "<br>";
  alertText = alertText + "To: " + rawArray.rcpt_to + "<br>";
  var injectionDateTime = rawArray.injection_time.replace("T", " ");
  injectionDateTime = injectionDateTime.replace(".000Z", "");
  alertText = alertText + "Injection Time: " + injectionDateTime + "<br>";
  //var eventtime = new Date(rawArray.timestamp).toLocaleTimeString("en-US");
  var eventtime = Number(rawArray.timestamp) + offsetInSeconds;
  eventtime = timeConverter(eventtime);
  alertText = alertText + "Event Time: " + eventtime + "<br>";
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
    if (rawArray.geo_ip)
    {
      if (rawArray.geo_ip.city != "" || rawArray.geo_ip.country != "" || rawArray.geo_ip.region != "")
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
  $.ajax({
    url:'getBodyFromS3.php',
    type: "POST",
    dataType : 'json',  //changed from txt in order to get both the body and the headers
    data: {"UID" : UID},
    complete: function (response) 
    {
      if (response.responseText == "error: The specified key does not exist.")
      {
        var hiddenVersion = alertText + "<div>OUCH - the email body is missing from the repository!!!</div>";
        document.getElementById('hiddenAlertText').innerHTML = hiddenVersion;
        alertText = alertText + "<div style='border:2px solid #666; border-radius:11px; padding:20px;'>OUCH - the email body is missing from the repository!!!</div>";
      }
      else
      {
        var formattedHeaders = formatHeaders(response.responseJSON.headers);
        alertText = alertText + "<button id='displaybutton' class='btn press' onclick='showhide()'>Headers (hide)</button> " + formattedHeaders + "<br>";
        alertText = alertText + "<br><strong>Email Body</strong><p>";
        var hiddenVersion = alertText + "<div style='border:2px solid #666;'><iframe readonly id='form-iframe' style='background-color:#FFFFFF; margin:0; border:none; overflow:hidden; width:1000px;height:100%' scrolling='no' onload='AdjustIframeHeightOnLoad()' src='" + response.responseJSON.tempStorefile + "'</iframe></div>";
        document.getElementById('hiddenAlertText').innerHTML = hiddenVersion;
        alertText = alertText + "<div style='border:2px solid #666; border-radius:11px; padding:20px;'><iframe readonly id='form-iframe' width='100%' style='background-color:#FFFFFF; margin:0; height:100%; border:none; overflow:hidden;' scrolling='no' onload='AdjustIframeHeightOnLoad()' src='" + response.responseJSON.tempStorefile + "'</iframe></div>";
      }
      if (theme=="wide")
      {
        displayDetailandEmailOnly();
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


function formatHeaders(headers)
{
  var tabledHeaders = "<table id='headertable'><tr><td style='padding-left: 15px;'>";
  headers = headers.substr(2);
  headers = headers.substring(0, headers.length-2);
  headers = headers.replace(/},{/g, "</tr></td><tr><td style='padding-left: 15px;'>");
  tabledHeaders = tabledHeaders + headers + "</td></tr></table>";
  return tabledHeaders;
}

function displayDetailandEmailOnly()
{
  
  $.ajax({
      url:'obtainOutputSizing.php',
      type: "GET",
      dataType : 'json',
      complete:function(parameters)
      {
        var width = parameters.responseJSON.width;
        var height = parameters.responseJSON.height;
        var windowParams = "location=1,scrollbars=1,width=" + width + ",height=" + height;
        //var opened = window.open("a", "", "location=1,scrollbars=1,width=1000,height=850");
        var opened = window.open("a", "", windowParams);
        if(!opened || opened.closed || typeof opened.closed=='undefined') 
        { 
          alert("Popup blocker found - cannot display email in new window");
        }
        else
        {
          var seeMe = document.getElementById('hiddenAlertText').innerHTML;
          seeMe = seeMe.replace(/&lt;/g, "<");
          seeMe = seeMe.replace(/&gt;/g, ">");
          opened.document.write(seeMe);
        }
      },
      error: function (parameters) 
      {
        if (response.responseJSON.details != null) $('#servererror').html(response.responseJSON.notes); else $('#servererror').html(response.responseJSON.error);
      }
  });
}

function uncheck(row) 
{     
  var checks = document.getElementsByName('detailcheck');
  for(var i = 0; i < checks.length; ++i)
  {
      checks[i].checked = false;
  }
}


$(document).ready(function() {
  $(this).on("click", ".koh-faq-question", function() {
    $(this).parent().find(".koh-faq-answer").toggle();
    $(this).find(".fa").toggleClass('active');
  });
});

$(function(){
  // From jo-geek https://github.com/Jo-Geek/jQuery-ResizableColumns
$('table.resizable').resizableColumns();
})

if ( $('#eDate')[0].type != 'date' ) $('#eDate').datepicker({ dateFormat: 'yy-mm-dd' });
if ( $('#sDate')[0].type != 'date' ) $('#sDate').datepicker({ dateFormat: 'yy-mm-dd' });

</script>

</body>
</html>