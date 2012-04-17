To turn all csv files into one spreadsheet per provider with each csv file as a seperate sheet..

First run results_to_spreadsheet.sh
This will look for csv files in the /results directory
and will 'save' ods files in spreadsheets/

What it actually does..
First, we make a copy of a blank .ods file, and rename it and store it in spreadsheets/
The script then opens the file in OpenOffice and imports the csv files as seperate sheets.
The resulting files are not actually saved at this stage.

We need to manually save all the files :-( but it's not too laborious.

This will give us a directory full of .ods files.

To convert .ods files in the directory to .xls run ods_to_xls.sh
This relies on the java file in jodconverter-2.2.2 directory.


The importing of csv files relies on an open office macro:
http://www.oooforum.org/forum/viewtopic.phtml?p=365860&highlight=#365860
(slightly adapted)


Sub ImportCSVIntoTable(tableName as String, fileName as String)

Dim oDoc as Object
Dim oSheet as Object
Dim oPlan as Object

   oDoc = thisComponent
   
   oSheet = oDoc.createInstance ( "com.sun.star.sheet.Spreadsheet" )
   oDoc.Sheets.insertByName ( tableName, oSheet )
   oPlan = oDoc.Sheets.getByName(tableName) 
      
      sURL = ConvertToURL ( fileName )
      sOrigin = ""
      sFilter = "Text - txt - csv (StarCalc)"
      'sOpc = "ASCII CODE Field Separator, ASCII CODE Text Delimiter, Character Set, Starting Line" is all you need to specify
      sOpc = "44,34,0,1"
      nModo = com.sun.star.sheet.SheetLinkMode.NORMAL
      ' link file
      oPlan.link(sURL, sOrigin, sFilter, sOpc, nModo)
      ' reset link
      oPlan.setLinkMode(com.sun.star.sheet.SheetLinkMode.NONE)
End Sub  
