/*
* FLV to MPlayer converter.
*
* Use this script to convert all FLV player instances in Moodle(1.9) to
* convert them to MPlayer.
* It finds all current FLV instances in all courses in moodle and then you
* can convert each video at once to MPlayer instance, by clicking "Convert!".
* Basically it takes all the FLV instance data and then combines it with
* extra MPlayer default data and creates a new MPlayer instance. Then it
* replaces the FLV instance ID in course_modules table, with the newly created
* MPlayer ID. The old FLV instance will be deleted. Thats it!
*
* Remember, you are using this at your own risk! If something goes, wrong dont
* blame it on me. Feel free to make neccessary modification for your own need.
*
* author Tõnis Tartes <tonis.tartes@gmail.com>
* package Moodle 1.9
*
*/

!!! ATTENTION !!!  USING THIS, IS AT YOUR OWN RISK !!!


INSTALLATION & USAGE:

Just place this script to your moodle installation root, where are index.php, file.php etc.

Example: /path/to/moodle/flv_to_mplayer.php

Login as admin and visit - www.yourmoodlesite.com/flv_to_mplayer.php

Then you will see all the instances of FLV players on your moodle site with details(IDs, Course IDs, etc).

Each row has a Convert button. 

When this button is pressed, the FLVPlayer instance will be converted to MPlayer and the old FLVPlayer instance will be DELETED!


I recommend you to do a backup before using this script, to prevent unneccessary losses of FLVPlayer instances, when something goes wrong.

YOU ARE WELCOME TO DO NECCESSARY MODIFICATIONS IN THE CODE, TO MATCH YOUR OWN NEEDS.



This script helped me to convert ~200 FLVPlayer instances to MPlayer instances in Moodle 1.9 and saved me alot of time. It was a neccessary move, before upgrading to Moodle 2.x.

There were some problems when FLV instances had descriptions, which had custom HTML code(different colors, font sizes etc). On those it did'nt work, quite well. I had to remove/copy the descriptions by hand before converting.

I havent tested it further, but it did what i needed.

Anyway this helped me, maybe it will help someone else too.


God speed, my friend! :)