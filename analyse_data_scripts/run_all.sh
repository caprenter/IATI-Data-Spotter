#! /bin/bash
# 


array=( hewlett-foundation
        #worldbank
        dipr
        ausaid
        #dfid
        minbuza_nl
        unops
        aa
        #eu
        sida
        #ewb_canada
        oxfamgb
        maec-dgpolde
        indtrust
        theglobalfund
        undp
        finland_mfa
        asdb
        pwyf
        #nrc
        #sciaf
        #spark
        #progressio
        #rem
      )
array=( CAFOD gavi )

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
done



