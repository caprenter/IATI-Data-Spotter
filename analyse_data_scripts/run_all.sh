#! /bin/bash
# 


array=( aa
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
array=( aa
        adrauk
        asdb )

#DIRECTORY="../data/"

##Loop through each element of the array
for i in "${array[@]}"
do
  echo $i

  #echo "element_count"
  #./element_count_all.sh $DIRECTORY$i

  #echo "data_analysis"
  #./data_analysis.sh $DIRECTORY$i

  #echo "name"
  #php name.php $DIRECTORY$i

  #echo "dates"
  #php dates.php $DIRECTORY$i

  #echo "attributes"
  #php attribute_counts.php $DIRECTORY$i

  #echo "strings"
  #php string_lengths.php $DIRECTORY$i
  
  #echo "percentage"
  #php percentage.php $DIRECTORY$i
  
  echo "activity dates"
  php activity-dates_test.php $i
  
  echo "basic stats"
  php basic_statistics.php $i
    
  echo "budgets"
  php budgets.php $i
  
  echo "documents"
  php documents.php $i
  
  echo "element counts"
  php element_counts.php $i
  
  echo "geography"
  php geography_tests.php $i
  
  echo "participation"
  php participation_org_tests.php $i
  
  echo "sector"
  php sector.php $i
  
  echo "transaction"
  php transaction_table.php $i
done



