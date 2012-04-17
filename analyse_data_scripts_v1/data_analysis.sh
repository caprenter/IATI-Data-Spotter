#!/bin/bash
# Will search all files in a directory and return a count of the number of times the term is found.
# Uses xml_grep which can be easily added to linux systems, but I have adapted it so that the count
# function returns a simpole integer (and no other reporting information)
#
# IATI data example:
# Too look for number of activities in a file we provide the xpath to that element
#
# To use the script enter ./data_analysis.sh [path to directory]
# We assume your data is in the ../data/ directory
# e.g. ./data_analysis.sh ../data/theglobalfund 
# N.B. No trailing slash

##$1 should be a path to our directory with files in
FILES=$1/*
  
  ##Check to see if the directory exists. If not create it
  #DIR=${FILES:8:2}
  DIR=${1#"../data/"}
  if [ ! -d "$DIR" ]
  then
     mkdir $DIR
  fi

##First move the old output file if it exists
if [ -e "$DIR/difficult_counts.csv" ]
then
  mv $DIR/difficult_counts.csv $DIR/difficult_counts.csv.bak
fi


#array=( iati-activities reporting-org title description activity-status budget iati-activity planned-disbursements )
array=( iati-activity/description iati-activity/title transaction/value transaction )
for i in "${array[@]}"
do
  SUM=0
  #echo $SUM
  #NUM=0
  COUNT=0
  #ELEMENT="budget"
  #FILES=../data/theglobalfund/*
  
  FILES=$1/*


  for f in $FILES
  do
  
    #echo "Processing $f file..."
    #COUNT=$(grep -o \<$i $f | wc -l)
    COUNT=$(xml_grep --count //$i $f)
    #echo $COUNT
    SUM=$[$SUM + $COUNT]
 
  # take action on each file. $f store current file name
  #cat $f
  done
  echo $i
  echo $SUM
  echo "$i,$SUM" >> $DIR/difficult_counts.csv
done
