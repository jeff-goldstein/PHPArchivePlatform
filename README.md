# PHPArchivePlaform

This project along with the blog series will walk you through how to create 'archive' copies of emails and storing them for audit and viewing purposes.

Since this code correspondes to a series of blogs with an endgame of designing and writing the necessary code for a full Archive Platform; after the first blog which describes the problem and the forthcoming solution, each blog there after will have a corresponding folder that represents the code written for that blog and all previous blog functionality.

My goal was to make this project as simple as possible and to rely on as little as possible.  With that said, I am leveraging Amazon S3 and MySQL, so appropriate steps need to be taken to leverage those features.  I do NOT describe the necessary steps for obtaining the appropriate libraries used for those services.  There are to many variables due to platform differences that I'm taking the lazy way out and letting you handle it!

Sense this solution depends on SparkPost using Webhook functionality to send data to a collector, the collector program(s) needs to be accessible to SparkPost via HTTP.  The rest of the supporting code doesn't need to be so open.

Here is a summary of the blog posts and supporting code:
1) Describe problem and solution
2) Create and store archive email along with reference data in SQL table (see step1Archiving folder for corresponding code)
3) Store all log data about archive and original email into SQL table (see step2StoringEvents folder for corresponding code)
4) Create an inbox UI to see the email and all associated events

I have created new folders that represent each step in the process.  Those folders represent the culmination of code up to that point of the blog/code series, not just the differences. This allows you to easily match the code with how much functionality you want to use.

As for installation, steps 1 and 2 can simply be dropped into a directory and run since there is no UI interphase.  Yes, you will need the appropriate libraries for S3 and MySQL, but other than that, installation is fairly straight forward.  Of course, if the location of your libraries are different that what I used, you need to update the locations I have within the code.

In step 3, I have a UI so if you have a different location for UI code, than processing code, you will have to change any file calls to the appropriate locations.

I have leveraged a config.php file as a location for flag settings, locations and username/password settings; you will need to set those appropriately as well.

Happy Sending,
Jeff
