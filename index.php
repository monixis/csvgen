<?php
//code to upload a csv file
if ( isset($_POST["submit"]) ) {
    if ( isset($_FILES["file"])) {
         //if there was an error uploading the file
        if ($_FILES["file"]["error"] > 0) {
            echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
        } else {
            //Print file details
            //if file already exists
            if (file_exists("upload/" . $_FILES["file"]["name"])) {
            echo $_FILES["file"]["name"] . " already exists. ";
            }
            else {
                //Store file in directory "upload" with the name of "uploaded_file.txt"
                $storagename = $_FILES["file"]["name"];
                move_uploaded_file($_FILES["file"]["tmp_name"], "upload/" . $storagename);
                
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
                $collection_name = $_POST['collection_name'];
                $parent_object = $_POST['parent_object'];
                $parent_predicate = $_POST['parent_predicate'];
                $cmodel = $_POST['cmodel'];                
                $typeOfResource = $_POST['typeOfResource'];
                 //$geographic = trim($csv[16][1]);  
                $geographic = trim($csv[16][1]);
                $title2 = trim($csv[14][2]);
                if($geographic!=""){
                    if($geographic === $title2){
                        $title = " ";
                    } 
                }
                if($_POST['select_column'] == 'names'){
                    $names = $title2;
                    $geographic = " ";
                    $subjects = " ";
                } else if($_POST['select_column'] == 'geographic'){
                    $names = " ";
                    $geographic = $title2;
                    $subjects = " ";
                } else if($_POST['select_column'] == 'none'){
                    $geographic = " ";
                    $names = " ";
                    $subjects = " ";
                } else if($_POST['select_column'] == 'subjects'){
                    $geographic = " ";
                    $names = " ";
                    $subjects = $title2; 
                }
                $access_condition = "Individuals requesting reproductions expressly assume the responsibility for compliance with all pertinent provisions of the Copyright Act, 17 U.S.C. ss101 et seq. Patrons further agree to indemnify and hold harmless the Marist College Archives & Special Collections and its staff in connection with any disputes arising from the Copyright Act, over the reproduction of material at the request of patrons. For more information please visit the following website: http://www.loc.gov/copyright/title17/.";
                $note_local = " ";
                $finding_aids_url = "http://library.marist.edu/exploro/exploro/viewEAD/".trim($collection_name)."/".$csv[14][1];
                $abstract = " ";
                $rows = count($csv);
                for ($row = 0; $row < $rows; $row++) {
                    $cols = count($csv[$row]);
                    for($col = 0; $col < $cols; $col++ ) {
                        if(trim($csv[$row][$col]) == "Box"){
                            $box_count++;
                            array_push($box_loc,$row);
                        }
                    }
                }
                // echo "number of boxes =".$box_count;
                // print_r($box_loc);
                $csv_data = "identifier,label,title,dates,size,format,binary_file,parent_object,parent_predicate,cmodel,names,typeOfResource,subjects,geographic,access_condition,note_local,finding_aids_url,abstract\n";//Column headers
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
                                $csv_data.="Box".$box_num.'-Item0'.$csv[$j][0].',"'.$csv[$j][1].'","'.$csv[$j][1].'","'.$csv[$j][2].'","'.$csv[$j][6].'","'.$csv[$j][7].'","'.$box_num.".".$csv[$j][0].'.jpg","'.$parent_object.'","'.$parent_predicate.'","'.$cmodel.'","'.$names.'","'.$typeOfResource.'","'.$subjects.'","'.$geographic.'","'.$access_condition.'","'.$note_local.'","'.$finding_aids_url.'","'.$abstract.'"'."\n";
                            } else {
                                // echo $box_num."-".$csv[$j][0]."-".$csv[$j][1]."-".$csv[$j][2].$csv[$j][6]."-".$csv[$j][7]."-".$box_num.".".$csv[$j][0]."jpg"."<br />"; 
                                $csv[$j][1] = str_replace('"','""',$csv[$j][1]);
                                $csv_data.="Box".$box_num.'-Item'.$csv[$j][0].',"'.$csv[$j][1].'","'.$csv[$j][1].'","'.$csv[$j][2].'","'.$csv[$j][6].'","'.$csv[$j][7].'","'.$box_num.".".$csv[$j][0].'.jpg","'.$parent_object.'","'.$parent_predicate.'","'.$cmodel.'","'.$names.'","'.$typeOfResource.'","'.$subjects.'","'.$geographic.'","'.$access_condition.'","'.$note_local.'","'.$finding_aids_url.'","'.$abstract.'"'."\n";
                            }
                        }
                    }
                }
                // echo '<pre>';
                // echo "The file ".$csv_filename." has been saved";
                // echo "Upload: " . $_FILES["file"]["name"] . "<br />";
                // echo "Type: " . $_FILES["file"]["type"] . "<br />";
                // echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
                // echo "Temp file: " . $_FILES["file"]["tmp_name"] . "<br />";
                // echo "Stored in: " . "upload/" . $_FILES["file"]["name"] . "<br />";
                // echo '</pre>'; 
                $csv_filename = "LTBATCH_".date("Y-m-d_H-i-s",time()).".csv";
                $filepath = $_SERVER["DOCUMENT_ROOT"].'/csvconv/' . $csv_filename;
                $fd = fopen ($csv_filename, "w");
                ob_clean();
                fwrite($fd,$csv_data);
                // header('Content-Type: application/octet-stream');
                header('Content-Type: text/plain; charset=UTF-8');
                header('Content-Disposition: attachment; filename="' . $csv_filename . '"');
                header('Pragma: no-cache');
                fclose($fd);
                
                flush();
                readfile($csv_filename);
                exit(0);
                // print_r($csv);
            }
            // echo $filepath;
            
        }
    } else {
        echo "No file selected <br />";
    }
}
//csv upload ends
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<title>CSVConv</title>
		<meta name="description" content="" />
		<meta name="author" content="Monish.Singh1" />
		<link rel="stylesheet" type="text/css" href="style/main.css" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0" />
		<!-- Replace favicon.ico & apple-touch-icon.png in the root of your domain and delete these references -->
		<link rel="shortcut icon" href="/favicon.ico" />
		<link rel="apple-touch-icon" href="/apple-touch-icon.png" />
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="http://evanplaice.github.io/jquery-csv/src/jquery.csv.min.js"></script>
        <!script src="jquery.csv.js"></script>
        <script>
            $(document).ready(function() {
            if(isAPIAvailable()) {
                $('#file').bind('change', handleFileSelect);
            }
            });

            function isAPIAvailable() {
            // Check for the various File API support.
            if (window.File && window.FileReader && window.FileList && window.Blob) {
                // Great success! All the File APIs are supported.
                return true;
            } else {
                // source: File API availability - http://caniuse.com/#feat=fileapi
                // source: <output> availability - http://html5doctor.com/the-output-element/
                document.writeln('The HTML5 APIs used in this form are only available in the following browsers:<br />');
                // 6.0 File API & 13.0 <output>
                document.writeln(' - Google Chrome: 13.0 or later<br />');
                // 3.6 File API & 6.0 <output>
                document.writeln(' - Mozilla Firefox: 6.0 or later<br />');
                // 10.0 File API & 10.0 <output>
                document.writeln(' - Internet Explorer: Not supported (partial support expected in 10.0)<br />');
                // ? File API & 5.1 <output>
                document.writeln(' - Safari: Not supported<br />');
                // ? File API & 9.2 <output>
                document.writeln(' - Opera: Not supported');
                return false;
            }
            }

            function handleFileSelect(evt) {
            var files = evt.target.files; // FileList object
            var file = files[0];

            // read the file metadata
            // var output = ''
                // output += '<span style="font-weight:bold;">' + escape(file.name) + '</span><br />\n';
                // output += ' - FileType: ' + (file.type || 'n/a') + '<br />\n';
                // output += ' - FileSize: ' + file.size + ' bytes<br />\n';
                // output += ' - LastModified: ' + (file.lastModifiedDate ? file.lastModifiedDate.toLocaleDateString() : 'n/a') + '<br />\n';

            // read the file contents
            printTable(file);
            

            // post the results
            // $('#list').append(output);
            }

            function printTable(file) {
                var reader = new FileReader();
                reader.readAsText(file);
                reader.onload = function(event){
                    var csv = event.target.result;
                    var data = $.csv.toArrays(csv);
                    console.log(data);
                    var html = '';
                    var geo = (data[16][1]);
                    var title2 = (data[14][2]) ;
                    // conditions covered
                    // 1 - if geo has a value - we store it in geo column of csv to be converted
                    // 2 - if geo is equal to title2(title[2]) then do not ask user the question to store the value
                    // 3 - if title2 is different from geo then, ask user the question and add a dropdown category as geography.
                    if((geo!="") && (title2!="")){
                        if(geo == title2){
                            //save info in geo column
                            html += "";
                        } else {
                            html += title2;
                        }
                    } else if(title2!=""){
                        if(geo != title2){
                            html += title2;
                        }
                    }
                    // html += data[14][2];
                    if(!html){//if geo and title2 are same, hide the question
                        document.getElementById("column_dropdown").style.display = 'none';
                        document.getElementById("select_column").style.display = 'none';
                        document.getElementById("contents").style.display = 'none';
                        $("#select_column option[value='geographic']").remove();
                    } else {//if geo and title2 are not  same, hide the question
                        // html+= "<option value='geographic'>Geographic</option>";
                        $("#select_column option[value='geographic']").remove();
                        document.getElementById("column_dropdown").style.display = '';
                        document.getElementById("select_column").style.display = '';
                        document.getElementById("contents").style.display = '';
                        $("#select_column").append(new Option("Geographic", "geographic"));
                        // $('#select_column').add("<option value='geographic'>Geographic</option>");
                        $('#contents').html(html);
                    }
                };
                
            }
    </script>
    </head>
    <body>
        <div id="headerContainer">
            <div id="header1" style="padding: 20px;">
                <h1 style="color: #ffffff">CSV Converter</h1>
                <p style="text-align: center; margin-top: -15px; font-weight: bold;">Convret CSV to Islandora compatible CSV  File</p>
            </div>
        </div>

        <div class="container_home">
            <div class="divContainer" style="background: rgba(255, 255, 255, 1);padding-top: 21px;margin-top: 25px;">
                <div id="step1">
                    <table style="border:0px;">
                        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
                        
                        <tr>
                            <td>Select file</td>
                            <td><input type="file" name="file" id="file" multiple/></td>
                        </tr>

                        <tr>
                            <td width="40%">Collection Name</td>
                            <td width="60%"><input type="text" name="collection_name" id="collection_name" /><br />
                            <span>Input the abbreviation of the collection name.(e.g. LTP)</span></td>
                        </tr>

                        <tr id="column_dropdown">
                            <td width="40%">Selet the column for this information</td>
                            <tr>
                            <td id="contents" style="color:red;"></td>
                            <td>
                                <select id="select_column" name="select_column">
                                    <option value="names">Names</option>
                                    <option value="subjects">Subjects</option>
                                    <option value="none" selected="selected">None</option>
                                </select>
                            </td>
                            </tr>
                        </tr>

                        <tr>
                            <td width="40%">parent_object</td>
                            <td width="60%"><input type="text" name="parent_object" id="parent_object" /></td>
                        </tr>
                        <tr>
                        <td width="40%">parent_predicate</td>
                            <td width="60%">
                            <select id="parent_predicate" name="parent_predicate">
                                <option value="isMemberOfCollection">isMemberOfCollection</option>
                                <option value="isConstituentOf">isConstituentOf</option>
                            </select>
                            <!--input type="file" name="file" id="file" dropdown here-->
                            </td>
                        </tr>
                        <tr>
                            <td width="40%">cmodel</td>
                            <td width="60%">
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
                            <td width="40%">typeOfResource</td>
                            <td width="60%">
                                <select id="typeOfResource" name="typeOfResource">
                                    <option value="still image">still image</option>
                                    <option value="cartographic">finding_aid</option>
                                    <option value="cartographic">cartographic</option>
                                    <option value="notated music">notated music</option>
                                    <option value="sound recording">sound recording</option>
                                    <option value="sound recording-musical">sound recording-musical</option>
                                    <option value="sound-recording-nonmusical">sound-recording-nonmusical</option>
                                    <option value="text">text</option>
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

                        <tr id="success">
                        </tr>

                        </form>
                    </table>
                    
                </div>
            </div>
        </div>
    </body>
</html>
