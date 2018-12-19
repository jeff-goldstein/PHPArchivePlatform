# PHPArchivePlaform

This project along with the blog series will walk you through how to create 'archive' copies of emails and storing them for audit and viewing purposes.

Since this code correspondes to a series of blogs with an endgame of designing and writing the necessary code for a full Archive Platform; after the first blog which describes the problem and the forthcoming solution, each blog there after will have a corresponding folder that represents the code written for that blog and all previous blog functionality.

My goal was to make this project as simple as possible and to rely on as little as possible.  With that said, I am leveraging Amazon S3 and MySQL, so appropriate steps need to be taken to leverage those features.  I do NOT describe the necessary steps for obtaining the appropriate libraries used for those services.  There are to many variables due to platform differences that I'm taking the lazy way out and letting you handle it!

Sense this solution depends on SparkPost using Webhook functionality to send data to a collector, the upload.php program needs to be accessible to SparkPost via HTTP.  The rest of the supporting code doesn't need to be so open.

Here is a summary of the blog posts and supporting code:
1) Describe problem and solution
2) Create and store archive email along with reference data in SQL table
3) Store all log data about archive and original email into SQL table
