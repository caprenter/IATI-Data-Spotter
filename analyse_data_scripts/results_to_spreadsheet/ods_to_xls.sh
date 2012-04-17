#! /bin/bash
# 
#To start OpenOffice as a service:
#http://www.artofsolving.com/node/10
soffice -headless -accept="socket,host=127.0.0.1,port=8100;urp;" -nofirststartwizard

#To convert all .ods to xls
#http://www.artofsolving.com/node/13
java -jar jodconverter-2.2.2/lib/jodconverter-cli-2.2.2.jar -f xls spreadsheets/*.ods
