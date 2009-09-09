<?php

class family_planning extends module{

	function family_planning(){ // class constructor
		$this->author = "darth_ali";
		$this->version = "0.1-".date("Y-m-d");
		$this->module = "family_planning";
		$this->description = "CHITS Module - Family Planning";
		
		//0.1 - created data entry forms for the family service record
		/* mechanics for family planning: 
			1. Each FP patient has tp fill out the Family Planning service record (Medical HX, Physical Examination, Obstetrical History,
			   Pelvic Examination. One FP patient = 1 family planning service record regardless of how many methods he/she has enrolled
			2. FP patient will be enrolled for a particular program. Female - 15 to 49, Male - Vasectomy or Condom
			3. If a patient is new to the method classify him or her to as NEW ACCEPTOR and CURRENT USER
			4. A patient is considered as dropout if: 1). conditions for being dropout based on the methods are applied, 2). the patient decided to
			   be drop out on purpose based on the conditions
		        5. If a patient re-applies again:
		            a. same method before drop out - RESTART , CURRENT USER (i.e. pills-drop out-pills)
		            b. different method before the drop out
		               i. if patient is already a previous user - CURRENT USER, CHANGE METHOD (i.e. dmpa-drop out-pills-drop out-dmpa)
		               ii. if patient chooses a new method - CURRENT USER, CHANGE METHOD, NEW ACCEPTOR (i.e. pills-drop out-dmpa)		
		        
		*/
		
		
	}

	//standard module functions 

	function init_deps(){
	    module::set_dep($this->module, "module");
	    module::set_dep($this->module, "healthcenter");
	    module::set_dep($this->module, "patient");
	}

	function init_lang(){
		module::set_lang("THEAD_FP_HEADER", "english", "FAMILY PLANNING SERVICE RECORD", "Y");
	}

	function init_stats(){

	}

	function init_help(){

	}

	function init_menu(){
		if(func_num_args()>0){
			$arg_list = func_get_args();
		}
	}

	function init_sql(){

		if(func_num_args()>0){
			$arg_list = func_get_args();
		}
		

		//m_lib_fp_methods -- create
		module::execsql("CREATE TABLE `m_lib_fp_methods` (".
			      "`method_id` varchar(10) NOT NULL default '',".
      			      "`method_name` varchar(100) NOT NULL default '',".
			      "`method_gender` SET('M','F') NOT NULL default '',".
			      "`fhsis_code` varchar(20) NOT NULL default '',".
			      "PRIMARY KEY (`method_id`)".
			      ") TYPE=MyISAM; ");

		//m_lib_fp_methods -- populate contents
	
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('PILLS', 'Pills','F','PILLS')");	    	
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('CONDOM', 'Condom','M','CON')");
	        module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('IUD', 'IUD','F','IUD')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('NFPLAM', 'NFP Lactational amenorrhea','F','NFP-LAM')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('DMPA', 'Depo-Lactational Amenorrhea ','F','DMPA')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('NFPBBT', 'NFP Basal Body Temperature','F','NFP-BBT')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('NFPCM', 'NFP Cervical Mucus Method','F','NFP-CM')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('NFPSTM', 'NFP Sympothermal Method','F','NFP-STM')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('NFPSDM', 'NFP Standard Days Method','F','NFP-SDM')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('FSTRBTL', 'Female Sterilization /Bilateral Tubal Ligation','F','FSTR/BTL')");
		module::execsql("INSERT INTO `m_lib_fp_methods` (`method_id`,`method_name`,`method_gender`,`fhsis_code`) VALUES ('MSV', 'Male Sterilization /Vasectomy','M','MSTR/Vasec')");

		//create library for medical history category of family planning
		module::execsql("CREATE TABLE `m_lib_fp_history_cat` (".
				"`cat_id` varchar(10) NOT NULL default '',".
				"`cat_name` varchar(50) NOT NULL default '',".
				"PRIMARY KEY (`cat_id`)".
				") TYPE=MyISAM; ");


		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('HEENT', 'HEENT')");
	    	module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('CXHRT', 'CHEST/HEART')");
    		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('ABD', 'ABDOMEN')");
    		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('GEN', 'GENITAL')");
    		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('EXT', 'EXTREMITIES')");
    		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('SKIN', 'SKIN')");
    		module::execsql("INSERT INTO `m_lib_fp_history_cat` (`cat_id`, `cat_name`) VALUES ('ANY', 'HISTORY OF ANY OF THE FOLLOWING')");



	//create table for fp medical history items
		module::execsql("CREATE TABLE `m_lib_fp_history` (".
		      "`history_id` varchar(10) NOT NULL default '',".
		      "`history_text` varchar(100) NOT NULL default '',".
		      "`history_cat` varchar(15) NOT NULL default '',".
		      "PRIMARY KEY (`history_id`)".
		      ") TYPE=MyISAM; ");

	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('EPILEPSY', 'Epilepsy/Convulsion/Seizure', 'HEENT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('HEADACHE', 'Severe headache/dizziness', 'HEENT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('VISION', 'Visual disturbance/blurring of vision', 'HEENT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('YCONJ', 'Yellowish conjunctive', 'HEENT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('ETHY', 'Enlarged thyroid', 'HEENT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('CXPAIN', 'Severe chest pain', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('FATIGUE', 'Shortness of breath and easy fatiguability', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('BRSTMASS', 'Breast/axillary masses', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('NIPBLOOD', 'Nipple discharges (blood)', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('NIPPUS', 'Nipple discharges (pus)', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('SYS140', 'Systolic of 140 & above', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('DIAS90', 'Diastolic of 90 & above', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('CVAHARHD', 'Family history of CVA (strokes), hypertension, asthma, rheumatic heart disease', 'CXHRT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('MASSABD', 'Mass in the abdomen', 'ABD')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('GALL', 'History of gallbladder disease', 'ABD')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('LIVER', 'History of liver disease', 'ABD')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('UTERUS', 'Mass in the uterus', 'GEN')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('VAGDISCH', 'Vaginal discharge', 'GEN')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('INTERBLEED', 'Intermenstrual bleeding', 'GEN')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('POSTBLEED', 'Postcoital bleeding', 'GEN')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('VARICOSE', 'Severe varicosities', 'EXT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('LEGPAIN', 'Swelling or severe pain in the legs not related to injuries', 'EXT')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('YELLOWSKIN', 'Yellowish skin', 'SKIN')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('SMOKING', 'Smoking', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('ALLERGY', 'Allergies', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('DRUGINTAKE', 'Drug intake (anti-TB, anti-diabetic, anticonvulsant)', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('STD', 'STD', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('MPARTNERS', 'Multiple partners', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('BLEEDING', 'Bleeding tendencies (nose, gums, etc.)', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('ANEMIA', 'Anemia', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('DIABETES', 'Diabetes', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('HMOLE', 'Hydatidiform mole (w/in the last 12 mos.)', 'ANY')");
	    module::execsql("INSERT INTO `m_lib_fp_history` (`history_id`, `history_text`, `history_cat`) VALUES ('ECTPREG', 'Ectopic pregnancy', 'ANY')");

		//table for fp PE category
		module::execsql("CREATE TABLE `m_lib_fp_pe_cat` (`pe_cat_id` VARCHAR( 20 ) NOT NULL ,`pe_cat_name` VARCHAR( 50 ) NOT NULL , PRIMARY KEY ( `pe_cat_id` )
) ENGINE = MYISAM ");

		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('CONJUNCTIVA','CONJUNCTIVA')");
		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('NECK','NECK')");
		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('BREAST','BREAST')");
		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('THORAX','THORAX')");
		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('ABDOMEN','ABDOMEN')");
		module::execsql("INSERT INTO `m_lib_fp_pe_cat` (`pe_cat_id`,`pe_cat_name`) VALUES ('EXTREMITIES','EXTREMITIES')");

		//table for fp PE items
		module::execsql(" CREATE TABLE `m_lib_fp_pe` (
				`pe_id` INT( 5 ) NOT NULL AUTO_INCREMENT ,
				`pe_name` VARCHAR( 100 ) NOT NULL ,
				`pe_cat` VARCHAR( 20 ) NOT NULL ,PRIMARY KEY ( `pe_id` )
				) ENGINE = MYISAM ");

		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Pale',`pe_cat`='CONJUNCTIVA'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Yellowish',`pe_cat`='CONJUNCTIVA'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Enlarged Thyroid',`pe_cat`='NECK'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Enlarged Lymph Nodes',`pe_cat`='NECK'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Mass',`pe_cat`='BREAST'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Nipple Discharge',`pe_cat`='BREAST'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Skin-orange-peel or dimpling',`pe_cat`='BREAST'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Enlarged Axillary Lymph Nodes',`pe_cat`='BREAST'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Abnormal Heart Sounds/Cardiac Rate',`pe_cat`='THORAX'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Abnormal Breath Sounds/Respiratory Rate',`pe_cat`='THORAX'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Enlarge Liver',`pe_cat`='ABDOMEN'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Mass',`pe_cat`='ABDOMEN'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Tenderness',`pe_cat`='ABDOMEN'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Edema',`pe_cat`='EXTREMITIES'");
		module::execsql("INSERT INTO `m_lib_fp_pe` SET `pe_name`='Varicosities',`pe_cat`='EXTREMITIES'");


		//table for pelvic PE exam categories
		module::execsql(" CREATE TABLE `m_lib_fp_pelvic_cat` (
				`pelvic_cat_id` VARCHAR( 20 ) NOT NULL ,
				`pelvic_cat_name` VARCHAR( 50 ) NOT NULL ,PRIMARY KEY 					( `pelvic_cat_id` )) ENGINE = MYISAM ");


		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('PERENIUM','PERENIUM')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('VAGINA','VAGINA')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('CERVIX','CERVIX')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('CERVIXCOLOR','Color')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('CERVIXCONSISTENCY','Consistency')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('UTERUSPOS','UTERUS POSITION')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('UTERUSSIZE','UTERUS SIZE')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('UTERUSMASS','UTERUS MASS')");
		module::execsql("INSERT INTO `m_lib_fp_pelvic_cat` (`pelvic_cat_id`,`pelvic_cat_name`) VALUES ('ADNEXA','ADNEXA')");

		//table for FP pelvic PE exam items
		module::execsql(" CREATE TABLE `m_lib_fp_pelvic` (
				`pelvic_id` INT( 5 ) NOT NULL AUTO_INCREMENT ,
				`pelvic_name` VARCHAR( 50 ) NOT NULL ,
				`pelvic_cat` VARCHAR( 50 ) NOT NULL ,PRIMARY KEY ( `pelvic_id` )) ENGINE = MYISAM ");

		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Scars',`pelvic_cat`='PERENIUM'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Warts',`pelvic_cat`='PERENIUM'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Reddish',`pelvic_cat`='PERENIUM'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Laceration',`pelvic_cat`='PERENIUM'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Congested',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Bartholin's cyst',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Warts',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Skene's Gland Discharge',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Rectocele',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Cytocele',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Congested',`pelvic_cat`='CERVIX'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Erosion',`pelvic_cat`='CERVIX'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Discharge',`pelvic_cat`='CERVIX'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Polyps/Cyst',`pelvic_cat`='CERVIX'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Laceration',`pelvic_cat`='CERVIX'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Pinkish',`pelvic_cat`='CERVIXCOLOR'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Bluish',`pelvic_cat`='CERVIXCOLOR'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Bartholin's cyst',`pelvic_cat`='VAGINA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Firm',`pelvic_cat`='CERVIXCONSISTENCY'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Soft',`pelvic_cat`='CERVIXCONSISTENCY'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Mid',`pelvic_cat`='UTERUSPOS'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Anteflexed',`pelvic_cat`='UTERUSPOS'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Retroflexed',`pelvic_cat`='UTERUSPOS'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Normal',`pelvic_cat`='UTERUSSIZE'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Small',`pelvic_cat`='UTERUSSIZE'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Large',`pelvic_cat`='UTERUSSIZE'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Normal',`pelvic_cat`='ADNEXA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Mass',`pelvic_cat`='ADNEXA'");
		module::execsql("INSERT INTO `m_lib_fp_pelvic` SET `pelvic_name`='Tenderness',`pelvic_cat`='ADNEXA'");
	}

			

	function drop_tables(){
		module::execsql("DROP table m_lib_fp_methods");
		module::execsql("DROP table m_lib_fp_history_cat");
		module::execsql("DROP table m_lib_fp_history");
		module::execsql("DROP table m_lib_fp_pe_cat");
		module::execsql("DROP table m_lib_fp_pe");
		module::execsql("DROP table m_lib_fp_pelvic_cat");
		module::execsql("DROP table m_lib_fp_pelvic");
	}


	//custom-built functions
	//all function starts here

	function _consult_family_planning(){
		
		if($exitinfo = $this->missing_dependencies('family_planning')){
			return print($exitinfo);
		}
		
		if(func_num_args()>0){
		      $menu_id = $arg_list[0];	   //from $_GET
		      $post_vars = $arg_list[1];   //from form submissions
		      $get_vars = $arg_list[2];    //from $_GET
		      $validuser = $arg_list[3];   //from $_SESSION
		      $isadmin = $arg_list[4];	   //from $_SESSION	
		}

		$fp = new family_planning;
		$fp->fp_menu($_GET["menu_id"],$_POST,$_GET,$_SESSION["validuser"],$_SESSION["isadmin"]);
		$fp->form_fp($menu_id,$post_vars,$get_vars,$isadmin);
		if($_POST["submit_fp"]):
			print_r($_POST);
		endif;
	}


	function form_fp(){
		echo "<table>";
		echo "<tr><td>".THEAD_FP_HEADER."</td></tr>";
		echo "<tr><td>";
		
		switch($_GET["fp"]){

		case "VISIT1":
			$this->form_fp_visit1();
			break;
		case "HX":
			$this->form_fp_history();
			break;
		case "PE":
			$this->form_fp_pe();
			break;

		case "PELVIC":
			$this->form_fp_pelvicpe();
			break;

		case "CHART":
			$this->form_fp_chart();			
			break;
		case "OBS":
			$this->form_fp_obs();
			break;
		case "SVC":		


		default:

			break;
		}
		
		echo "</td></tr>";
		echo "</table>";
	}

	function fp_menu(){   			 /* displays main menus for FP */

		//this will redirect view to the VISIT1 interface
		if(!isset($get_vars[fp])){ 
			//header("location: $_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=VISIT1");
		}

		echo "<table>";
		echo "<tr><td>";
	        echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=VISIT1#visit1' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'VISIT1','VISIT1')."</a>";
				
	        echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=HX#hx' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'HX','FP HX')."</a>";
	        
	        echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=OBS#obs' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'OBS','OSTETRICAL HX')."</a>";

	        echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=PE#pe' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'PE','FP PE')."</a>";

	        echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=PELVIC#pelvic' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'PELVIC','PELVIC EXAM')."</a>";

		echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=CHART#chart' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'CHART','FP CHART')."</a>";		

		echo "<a href='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GET[module]&fp=SERVICES#services' class='groupmenu'>".$this->menu_highlight($_GET["fp"],'SVC','SERVICES')."</a>";		

				
		echo "</td></tr>";
		echo "<table>";
	}
	
	function form_fp_visit1(){
			
		echo "<form name='form_visit1' action='$_SERVER[PHP_SELF]?page=$_GET[page]&menu_id=$_GET[menu_id]&consult_id=$_GET[consult_id]&ptmenu=$_GET[ptmenu]&module=$_GE[module]&fp=VISIT1' method='POST'>";
		
		echo "<a name='visit1'></a>";
		
		echo "<table>";

		echo "<tr><td colspan='2'>FAMILY PLANNING DATA</td></tr>";

		echo "<tr><td>DATE OF REGISTRATION</td><td><input type='text' name='txt_date_reg' size='8' maxlength='10'>";
		
		print "<a href=\"javascript:show_calendar4('document.form_visit1.txt_date_reg', document.form_visit1.txt_date_reg.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";
                        		
		echo "</td></tr>";
		
		echo "<tr><td>TYPE OF METHOD</td><td>";
		$this->get_methods("sel_method");
		echo "</td></tr>";

		echo "<tr><td>PLANNING FOR MORE CHILDREN?</td>";
		echo "<td>";
		echo "<select name='form_visit1_children' size='1'>";
		echo "<option value='Y' selected>Yes</option>";
		echo "<option value='N'>No</option>";		
		echo "</select>";
		echo "</td></tr>";
		
		echo "<tr><td>NO. OF LIVING CHILDREN</td><td>";
		echo "<input name='num_child' type='text' size='3' maxlength='2'></input>";
		echo "</td></tr>";


		echo "<tr><td>HIGHEST EDUCATIONAL ATTAINMENT</td><td>";
		$this->get_education("mother_educ");
		echo "</td></tr>";

		echo "<tr><td>OCCUPATION</td><td>";
		$this->get_occupation("mother_occupation");
		echo "</td></tr>";

		echo "<tr><td>NAME OF SPOUSE</td>";
		echo "<td><input name='spouse_name' type='text' size='20' disabled></input>&nbsp;<input type='button' name='btn_search_spouse' value='Search'></input>";

		echo "</td></tr>";
		echo "<tr><td>HIGHEST EDUCATIONAL ATTAINMENT</td><td>";
		$this->get_education("spouse_educ");
		echo "</td></tr>";

		echo "<tr><td>OCCUPATION</td><td>";
		$this->get_occupation("spouse_occupation");
		echo "</td></tr>";

		echo "<tr><td>AVERAGE MONTHLY FAMILY INCOME</td>";
		echo "<td>";
		echo "<input name='ave_income' type='text' size='5'></input>";
		echo "</td></tr>";

		echo "<tr><td colspan='2' align='center'><input type='submit' name='submit_fp' value='Save Family Planning First Visit'></td></tr>";
		
		echo "</table>";

		echo "</form>";
	}

	
	function form_fp_history(){
		$q_hx_cat = mysql_query("SELECT cat_id, cat_name FROM m_lib_fp_history_cat") or die("Cannot query: 280");
		
		if(mysql_num_rows($q_hx_cat)!=0):
			echo "<form action='$_SERVER[PHP_SELF]' name='form_fp_hx' method='POST'>";
			echo "<a name='hx'></a>";
			echo "<table>";
			echo "<thead><td>MEDICAL HISTORY</td></thead>";
			while($res_hx_cat = mysql_fetch_array($q_hx_cat)){

				$q_hx = mysql_query("SELECT history_id,history_text FROM m_lib_fp_history WHERE history_cat='$res_hx_cat[cat_id]'") or die("Cannot query: 287");

				echo "<tr><td>$res_hx_cat[cat_name]</td></tr>";
				
				echo "<tr><td>";
				
				while($res_hx = mysql_fetch_array($q_hx)){
					echo "<input type='checkbox' name='sel_hx[]' value='$res_hx[history_id]'>".$res_hx["history_text"]."</input><br>";

				}
				echo "</td></tr>";
			}
			echo "<tr><td><input type='submit' name='submit_fp' value='Save History'></td></tr>";
			echo "</table>";
			echo "</form>";
		else:
			echo "<font color='red'>FP History Library not found.</font>";
		endif;		
	}

	function form_fp_pe(){
		
		$q_pe_cat = mysql_query("SELECT pe_cat_id, pe_cat_name FROM m_lib_fp_pe_cat") or die("Cannot query: 350");
		echo "<a name='pe'></a>";
		if(mysql_num_rows($q_pe_cat)!=0):
		echo "<form method='post' name='form_fp_pe'>";
		
		echo "<table border='1'>";
		echo "<thead><td colspan='2' align='center'>PHYSICAL EXAMINATION</td></thead>";

		echo "<tr><td colspan='2'>";
		
		echo "<table border='1'>";
		echo "<tr><td>Blood Pressure&nbsp;<input type='text' name='txt_fp_bp' size='4' maxlength='8'></td>";
		echo "<td>Weight&nbsp;<input type='text' name='txt_fp_wt' size='4' maxlength='3'> kgs</td>";
		echo "<td>Pulse Rate&nbsp;<input type='text' name='txt_fp_pr' size='4' maxlength='8'> per Minute</td></tr>";
		echo "</table>";
		
		echo "</td></tr>";
		
		echo "<tr><td><table>";

		while($r_pe_cat = mysql_fetch_array($q_pe_cat)){
			$q_pe = mysql_query("SELECT pe_id, pe_name FROM m_lib_fp_pe WHERE pe_cat='$r_pe_cat[pe_cat_id]'") or die("Cannot query: 356");
			//echo "<tr><td>".$r_pe_cat["pe_cat_name"]."</td></tr>";
			echo "<tr><td valign='top'>".$r_pe_cat["pe_cat_name"]."</td>";
			echo "<td>";
			while($r_pe = mysql_fetch_array($q_pe)){				
				echo "<input type='checkbox' name='sel_pe[]' value='$r_pe[pe_id]'>".$r_pe["pe_name"]."</input><br>";
			}
			echo "</td></tr>";
		}

		echo "<tr><td>OTHERS&nbsp;<input type='text' name='txt_pe_others' length='10'></input></td></tr>";

		echo "</table></td>";			

		echo "</tr>";

		echo "<tr align='center'><td colspan='2'><input type='submit' name='submit_fp' value='Save Physical Examination'></input></td></tr>";	

		echo "</table>";
		echo "</form>";
		
		else:
			echo "<font color='red'>FP Physical Exam library is not found.</font>";
		endif;

		
	}


	function form_fp_pelvicpe(){
		
		//$q_pelvic_exam = mysql_query("SELECT a.pelvic_id, a.pelvic_name, b.pelvic_cat_name FROM m_lib_fp_pelvic a, m_lib_fp_pelvic_cat b WHERE a.pelvic_cat=b.pelvic_cat_id ORDER by a. pelvic_id") or die(mysql_error());
		$q_pelvic_exam = mysql_query("SELECT pelvic_cat_id,pelvic_cat_name FROM m_lib_fp_pelvic_cat") or die(mysql_error());
		
		if(mysql_num_rows($q_pelvic_exam)!=0):
			echo "<form action='$_SERVER[PHP_SELF]' method='POST' name='form_pelvic'>";
			echo "<a name='pelvic'></a>";
			echo "<table border='1'>";	
			echo "<thead><td align='center' colspan='2'>PELVIC EXAMINATION</td></thead>";
			
			while($r_pelvic_exam = mysql_fetch_array($q_pelvic_exam)){
				$cat = $r_pelvic_exam[pelvic_cat_id];	
				
				echo "<tr><td>$r_pelvic_exam[pelvic_cat_name]</td>";
                    
				$q_pelvic_cat = mysql_query("SELECT pelvic_id,pelvic_name,pelvic_cat FROM m_lib_fp_pelvic WHERE pelvic_cat='$r_pelvic_exam[pelvic_cat_id]'") or die("Cannot query: 464");
				
				echo "<td>";
				
				if($r_pelvic_exam[pelvic_cat_id]=="UTERUSMASS"):
					echo "<font size='1'><b>Uterine Depth (for Intended IUD Users) <input type='text' name='txt_uterine_depth' size='5' maxlength='4'></input> cms</b></font>";
				else:									
					while($r_pelvic_cat=mysql_fetch_array($q_pelvic_cat)){
						echo "<input type='checkbox' name='sel_pecat[]' value='$r_pelvic_cat[pelvic_id]'>$r_pelvic_cat[pelvic_name]</input>";
					}					
				endif;
				
		
				echo "</td>";				
				echo "</tr>";			
			}
			
			echo "<tr><td colspan='2' align='center'><input type='submit' name='submit_fp' value='Save Pelvic Examination'></input></td></tr>";
			
			echo "</table>";
			echo "</form>";
		else:
		
		endif;	

	}

	function menu_highlight(){  //this function highlights the active fp submenu
		if(func_num_args()>0){
			$val = func_get_args();
			$get_val = $val[0];
			$str = $val[1];	
			$disp_str = $val[2];
		}

		if(strtoupper($get_val)==$str):
			return "<b>".$disp_str."</b>";
		else:
			return $disp_str;
		endif;
	}


	function _details_family_planning(){
		if(func_num_args()>0){
			$menu_id = $arg_list[0];
			$post_vars = $arg_list[1];
			$get_vars = $arg_list[2];
			$validuser = $arg_list[3];
			$isadmin = $arg_list[4];		
		}
		

	}

	function get_education($form_name){

		$q_educ = mysql_query("select * from m_lib_education order by educ_name") or die("cannot query 187");

		if(mysql_num_rows($q_educ)!=0):
			echo "<select name='$form_name' size='1'>";
			while($r_educ = mysql_fetch_array($q_educ)){
				echo "<option value='$r_educ[educ_id]'>$r_educ[educ_name]</option>";
			}
			echo "</select>";
		else:
			echo "<font color='red'>Education library not found.</font>";
		endif;
	}

	function get_occupation($form_name){
		$q_job = mysql_query("SELECT occup_id, occup_name FROM m_lib_occupation ORDER by occup_name") or die("Cannot query: 187");
		
		if(mysql_num_rows($q_job)!=0):
			echo "<select name='$form_name' size='1'>";

			while($r_job = mysql_fetch_array($q_job)){
				echo"<option value='$r_job[occup_id]'>$r_job[occup_name]</option>";
			}
			echo "</select>";
		else:
			echo "<font color='red'>Occupation library not found.</font>";
		endif;
	}

	function get_methods($form_name){
		$pxid = healthcenter::get_patient_id($_GET[consult_id]);

		$q_gender =  mysql_query("SELECT patient_gender FROM m_patient WHERE patient_id='$pxid'") or die("Cannot query: 158");
		list($gender) = mysql_fetch_array($q_gender);

		$q_methods = mysql_query("SELECT method_id,method_name FROM m_lib_fp_methods WHERE method_gender='$gender' ORDER by method_name ASC") or die("Cannot query: 268");
		
		if(mysql_num_rows($q_methods)!=0):
			echo "<select name='$form_name'>";
			while($r_methods = mysql_fetch_array($q_methods)){
				echo "<option value='$r_methods[method_id]'>$r_methods[method_name]</option>";	
			}
			echo "</select>";
		else:
			echo "<font color='red'>FP Method library not found.</font>";
		endif;
	}
	
	function form_fp_chart(){
		echo "<form action='$_SERVER[PHP_SELF]' method='POST' name='form_fp_chart'>";
		
		echo "<a name='chart'></a>";
		
		echo "<table>";
		echo "<thead><td>FP CHART</td></thead>";
		
		echo "<tr><td>DATE SERVICE GIVEN</td><td><input type='text' name='txt_date_service' size='7' maxlength='11'>";
		echo "<a href=\"javascript:show_calendar4('document.form_fp_chart.txt_date_service', document.form_fp_chart.txt_date_service.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";		
		echo "</input></td></tr>";
		
		echo "<tr><td>REMARKS</td><td><textarea cols='27' rows='5' name='txt_remarks'></textarea></td></tr>";
		echo "<tr><td>TREATMENT PARTNER</td><td><input type='text' name='txt_tx_partner' size='20'></input></td></tr>";
		echo "<tr><td>NEXT SERVICE DATE</td><td><input type='text' name='txt_next_service_date' size='7' maxlength='11'>";
		echo "<a href=\"javascript:show_calendar4('document.form_fp_chart.txt_next_service_date', document.form_fp_chart.txt_next_service_date.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";				
		echo "</input></td></tr>"; 
		
		echo "<tr><td colspan='2' align='center'><input type='submit' name='submit_fp' value='Save FP Chart'></td></tr>";
		echo "</table>";	
		echo "</form>";
	}
	
	function form_fp_obs(){
		echo "<form action='$_SERVER[PHP_SELF]' method='POST' name='form_fp_obs'>";
		echo "<a name='obs'></a>";
		echo "<table>";
		echo "<thead><td colspan='2'>OBSTETRICAL HISTORY</td></thead>";
		
		echo "<tr><td>Number of Pregnancies (FPAL)</td>";		
		echo "<td><input type='text' name='txt_fp_fpal' size='3' maxlength='4'></td></tr>";
		
		echo "<tr><td>Date of Last Delivery</td><td><input type='text' name='txt_last_delivery' size='7' maxlength='11'>";
		
		echo "<a href=\"javascript:show_calendar4('document.form_fp_obs.txt_last_delivery', document.form_fp_obs.txt_last_delivery.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";
		echo "</input></td></tr>";
		
		echo "<tr><td>TYPE OF LAST DELIVERY</td><td><input type='text' name='txt_type_delivery' size='10'></td></tr>";
		
		echo "<tr><td>PAST MENSTRUAL PERIOD</td><td><input type='text' name='txt_past_mens' size='7' maxlength='11'>";
		echo "<a href=\"javascript:show_calendar4('document.form_fp_obs.txt_past_mens', document.form_fp_obs.txt_past_mens.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";
		echo "</input></td></tr>";
		
		echo "<tr><td>LAST MENSTRUAL PERIOD</td><td><input type='text' name='txt_last_mens' size='7' maxlength='11'>";
		echo "<a href=\"javascript:show_calendar4('document.form_fp_obs.txt_last_mens', document.form_fp_obs.txt_last_mens.value);\"><img src='../images/cal.gif' width='16' height='16' border='0' alt='Click here to pick up date'></a>";
		echo "</input></td></tr>";
		

		echo "<tr><td>Duration and Character of Menstrual Bleeding</td><td><input type='text' name='txt_mens_bleed' size='3'></input> days</td></tr>";		
		
		echo "<tr><td colspan='2' align='center'><input type='submit' name='submit_fp' value='Save Obstectrical History'></td></tr>";
		
		echo "</table>";
		echo "</form>";
	}
	
}
?>