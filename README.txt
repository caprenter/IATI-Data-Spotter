International Aid Transparency Initiative Data Spotter 

This little app was built to help in the checking of data quality of IATI format XML files.
See http://iatistandard.org/ for more info.

==Licence==
GNU Affero General Public License (except where stated)
All javascript used has been re-used from websites. Credit is given in those files.
Images in themes/images are all GPL from the Gnome desktop: http://art.gnome.org/themes/icon

==Install==

1) Place all the files in the web directory of your server and point your browser at that directory.

2) Get some data
To view IATI data you'll need to get some. Browse available data at:
http://iatiregistry.org/

3) Create a directory called 'data' in the webroot of the application
4) Create sub-directories to hold data for each of the data providers files you want to interogate.
5) Create a 'variable' file in the variables directory for this data provider.
6) For security (and to see your data), variables/available-groups.php will need to be edited.

7) Edit variables/example.site_vars.php to enable filesize functions. Rename the file site_vars.php

===Tweaks===
in /themes/header.php we have the following:
error_reporting(0); //Kills error reporting. Comment this line out to help you debug.
ini_set("memory_limit","128M"); //Some of the processes are quite memory intensive, you may need to set this for your servers abilities.

===Applications Within Applications===
====Analyse Data Scripts====
These are a set of scripts that run over the data in the /data duirectory and produce a bunch of stats

====Where_is====
This is a small app that can display a view on processed/aggregated data.
Running where_can_i_find.php will generate files in the where_is/data/ directory.
The rest of the app will use this data to display stuff!
Point your browser at /where_is/ to see it in action.
