#! /bin/bash
# 

#Puts many csv files into one spreadsheet as seperate sheets.
#The csv files are generated with a file name like:
# worldbank_activity_dates.csv (i.e. provider_title.csv)

#List of 'titles'
title=( activity_dates
        basic_stats
        budgets
        document
        elements
        geography_new
        participating_org
        sector
        transactions
      )

#List of providers - should be the start of the file name in results
providers=( aa
            adrauk
            asdb
            ausaid
            cafod
            dfid
            dipr
            eu
            ewb_canada
            finland_mfa
            gavi
            hewlett-foundation
            indtrust
            maec-dgpolde
            minbuza_nl
            oxfamgb
            progressio
            pwyf
            rem
            sida
            spark
            theglobalfund
            undp
            unops
            worldbank
          )
#Uncomment below to test/overide the big list above          
providers=( sida )

#Loop through each provider
for PROVIDER in "${providers[@]}"
do
  #Copy a blank spreadsheet, and rename it with the name of the provider, ready to accept new sheets
  cp blank.ods spreadsheets/$PROVIDER.ods
  echo $PROVIDER
  
  #Loop trough each results file and add the sheets to the spreadsheet
  #http://www.oooforum.org/forum/viewtopic.phtml?t=55076 for the command
  for i in "${title[@]}"
  do
    echo $i
    oocalc -calc "spreadsheets/"$PROVIDER.ods "macro:///Standard.Module1.ImportCSVIntoTable($i,/home/david/Webs/aidinfo-batch/analyse_data_scripts/results/"$PROVIDER"_"$i".csv)"
  done
done
