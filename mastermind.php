<html>


<head>
    <link rel="stylesheet" href="main.css">
</head>

<body>

<?php


manageGame();


//----------------------------------------------------------------------------------------------------------------------
//game manager

function manageGame()
{

    // setzt, falls möglich, die vorhandene SESSION fort, falls nicht, wird eine neue gestartet
    session_start();


    // erstellt die zu erratende mastermind-kombination und speichert diese in der SESSION
    initMastermindCombination();


    // verarbeitet und speichert die pro versuch anfallenden daten (s.u.)
    evaluateGame();


    // verantwortlich für die versuchsverwaltung, also initiierung und erhöhung der versuche
    manageTries();


    // entscheidet und ruft methoden auf, welche "seite" aufgerufen werden soll
    runningGameManager();
}


//----------------------------------------------------------------------------------------------------------------------
// methoden zur seitenverwaltung


// erstellt die seite zum spielen
function createGamePage()
{

    // gibt die eingabefelder für den nutzer aus
    printInputTools();


    // erstellt die beiden tabellen zur darstellung bisheriger versuche
    printTrylistBeautiful();
}


// erstellt die seite zum anzeigen aller highscores und eingeben des eigenen
function createHighscorePage()
{


    // erstellt das eingabefeld für den nutzernamen zur highscore-speicherung
    printInputForName();

    // erstellt die beiden tabellen zur darstellung bisheriger versuche
    printTrylistBeautiful();


    // verantwortlich für die eingabe eines neuen highscore, sowie die zerstörung der session
    highScoreManager();

    // gibt alle highscores aus
    printHighscores(getHighscores());
}


//----------------------------------------------------------------------------------------------------------------------
// verwaltende funktionen


// initiiert die mastermindkombination
function initMastermindCombination()
{

    // falls mastermind in der session noch nicht gesetzt ist
    if (!isset($_SESSION['mastermind'])) {

        // mastermind wird auf 4 zufällig werte mit 0 <= x <= 7 gesetzt
        $_SESSION['mastermind'] = array(rand(0, 7), rand(0, 7), rand(0, 7), rand(0, 7));
    }
}


// verantwortlich für die eingabe eines neuen highscore, sowie die zerstörung der session
function highScoreManager()
{

    // testet ob der username via POST übertragen wird, also ob der nutzer seinen namen submitted hat
    // (trotzdem kann noch guest drin stehen)
    if (isset($_POST['username'])) {

        // zieht den namen aus dem post raus und setzt ihn in eine lokale variable
        $name = $_POST['username'];

        // default wert für den score, extra hoch, damit falsche werte nicht oben stehen können (vielleicht überflüssig)
        $score = 99;

        // wenn der current try gesetzt ist (was er eigentlich immer sein müsste,
        // was die vorherige zeile redundant macht) wird der die anzahl der versuche in score eingetragen
        if (isset($_SESSION['currentTry'])) {

            // score wird auf die anzahl der versuche gesetzt
            $score = $_SESSION['currentTry'];
        }

        // trägt den highscore in die datenbank ein
        submitHighscore($name, $score);

        // die session wird zerstört, dass ein neues spiel beginnen kann
        session_destroy();
    }
}

//entscheidet und ruft methoden auf, welche "seite" aufgerufen werden soll
function runningGameManager()
{
    // wahr falls:  - der aktuelle versuch zu hoch ist, also mehr als die angegebenen maximale versuche ist
    //              - die indicators gesetzt sind und der letzte eintrag "0000" entspricht, also 4 treffern mit
    //                      richtiger position
    // also wahr, falls das spiel beendet werden soll
    if ($_SESSION['currentTry'] > $_SESSION['maxTries'] || (isset($_SESSION['indicators'])
            && end($_SESSION['indicators']) == "0000")) {

        // gibt die highscore seite aus, also die seite mit den highscores und dem eingabefeld für den eigenen score
        createHighscorePage();

    // else wird aufgerufen, wenn das spiel weiterläuft
    } else {

        // falls der currentTry gesetzt ist, wird dieser um 1 erhöht
        if (isset($_SESSION['currentTry'])) {

            // erhöhung um 1
            $_SESSION['currentTry']++;
        }

        // die gamepage wird ausgegeben
        createGamePage();
    }
}


// initiiert die maximale versuchsanzahl und die aktuelle versuchszahl
function manageTries()
{
    // initiiert die maximale versuchszahl, falls diese nicht gesetzt ist
    if (!isset($_SESSION['maxTries'])) {

        // server ist immer 1 vorraus, da die daten ja erst beim server sind,
        // generiert werden und dann erst zum nutzer kommen
        $_SESSION['maxTries'] = 7;
    }

    // initiiert die aktuelle versuchszahl
    if (!isset($_SESSION['currentTry'])) {
        // auch 1 weniger, aus dem gleichen grund wie max tries, 2 zeilen drüber
        $_SESSION['currentTry'] = 0;
    }
}

// verarbeitet die vom nutzer eingegebenen daten, also seine auswahl wird gespeichert und die indikatoren werden
// erzeugt und auch gespeichert
function evaluateGame()
{

    // überprüft ob die zelle0 gesetzt ist, da ansonsten die seite zum ersten mal aufgerufen wird und noch nichts
    // eingegeben wurde und daher auch nichts im POST sein kann
    if (isset($_POST['cell0'])) {

        // hängt an das indicator array den string mit den punkten an
        $_SESSION['indicators'][] = checkPoints();

        // erstellt einen neuen eintrag für die versuchsliste, also hier wird eine liste mit den letzten eingaben des
        // nutzers angelegt und newTryListEntry zugewiesen
        $newTryListEntry = array($_POST['cell0'], $_POST['cell1'], $_POST['cell2'], $_POST['cell3']);

        // nun wird an die eigentliche try-list der eben neu erstellte eintrag zugewiesen
        $_SESSION['tryList'][] = $newTryListEntry;
    }
}


// berechnet die indikatoren
function checkPoints()
{

    // nutzer eintrag wird als array einer variable zugewiesen
    $userEntry = array($_POST['cell0'], $_POST['cell1'], $_POST['cell2'], $_POST['cell3']);

    // kopie der mastermind kombination, um diese auch ohne seiteneffekte zu bearbeiten können
    $masterCombination = $_SESSION['mastermind'];

    // rückgabe variable, die als leerer string initiiert wird
    $result = "";

    // geht durch die einzelnen einträge der USER eingabe
    for ($i = 0; $i < 4; $i++) {

        // wenn der nutzer an der stelle i das gleiche wie an der stelle i in der mastermind-kombination steht,
        // eingegeben hat, wird 0 für einen schwarzen "bobbel" zurückgegeben
        if ($userEntry[$i] == $masterCombination[$i]) {

            // die 0 wird an das result angegängt
            $result .= "0";

            // die lokale mastercombination wird an der stelle i auf -1 gesetzt um mehrfache treffer zu verhindern
            $masterCombination[$i] = -1;
        }
    }

    // geht noch einmal neu durch die einzelnen einträge der USER eingabe
    for ($i = 0; $i < 4; $i++) {

        // geht einzeln durch die einträge der mastermindkombination
        for ($j = 0; $j < 4; $j++) {

            // wenn der i-te eintrag der nutzers mit dem j-ten eintrag der mastermindkombination übereinstimmt
            if ($userEntry[$i] == $masterCombination[$j]) {

                // dann wird eine 1 angehängt
                $result .= "1";

                // die mastermindkombination wird an der stelle j auf -1 gestzt,
                // wieder um mehrfach treffer zu verhindern
                $masterCombination[$j] = -1;

                // continue heißt das die nächste schleife (also die die durch die mastermindkombination durchgeht)
                // unterbrochen wird und mit dem nächsten schleifendurchgang fortgestzt wird,
                // continue 2 macht das gleiche nur mit der 2 nächsten schleife
                continue 2;
            }
        }
    }

    // falls kein treffer entsteht soll das result trotzdem etwas beinhalten, also wird das result auf -1 gesetzt
    if ($result == "") {

        //leeres result wird auf -1 gesetzt
        $result = "-1";
    }

    // das result wird zurückgegeben
    return $result;
}


//----------------------------------------------------------------------------------------------------------------------
//funktionen zur datenbankverbindung


// highscore wird in datenbank eingetrangen
function submitHighscore($username, $score)
{

    // connection wird eröffnet und conn zugewiesen
    $conn = getConnection();

    // mysql query wird erstellt
    $myquery = 'insert into `highscore` (`name`, `score`, `date`) values ("$username", $score, now());';

    // die string query wird übersetzt und an die datenbank aus $conn geschickt
    if ($conn->query($myquery)) {
        echo "<p>Highscore wurde erfolgreich in Datenbank eingetragen</p>";
    }

    // die connection wird wieder geschloßen
    $conn->close();
}

// alle highscores werden zurückgegeben
function getHighscores()
{

    // es wird wieder eine verbindung hergestellt
    $conn = getConnection();

    // es wird wieder eine query erstellt
    $query = "select * from `highscore`";

    // query wird wieder an connection geschickt und das ergebnis aus der DB wird in $res gespeichert
    if ($res = $conn->query($query)) {
        // $res wird zurückgegeben
        return $res;
    }

    // die verbindung wird geschlossen
    $conn->close();
}


// stellt eine verbindung zur datenbank her und gibt diese zurück
function getConnection()
{
    // daten für die verbindung werden zugewiesen
    $servername = "localhost";
    $username = "mastermind";
    $password = "quentin187";
    $dbname = "mastermind";

    // connection wird hergestellt
    $conn = new mysqli($servername, $username, $password, $dbname);

    // falls die verbindung fehlerhaft ist wird eine fehlermeldung ausgegeben
    if ($conn->connect_error) {

        // fehlermeldung
        echo "<p>Connection failed</p>";
    } else {

        //connection wird zurückgegeben
        return $conn;
    }
}


//----------------------------------------------------------------------------------------------------------------------
//werte spendende funktionen



function getColorNameForValue($value)
{
    switch ($value) {
        case 0:
            return "rot";
            break;
        case 1:
            return "pink";
            break;
        case 2:
            return "gelb";
            break;
        case 3:
            return "grün";
            break;
        case 4:
            return "türkis";
            break;
        case 5:
            return "blau";
            break;
        case 6:
            return "lila";
            break;
        case 7:
            return "braun";
            break;
        default:
            return "fehler";
    }
}



// der hexcode für die eingegebene zahl wird zurückgegeben (für die tabelle)
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
            return "8800FF";
            break;
        case 7:
            return "663300";
            break;
        default:
            return "000000";
    }
}

// der hexcode für die verschiedenen indikatoren
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


//----------------------------------------------------------------------------------------------------------------------
//funktionen zur html erstellung


// gibt die tabelle mit den bisherigen versuchen und den bisherigen indikatoren aus
function printTrylistBeautiful()
{

    // nur falls trylist gesetzt ist
    if (isset($_SESSION['tryList'])) {

        // content als wrapper um die beiden tabellen herum
        echo "<div class='content'>";

        // trylist wird von div umgeben
        echo "<div class='tryList'><table>";

        // für jeden versuch aus der trylist wird die schleife durchgegangen,
        // wobei der aktuelle versuch immer in $currentTry ist
        foreach ($_SESSION['tryList'] as $currentTry) {

            // reihe wird geöffnet
            echo "<tr>";

            // die 4 eingaben aus dem aktuellen versuch werden durchgegangen
            for ($i = 0; $i < 4; $i++) {

                // colorcode für den aktuellen eintrag wird abgefragt
                $colorCode = getColorCodeForValue($currentTry[$i]);

                // td wird ausgegeben mit entsprechender hintergrundfarbe
                echo "<td bgcolor='$colorCode'></td>";
            }

            // reihe wird geschlossen
            echo "</tr>";
        }

        // trylist-tabelle und -div werden geschlosse
        echo "</table></div>";


        //-----------------------------------------------------------------
        // indicators tabelle


        // indicators tabelle wird auch von div umschlosse
        echo "<div class='indicators'><table>";

        // für alle einträge in indicators wird die schleife durchgelaufen
        foreach ($_SESSION['indicators'] as $currentIndicators) {

            // reihe wird geöffnet
            echo "<tr>";

            // der string mit den indikatoren wird in ein array umgewandelt, also anstatt "000" haben wir { 0, 0, 0},
            // durch welches wir wieder eine schleife laufen lassen können
            $indicators = str_split($currentIndicators);

            // länge des in der letzten zeile entstandenen arrays
            $length = count($indicators);

            // die aktuelle zeile von indikatoren wird durchlaufen und angezeigt
            for ($i = 0; $i < $length; $i++) {

                // colorcode für den entsprechenden indikator
                $colorCode = getColorCodeForIndicator($indicators[$i]);

                // das fertige td wird ausgegeben mit entsprechender hintergrundfarbe
                echo "<td bgcolor='$colorCode' class='indicator'></td>";
            }

            // tr wird geschlossen
            echo "</tr>";
        }

        // table und div von indicators wird geschlossen
        echo "</table></div>";

        // wrapper div wird geschlossen
        echo "</div>";
    }
}


// highscores werden ausgegeben
function printHighscores($highscores)
{

    // tabelle wird geöffnet
    echo "<table>";

    // tabellenkopf wird fest ausgegeben
    echo "<tr><th>Name</th><th>Versuche</th><th>Datum</th></tr>";

    // für alle reihen in highscores wird die schleife durchlaufen
    while ($row = $highscores->fetch_array()) {

        // reihe geöffnet
        echo "<tr>";

        // name wird ausgegeben
        echo "<td>" . $row['name'] . "</td>";

        // score wird ausgegeben
        echo "<td>" . $row['score'] . "</td>";

        // datum wird ausgegeben
        echo "<td>" . $row['date'] . "</td>";

        // reihe wird geschlossen
        echo "</tr>";
    }

    // tabelle wird geschlossen
    echo "</table>";
}


// gibt das eingabefeld für den namen aus um den highscore zu speichern
function printInputForName()
{

    // neues form wird geöffnet
    echo "<form action='mastermind.php' method='post' id='highscore_form'>";

    // eigentliches eingabefeld wird ausgegeben, default wert ist guest
    echo "<input type='text' name='username' value='guest'>";

    // button zum submitten der eingegebenen daten
    echo "<button type='submit' form='highscore_form' value='Submit'>Submit</button>";

    // form wird geschlossen
    echo "</form>";
}


// gibt eingabefelder für das spiel aus
function printInputTools()
{

    // form wird geöffnet
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
            $shown_value = getColorNameForValue($j);

            // colorcode für die auswahlmöglichkeiten
            $colorCode = getColorCodeForValue($j);


            // eigentlicher eintrag im auswahlelement mit value 0 bis 7 und mit angezeigtem value ("farbe") von 1 bis 8
            // und entsprechender farbe als hintergrundfarbe
            echo "<option value='{$j}' style='background-color:$colorCode'>{$shown_value}</option>";
        }

        // select wird geschlossen
        echo "</select>";
    }


    // button zum abschicken des versuchs
    echo "<button type='submit' form='form1' value='Submit'>Submit</button>";

    // form wird geschlossen
    echo "</form>";
}

?>


</body>


</html>
