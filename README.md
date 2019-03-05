Overview  
===  
- This server application was built to meet the requirements of software engineering position as such it is not a fully realized application (with well defined spec etc.).  
- the application uses the Zend Expressive Framework and PHPUnit
- a client application was not created because of time constraints (./public/index.html is the first steps towards that).  
- opening a browser to http://localhost:8080 shows a page with available API endpoints (./public/index.html)  


Zend Expressive Framework Errors
====  
1. Running expressive via composer  
$ composer expressive ...  
- error: "Comand expressive not defined"  
- resolved by updating composer.json as per   https://github.com/zendframework/zend-expressive-skeleton/blob/master/composer.json  

2. Running server  
$ composer serve  
- error: public/public/index.php not found
- resolved by changing composer.json/scripts/server to remove "public/"  

3. Creating API handlers  
$ composer expressive handler:create "App\Handler\HelloHandler"  
- error: There are no commands defined in the "handler" namespace.
- resolved by followinig PingAction.php and associated code to create Actions  
- this may be related to the application versions on my system  

4. Expressive does not return a version number
$ ./vendor/bin/expressive -V

- returns: expressive %version%
- no resolution at this time  
- suspect version 3 because of incompatibilities with various online documentation  


Security  
===  
- An application like this begs for security.  Unfortunately, time and obligations did not permit implementation.  Rather than use a synthetic approach with simulated session IDs etc I chose to leave it alone.  Note, however, the Framework and Actions will accept any security method as written.
- all submitted data is sanitized  


Data  
===  
- SQLite data file:  
	./data/appts.sqlite  

- Table structure:  
	CREATE TABLE `appts` (  
		`id`	INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT UNIQUE,  
		`patient`	TEXT,  
		`reason`	TEXT,  
		`starttime`	INTEGER,		#unix timestamp  
		`endtime`	INTEGER				#unix timestamp  
	);  

- Default records:  
	Insert Into appts (patient, reason, starttime, endtime) Values  
     ('Bill Bailey', 'Brain Fog', 1546300800, 1546302600),  
     ('Emma Stone', 'Weight Gain', 1546302600, 1546304400),  
     ('Frankie', 'Travel Itch', 1546304400, 1546306200)  

- Sample JSON response object:  
	{  
		"api":"appt.create",  
		"error":false,  
		"msg":"Record created",  
		"data":[  
			{"id":"20","patient":"Mama Cass","reason":"weepy eyes",  
			"starttime":"2019-01-01 00:00:00","endtime":"2019-01-01 00:30:00"}  
		]  
}  

Notes
===
- start and end times are stored as UNIX timestamps (number of seconds since Jan 1/1970)  
- start and end times are exchanged with the client as MySQL date strings (YYYY-MM-DD HH:MM)  
- the application will accept dates compatible with PHP's strtotime function, see http://php.net/manual/en/datetime.formats.compound.php  
- SQLite's datetime() will convert timestamps to a MySQL data string format  
- SQLite's strftime() will convert from a string date to UNIX timestamp  
- SQL example of date conversions:  
select starttime, datetime(starttime, 'unixepoch'), strftime('%s',	datetime(starttime, 'unixepoch')) from appts  


API Endpoints  
===  
http://localhost:8080/appt/read  
http://localhost:8080/appt/read?id=[interger]  
http://localhost:8080/appt/create  
http://localhost:8080/appt/update
http://localhost:8080/appt/delete?id=[interger]  


Application Versions used for Development  
===  
- Debian v9.5 with LXDE Desktop  
- PHP 7.0.33  
- PHP Unit v6.5.14  
- Zend Expressive v%version% (hard coded error returning version number)  
- Composer v1.2.2  
- FireFox 60 ESR  


Thanks To  
===  
https://atom.io/  
https://www.debian.org/  
http://github.com/  
https://lxde.org/  
https://www.mozilla.org  
http://php.net  
https://phpunit.de/  
https://www.sqlite.org  
https://sqlitebrowser.org/  
https://docs.zendframework.com/zend-expressive/  
