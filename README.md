# genGet
Retrieves a specificed thread from 4chan and auto-updates at a specified interval.
Good for custom homepages and websites.
No more hunting down general threads that you enjoy on 4chan.

##How to Use
1. Requires PHP
2. Upload it to your webserver
3. Include it with: 
>include_once 'genGet.php';
4. Call it with:
>genGet::display();

##How to configure
Open it up, notice that it's commented but continue reading here, 
change the board you wish to grab from, change the thread keywords(this is good for general threads) 
and the method you wish to save with. It currenty supports database or text file usage.

