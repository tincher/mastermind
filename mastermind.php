<html>

<body>
<form action="mastermind.php" method="post" id="form1">

<?php


//-----------------------------------------------------------------------------
//game manager

function manageGame()
{

    session_start();
    if (!isset($_SESSION['mastermind'])) {
        $_SESSION['mastermind'] = array(rand(0, 7), rand(0, 7), rand(0, 7), rand(0, 7));
    }
    if (isset($_POST['cell0'])) {
        $_SESSION['indicators'][] = checkPoints();
        $newTrylistEntry = array($_POST['cell0'], $_POST['cell1'], $_POST['cell2'], $_POST['cell3']);
        $_SESSION['tryList'][] = $newTrylistEntry;
        printTryList();

    }
    if (!isset($_SESSION['maxTries'])) {
        $_SESSION['maxTries'] = 8;
    }
    if (!isset($_SESSION['currentTry'])) {
        $_SESSION['currentTry'] = 0;
    } else {
        $_SESSION['currentTry']++;
    }
    if ($_SESSION['maxTries'] == $_SESSION['currentTry']) {
        createHighscorePage();
    } else {
        createGamePage();
    }
}




//-------------------------------------------------------------------------------------------


function submitHighscore($username, $score) {
    $conn = getConnection();
    $query = 'insert into `highscore` (`name`, `score`, `date`) values ("$username", $score, now());';
    if ($conn->query($query)) {
        echo "mir hens gschafft";
    } else {
        echo "river me a cry";
    }
    $conn->close();

}

function getHighscores() {
    $conn = getConnection();
    $query = "select * from `highscore`";
    if ($res = $conn->query($query)) {
        echo "mir hens nomal gschafft";
        return $res;
    } else {
        echo "river me another cry";
    }
    $conn->close();
}


function getConnection() {
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mastermind";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn -> connect_error){
        echo "Connection failed";
    } else {
        return $conn;
    }
}



function printTryList()
{
    foreach ($_SESSION['tryList'] as $currentTry) {
        for ($i = 0; $i < 4; $i++) {
            echo $currentTry[$i];
        }
        echo "   ";
    }
    echo "<br>";
    foreach ($_SESSION['indicators'] as $currentIndicators) {
        echo $currentIndicators;
        echo "    ";
    }
}

function checkPoints()
{
    $userEntry = array($_POST['cell0'], $_POST['cell1'], $_POST['cell2'], $_POST['cell3']);
    $masterCombination = $_SESSION['mastermind'];
    $result = array();
    for ($i = 0; $i < 4; $i++) {
        if ($userEntry[$i] == $masterCombination[$i]) {
            $result[] = 0;
            $masterCombination[$i] = -1;
            continue;
        }
    }
    for ($i = 0; $i < 4; $i++) {
        for ($j = 0; $j < 4; $j++) {
            if ($userEntry[$i] == $masterCombination[$j]) {
                $result[] = 1;
                $masterCombination[$j] = -1;
                continue 2;
            }
        }

    }
    $stringResult = implode("", $result);
    return $stringResult;
}

manageGame();


//-------------------------------------------------------------------------------------------

function createGamePage() {
    createInputTools();
}

function createHighscorePage(){
    $name = $_POST['username'];
    $score = 99;
    if(isset($_SESSION['currentTry'])){
        $score = $_SESSION['currentTry'];
    }

    submitHighscore($name, $score);
    $highscores = getHighscores();
    printHighscores($highscores);

    session_destroy();


}


//---------------------------------------------------------------------------------------------------------------
//funktionen zur html erstellung




function printHighscores($highscores){

    echo "<table>";
    echo "<tr><th>Name</th><th>Versuche</th><th>Datum</th></tr>";

    while($row = $highscores->fetch_array()){
        echo "<tr>";
        echo "<td>".$row['name']."</td>";
        echo "<td>".$row['score']."</td>";
        echo "<td>".$row['date']."</td>";
        echo "</tr>";

    }
    echo "</table>";


}

function createInputForName()
{

    echo "<form action='highscore.php' method='post' id='highscore_form'>";


		echo "<input type='text' name='username' value='guest'>";

		echo "<button type='submit' form='highscore_form' value='Submit'>Submit</button>";

		echo "</form>";
	}



	function createInputTools()
    {
        // schleife von 0 bis 3 für die 4 auswahlelemente für die einzelnen "bobbel"
        for ($i = 0; $i < 4; $i++) {

            // name der zelle für das angezeigte label
            $cell_number = $i + 1;
            // gibt nummeriertes label aus
            echo "<label>{$cell_number}.Zelle:</label>";

            // gibt auswahl element aus für den aktuellen schleifendurchgang,
            // also das eigentliche auswahlelement für einen "bobbel"

            echo "<select name='cell{$i}'>";


            // schleife von 0 bis 7 für die 8 auswahlmöglichkeiten für jeden "bobbel"

            for ($j = 0; $j < 8; $j++) {

                // value ist die aktuelle "farbe" nur halt als zahl und da wir ab 0 anfangen, muss 1 draufgezählt werden

                $shown_value = $j + 1;


                // eigentlicher eintrag im auswahlelement mit value 0 bis 7 und mit angezeigtem value ("farbe") von 1 bis 8

                echo "<option value='{$j}'>{$shown_value}</option>";
            }


            echo "</select>";
        }
    }

    ?>


    <!--  button zum übertragen der eingegebenen daten  -->
    <button type="submit" form="form1" value="Submit">Submit</button>

</form>

</body>


</html>