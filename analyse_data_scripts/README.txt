IATI-Data-Spotter is free software: you can redistribute it and/or modify
it under the terms of the GNU Affero General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

IATI-Data-Spotter is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Affero General Public License for more details.

You should have received a copy of the GNU Affero General Public License
along with IATI-Data-Spotter.  If not, see <http://www.gnu.org/licenses/>.

IATI-Data-Spotter relies on other free software products. See the README.txt file 
for more details.

Copyright 2012 caprenter <caprenter@gmail.com>

===analyse_data_scripts/===
This directory contains a number of scripts to run over IATI datasets to generate statistics.
The resulting files are csv files and are stored in a results/ driectory that you need to create.
The scripts are mainly php-cli scripts and should be run from the command line.
A single editable bash script can be called to run many tests over many (or few) datasets.
A routine to compile the resulting csv files into a spreadsheet is provided. It relies on OpenOffice and your ability to use macros.

==Get started==
Make a copy of settings.php.example and rename it settings.php
Make a copy of run_all.sh.example and rename it run_all.sh
(you may need to make this file executable)

==Grab your data==
Your data should be placed in a directory at the same level as the analyse_data_scripts/ directory
e.g. your directory structure should be:
analyse_data_scripts/
 - sectors.php
 - run_all.sh
 - etc
data/
 - worldbank
    - filename.xml
    - filename2.xml
 - dfid
 - etc
 
 You might like to use:
 https://github.com/caprenter/IATI-Registry-Refresher
 as a way to get some IATI data.
 
 ==Create a results directory called:==
 results/
 If you don't do this, the scripts don't do it for you and you won't get much useful output.
 
 ==To run a single test:==
 php <test> <publisher>
 where <publisher> is a named directory in the data/directory
 e.g. 
 php sector.php dfid
 
 ==To run all the php tests:==
 ./run_all.sh
 
 This will run over all the tests agains all the directories named in the array within the file.
 You may need to open the file and edit the array first.
 
 ==Getting all the csv files into one spreadsheet.==
 See the README file under:
 results_to_spreadsheets/
