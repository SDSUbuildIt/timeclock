<html>
 <head>
  <title>build IT User Lookup</title>
 </head>
 <body>
   <?php
   ob_start();
   echo '<meta http-equiv="refresh" content="20;http://buildit.sdsu.edu/lookup-index.php" />';
   ob_flush();
   //return to entry page after 20 seconds
   $link = mysqli_connect('HOST_REDACTED', 'USERNAME_REDACTED', 'PASSWORD_REDACTED', 'DATABASE_REDACTED', 'CONNECTION_REDACTED');
     //Connect to localhost, with username root, blank password
     //and the database is "buildit_entry"

      if (mysqli_connect_errno()) {
         printf("Connect failed: %s\n", mysqli_connect_error());
         exit();
     }

       $query = $_GET['query'];
       // gets value sent over search form

       $req_length = 9;
       if(strlen($query) == $req_length){
         //if redid is just typed, rename variable
         $redid = $query;
       }else{
         //else, parse redid into new variable
         $redid = stristr($query, '+');
         $redid = str_replace('+', '', $redid);
         $redid = substr($redid, 0, 9);
       }

       if(strlen($redid) == $req_length){ // check if query length is required length of 9

           $raw_results = mysqli_query($link, "SELECT * FROM `builditentry` WHERE (`RedID` = '$redid')") or die(mysqli_error());


           if(mysqli_num_rows($raw_results) > 0){ // if one or more rows are returned do following


               while($results = mysqli_fetch_array($raw_results)){

		   echo "<center><h1 style='font-family: sans-serif;'> build IT User Lookup</h1></center>";

		   //output picture
                   echo "<div style='padding: 50px 20px 20px 50px; width: 75%; margin:auto; font-family: sans-serif;'>
                   <span style='margin: auto; float: left;padding-right: 20px;'> <img src=".$results['Photo']." style='width:350px;height:350px;'></span>";

               //print Name
                   echo "<h2>".$results['Name']."</h2>";

              //AFTER HOURS ACCESS

                   //if they have after hours access, the font is green; else it's red
                   $color = "font color='green'";
                   if ($results['MB_S17'] == 'No'){
                        $color = "font color='red'";
                   }
                   //print after hours access value
                   echo "<h2><$color>After Hours Access: ".$results['MB_S17']."</font></h2>";

	      //TIMETABLE

        ?>
        <html><form action="<?php echo htmlentities($_SERVER[‘PHP_SELF’], ENT_QUOTES); ?>" method="POST">
            <input type="submit" name="form_submit" value="Clock In/Out">
        </form></html><?php

        if(isset( $_POST['form_submit'] )) {

		 //if they're out, set them in and record time in
	           if ($results['Status'] == 0){
	             mysqli_query($link, "UPDATE builditentry SET Time_OUT='00:00:00' WHERE RedID=".$redid."");
	             mysqli_query($link, "UPDATE builditentry SET Status=(1) WHERE RedID=".$redid."");
	             mysqli_query($link, "UPDATE builditentry SET Time_In=(NOW()) WHERE RedID=".$redid."");

	           }

	           else {
	             //if they're in, set them out and update time out, date out, and total time
	             mysqli_query($link, "UPDATE builditentry SET Time_Out=(NOW()) WHERE RedID=".$redid."");
	             mysqli_query($link, "UPDATE builditentry SET Date_Out=(NOW()) WHERE RedID=".$redid."");
	             mysqli_query($link, "UPDATE builditentry SET Status=(0) WHERE RedID=".$redid."");

	             //time in
	             $time1 = new DateTime($results['Time_In']);

	            //get current time in UTC
	             $time2 = new DateTime();



	             //Find differenece between times and format
	             $diffis = $time1->diff($time2);
	             $workduration = $diffis->format('%H%I%S');
	             $todaytotal = $workduration;

	             //record total time
	             mysqli_query($link, "UPDATE builditentry SET Total_Time=(Total_Time + $workduration) WHERE RedID=".$redid."");
	             }


		   // print header for timeclock table
		   echo "<span style='width: 200px;float: left;margin: auto;'><h2>Timeclock</h2>";

		   //print time table
		  if ($results['Status'] == '1') {
		  	echo "Clocked In";
		  }

		  else {
		  	echo "Clocked Out";
		  }

	//	    echo "<form action='http://buildt.sdsu.edu/lookup.php'>
        //          <input type='submit' value='Clock In' />
        //      </form>";

		   echo "<p style='font-size: 16px;'>Time In: ".$results['Time_In']." </br>
		   Time Out: ".$results['Time_Out']."</br>
		   Today's Total Time:";
		   echo $todaytotal;
		   echo"</br> Total Time: ".$results['Total_Time']."</p></span>";

                   //print header for training table
                   echo "<span style='float: left; width: 200px; margin: auto;'><h2>Trained</h2>";


                   //check whether trainings have been completed. If yes, font color is green.
                   //if no, font color is red
                   $color1 = "font color='green'";
                   if ($results['Sewing'] == 'No'){
                        $color1 = "font color='red'";
                   }
                   $color2 = "font color='green'";
                   if ($results['Carvey'] == 'No'){
                        $color2 = "font color='red'";
                   }
                   $color3 = "font color='green'";
                   if ($results['Makerbot'] == 'No'){
                        $color3 = "font color='red'";
                   }
                   $color4 = "font color='green'";
                   if ($results['Cameo'] == 'No'){
                        $color4 = "font color='red'";
                   }
                   $color5 = "font color='green'";
                   if ($results['Soldering'] == 'No'){
                        $color5 = "font color='red'";
                   }

                  //print training results

                   echo "<p><$color4>Cameo: ".$results['Cameo']."</font> <br />
                            <$color2>Carvey: ".$results['Carvey']."</font> <br />
			    <$color3>Makerbot: ".$results['Makerbot']."</font> <br />
			    <$color1>Sewing: ".$results['Sewing']."</font> <br />
                            <$color5>Soldering Iron: ".$results['Soldering']."</font> <br /> </p> </span></div>";

               }

               //update number of entries into buildit and date of last entry
               mysqli_query($link, "UPDATE builditentry SET NumEntries=(NumEntries+1) WHERE RedID=".$redid."");
               mysqli_query($link, "UPDATE builditentry SET Date_of_last_entry ='".date('Y-m-d')."' WHERE RedID=".$redid."");

               mysqli_free_result($raw_results);
           }
            else{ // if there is no matching rows do following
               echo "RedID not found.";
               ?><html><form action="http://buildit.sdsu.edu/lookup-index.php">
                  <input type="submit" autofocus="autofocus" value="Return" />
              </form></html><?php
           }

       }
       else{ // if query length is different than required
           echo "Required length is ".$req_length;
               ?><html><form action="http://buildit.sdsu.edu/lookup-index.php">
                  <input type="submit" autofocus="autofocus" value="Return" />
              </form></html><?php
       }
   ?>

 </body>
</html>
