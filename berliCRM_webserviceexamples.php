<?php
//====================================================================================================+
// File name   : berliCRM_webserviceexamples.php
// Begin       : 2014-01-02
// Last Update : 2018-02-25
// Author      : Alexander Krawczyk - info@crm-now.de - http://www.crm-now.de
// Version     : 2.0.0
// License     : GNU LGPL (http://www.gnu.org/copyleft/lesser.html)
//-----------------------------------------------------------------------------------------------------
//  Copyright (C) 2004-2018  crm-now GmbH
//
// 	This program is free software: you can redistribute it and/or modify
// 	it under the terms of the GNU Lesser General Public License as published by
// 	the Free Software Foundation, either version 2.1 of the License, or
// 	(at your option) any later version.
//
// 	This program is distributed in the hope that it will be useful,
// 	but WITHOUT ANY WARRANTY; without even the implied warranty of
// 	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// 	GNU Lesser General Public License for more details.
//
// 	You should have received a copy of the GNU Lesser General Public License
// 	along with this program.  If not, see <http://www.gnu.org/licenses/>.
//
// 	See LICENSE.TXT file for more information.
//-----------------------------------------------------------------------------------------------------
//
// Description : These are examples for API operation using a special PHP class for berliCRM communications
//
//
// Main features:
//  * examples for the most frequently used CRM API operations;
//
//-----------------------------------------------------------------------------------------------------
// Contributors:
//
//====================================================================================================+
//========== START EXAMPLES ==========================================================================+
//
//
//========== Definitions =============================================================================+
require_once("berliCRM_WS_Curl_Class20.php");
//
// provide user's credentials
//
// CRM URL
$url = "https://vtiger.talecsystem.com/webservice.php";
//
// CRM user (CRM login name)
$username = "admin";
//
// CRM user's access key (from My preferences menu)
$accessKey = "Aw3535$$";
//
//========== EXAMPLE 1: Login Start ==================================================================+
//
// create web service object
$wsC = new WS_Curl_Class($url, $username, $accessKey);
//
// Login
// the Login procedure requires two API operations which are exercised by the Curl Class in one step
// step 1: get a token (session id)
// step 2: use token for login
if (!$wsC->login()) {
	// ERROR handling if Login was not successful
	echo $wsC->errorMsg;
}
//========== EXAMPLE 1: Login END ==================================================================+
//
//========== EXAMPLE 2: listTypes Start ============================================================+
// prerequisite: you are logged in and the session is still valid
//
// call listTypes API operation
$listTypes = $wsC->operation("listTypes", array(), "GET");
if (!$listTypes) {
	// ERROR handling if listTypes operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "<b>Modules which can be reached by web services with current user</b><br>";
	echo "<table border='1'>";
		foreach ($listTypes['types'] as $value){
			echo "<tr>";
				echo "<td>".$value."</td>";
			echo "</tr>";
		}
	echo "</table></br>";
}
//========== EXAMPLE 2: listTypes End ==============================================================+
//
//========== EXAMPLE 3: describe Start =============================================================+
// prerequisite: you are logged in and the session is still valid
//
// list the properties of a specific module
// use Contacts module
$describe = $wsC->operation("describe", array("elementType" => "Contacts"), "GET");

if (!$describe) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "<b>Contacts Fields:</b><br>";
	echo "<table border='1'>";
		echo "<tr>";
			echo "<th>Field Label</th>";
			echo "<th>CRM Field Name</th>";
		echo "</tr>";
		foreach ($describe['fields'] as $pkey => $value){
			//show field properties
			if ($pkey == 'fields') {
				foreach ($describe['fields'] as $fkey => $fvalue){
					echo "<tr>";
						echo "<td>".$fvalue['label']."</td>";
						echo "<td>".$fvalue['name']."</td>";
					echo "</tr>";
				}
			}
		}
	echo "</table></br>";
}
//========== EXAMPLE 3: describe End ===============================================================+
//
//========== EXAMPLE 4: query Start ================================================================+
// prerequisite: you are logged in and the session is still valid
//
// get contact id information from one Contact
// shows use of limit
$first_contact_id ='';
$Contact_Details = $wsC->operation("query", array("query" => "SELECT * FROM Contacts limit 1;"), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	//keep Contact ID for next example 5
	$first_contact_id = $Contact_Details[0]['id'];
	echo "contact id: ".$first_contact_id."<br>";
}

//========== EXAMPLE 4: query End ==================================================================+
//
//========== EXAMPLE 5: retrieve Start =============================================================+
// prerequisite: you are logged in and the session is still valid
//
// get information from same Contact as found in example 4
if ($first_contact_id !='') {
	$Contact_Details = $wsC->operation("retrieve", array("id" => $first_contact_id), "GET");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		echo "First Name: ".$Contact_Details['firstname']."<br>";
		echo "Last Name: ".$Contact_Details['lastname']."<br>";
		echo "Related Account ID: ".$Contact_Details['account_id']."<br>";
	}
}
//========== EXAMPLE 5: retrieve End ===============================================================+
//
//========== EXAMPLE 6: update Start ===============================================================+
// prerequisite: you are logged in and the session is still valid
//
// update Salutation for the same Contact as found in example 4
if ($first_contact_id !='') {
	$Contact_Details = $wsC->operation("retrieve", array("id" => $first_contact_id), "GET");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		// change field contents for existing data entry
		$Contact_Details['salutationtype'] = 'Dear';
		// update existing data entry
		$result = $wsC->operation("update", array("element" => json_encode($Contact_Details)), "POST");
		if ($wsC->errorMsg) {
			// ERROR handling if describe operation was not successful
			echo $wsC->errorMsg;
		}
		else {
			// check the update result
			echo "Salutation: ".$result['salutationtype']."<br>";
		}
	}
}
//========== EXAMPLE 6: update End =================================================================+
//
//========== EXAMPLE 7: loop query Start ===========================================================+
// prerequisite: you are logged in and the session is still valid
//
// get a list of all contacts
// shows use of count, limit and offset
$Contact_Count = $wsC->operation("query", array("query" => "SELECT count(*) FROM Contacts;"), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	$number_of_contacts = $Contact_Count[0]['count'];
	echo "Contacts Count: ".$number_of_contacts."<br>";
	//if there are more than 100 entries we have to loop to get all contacts
	$num_loop_pages = ceil($number_of_contacts / 100);
	for ($i = 0; $i < $num_loop_pages; ++$i) {
		$offset = $i*100;
		$wsquery = "SELECT * FROM Contacts limit ".$offset.", 100;";
		$Contact_List[$i] = $wsC->operation("query", array("query" => $wsquery), "GET");
		if ($wsC->errorMsg) {
			// ERROR handling if query operation was not successful
			echo $wsC->errorMsg;
		}
	}
	//show table entries for every Contact
	echo "<b>List of Contacts</b><br>";
		echo "<table border='1'>";
			echo "<tr>";
				echo "<th>Contact NO</th>";
				echo "<th>Last Name</th>";
				echo "<th>First Name</th>";
			echo "</tr>";
		foreach ($Contact_List as $valueblocks){
			foreach ($valueblocks as $value){
				echo "<tr>";
					echo "<td>".$value["contact_no"]."</td>";
					echo "<td>".$value["lastname"]."</td>";
					echo "<td>".$value["firstname"]."</td>";
				echo "</tr>";
			}
		}
		echo "</table></br>";

}
//========== EXAMPLE 7: loop query End =============================================================+
//
//========== EXAMPLE 8: create Contacts from file Start ============================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: UTF8 coded CSV file with Contacts exists (for more samples csv file see CRM's help menu)
//
// "Dear","Björn","Mustermann","b.mustermann@mustergmbh.de","(030) 111-1222","Muster GmbH"
// "Dear","Joe","Brown","jb@mustergmbh.de","(030) 111-1222","Sample Inc."
// "Dear","Bettina","Muster","b.muster@musterag.com","(0425) 123-4356","Sample Inc."
//
// prerequisite: CRM user with id 19x1 exists
// prerequisite: Accounts entries already exist
//
// get data from CSV file
$filename = 'contact_import_data.csv';
if (file_exists($filename)) {
	$handle = fopen ($filename,"r");
	while ( ($importdata = fgetcsv ($handle, 15000, ",")) !== FALSE ) {
		set_time_limit(0);
		$salutation = $importdata[0];
		$firstname = trim($importdata[1]);
		$lastname = trim($importdata[2]);
		if (empty($lastname)) {
			$lastname = 'unknown';
		}
		$email = trim($importdata[3]);
		$phone = trim($importdata[4]);
		$companyname = trim($importdata[5]);

		$type = 'Contacts';
		$element = array(
			'salutationtype'=>$salutation,
			'lastname'=>$lastname,
			'firstname'=>$firstname,
			'email'=>$email,
			'phone'=>$phone,
			//assign to user admin, groups would have the prefix 20
			'assigned_user_id'=> '19x1',
		);
		if (!empty($companyname)) {
			//get the matching Account
			$wsqueryACC = "SELECT id FROM Accounts where accountname = '".$companyname."';";
			$account_Details = $wsC->operation("query", array("query" => $wsqueryACC ), "GET");
			if ($wsC->errorMsg) {
				// ERROR handling if describe operation was not successful
				echo $wsC->errorMsg;
			}
			$matching_account_id=$account_Details[0]['id'];
			//add related Account to Contact data
			$element['account_id'] = $matching_account_id;
		}
		$result = $wsC->operation("create", array("elementType" => $type, "element" => json_encode($element)), "POST");
		if ($wsC->errorMsg) {
			// ERROR handling if describe operation was not successful
			echo $wsC->errorMsg;
		}
	}
	fclose ($handle);
}

//========== EXAMPLE 8:  create Contacts from file End =============================================+
//
//========== EXAMPLE 9: get Event and Tasks ========================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: CRM calendar entries as task and event exists
//
// get first Event entry
$result_event = $wsC->operation("query", array("query" => "SELECT * FROM Events LIMIT 1;"), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "Event Title: ".$result_event[0]['subject']."<br>";
}
// get first Task entry
$result_task = $wsC->operation("query", array("query" => "SELECT * FROM Calendar LIMIT 1;"), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "Task Title: ".$result_task[0]['subject']."<br>";
}
//========== EXAMPLE 9:  create Contacts from file End =============================================+

//========== EXAMPLE 10: create product Start ======================================================+
// prerequisite: you are logged in and the session is still valid
//
// create product entry
$element = array (
	"productname" => "Sample Product 1",
	"productcode" => "0815",
	"qty_per_unit" => '1.00',
	"qtyindemand" => '0',
	"qtyinstock" => '1000',
	"discontinued" =>  '1',
	"notecontent" => "WS Upload",
	"manufacturer" => 'Sample Inc.' ,
	'reorderlevel'=> '0',
	'unit_price'=> '90.000',
	'usageunit'=> 'Box',
	'commissionrate'=> '30.000',
    'description' => 'my product description for quotes, orders and invoices',
	"assigned_user_id" => "19x1"
);
$result = $wsC->operation("create", array("elementType" => "Products", "element" => json_encode($element)), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
//========== EXAMPLE 10: create product  End =======================================================+
//
//========== EXAMPLE 11: create and upload Document file Start =====================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a file named 'sampletext.txt' to upload
// prerequisite: you have a CRM user with id '19x1'
//
// define document
// get a document folder
$Document_Folder = $wsC->operation("query", array("query" => "SELECT * FROM DocumentFolders limit 1;"), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	$document_folder_id = $Document_Folder[0]['id'];
	$file_path = 'sampletext.txt';
	$document_properties = array (
		"notes_title" => "Web Service Document 2",
		"filelocationtype" => "I",
		"filestatus" => 1,
		"folderid" => $document_folder_id,
		"notecontent" => "WS Upload",
		"filename" => $file_path ,
		"filetype" => "plain/text",
		'filesize'=>filesize ($file_path ),
		"assigned_user_id" => "19x1"
	);

	$result = $wsC->operation("create", array("elementType" => "Documents", "element" => json_encode($document_properties)), "POST", $file_path);
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		$document_id = $result['id'];
		echo "Document ID: ".$document_id."<br>";
	}
}
//========== EXAMPLE 11: upload Document End ========================================================+
//
//========== EXAMPLE 12: retrieve related document entries Start ====================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a Document with id '15x636' which is related to other entries
//
$document_ids = array('15x636');
$result = $wsC->operation("get_document_relations", array("docids" => json_encode($document_ids)), "GET");
if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
else {
	$existing_relations = $result['found'];
	foreach ($existing_relations as $moduleName => $moduleDetails) {
		$entity_id = array_keys($moduleDetails);
		echo "Module: ".$moduleName." with ID: ".$entity_id['0']."<br>";
	}
}
//========== EXAMPLE 12: retrieve related document entries End ======================================+
//
//========== EXAMPLE 13: relate document to other entries Start =====================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a Document with id '15x636'
// prerequisite: you have a Quote with id '4x207'
// prerequisite: you have a Account with id '11x645'
//
// relate a documents to Quote and Account
$document_id = '15x636';
$relids = '4x207,11x645';
$result = $wsC->operation("update_document_relations", array("docid" => $document_id,"relids" => $relids,"preserve" => true), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "added Relation IDs: ".$result['relids']."<br>";
}
//========== EXAMPLE 13: relate document to other entries  End ======================================+
//
//========== EXAMPLE 14: retrieve Document file Start ===============================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a document id
// prerequisite: there no previous header output
//
// download with browser
if ($document_id !='') {
	$Document_Details = $wsC->operation("retrieve", array("id" => $document_id), "GET");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		$retrievedocattachment = $wsC->operation("retrievedocattachment", array("id" => $document_id, "returnfile" =>true), "GET");
		if ($wsC->errorMsg) {
			// ERROR handling if describe operation was not successful
			echo $wsC->errorMsg;
		}
		else {
			// fix for IE catching or PHP bug issue
			header("Pragma: public");
			header("Expires: 0"); // set expiration time
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");

			// force download dialog
			header("Content-Type: application/force-download");
			header("Content-Type: application/download");

			// use the Content-Disposition header to supply a recommended filename and
			// force the browser to display the save dialog.
			header('Content-Disposition: attachment; filename="'.$retrievedocattachment[$document_id]['filename'].'"');

			/*
			The Content-transfer-encoding header should be binary, since the file will be read
			directly from the disk and the raw bytes passed to the downloading computer.
			The Content-length header is useful to set for downloads. The browser will be able to
			show a progress meter as a file downloads. The content-length can be determines by
			filesize function returns the size of a file.
			*/
			header("Content-Transfer-Encoding: binary");
			echo base64_decode($retrievedocattachment[$document_id]['attachment']);
		}
	}
}
//========== EXAMPLE 14: retrieve Document End ======================================================+
//
//========== EXAMPLE 15: create Sales Order Start ===================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have an Account with ID '11x645'
// prerequisite: you have Products with ID '14x11'
// prerequisite: you have Service with ID '25x13'
// prerequisite: you have a CRM user with id '19x1'
//
$accountId = '11x645';
$product_1_id = '14x11';
$service_1_id = '25x13';
$CRM_user_id = '19x1';
//$result = $wsC->operation("retrieve", array("id" => "6x17"), "GET");

$element =  array (
	'assigned_user_id' => $CRM_user_id,
	'subject' => 'REST salesOrderSubject 6',
	'bill_city' => 'Drachten',
	'bill_code' => '9205BB',
	'bill_country' => 'Netherland',
	'bill_pobox' => '',
	'bill_state' => '',
	'bill_street' => 'schuur 86',
	'carrier' => NULL,
	'contact_id' => NULL,
	'conversion_rate' => '1.000',
	'currency_id' => '21x1',
	'customerno' => NULL,
	'description' => 'Producten in deze verkooporder: 2 X Heart of David - songbook 2',
	'duedate' => '2018-11-06',
	'enable_recurring' => '0',
	'end_period' => NULL,
	'exciseduty' => '0.000',
	'invoicestatus' => 'Approved',
	'payment_duration' => NULL,
	'pending' => NULL,
	'potential_id' => NULL,
	'vtiger_purchaseorder' => NULL,
	'quote_id' => NULL,
	'recurring_frequency' => NULL,
	'salescommission' => '0.000',
	'ship_city' => 'schuur 86',
	'ship_code' => '9205BB',
	'ship_country' => 'Netherlands',
	'ship_pobox' => NULL,
	'ship_state' => NULL,
	'ship_street' => 'Drachten',
	'account_id' => $accountId,
	'sostatus' => 'Approved',
	'start_period' => NULL,
	'salesorder_no' => NULL,
	'terms_conditions' => 'The payment is expected within 30 days.',
	'discount_type_final' => 'percentage',  //  zero/amount/percentage
	'hdnDiscountAmount' => '0.000',  // only used if 'discount_type_final' == 'amount'
	'hdnDiscountPercent' => '20.000',  // only used if 'discount_type_final' == 'percentage'
    'pre_tax_total' => '1000.00',
	'txtAdjustment' => '25.00',
    'hdnS_H_Amount' => '20.00',
    'hdnS_H_Percent' => '19',
	'shipping_handling_charge' => 0,
	'shtax1' => 0,   // apply this tax, MUST exist in the application with this internal taxname
	'shtax2' => 0,   // apply this tax, MUST exist in the application with this internal taxname
	'shtax3' => 0,   // apply this tax, MUST exist in the application with this internal taxname
	'adjustmentType' => 'add',  //  none/add/deduct
	'hdnTaxType' => 'group', // group or individual  taxes are obtained from the application
	'LineItems' => Array(
		'0' => Array (
                    'productid' => $product_1_id,
                    'sequence_no' => '1',
                    'quantity' => '1.000',
                    'listprice' => '500.00',
                    'discount_percent' => null,
                    'discount_amount' => null,
                    'comment' => 'sample comment product',
                    'description' => 'product description',
                    'incrementondel' => '0',
                    'tax1' => '19.00',
                    'tax2' => '0.00',
                    'tax3' => '0.00'
               ),

        '1' => Array (
                    'productid' => $service_1_id,
					'sequence_no' => '2',
                    'quantity' => '1.000',
                    'listprice' => '500.00',
                    'discount_percent' => null,
                    'discount_amount' => null,
                    'comment' => 'sample comment services',
                    'description' => 'service description',
                    'incrementondel' => '0',
                    'tax1' => '19.00',
                    'tax2' => '0.00',
                    'tax3' => '0.00'
                )
	)
);
	//name of the module for which the entry has to be created.
	$type = 'SalesOrder';
	$result = $wsC->operation("create", array("elementType" => $type, "element" => json_encode($element)), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
//========== EXAMPLE 15: create Sales Order End =====================================================+
//
//========== EXAMPLE 16: retrieve Campaign related entities Start ===================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a Campaign with id '1x89'
// prerequisite: there are Contacts, Leads or Accounts related to campaign
//
// relate a documents to Quote and Account
$campaign_id = '1x89';
$result = $wsC->operation("get_campaign_entities", array("campaignid" => $campaign_id,"returnresults" => null), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	foreach ($result as $moduleName => $idString) {
		echo "Module: ".$moduleName." with IDs: ".$idString."<br>";
	}
}
//========== EXAMPLE 16: retrieve Campaign related entities  End ====================================+
//
//========== EXAMPLE 17: sync Start =================================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a modified or deleted an entry in the past
//
date_default_timezone_set("Europe/Helsinki");
echo "Europe/Helsinki:".time()."<br>";
$unixtime = strtotime('-100 days');

$result = $wsC->operation("sync", array("modifiedTime" => $unixtime ,"elementType" => 'Accounts'), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	printf("This debug is in %s on line %d\n  for result1",__FILE__, __LINE__);
	print_r("<PRE>");
	print_r($result);
	print_r("</PRE>");
}
//========== EXAMPLE 17: sync End ===================================================================+
//
//========== EXAMPLE 18: revise Start ===============================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a Contact with id '12x4'
//
// revise description field
$element = array (
	'id' => '12x4',
	'description' => 'My new description',
);
$element = 	json_encode($element);
$revice_result = $wsC->operation("revise", array("element" =>$element ), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	printf("This debug is in %s on line %d\n  for revice_result",__FILE__, __LINE__);
	print_r("<PRE>");
	print_r($revice_result);
	print_r("</PRE>");
}
//========== EXAMPLE 18: revise End =================================================================+
//
//========== EXAMPLE 19: convert Lead Start =========================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a Lead with id '10x36'
//
// create Potential with related Contact and Account
$lead_id = '10x36';
$Lead_Details = $wsC->operation("retrieve", array("id" => $lead_id), "GET");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
    $element['leadId'] = $Lead_Details['id'];
	// if you do not assign a user id, the user id of the logged in user is used
    $element['assignedTo'] = '';  //$Lead_Details['assigned_user_id'];
	//provide information for Account creation
    $element['entities']['Accounts']['create']=true;
    $element['entities']['Accounts']['name']='Accounts';
    $element['entities']['Accounts']['accountname'] = $Lead_Details['company'];
	$element['entities']['Accounts']['industry']=$Lead_Details['industry'];
 	//provide information for Potentials creation
    $element['entities']['Potentials']['create']=true;
    $element['entities']['Potentials']['name']='Potentials';
    $element['entities']['Potentials']['potentialname']=$Lead_Details['company'].'-Potential';
    $element['entities']['Potentials']['closingdate']= date("Y-m-d", strtotime("+1 week Saturday"));
    $element['entities']['Potentials']['sales_stage']= 'Prospecting';
    $element['entities']['Potentials']['amount']= 0;
  	//provide information for Contacts creation
	$element['entities']['Contacts']['create']=true;
	$element['entities']['Contacts']['name']='Contacts';
	$element['entities']['Contacts']['lastname']=$Lead_Details['lastname'];
	$element['entities']['Contacts']['firstname']=$Lead_Details['firstname'];
	$element['entities']['Contacts']['email']=$Lead_Details['email'];

	$element_json = json_encode($element);

	$result = $wsC->operation("convertlead", array("element" =>$element_json ), "POST");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		printf("This debug is in %s on line %d\n  for result",__FILE__, __LINE__);
		print_r("<PRE>");
		print_r($result);
		print_r("</PRE>");
	}

 }
 //========== EXAMPLE 19: convert Lead End ==========================================================+
//
//========== EXAMPLE 20: changePassword Start =======================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have a your current password
// prerequisite: you have a a user with id '19x5'
//
// revise description field
$user_id = '19x5';
$oldPassword ='<your current PW>';
$newPassword = '<your new PW>';
$result = $wsC->operation("changePassword", array("id" => $user_id,'oldPassword' =>$oldPassword, 'newPassword' =>$newPassword, 'confirmPassword' =>$newPassword), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	echo "Message: ".$result['message']."<br>";
}
//========== EXAMPLE 20: changePassword End =========================================================+
//
//========== EXAMPLE 21: delete Start ===============================================================+
// prerequisite: you are logged in and the session is still valid
// prerequisite: you have an id of the entry to delete
// prerequisite: the logged in user has delete privileges
//
// delete Contact as found in example 4
if ($first_contact_id !='') {
	$result = $wsC->operation("delete", array("id" => $first_contact_id), "POST");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
		echo "Message: ".$result['message']."<br>";
	}
}
//========== EXAMPLE 21: delete End =================================================================+
//
//========== EXAMPLE 22: logout Start ===============================================================+
// prerequisite: you are logged in and the session is still valid
//
$result = $wsC->operation("logout", array(), "POST");
if ($wsC->errorMsg) {
	// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
//========== EXAMPLE 22: logout End =================================================================+
//
//========== EXAMPLE 23: get multi relations Start ===============================================================+
// prerequisite: you are logged in and the session is still valid
// Parameters:
// -string id: is a CRM entity id obtained by webservice call to entity
// Returns:
// - array('relids'=>list of relids)

// get Relations for an existing product
$product_id = '6x42798';
$result = $wsC->operation("get_multi_relations", array("id" => $product_id), "GET");
if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
	echo $wsC->errorMsg;
}
else {
	printf("This debug is in %s on line %d\n  for result",__FILE__, __LINE__);
	print_r("<PRE>");
	print_r($result);
	print_r("</PRE>");
}
//========== EXAMPLE 23: get multi relations  End =================================================================+
//
//========== EXAMPLE 24: update product relations Start ===============================================================+
// Parameters:
// -string productid: is the product id obtained by webservice call to product entity
// -string relids: is a JSON encoded string from array that contains the webservice ids the product will be relate to
// -string preserve: is a var that dictates the deletion behavior
// Returns:
// - array('relids'=>list of relids as provided)

// set Relations for an existing product
$product_id = '6x42798';
$rel_ids = array('4x41656,4x42160');

if ($product_id !='') {
	$result = $wsC->operation("update_product_relations", array("productid" => $product_id, "relids" => json_encode($rel_ids)), "POST");
	if ($wsC->errorMsg) {
		// ERROR handling if describe operation was not successful
		echo $wsC->errorMsg;
	}
	else {
printf("This debug is in %s on line %d\n  for result",__FILE__, __LINE__);
print_r("<PRE>");
print_r($result);
print_r("</PRE>");

	}
}
//========== EXAMPLE 23: get multi relations  End =================================================================+

?>
