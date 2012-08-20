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
        acord
        addinternational
        adrauk
        aet
        afrikids
        ai_1064413
        akfuk73
        art19
        asdb
        ausaid
        basicneeds
        bond
        buildafrica
        cafod
        camfed
        canoncollinstrust
        cdc
        childhopeuk
        cic
        concernuk
        cu
        danida
        dfid
        dipr
        eu
        evc
        ewb_canada
        ffi_publisher
        finland_mfa
        fm
        foe_ewni
        fyf
        gavi
        gb-cc-220949
        globalgiving
        globalintegrity
        hewlett-foundation
        hfhgb
        hi
        hp_12
        hpa
        icauk
        icn
        indtrust
        infid
        iww_publish
        karuna
        lead_international
        libertic-520640889
        livingearth12
        maec-dgpolde
        mapaction
        mcs
        minbuza_nl
        mrdf
        nrc
        opportunity-international-uk
        oxfamgb
        pont
        pontis
        power
        progressio
        pwyf
        rem
        rspb_
        sciaf
        self-help-africa
        sendacow
        sense_international
        sida
        sossaheluk
        spark
        spuk
        surf
        tao-03473165
        tearfund
        theglobalfund
        theict
        traidcraft
        transparency-international
        troc
        twin-iati
        undp
        #unops
        wacc-uk
        waronwant
        womankindworld
        worldbank
        wsup
        wvuk
        yipl
      )
#Uncomment below to test/overide the big list above          
#providers=( sida )

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
