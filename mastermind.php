<html>


<head>
    <link rel="stylesheet" href="main.css">
</head>

<body>

<?php


manageGame();


//-----------------------------------------------------------------------------
//game manager

function manageGame()
{
    session_start();

    initMastermindCombination();

    evaluateGame();

    manageTries();

    runningGameManager();
}


//-------------------------------------------------------------------------------------------
//methoden zur seitenverwaltung

function createGamePage()
{
    printInputTools();

    printTrylistBeautiful();
}


function createHighscorePage()
{

    printInputForName();

    printTrylistBeautiful();

    highScoreManager();

//    printHighscores(getHighscores());

}


//-------------------------------------------------------------------------------------------
//verwaltende funktionen

function highScoreManager() {
    if (isset($_POST['username'])) {
        $name = $_POST['username'];
        $score = 99;
        if (isset($_SESSION['currentTry'])) {
            $score = $_SESSION['currentTry'];
        }
        submitHighscore($name, $score);
        session_destroy();
    }
}

function runningGameManager()
{
    if ($_SESSION['currentTry'] > $_SESSION['maxTries'] || (isset($_SESSION['indicators']) && $_SESSION['indicators'] == "0000")) {
        createHighscorePage();
    } else {
        if (isset($_SESSION['currentTry'])) {
            $_SESSION['currentTry']++;
        }

        createGamePage();
    }
}

function manageTries()
{
    if (!isset($_SESSION['maxTries'])) {
        // server ist immer 1 vorraus
        $_SESSION['maxTries'] = 7;
    }
    if (!isset($_SESSION['currentTry'])) {
        $_SESSION['currentTry'] = 0;
    }


}

function evaluateGame()
{
    if (isset($_POST['cell0'])) {
        $_SESSION['indicators'][] = checkPoints();
        $newTrylistEntry = array($_POST['cell0'], $_POST['cell1'], $_POST['cell2'], $_POST['cell3']);
        $_SESSION['tryList'][] = $newTrylistEntry;

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
    if ($stringResult == "") {
        $stringResult = -1;
    }
    return $stringResult;
}



//----------------------------------------------------------------------------------------------------------------------
//Spiel initialisierer


function initMastermindCombination()
{
    if (!isset($_SESSION['mastermind'])) {
        $_SESSION['mastermind'] = array(rand(0, 7), rand(0, 7), rand(0, 7), rand(0, 7));
    }
}


//----------------------------------------------------------------------------------------------------------------------
//funktionen zur datenbankverbindung

function submitHighscore($username, $score)
{
    echo $username . "+" . $score;
//    $conn = getConnection();
//    $query = 'insert into `highscore` (`name`, `score`, `date`) values ("$username", $score, now());';
//    if ($conn->query($query)) {
//        echo "mir hens gschafft";
//    } else {
//        echo "river me a cry";
//    }
//    $conn->close();

}

function getHighscores()
{
//    $conn = getConnection();
//    $query = "select * from `highscore`";
//    if ($res = $conn->query($query)) {
//        echo "mir hens nomal gschafft";
//        return $res;
//    } else {
//        echo "river me another cry";
//    }
//    $conn->close();
}


function getConnection()
{
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "mastermind";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        echo "Connection failed";
    } else {
        return $conn;
    }
}



//----------------------------------------------------------------------------------------------------------------------
//werte spendende funktionen



function getColorCodeForValue($value)
{
    switch ($value) {
        case 0:
            return "FF0000";
            break;
        case 1:
            return "FF00FF";
            break;
        case 2:
            return "FFFF00";
            break;
        case 3:
            return "00FF00";
            break;
        case 4:
            return "00FFFF";
            break;
        case 5:
            return "0000FF";
            break;
        case 6:
            return "FFF000";
            break;
        case 7:
            return "F000FF";
            break;
        default:
            return "000000";
    }
}

function getColorCodeForIndicator($value)
{
    switch ($value) {
        case 0:
            return "000000";
            break;
        case 1:
            return "FFFFFF";
            break;
        default:
            return "transparent";
            break;
    }
}


//---------------------------------------------------------------------------------------------------------------
//funktionen zur html erstellung


function printTrylistBeautiful()
{
    if (isset($_SESSION['tryList'])) {
        echo "<div class='content'>";
        echo "<div class='tryList'><table>";
        foreach ($_SESSION['tryList'] as $currentTry) {
            echo "<tr>";
            for ($i = 0; $i < 4; $i++) {
                $colorCode = getColorCodeForValue($currentTry[$i]);
                echo "<td bgcolor='$colorCode'></td>";
            }
            echo "</tr>";
        }
        echo "</table></div>";


        echo "<div class='indicators'><table>";
        foreach ($_SESSION['indicators'] as $currentIndicators) {
            echo "<tr>";
            $indicators = str_split($currentIndicators);
            $length = count($indicators);
            for ($i = 0; $i < $length; $i++) {
                $colorCode = getColorCodeForIndicator($indicators[$i]);
                echo "<td bgcolor='$colorCode' class='indicator'></td>";
            }

            echo "</tr>";
        }
        echo "</table></div>";
        echo "</div>";

    }
}

//unused
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


//disabled
function printHighscores($highscores)
{

    echo "<table>";
    echo "<tr><th>Name</th><th>Versuche</th><th>Datum</th></tr>";

//    while ($row = $highscores->fetch_array()) {
//        echo "<tr>";
//        echo "<td>" . $row['name'] . "</td>";
//        echo "<td>" . $row['score'] . "</td>";
//        echo "<td>" . $row['date'] . "</td>";
//        echo "</tr>";
//
//    }
    echo "</table>";


}

function printInputForName()
{

    echo "<form action='mastermind.php' method='post' id='highscore_form'>";

    echo "<input type='text' name='username' value='guest'>";

    echo "<button type='submit' form='highscore_form' value='Submit'>Submit</button>";

    echo "</form>";
}


function printInputTools()
{

    echo "<form action='mastermind.php' method='post' id='form1'>";

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


            $colorCode = getColorCodeForValue($j);


            // eigentlicher eintrag im auswahlelement mit value 0 bis 7 und mit angezeigtem value ("farbe") von 1 bis 8

            echo "<option value='{$j}' style='background-color:$colorCode'>{$shown_value}</option>";
        }


        echo "</select>";
    }

    echo "<button type='submit' form='form1' value='Submit'>Submit</button>";

    echo "</form>";

}

?>


</body>


</html>