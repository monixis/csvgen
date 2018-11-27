
<html>
<body>

    <table width="600">
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        
        <tr>
        <td width="20%">Select file</td>
        <td width="80%"><input type="file" name="file" id="file" /></td>
        </tr>
        <tr>
        <td width="20%">parent_object</td>
        <td width="80%"><input type="text" name="parent_object" id="parent_object" /></td>
        </tr>
        <tr>
        <td width="20%">parent_predicate</td>
            <td width="80%">
            <select id="parent_predicate" name="parent_predicate">
                <option value="isMemberOfCollection">isMemberOfCollection</option>
                <option value="isConstituentOf">isConstituentOf</option>
            </select>
            <!--input type="file" name="file" id="file" dropdown here-->
            </td>
        </tr>
        <tr>
            <td width="20%">cmodel</td>
            <td width="80%">
                <select id="cmodel" name="cmodel">
                    <option value="islandora:sp_basic_image">islandora:sp_basic_image</option>
                    <option value="islandora:sp_pdf">islandora:sp_pdf</option>
                    <option value="islandora:sp-audioCModel">islandora:sp-audioCModel</option>
                    <option value="islandora:sp_videoCModel">islandora:sp_videoCModel</option>
                    <option value="islandora:newspaperCModel">islandora:newspaperCModel</option>
                    <option value="islandora:newspaperPageCModel">islandora:newspaperPageCModel</option>
                    <option value="islandora:newspaperIssueCModel">islandora:newspaperIssueCModel</option>
                    <option value="ir:thesisCModel">ir:thesisCModel</option>
                </select>
                <!--input type="file" name="file" id="file" dropdown here-->
            </td>
        </tr>
        <tr>
            <td width="20%">typeOfResource</td>
            <td width="80%">
                <select id="typeOfResource" name="typeOfResource">
                    <option value="text">text</option>
                    <option value="cartographic">cartographic</option>
                    <option value="notated music">notated music</option>
                    <option value="sound recording">sound recording</option>
                    <option value="sound recording-musical">sound recording-musical</option>
                    <option value="sound-recording-nonmusical">sound-recording-nonmusical</option>
                    <option value="still image">still image</option>
                    <option value="moving image">moving image</option>
                    <option value="three dimensional object">three dimensional object</option>
                    <option value="software, multimedia">software, multimedia</option>
                    <option value="mixed material">mixed material</option>
                </select>
            </td>
        </tr>

        <tr>
        <td>Convert</td>
        <td><input type="submit" name="submit" /></td>
        </tr>
        
        </form>
        </table>

</body>
</html>

<?php

//code to upload a csv file
if ( isset($_POST["submit"]) ) {
    // echo $_POST['parent_object'];
    // echo $_POST['parent_predicate'];
    // echo $_POST['cmodel'];
    // echo $_POST['typeOfResource'];

if ( isset($_FILES["file"])) {

         //if there was an error uploading the file
     if ($_FILES["file"]["error"] > 0) {
         echo "Return Code: " . $_FILES["file"]["error"] . "<br />";

     }
     else {
        //Print file details
        echo "Upload: " . $_FILES["file"]["name"] . "<br />";
        echo "Type: " . $_FILES["file"]["type"] . "<br />";
        echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
        echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";

            //if file already exists
        if (file_exists("upload/" . $_FILES["file"]["name"])) {
        echo $_FILES["file"]["name"] . " already exists. ";
        }
        else {
                //Store file in directory "upload" with the name of "uploaded_file.txt"
            $storagename = $_FILES["file"]["name"];
            move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $storagename);
            echo "Stored in: " . "upload/" . $_FILES["file"]["name"] . "<br />";
            
            $csv = array();
            $lines = file("upload/" . $_FILES["file"]["name"], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $key => $value)
            {
                
                $csv[$key] = str_getcsv($value);
            }
            // $csv = array_map('array_filter',$csv);
            // $csv = array_filter($csv);
            $csv_size = sizeof($csv);
            $box_count = 0;
            $box_loc = array();
            echo '<pre>';
            // echo 'Country name: '.$csv[16][1].'<br />';
            // echo 'typeOfResource: '.$csv[19][2].'<br />';
            // echo 'subjects: '.$csv[20][2].'<br />';
            $parent_object = $_POST['parent_object'];
            $parent_predicate = $_POST['parent_predicate'];
            $cmodel = $_POST['cmodel'];
            $names = " ";
            $typeOfResource = $_POST['typeOfResource'];
            $subjects = trim($csv[20][2]);
            $geographic = trim($csv[16][1]);
            $access_condition = "Individuals requesting reproductions expressly assume the responsibility for compliance with all pertinent provisions of the Copyright Act, 17 U.S.C. ss101 et seq. Patrons further agree to indemnify and hold harmless the Marist College Archives & Special Collections and its staff in connection with any disputes arising from the Copyright Act, over the reproduction of material at the request of patrons. For more information please visit the following website: http://www.loc.gov/copyright/title17/.";
            $note_local = " ";
            $finding_aids_url = "http://library.marist.edu/exploro/exploro/viewEAD/LTP/".$csv[22][1];
            $abstract = " ";
            $rows = count($csv);
            for ($row = 0; $row < $rows; $row++) {
                $cols = count($csv[$row]);
                for($col = 0; $col < $cols; $col++ ) {
                        if(trim($csv[$row][$col]) == "Box"){
                            // echo "csv".$row." ".$col."<br />";
                            $box_count++;
                            array_push($box_loc,$row);
                            // if(!isset($box_loc)){
                            //     $box_loc[] = $row;
                            // } else {
                            //     $box_loc[] .= $row;
                            // }
                        }
                }
            }
            // echo "number of boxes =".$box_count;
            // print_r($box_loc);
            $csv_data = "identifier,label,title,dates,size,format,binary_file,parent_object,parent_predicate,cmodel,names,typeOfResource,subjects,geographic,access_condition,note_local,finding_aids_url,abstract \n";//Column headers
            for($i=0;$i<count($box_loc);$i++){
                //echo $box_loc[$i];
                $curr = $i;
                $lowlimit = $box_loc[$curr]; 
                $array_keys = array_keys($box_loc);
                $lastElement = end($array_keys);
                //echo $lastElement;
                $check = ++$curr;
                if($check <= $lastElement){
                    $uplimit = $box_loc[$check];
                } else {
                    $uplimit = count($csv);
                }
                if(trim($csv[$lowlimit][0]) == "Box"){
                    $box_num = trim($csv[$lowlimit][1]);
                }
                // echo $lowlimit."-".$uplimit."<br />";
                // echo "identifier, title, dates, size, format,binary file<br />";
                

                for($j=$lowlimit;$j<$uplimit;$j++){
                    // $cols = count($csv[$j]);
                    if((trim($csv[$j][0]) != "Box") && (trim($csv[$j][0]) !="FILE") && (($csv[$j][0]) !="")){
                        if(($csv[$j][0] >= 1) && ($csv[$j][0] <= 9)){
                            // echo $box_num."-0".$csv[$j][0]."-".$csv[$j][1]."-".$csv[$j][2]."-".$csv[$j][6]."-".$csv[$j][7]."-".$box_num.$csv[$j][0]."jpg"."<br />";  
                            $csv[$j][1] = str_replace('"','""',$csv[$j][1]);
                            $csv_data.=$box_num.'-0'.$csv[$j][0].',"'.$csv[$j][1].'","'.$csv[$j][1].'","'.$csv[$j][2].'","'.$csv[$j][6].'","'.$csv[$j][7].'","'.$box_num.$csv[$j][0].'.jpg","'.$parent_object.'","'.$parent_predicate.'","'.$cmodel.'","'.$names.'","'.$typeOfResource.'","'.$subjects.'","'.$geographic.'","'.$access_condition.'","'.$note_local.'","'.$finding_aids_url.'","'.$abstract.'"'."\n";
                        } else {
                            // echo $box_num."-".$csv[$j][0]."-".$csv[$j][1]."-".$csv[$j][2].$csv[$j][6]."-".$csv[$j][7]."-".$box_num.".".$csv[$j][0]."jpg"."<br />"; 
                            $csv[$j][1] = str_replace('"','""',$csv[$j][1]);
                            $csv_data.=$box_num.'-'.$csv[$j][0].',"'.$csv[$j][1].'","'.$csv[$j][1].'","'.$csv[$j][2].'","'.$csv[$j][6].'","'.$csv[$j][7].'","'.$box_num.$csv[$j][0].'.jpg","'.$parent_object.'","'.$parent_predicate.'","'.$cmodel.'","'.$names.'","'.$typeOfResource.'","'.$subjects.'","'.$geographic.'","'.$access_condition.'","'.$note_local.'","'.$finding_aids_url.'","'.$abstract.'"'."\n";
                        }
                        
                    }
                }
            }
            $csv_filename = "LTBATCH_".date("Y-m-d_H-i-s",time()).".csv";
            $fd = fopen ($csv_filename, "w");
            fwrite($fd,$csv_data);
            fclose($fd);
            echo "The file ".$csv_filename." has been saved";
            // print_r($csv);
            echo '</pre>';
            // $csv_data = "identifier,label,title,dates,size,format,binary_file,parent_object,parent_predicate,cmodel,names,typeOfResource,subjects,geographic,access_condition,note_local,finding_aids_url,abstract \n";//Column headers
            // $box_count=0;
            // $box_loc;
            // for($i=0; $i<$csv_size; $i++)
            //     {
            //         for($j=0;$j<$i.length();$j++)
            //         if($csv[$i][$j] == "Box"){
            //             $box_count++;
            //             if(isset($box_loc)){
            //                 $box_loc .= //save the location of boxes 
            //                 //then open another for loop and run it thru for each boxes 

            //             }

            //         }
            //         if(isset($csv[$i][0])){
            //             // $csv_data.=$csv[25][1].$csv[$i][0]."\n";
            //         }
            //     }
            // $csv_filename = "LTBATCH_".date("Y-m-d_H-i",time()).".csv";
            // $fd = fopen ($csv_filename, "w");
            // fwrite($fd,$csv_data);
            // fclose($fd);

        }
     }
  } else {
          echo "No file selected <br />";
  }
}
//csv upload ends


?>
