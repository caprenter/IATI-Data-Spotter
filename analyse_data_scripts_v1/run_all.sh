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
#array=( CAFOD gavi )
#array=( dfid )
#array=( aa )
#array=( undp )
DIRECTORY="../data/"

##Loop through each element of the array
for i in "${array[@]}"
do
  echo $i

  echo "element_count"
  ./element_count_all.sh $DIRECTORY$i

  echo "data_analysis"
  ./data_analysis.sh $DIRECTORY$i

  echo "name"
  php name.php $DIRECTORY$i

  echo "dates"
  php dates.php $DIRECTORY$i

  echo "attributes"
  php attribute_counts.php $DIRECTORY$i

  echo "strings"
  php string_lengths.php $DIRECTORY$i
  
  echo "percentage"
  php percentage.php $DIRECTORY$i
  
  echo "activity elements"
  php element_counts2.php $DIRECTORY$i
  
  echo "per activity counts"
  php per_activity_counts.php $DIRECTORY$i
  
  echo "hierarchy"
  php hierarchy.php $DIRECTORY$i
done



