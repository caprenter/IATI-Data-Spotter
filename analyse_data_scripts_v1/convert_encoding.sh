#!/bin/bash
# Will convert file encoding
# Currently you need to specify manually in this file the conversion encodings
# Also has a convoluted file saving structure
# Assumes your files to convert are at ../data/some_directory
# Saves them in ./some_directory/utf8

##$1 should be a path to our directory with files in
FILES=$1/*
  
  ##Check to see if the directory exists. If not create it
  DIR=${1#"../data/"}
  if [ ! -d "$DIR/utf8" ]
  then
     mkdir "$DIR/utf8"
  fi



  COUNTER=0
  ##Loop through every file in the directory
  for f in $FILES
  do
  
    #echo "Processing $f file..."
    iconv -f UTF-16LE -t UTF-8 $f > $DIR/utf8/$COUNTER.xml
    let COUNTER=COUNTER+1 
 
  done
  

