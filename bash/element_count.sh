#!/bin/bash
# Will search all files in a directory and return a count of the number of times the term is found
# IATI data example:
# Too look for number of activities in a file we need to count the <iati-activity> element
# it is sufficient to look for the string "<iati-activity
#
# To use the script enter ./element_count.sh [path to file] [search term]
# e.g. ./element_count.sh ../data/theglobalfund iati-activity

SUM="0"
NUM="0"
COUNT="0"
#ELEMENT="budget"
#FILES=../data/theglobalfund/*

FILES=$1/*

for f in $FILES
do
  
  echo "Processing $f file..."
  COUNT=$(grep -o \<$2 $f | wc -l)
  echo $COUNT
  SUM=$[$SUM + $COUNT]
 
  # take action on each file. $f store current file name
  #cat $f
done
echo $SUM
