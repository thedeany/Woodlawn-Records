<?php

function secondsToTime($seconds) {
	$minute = floor($seconds / 60);
	$second = $seconds % 60;

	return $minute . ":" . $second;
}

$db_host = "localhost";
$db_user = "root";
$db_pass = "root";
$db_name = "woodlawnRecords";

// PDO
/* Connect to an ODBC database using driver invocation */
$dsn = 'mysql:dbname='.$db_name.';host='.$db_host;

try {
    $dbh = new PDO($dsn, $db_user, $db_pass);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}

$artistsQuery = $dbh->query("SELECT * FROM Artists ORDER BY added DESC;");


$artistsSectionHTML = "";

foreach($artistsQuery as $artistItem) {
	// retrieve artist title and artistUID.
	
	$artistsSectionHTML .= "
	<!-- begin artist -->
	<div class='artist'>
	<h3 id='".$artistItem["itemUID"]."'>".$artistItem["name"]."</h3>
	<div class='drawer drawer_".$artistItem["itemUID"]."'>
		<div class='bio'>Caprica consists of only Jacob W. Jones at the moment. Under the moniker Caprica, he produces and DJ electronic dance music (EDM).</div>
		<div class='releases'>";

	// 2. inside the loop, do another query to search for all albums in the albumTable based on the current artistUID
	$albumsQuery = $dbh->query("SELECT * FROM Albums WHERE artistUID = '".$artistItem["itemUID"]."' AND type = 'album';");
	
	foreach($albumsQuery as $albumItem) {
	
		$artistsSectionHTML .= "<div class='release'>
			<img src='".$albumItem["albumArt"]."' alt='' />
			<h4>".$albumItem["title"]."</h4>
			<ol class='tracks'>";

		$songsQuery = $dbh->query("SELECT * FROM Albums WHERE type = 'song' AND parentUID = '".$albumItem["itemUID"]."'");

		foreach($songsQuery as $songItem) {
			$artistsSectionHTML .= "<li>".$songItem["title"]."<span id='duration'>".secondsToTime($songItem["duration"])."</span></li>";
		}

		$artistsSectionHTML.= "
			</ol>
		</div>";

	}

	$artistsSectionHTML.= "
		</div>
	</div>
	</div>
	<!-- end artist -->";

}


?>


<!DOCTYPE html>
<html>
<head>
	<title>Woodlawn Records</title>
	
	<link rel="stylesheet" href="assets/css/fonts.css">
	<link href='http://fonts.googleapis.com/css?family=Lato:100,300,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" href="assets/css/common.css">
	<script src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
	<script src="http://code.jquery.com/color/jquery.color-2.1.1.min.js"></script>
	<script>

	$(document).ready(function() {

		drawerToggle();
		resizePage();
			$(window).bind("resize", resizePage);
		playPause();

		function drawerToggle() {
			var drawer = $(".drawer");
			var artist = $(".artist h3");
			drawer.hide();
			artist.click(function() {

				var id=$(this).attr("id");
				var thisDrawer = $(".drawer_" + id);
				// thisDrawer.toggle(700);

				if($(this).hasClass("open")) {
					thisDrawer.hide(500);
					$(this).removeClass("open");
				} else {
					if($("h3").hasClass("open")) {
						$(".drawer").hide(500);
						$("h3").removeClass("open");
					}
					thisDrawer.show(500);
					$(this).addClass("open");
				}
			});
		};

		function scrollToAnchor(aid){
		    var aTag = $("a[name='"+ aid +"']");
		    $('html,body').animate({scrollTop: aTag.offset().top},700);
		}
			$("#homeLink").click(function() {
				scrollToAnchor('home');
			});
			$("#artistLink").click(function() {
				scrollToAnchor('artists');
			});
			$("#missionLink").click(function() {
				scrollToAnchor('mission');
			});
			$("#contactLink").click(function() {
				scrollToAnchor('contact');
			});


		function sortUsingNestedText(parent, childSelector, keySelector) {
		var items = parent.children(childSelector).sort(function(a, b) {
			var vA = $(keySelector, a).text();
			var vB = $(keySelector, b).text();
			return (vA < vB) ? -1 : (vA > vB) ? 1 : 0;
		});
			parent.append(items);
		}

		sortUsingNestedText($("#artists"), "div", "h3");


		// var audio = $(".audio");
		// var audioCtrl = $(".audioCtrl");
		// audioCtrl.click(function() {
		// 	var pause = audioCtrl.innerHTML === "Pause";
		// 	audioCtrl.innerHTML = pause ? "Play" : "Pause";

		// 	var method = pause ? "pause" : "play";
		// 	audio[method]();

		// 	return false;
		// });


		// var winWidth = $(window).innerWidth();
		// var winHeight = $(window).innerHeight();
		// $(window).resize(function() {
		// 	winWidth = $(window).innerWidth();
		// 	winHeight = $(window).innerHeight();
		// 	console.log(winWidth,winHeight);
		// });

		function resizePage() {
			var winHeight = $(window).innerHeight();
			var pageHeight = winHeight - 105;
			var logo = $("#home h1");

			pageHeight = parseInt(pageHeight) + "px";
			$("#home").css("height", pageHeight);

			// logo.css("margin", "auto 0");
		};


		function playPause() {
			var button = $(".controls");
			var nowPlaying;

			// when a "Play" or "Pause" button is clicked
			button.click(function() {
				// "song" is a variable for the audio file which we should play or pause
				var song = $(this).next("audio")[0];
				// if this song is paused
				if(song.paused) {
					// first, check to see if anything is playing currently
					if(nowPlaying) {
						// if there is, stop it
						$("#nowPlaying").prev().text("Play");
						$("#nowPlaying")[0].pause();
						$("#nowPlaying").attr("id","");
					}
					// play the selected song
					song.play();
					// change the control label
					$(this).text("Pause");
				// if this song is already playing (we want to pause it)
				} else {
					// pause the song
					song.pause();
					// set it to null instead of "nowPlaying"
					$(this).next("audio").attr("id","");
					// make sure the page knows nothing is currently playing
					nowPlaying = null;
					// change the control label
					$(this).text("Play");
				}
				$("#nowPlaying").attr("id","");
				nowPlaying = $(this).next().attr("id","nowPlaying");
			});
		}

	});

	</script>
</head>
<body>
	<div id="container">
		<div id="nav">
			<ul>
				<li><a href="#home" id="homeLink" class="navlink">Home<span></span></a></li>
				<li><a href="#artists" id="artistLink" class="navlink">Artists<span></span></a></li>
				<li><a href="#mission" id="missionLink" class="navlink">Mission<span></span></a></li>
				<li><a href="#contact" id="contactLink" class="navlink">Contact<span></span></a></li>
			</ul>
		</div>
		<div id="content">
			<section id="home" class="home">
				<a name="home" class="pageAnchor homeAnchor"></a>
				<h1><span class="bold">Woodlawn</span> Records</h1>
			</section>
			<section id="artists" class="page">
				<a name="artists" class="pageAnchor"></a>
				<h2><span class="bold">Our</span> Artists</h2>
					<!-- begin artist -->
 					<?php // echo $artistsSectionHTML; ?>
 					<div id="caprica" class="artist">
					<h3 id="caprica">Caprica</h3>
					<div class="drawer drawer_caprica">
						<div class="bio">Caprica consists of only Jacob W. Jones at the moment. Under the moniker Caprica, he produces and DJ electronic dance music (EDM).</div>
						<div class="releases">
							<div class="release">
								<img src="assets/img/intervention.jpg" alt="" />
								<div class="releaseInfo">
									<h4>Intervention EP</h4>
									<ol class="tracks">
										<li>They Found Us (with Forerunner) <span id="duration">3:26</span></li>
										<li>Colors Colliding (with Forerunner) <span id="duration">4:10</span></li>
										<li>Letting Go (with Forerunner) <span id="duration">3:23</span></li>
									</ol>
								</div>
							</div>
						</div>
					</div>
					</div>


					<div id="anotherCaprica" class="artist">
					<h3 id="anotherCaprica">Another Caprica</h3>
					<div class="drawer drawer_anotherCaprica">
						<div class="bio">Caprica consists of only Jacob W. Jones at the moment. Under the moniker Caprica, he produces and DJ electronic dance music (EDM).</div>
						<div class="releases">
							<div class="release">
								<img src="assets/img/ellipsis.jpg" alt="" />
								<div class="releaseInfo">
									<h4>...</h4>
									<ol class="tracks">
										<li>...<span class="trackInfo"><span class="duration">2:52</span><span class="controls">Play</span><audio src="assets/audio/ellipsis.mp3" data-itemUID="SG4"></audio></span></li>
									</ol>
								</div>
							</div>
							<div class="release">
								<img src="assets/img/carried.jpg" alt="" />
								<div class="releaseInfo">
									<h4>Carried Away (Caprica Remix)</h4>
									<ol class="tracks">
										<li>Carried Away (Caprica Remix)<span class="trackInfo"><span class="duration">3:38</span><span class="controls">Play</span><audio src="assets/audio/carriedAway.mp3" data-itemUID="SG5"></audio></span></li>
									</ol>
								</div>
							</div>
							<div class="release">
								<img src="assets/img/intervention.jpg" alt="" />
								<div class="releaseInfo">
									<h4>Intervention EP</h4>
									<ol class="tracks">
										<li>They Found Us (with Forerunner)<span class="trackInfo"><span class="duration">3:26</span><span class="controls">Play</span><audio src="assets/audio/theyFoundUs.mp3"></audio></span></li>
										<li>Colors Colliding (with Forerunner) <span class="trackInfo"><span class="duration">4:10</span><span class="controls">Play</span><audio src="assets/audio/colorsColliding.mp3"></audio></span></li>
										<li>Letting Go (with Forerunner) <span class="trackInfo"><span class="duration">3:23</span><span class="controls">Play</span><audio src="assets/audio/lettingGo.mp3"></audio></span></li>
									</ol>
								</div>
							</div>
						</div>
					</div>
					</div>

					<!-- end artist -->

			</section>
			<section id="mission" class="page">
				<a name="mission" class="pageAnchor"></a>
				<h2><span class="bold">Here at</span> Woodlawn</h2>
					<p>...we strive to bring you the latest and the best in the music scene straight through the Internet. Lorem ipsum dolor sit amet, consectetur adipisicing elit. Veniam, eaque dolore natus dolorem facilis error vel molestiae maxime voluptate reprehenderit velit accusamus perspiciatis quod non blanditiis molestias minus repellendus sed!</p>
					<p>Et, ducimus, accusamus, ex, voluptatum exercitationem quaerat reiciendis velit sapiente dicta deserunt architecto aperiam assumenda aliquid molestias quidem unde rem nemo iusto eaque natus numquam sed harum eum iure beatae.</p>
					<p>Impedit, laborum, ut facere eum perspiciatis minima odio cum omnis maiores a dolore nisi corrupti vitae alias eligendi quis non totam architecto. Ea, blanditiis, beatae ad ipsam accusantium ratione vitae?</p>
			</section>
			<section id="contact" class="page">
				<a name="contact" class="pageAnchor"></a>
				<h2><span class="bold">Reach Out</span> to Us</h2>
					<p>If you have any questions regarding Woodlawn or our artists, please email us at <a href="mailto:info@woodlawnrecords.com" target="_blank">info@woodlawnrecords.com</a> and we will get back to you as soon as we can to provide you with the information you need!</p>
			</section>
			<footer id="footer" class="page">
				<ul id="sitemap">
					<li><a href="#home" class="navlink">Woodlawn Records</a></li>
					<li><a href="#artists" class="navlink">Artists</a></li>
					<li><a href="#mission" class="navlink">Mission</a></li>
					<li><a href="#contact" class="navlink">Contact</a></li>
				</ul>
				<p>Contact us at <a href="mailto:info@woodlawnrecords.com" target="_blank">info@woodlawnrecords.com.</a></p>
				<p>Copyright Woodlawn Records &copy; <?php echo date("Y"); ?>. All rights reserved. Web design by Dean Designs &copy; <?php echo date("Y"); ?>. All rights reserved.</p>
			</footer>
		</div>
	</div>
</body>
</html>