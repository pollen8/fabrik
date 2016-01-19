/**
* jQuery Plugin for the Validation of South African ID Numbers.
*
* Javascript and jQuery 
*
* @category jquery Plugin
* @package  RSA_ID_Validator
* @author   Philip Csaplar <philip@osit.co.za>
* @version  1.2.3
* @link     http://www.VerifyID.co.za
* 
* Copyright (C) 2013  Philip Csaplar <philip@osit.co.za>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

(function($){  
 $.fn.rsa_id_validator = function(options) {  
 
	var defaults = {  
		displayValid: [true,"<font color='#00CC00'>IDNo Valid</font>","<font color='#FF0000'>IDNo InValid</font>"],
		displayDate: [true,"<b>Date of Birth:</b> "],
		displayAge: [true,"<b>Age:</b> "],  
		displayGender: [true,"<b>Gender:</b> Male","<b>Gender:</b> Female"],  
		displayCitizenship: [true,"<b>Citizenship:</b> South African","<b>Citizenship:</b> Foreign Citizenship"],
		displayValid_id: "id_results",
		displayDate_id: "id_results",  
		displayAge_id: "id_results",  
		displayGender_id: "id_results",  
		displayCitizenship_id: "id_results"  
	};  
	var options = $.extend(defaults, options);
  
    return this.each(function() {  
		obj = $(this);
		$(obj).attr('maxlength','13');
		$(obj).keydown(function(event) {
			// Allow: backspace, delete, tab, escape, and enter
			if ( event.keyCode == 46 || event.keyCode == 8 || event.keyCode == 9 || event.keyCode == 27 || event.keyCode == 13 || 
				 // Allow: Ctrl+A
				(event.keyCode == 65 && event.ctrlKey === true) || 
				 // Allow: home, end, left, right
				(event.keyCode >= 35 && event.keyCode <= 39)) {
					 // let it happen, don't do anything
					 return;
			}
			else {
				// Ensure that it is a number and stop the keypress
				if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
					event.preventDefault(); 
				}   
			}
    	});
		
	 	$(obj).bind("keyup", function(){
			
			//error array creation variables
			var displayResults = [[], [], [], [], []];

			//Variable declaration
			var currentTime = new Date();
			var id_number = $(this).val();
			
			
			//display date of birth
			if(options.displayDate[0] == true){ //start if statement
				if (id_number.length > 5){ //start if statement
					var date = "";
					var year      	= id_number.substr ( 0  , 2 );
					var nowYearNotCentury = currentTime.getFullYear() + '';
					nowYearNotCentury = nowYearNotCentury.substr(2,2);
					if (year <= nowYearNotCentury){
						date = '20' + year+ "-" + id_number.substr(2, 2) + "-" + id_number.substr(4, 2);
					} else {
						date = '19' + year+ "-" + id_number.substr(2, 2) + "-" + id_number.substr(4, 2);
					}
					displayResults[1].push(options.displayDate[1] + date);
				}
			}
			
			//display age
			if(options.displayAge[0] == true){ //start if statement
				if (id_number.length > 5){  //start if statement
					var year      	= id_number.substr ( 0  , 2 );
					var nowYearNotCentury = currentTime.getFullYear() + '';
					nowYearNotCentury = nowYearNotCentury.substr(2,2);
					if (year <= nowYearNotCentury){
						year = '20' + year;
					} else {
						year = '19' + year;
					}
					var birthDate = new Date(year);
					var age = currentTime.getFullYear() - birthDate.getFullYear();
					var m = currentTime.getMonth() - birthDate.getMonth();
					if (m < 0 || (m === 0 && currentTime.getDate() < birthDate.getDate())) {
						age--;
					}
					displayResults[2].push(options.displayAge[1] + age);
				}
			}
			
			//display gender
			if(options.displayGender[0] == true){ //start if statement
				if (id_number.length > 9){ //start if statement
					var MaleFemale = id_number.substr(6, 4);
					MaleFemale = (MaleFemale * 1) + 0;
					
					if ((MaleFemale >= 0) & (MaleFemale < 5000)){
						displayResults[3].push(options.displayGender[2]);
					}
					
					if ((MaleFemale > 4999) & (MaleFemale < 10000)){
						displayResults[3].push(options.displayGender[1]);
					}
				}
			}
			
			//display citizenship
			if(options.displayCitizenship[0] == true){ //start if statement
				if (id_number.length > 12){ //start if statement
					var citizen  = (id_number.substr ( 10 , 2 )*1);
					if ((citizen == 8) || (citizen == 9)){
						displayResults[4].push(options.displayCitizenship[1]);
					}	
					else if ((citizen == 18) || (citizen < 19)){
						displayResults[4].push(options.displayCitizenship[2]);
					}
				}
			}
			
			//check validility of IDNO
			if(options.displayValid[0] == true){ //start if statement
				if (id_number.length == 13){ //start if statement
					var testResult = ValidateID(id_number);
					debugger;
					if (testResult[0] == 1){
						displayResults[0].push(options.displayValid[1]);
						options.onSuccess(testResult, displayResults);
					} else {

						options.onFailure(testResult);
						return;
					}
				}
			}
			
			});//end of function
			
			function ValidateID(id_number){
				var sectionTestsSuccessFull = 1;
				var MessageCodeArray 		= [];
				var MessageDescriptionArray = [];
				var currentTime 			= new Date();
				
				/* DO ID LENGTH TEST */
				if (id_number.length == 13){ 
					/* SPLIT ID INTO SECTIONS */
					var year      	= id_number.substr ( 0  , 2 );
					var month     	= id_number.substr ( 2  , 2 );
					var day       	= id_number.substr ( 4  , 2 );
					var gender    	= (id_number.substr ( 6  , 4 )*1);
					var citizen   	= (id_number.substr ( 10 , 2 )*1);
					var check_sum 	= (id_number.substr ( 12 , 1 )*1);
					
					/* DO YEAR TEST */
					var nowYearNotCentury = currentTime.getFullYear() + '';
					nowYearNotCentury = nowYearNotCentury.substr(2,2);
					if (year <= nowYearNotCentury){
						year = '20' + year;
					} else {
						year = '19' + year;
					}
					if ((year > 1900) && (year < currentTime.getFullYear())){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 1;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Year is not valid, ';
					}
					
					/* DO MONTH TEST */
					if ((month > 0) && (month < 13)){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 2;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Month is not valid, ';
					}
					
					/* DO DAY TEST */
					if ((day > 0) && (day < 32)){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 3;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Day is not valid, ';
					}
					
					/* DO DATE TEST */
					if ((month==4 || month==6 || month==9 || month==11) && day==31) {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 4;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Date not valid. This month does not have 31 days, ';
					}
					if (month == 2) { // check for february 29th
						var isleap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));
						if (day > 29 || (day==29 && !isleap)) {
							sectionTestsSuccessFull = 0;
							MessageCodeArray[MessageCodeArray.length] = 5;
							MessageDescriptionArray[MessageDescriptionArray.length] = 'Date not valid. February does not have ' + day + ' days for year ' + year +', ';
						}
					}
					
					/* DO GENDER TEST */
					if ((gender >= 0) && (gender < 10000)){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 6;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Gender is not valid, ';
					}
					
					/* DO CITIZEN TEST */
					//08 or 09 SA citizen
					//18 or 19 Not SA citizen but with residence permit
					if ((citizen == 8) || (citizen == 9) || (citizen == 18) || (citizen == 19)){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 7;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Citizen value is not valid, ';
					}
					
					/* GET CHECKSUM VALUE */
					var check_sum_odd         = 0;
					var check_sum_even        = 0;
					var check_sum_even_temp   = "";
					var check_sum_value       = 0;
					var count = 0;
					// Get ODD Value
					for( count = 0 ; count < 11 ; count += 2 ){
						check_sum_odd += (id_number.substr ( count , 1 )*1);
					}//end for
					// Get EVEN Value
					for( count = 0 ; count < 12 ; count += 2 ){
						check_sum_even_temp = check_sum_even_temp + (id_number.substr ( count+1 , 1 )) + '';
					}//end for
					check_sum_even_temp = check_sum_even_temp * 2;
					check_sum_even_temp = check_sum_even_temp + '';
					for( count = 0 ; count < check_sum_even_temp.length ; count++ ){
						check_sum_even += (check_sum_even_temp.substr( count , 1 ))*1;
					}//end for
					// GET Checksum Value
					check_sum_value = (check_sum_odd*1) + (check_sum_even*1);
					check_sum_value = check_sum_value + 'xxx';
					check_sum_value = ( 10 - (check_sum_value.substr( 1 , 1 )*1) );
					if(check_sum_value == 10)
						check_sum_value = 0;
					
					/* DO CHECKSUM TEST */
					if(check_sum_value == check_sum){
						//correct
					} else {
						sectionTestsSuccessFull = 0;
						MessageCodeArray[MessageCodeArray.length] = 8;
						MessageDescriptionArray[MessageDescriptionArray.length] = 'Checksum is not valid, ';
					}
					
				} else {
					sectionTestsSuccessFull = 0;
					MessageCodeArray[MessageCodeArray.length] = 0;
					MessageDescriptionArray[MessageDescriptionArray.length] = 'IDNo is not the right length, ';
				}
				
				var returnArray = [ sectionTestsSuccessFull, MessageCodeArray, MessageDescriptionArray];
				return returnArray;
			}
	});

 };  
})(jQuery); 