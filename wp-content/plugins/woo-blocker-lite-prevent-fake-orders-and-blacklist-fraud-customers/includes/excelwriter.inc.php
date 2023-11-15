<?php
	
     /*
     ###############################################
     ####                                       ####
     ####    Author : Harish Chauhan            ####
     ####    Date   : 31 Dec,2004               ####
     ####    Updated:                           ####
     ####                                       ####
     ###############################################

     */

	 
	 /*
	 * Class is used for save the data into microsoft excel format.
	 * It takes data into array or you can write data column vise.
	 */


	Class ExcelWriter
	{
			
		var $fp=null;
		var $error;
		var $state="CLOSED";
		var $newRow=false;
		
		/*
		* @Params : $file  : file name of excel file to be created.
		* @Return : On Success Valid File Pointer to file
		* 			On Failure return false	 
		*/
		 
		function ExcelWriterFun($file="")
		{
			return $this->open($file);
		}
		
		/*
		* @Params : $file  : file name of excel file to be created.
		* 			if you are using file name with directory i.e. test/myFile.xls
		* 			then the directory must be existed on the system and have permissioned properly
		* 			to write the file.
		* @Return : On Success Valid File Pointer to file
		* 			On Failure return false	 
		*/
		function open($file)
		{
			if("CLOSED" !== $this->state)
			{
				$this->error="Error : Another file is opend .Close it to save the file";
				return false;
			}	
			
			if(!empty($file))
			{
			    $this->fp = WP_Filesystem_Direct::get_contents( $file );

			}
			else
			{
				$this->error="Usage : New ExcelWriterFun('fileName')";
				return false;
			}	
			if( false === $this->fp)
			{
				$this->error="Error: Unable to open/create File.You may not have permmsion to write the file.";
				return false;
			}
			$this->state="OPENED";
            WP_Filesystem_Direct::put_contents($this->fp,$this->GetHeader());
			return $this->fp;
		}
		
		function close()
		{
			if("OPENED" !== $this->state)
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if($this->newRow)
			{
                WP_Filesystem_Direct::put_contents($this->fp,"</tr>");
				$this->newRow=false;
			}
            WP_Filesystem_Direct::put_contents($this->fp,$this->GetFooter());
            WP_Filesystem_Direct::delete($this->fp);
			$this->state="CLOSED";
			return ;
		}
		/* @Params : Void
		*  @return : Void
		* This function write the header of Excel file.
		*/
		 							
		function GetHeader()
		{
			$header = <<<EOH
				<html xmlns:o="urn:schemas-microsoft-com:office:office"
				xmlns:x="urn:schemas-microsoft-com:office:excel"
				xmlns="http://www.w3.org/TR/REC-html40">

				<head>
				<meta http-equiv=Content-Type content="text/html; charset=us-ascii">
				<meta name=ProgId content=Excel.Sheet>
				<!--[if gte mso 9]><xml>
				 <o:DocumentProperties>
				  <o:LastAuthor>Lead</o:LastAuthor>
				  <o:LastSaved>2005-01-02T07:46:23Z</o:LastSaved>
				  <o:Version>10.2625</o:Version>
				 </o:DocumentProperties>
				 <o:OfficeDocumentSettings>
				  <o:DownloadComponents/>
				 </o:OfficeDocumentSettings>
				</xml><![endif]-->
				<style>
				<!--table
					{mso-displayed-decimal-separator:"\.";
					mso-displayed-thousand-separator:"\,";}
				@page
					{margin:1.0in .75in 1.0in .75in;
					mso-header-margin:.5in;
					mso-footer-margin:.5in;}
				tr
					{mso-height-source:auto;}
				col
					{mso-width-source:auto;}
				br
					{mso-data-placement:same-cell;}
				.style0
					{mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					white-space:nowrap;
					mso-rotate:0;
					mso-background-source:auto;
					mso-pattern:auto;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					border:none;
					mso-protection:locked visible;
					mso-style-name:Normal;
					mso-style-id:0;}
				td
					{mso-style-parent:style0;
					padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					border:none;
					mso-background-source:auto;
					mso-pattern:auto;
					mso-protection:locked visible;
					white-space:nowrap;
					mso-rotate:0;}
				.xl24
					{mso-style-parent:style0;
					white-space:normal;}
				-->
				</style>
				<!--[if gte mso 9]><xml>
				 <x:ExcelWorkbook>
				  <x:ExcelWorksheets>
				   <x:ExcelWorksheet>
					<x:Name>Export Lead</x:Name>
					<x:WorksheetOptions>
					 <x:Selected/>
					 <x:ProtectContents>False</x:ProtectContents>
					 <x:ProtectObjects>False</x:ProtectObjects>
					 <x:ProtectScenarios>False</x:ProtectScenarios>
					</x:WorksheetOptions>
				   </x:ExcelWorksheet>
				  </x:ExcelWorksheets>
				  <x:WindowHeight>10005</x:WindowHeight>
				  <x:WindowWidth>10005</x:WindowWidth>
				  <x:WindowTopX>120</x:WindowTopX>
				  <x:WindowTopY>135</x:WindowTopY>
				  <x:ProtectStructure>False</x:ProtectStructure>
				  <x:ProtectWindows>False</x:ProtectWindows>
				 </x:ExcelWorkbook>
				</xml><![endif]-->
				</head>

				<body link=blue vlink=purple>
				<table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse: collapse;table-layout:fixed;'>
EOH;
			return $header;
		}

		function GetFooter()
		{
			return "</table></body></html>";
		}
		
		function writeLine($line_arr)
		{
			if("OPENED" !== $this->state)
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if(!is_array($line_arr))
			{
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
            WP_Filesystem_Direct::put_contents($this->fp,"<tr>");
			foreach($line_arr as $col)
                WP_Filesystem_Direct::put_contents($this->fp, "<td class=xl24 width=64 >$col</td>");
                WP_Filesystem_Direct::put_contents($this->fp,"</tr>");

		}

		function writeRow()
		{
			if("OPENED" !== $this->state)
			{
				$this->error="Error : Please open the file.";
				return false;
			}	
			if(false === $this->newRow)
                WP_Filesystem_Direct::put_contents($this->fp,"<tr>");

			else
                WP_Filesystem_Direct::put_contents($this->fp,"</tr><tr>");
			$this->newRow=true;
		}

		function writeCol($value)
		{
			if("OPENED" !== $this->state)
			{
				$this->error="Error : Please open the file.";
				return false;
			}
            WP_Filesystem_Direct::put_contents($this->fp,"<td class=xl24 width=64 >$value</td>");

		}
	}