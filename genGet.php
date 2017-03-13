<?php	
	class genGet {
		//Use a database or text file?
		private static $useDB = FALSE;
		private static $db = array('host' => 'localhost', 'user' => 'root', 'password' => '', 'dbname' => 'genGet');
		//Change the board to whichever general thread your board relies in, e.g. '/vg/', '/pol/' 
		private static $board = '/vg/';
		//Keywords that the thread subject usually contains, this is how we form our link e.g. '/agdg/', '/ss13/', '/wpsg/', '/egg/'
		private static $keywords = array('ss13', 'spessmen', 'spessman', 'spacemen', 'spaceman');
		//How long, in minutes, should we wait to check for a new general thread. Don't set it too low.
		private static $wait = 10;
		//The image that will be displayed on your website/link to the general, change this if you want.
		private static $image = '4chan_icon.png';
		
		//Don't touch these
		private static $file = 'time.txt';
		private static $lastChecked = '';
		private static $url = '';
		
		public static function display() {
			//Are we using a database?
			if (self::$useDB) {
				$dbh = new PDO("mysql:host=".self::$db['host'].";dbname=".self::$db['dbname']."", self::$db['user'], self::$db['password']);
				$stmt = $dbh->prepare("SELECT `id`, `url`, `time` FROM `data`");
				if ($stmt->execute()) {
					$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
					if (empty($results)) { //table is empty, fill it
						self::$url = self::getURL();
						self::$lastChecked = time();
						$stmt = $dbh->prepare("INSERT INTO `data`(url, time) VALUES(:url, :time)");
						$stmt->execute(array("url" => self::$url, "time" => self::$lastChecked));
					} else { //table returns results
						self::$url = $results[0]['url'];
						self::$lastChecked = $results[0]['time'];
						if (time() >= self::$lastChecked + (self::$wait * 60)) {
							self::update($dbh);
						}
					}
				} else { //table wasn't found, make it
					$stmt = $dbh->prepare("create table `data` (id int(11) AUTO_INCREMENT, url varchar(64) NOT NULL, time int(11) NOT NULL, PRIMARY KEY (ID))");
					$stmt->execute();
					self::update($dbh);
				}
			} else { //We're using a file
				if (!file_exists(self::$file)) {
					self::update();
				}
				//Load the file
				$data = file(self::$file);
				
				//compare times, see if we need to update the link
				if (isset($data[1])) {
					self::$lastChecked = $data[1];
					if (time() >= self::$lastChecked + (self::$wait * 60)) {
						self::update();
					}
				}
				if (isset($data[0])) {
					self::$url = $data[0];
				}
			}
			
			//Output the linked image
			echo '<a href="'.self::$url.'"><img src="'.self::$image.'" /></a>';
		}
		
		private static function update($dbh = NULL) {
			if (self::$useDB) {
				$stmt = $dbh->prepare("UPDATE `data` SET `url` = :url, `time` = :time, WHERE `id` = 1");
				$stmt->execute(array("url" => self::getURL(), "time" => time()));
			} else {
				$handle = fopen(self::$file, 'w+');
				fwrite($handle, self::getURL()."\r\n".time());
				fclose($handle);
			}
		}
		
		private static function getURL() {
			//This is why we run it every 10 minutes
			$url = 'https://a.4cdn.org'.self::$board.'catalog.json';
			$url = file_get_contents($url);
			$url = json_decode($url, TRUE);
			//there's a few empty arrays in 4chans json so we must cycle through it
			foreach($url as $page) {
				foreach($page as $threads) {
					if (is_array($threads)) {
						foreach($threads as $value) {
							foreach(self::$keywords as $title) {
								if (strpos(strtolower($value['sub']), strtolower($title)) !== FALSE) {
									return 'http://boards.4chan.org'.strtolower(self::$board).'thread/'.$value['no'];
								}
							}
						}
					}
				}
			}
		}
	}
?>
