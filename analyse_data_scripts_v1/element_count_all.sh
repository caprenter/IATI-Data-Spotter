#!/bin/bash
# Will search all files in a directory and return a count of the number of times the term is found
# IATI data example:
# Too look for number of activities in a file we need to count the <iati-activity> element
# it is sufficient to look for the string "<iati-activity
# 
# Limitations:
# This script can count most but not all elements in the way we would like
# e.g. all <description> elements will be counted, making no distinction about whether they are top level descriptions, or
# sub-elements of other elements.
#  
# For certain IATI elements, e.g. <transaction, this test will also match <transaction-type
# so that element (transaction) is counted elsewhere
#
# Does not care if this an organisation file, or an activity file or any other file. It just looks for the string
# document-link, iati-identifier, and others appear in both IATI activity and organisation files
#
# To use the script enter ./element_count_all.sh [path to file] 
# We assume your data is in the ../data/ directory
# e.g. ./element_count_all.sh ../data/theglobalfund

##$1 should be a path to our directory with files in
FILES=$1/*
  
  ##Check to see if the directory exists. If not create it
  DIR=${1#"../data/"}
  if [ ! -d "$DIR" ]
  then
     mkdir $DIR
  fi

#DIR="ausaid"

##First move the old output file if it exists
if [ -e "$DIR/data_anaysis_counts.csv" ]
then
  mv $DIR/data_anaysis_counts.csv $DIR/data_anaysis_counts.csv.bak
fi

##Create an array of elements that we want to count
#array=( iati-activities reporting-org activity-status budget iati-activity planned-disbursements )
array=( iati-activity
        iati-activities
        reporting-org
        other-identifier
        activity-status
        activity-date
        contact-info
        participating-org
        sector
        policy-marker
        collaboration-type
        default-tied-status
        default-flow-type
        default-aid-type
        default-finance-type
        recipient-country
        recipient-region  
        location
        coordinates
        budget
        planned-disbursement
        transaction-type
        provider-org
        receiver-org
        document-link
        related-activity
        conditions 
        result
        indicator )

#array=( iati-activity iati-identifier indicator )

##Loop through each element of the array
for i in "${array[@]}"
do
  SUM=0
  #echo $SUM
  #NUM=0
  COUNT=0
  #ELEMENT="budget"
  #FILES=../data/theglobalfund/*
  
  ##Loop through every file in the directory
  for f in $FILES
  do
  
    #echo "Processing $f file..."
    COUNT=$(grep -o \<$i $f | wc -l)
    #echo $COUNT
    SUM=$[$SUM + $COUNT]
 
  done
  
  ##Calcluate percentages
  ##First get total number of activites
  if [ "$i" == "iati-activity" ]
    then
      ACTIVITY=$SUM
  fi
  ##Now calculate this as a percentage
  PERCENTAGE=$(($SUM * 100 / $ACTIVITY ))
  
  ##Write the output to a file
  echo "$i,$SUM,$PERCENTAGE" >> $DIR/data_anaysis_counts.csv
  #echo "$i,$SUM" >> $DIR/data_anaysis_counts.csv
  #echo "$i,$SUM,$PERCENTAGE" >> data_anaysis_counts.csv
done
