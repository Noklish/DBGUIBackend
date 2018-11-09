# DBGUIBackend
Backend DB development for CSE 3330

@@ -2,3 +2,5 @@ Refer to this page for details about how to connect to the EC2 instance.
https://docs.aws.amazon.com/AWSEC2/latest/UserGuide/AccessingInstances.html?icmpid=docs_ec2_console
Private keys are in the directory (.ppk file is for Windows Putty, .pem is for Linux and Mac)
The default username is ec2-user, and the public DNS is ec2-35-160-79-103.us-west-2.compute.amazonaws.com



HOW TO LAUNCH THE DATABASE ON THE AWS SERVER: 
Download the appropriate key and follow the instructions based on the link in the AWS_KEYS readme. 
If you have questions about how to actually login, just spam @ Andrew until I respond. 
Once in the database, navigate to the DBGUIBackend/SlimDataPHP folder (cd DBGUIBackend/SlimDataPHP).
Run the command "php -S ec2-54-203-53-152.us-west-2.compute.amazonaws.com:8080 -t public index.php". 
The Slim server should then be up at that point.

If you ever want to view what is actually stored in the database (testing the quality of routes, etc), shut down the Slim server if it's running. 
Run the command "mysql -u root", then "use AnchorManAgement". 
From there, use your knowledge of SQL to view the database data. 
If you need help with that, I will still be available, or ask either of the actual DB people. 
