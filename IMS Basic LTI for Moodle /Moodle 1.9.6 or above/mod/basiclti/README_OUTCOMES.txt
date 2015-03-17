Sun Aug  8 00:58:53 EDT 2010

Hello, 

This is my first cut at supporting the IMS Simple Outcomes (not a spec) 
in the Moodle 1.9 mod.

I have made the following changes:

-  Updated the data model to have grading related columns
-  Updated the admin and instructor UI to support grading
-  Updated locallib.php to send the extension values if requested
-  Added setoutcome.php to receive the outcomes and place in the gradebook

You can do a diff to find all the changes.  Questions and comments 
welcome.

Here are some issues I would call your attention to:

- Look in mod_form.php - I needed to set a gradesecret once and then keep it.
There is no UI to  manipulate the gradesecret but I need to set one if
grades are to be used.  I did it in defaultvalues - I have no idea if this is
bad or good style.

NIKOLAS: If it is just here, it will not enter the database. At least it doesn't enter.

- In setoutcome.php, it has a few to-dos and I am absolutely a beginner
in the use of the grade API - so I am worried that what I did is not
appropriate.   A full review is warranted.

- I edited db/install.xml but did noothing to the upgrade script - this
should be done - four columns need to be added - I just dropped the mod
and re-added it :) - but before we release, that needs to be right.

I now have code in my IMS test harness (tool.php) that knows how to 
talk the protocol. You can download a copy at:

http://www.imsglobal.org/developers/BLTI/dist.zip

Unzip this and make it available.  Point to tool.php, enable grades 
for your instance, and you should be off and running.

/Chuck
csev@umich.edu
http://www.dr-chuck.com/

