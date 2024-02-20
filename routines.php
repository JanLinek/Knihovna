<?php

function Connect()
{
    global $connection;
    if (empty(mysqli_fetch_array(mysqli_query(mysqli_connect("localhost", "root", ""),
                "SHOW DATABASES LIKE 'knihovna'")))) {
        VytvorDatabazi();
    }
    $connection = mysqli_connect("localhost", "root", "", "knihovna");
}

function NactiDatabazi($jmenoDatabaze)
{
    global $hlavicka;
    global $hlavickaZpracovani;
    global $jmenoTabulky;
    switch ($jmenoDatabaze) {
        case "knihy":
            $hlavicka = array(
                "id",
                "Titul knihy",
                "Jméno autora",
                "Příjmení autora",
                "Rok vydání",
                "Půjčeno dne",
                "Půjčeno čtenáři"
            );
            $hlavickaZpracovani = array_slice($hlavicka, 0, 5);
            $jmenoTabulky = "knihy";
            break;
        case "ctenari":
            $hlavicka = array("id", "Jméno", "Příjmení", "E-mail", "Adresa", "Telefon", "Půjčeno knih");
            $hlavickaZpracovani = array_slice($hlavicka, 0, 6);
            $jmenoTabulky = "ctenari";
            break;
    }
}

function HlavniProcedura()
{
    global $hlavicka;
    global $hlavickaZpracovani;
    global $jmenoTabulky;
    global $nactenyFormular;
    $odeslano = $_POST;
    end($odeslano);
    $tlacitkoName = key($odeslano);
    VypisNadpis($jmenoTabulky, $tlacitkoName);
    $nactenyFormular = ZpracujFormularVyberu($hlavickaZpracovani);
    if (str_contains($tlacitkoName, "vypis")) {
        VypsatDatabazi($jmenoTabulky);
    } else if (str_contains($tlacitkoName, "detail")) {
        VypsatDetail($jmenoTabulky, $tlacitkoName);
    } else if (str_contains($tlacitkoName, "vymazat")) {
        Vymazat($jmenoTabulky, $tlacitkoName);
    } else if (str_contains($tlacitkoName, "pridat")) {
        Pridat($jmenoTabulky, $tlacitkoName);
    } else if (str_contains($tlacitkoName, "editovat")) {
        Editovat($jmenoTabulky, $tlacitkoName);
    }
}

function VypsatDatabazi($jmenoTabulky)
{
    //PROCEDURA PRO VYPIS CELE DATABAZE
    global $hlavicka;
    if (($jmenoTabulky) == "knihy") {
        echo "<h2>Výpis knih v knihovně</h2>";
    } else {
        echo "<h2>Výpis registrovaných čtenářů</h2>";
    }
    VypisTabulku(NactiTabulku($jmenoTabulky), $hlavicka);
}

function VypsatDetail($jmenoTabulky, $tlacitkoName)
{
    global $hlavickaZpracovani;
    global $hlavicka;
    if (str_contains($tlacitkoName, "Vyber")) {
        $id = ExtrahovatId($tlacitkoName);
        VypisDetailDleId($hlavicka, $id);
        DoplneniDetailu($jmenoTabulky, $id);
    } else {
        FormularHledani($hlavickaZpracovani, "Hledat položku pro detailní výpis", "$tlacitkoName");
        FormularVyberu("Potvrdit", "$tlacitkoName" . "Vyber", "$jmenoTabulky.php");
    }
}

function Vymazat($jmenoTabulky, $tlacitkoName)
{
    global $hlavickaZpracovani;
    global $hlavicka;
    if (str_contains($tlacitkoName, "Vyber")) {
        $id = ExtrahovatId($tlacitkoName);
        if (!str_contains($tlacitkoName, "potvrzeno")) {
            echo "<h4>Položka vybraná k vymazání:$id</h4>";
            VypisDetailDleId($hlavicka, $id);
            MoznoSmazat($jmenoTabulky, $id);
            Potvrdit("Opravdu smazat?", $tlacitkoName, $id);
        } else {
            echo "<h4>Byla vymazána položka:</h4>";
            VypisDetailDleId($hlavicka, $id);
            if ($jmenoTabulky == "knihy") {
                $pujcenoCtenari = PolozkaDleID($id)["ctenar"];
                if ($pujcenoCtenari)
                    ProvedSQL("UPDATE ctenari SET pujceno_knih=pujceno_knih-1 WHERE id =$pujcenoCtenari");
            }
            if ($jmenoTabulky == "ctenari") {
                ProvedSQL("UPDATE knihy SET pujceno=null, ctenar=null WHERE ctenar=$id");
            }
            ProvedSQL("DELETE FROM $jmenoTabulky WHERE id = $id");
        }
    } else {
        FormularHledani($hlavickaZpracovani, "Hledat položku pro vymazání", "$tlacitkoName");
        FormularVyberu("Potvrdit", "$tlacitkoName" . "Vyber", "$jmenoTabulky.php");
    }
}

function Pridat($jmenoTabulky, $tlacitkoName)
{
    global $hlavickaZpracovani;
    global $nactenyFormular;
    if (str_contains($tlacitkoName, "Vyplneno")) {
        $nevyplnenych = TestUplnosti();
        $duplicitnich = TestDuplicity();
        if ($nevyplnenych > 0) {
            FormularHledani($hlavickaZpracovani, "Přidat ZÁZNAM", $tlacitkoName);
            VypisZpravu("Vyplňte prosím VŠECHNY položky záznamu.");
        } else if ($duplicitnich > 0 && !str_contains($tlacitkoName, "Potvrzeno")) {
            FormularHledani($hlavickaZpracovani, "Přesto přidat", "Potvrzeno" . $tlacitkoName);
            VypisZpravu("Záznam se zadanými parametry již existuje.");
            VypisZpravu("Opravdu přidat do databáze?");
        } else {
            PridejNovyZaznam($nactenyFormular);
        }
    } else {
        FormularHledani($hlavickaZpracovani, "Přidat záznam", "Vyplneno" . $tlacitkoName);
    }
}

function Editovat($jmenoTabulky, $tlacitkoName)
{
    global $hlavickaZpracovani;
    if (str_contains($tlacitkoName, "Vyber")) {
        $id = ExtrahovatId($tlacitkoName);
        if (!str_contains($tlacitkoName, "editKonec")) {
            EditujZaznam($hlavickaZpracovani, $tlacitkoName, $id);
        } else {
            $nevyplnenych = TestUplnosti();
            $duplicitnich = TestDuplicity();
            if ($nevyplnenych > 0) {
                FormularHledani($hlavickaZpracovani, "Uložit zadané údaje", $tlacitkoName);
                VypisZpravu("Vyplňte prosím VŠECHNY položky záznamu.");
            } else if ($duplicitnich > 0 && !str_contains($tlacitkoName, "Potvrzeno")) {
                FormularHledani($hlavickaZpracovani, "Přesto uložit", "Potvrzeno" . $tlacitkoName);
                VypisZpravu("Záznam se zadanými parametry již existuje.");
                VypisZpravu("Opravdu uložit záznam?");
            } else {
                DokonciEditaci($tlacitkoName);
            }
        }
    } else {
        FormularHledani($hlavickaZpracovani, "Hledat položku pro editaci", "$tlacitkoName");
        FormularVyberu("Potvrdit", "$tlacitkoName" . "Vyber", "$jmenoTabulky.php");
    }
}

function VypisNadpis($jmenoTabulky, $tlacitkoName)
{
    $jmenoTabulkyNadpis = $jmenoTabulky;
    if ($jmenoTabulky == "ctenari") {
        $jmenoTabulkyNadpis = "čtenáře";
    }
    if (str_contains($tlacitkoName, "detail")) {
        echo "<h2>Detailní výpis $jmenoTabulkyNadpis</h2>";
    }
    if (str_contains($tlacitkoName, "editovat")) {
        echo "<h2>Editace $jmenoTabulkyNadpis</h2>";
    }
    if (str_contains($tlacitkoName, "pridat")) {
        echo "<h2>Přidání $jmenoTabulkyNadpis</h2>";
    }
    if (str_contains($tlacitkoName, "vymazat")) {
        echo "<h2>Vymazání $jmenoTabulkyNadpis</h2>";
    }
    if (str_contains($tlacitkoName, "vratit")) {
        echo "<h2>Vrácení $jmenoTabulkyNadpis</h2>";
    }
    if (str_contains($tlacitkoName, "pujcit")) {
        echo "<h2>Půjčení $jmenoTabulkyNadpis</h2>";
    }
}

function NactiTabulku($jmenoTabulky)
{
    global $connection;
    Connect();
    global $jmenoTabulky;
    $tabulka = [];
    $result = mysqli_query($connection, "SELECT * FROM " . $jmenoTabulky);
    while ($row = mysqli_fetch_assoc($result)) {
        $tabulka[] = $row;
    }
    return $tabulka;
}

function VypisTabulku($tabulka, $hlavicka)
{
    echo '<table style="width:70%">';
    echo "<tr>";
    foreach ($hlavicka as $bunkaHlavicky) {
        echo "<th> " . $bunkaHlavicky . " </th>";
    }
    echo "</tr>";
    foreach ($tabulka as $radek) {
        echo "<tr>";
        if (in_array("Půjčeno dne", $hlavicka)) {
            if ($radek["pujceno"])
                $radek["pujceno"] = date("d-m-Y", strtotime($radek["pujceno"]));
        }
        foreach ($radek as $bunka) {
            echo "<td> " . $bunka . " </td>";
        }
        echo "</tr>";
    }
    echo "</table></br>";
}

function VypisDetailDleId($hlavicka, $id)
{
    global $jmenoTabulky;
    global $connection;
    $result = mysqli_query($connection, "SELECT * FROM $jmenoTabulky WHERE id = $id");
    if ($result)
        VypisTabulku($result, $hlavicka);
    else
        echo "Něco se posralo...";
}

function FormularHledani($hlavicka, $tlacitkoValue, $tlacitkoName)
{
    if (str_contains($tlacitkoName, "prid") || str_contains($tlacitkoName, "editKonec")) {
        echo "<h4>Vyplňte prosím všechny položky:</h4>";
    } else {
        echo "<h4>Vyplňte prosím filtr pro hledání...</h4>";
    }
    global $nactenyFormular;
    $names = NactiSloupce("Field");
    $types = NactiSloupce("Type");
    $values = [];
    echo '<form method="post">';
    for (
        $i = 0;
        $i < count($hlavicka);
        $i++
    ) {
        echo '<div class="form-floating w-50">';
        if ($i) {
            $name = $names[$i];
            $type = $types[$i];
            $value = "";
            if ($nactenyFormular[$name]) {
                $value = $nactenyFormular[$name]; //echo "načtená položka:" . $nactenyFormular[$name];
            }
            if (isset($_POST["$name"])) {
                $value = $_POST["$name"];
            }
            $values[] = $value;
            if (str_contains($type, "int")) {
                $type = "number";
            } else {
                $type = "text";
            }
            echo '<input type="' . $type . '" class="form-control" id="floatingInput" value="' . $value . '" name="' . $name . '"
                   placeholder="' . "$hlavicka[$i]" . '"><br/>';
            echo '<label for="floatingInput">' . $hlavicka[$i] . '</label>';
            echo '</div>';
        }
    }
    echo '<button class="tlacitko3" name ="' . $tlacitkoName . '">' . $tlacitkoValue . '</button>';
    echo '</form>';
    echo '</div>';
    return $values;
}

function NactiSloupce($key)
{
    global $jmenoTabulky;
    global $connection;
    Connect();
    $sloupecInfo = [];
    $sql = "SHOW COLUMNS FROM $jmenoTabulky";
    $result = mysqli_query($connection, $sql);
    foreach ($result as $column) {
        $sloupecInfo[] = $column["$key"];
    }
    return $sloupecInfo;
}

function ZpracujFormularVyberu($hlavickaZpracovaniAktualni)
{
    $names = NactiSloupce("Field");
    $vstupy = [];
    for ($i = 1; $i < count($hlavickaZpracovaniAktualni); $i++) {
        $name = $names[$i];
        $value = null;
        if (isset($_POST["$name"])) {
            $value = $_POST["$name"];
        }
        $vstupy += [$name => $value];
    }
    return $vstupy;
}

function PridejNovyZaznam($vstupy)
{
    global $hlavicka;
    global $jmenoTabulky;
    global $connection;
    Connect();
    $types = array_slice(NactiSloupce("Type"), 1);
    $query1 = "INSERT INTO $jmenoTabulky (";
    $query2 = "VALUES (";
    for ($i = 0; $i < count($vstupy); $i++) {
        $name = array_keys($vstupy)[$i];
        $vstup = $vstupy[$name];
        if (!str_contains($types[$i], "int")) {
            $vstup = "'" . $vstup . "'";
        }
        $query1 = $query1 . $name;
        $query2 = $query2 . $vstup;
        if ($i < (count($vstupy) - 1)) {
            $query1 = $query1 . ",";
            $query2 = $query2 . ",";
        }
    }
    $sql = $query1 . ") " . $query2 . ")";
    $result2 = mysqli_query($connection, $sql);
    if ($result2) {
        $last_id = mysqli_insert_id($connection);
        echo "<h3>Do databáze přidán nový záznam:</h3>";
        VypisDetailDleId($hlavicka, $last_id);
    }
}

function FormularVyberu($tlacitkoText, $tlacitkoName, $odkud)
{
    global $jmenoTabulky;
    global $nactenyFormular;
    $text = "";
    foreach ($nactenyFormular as $key => $value) {
        if ($value) {
            $text = $text . $key
                . ' LIKE "%' . $value . '%" AND ';
        }
    }
    if ($text) {
        $text = " WHERE " . substr($text, 0, strlen($text) - 5);
    }
    $result = ProvedSQL("SELECT * FROM " . $jmenoTabulky . $text);
    $nalezenych = 0;
    $kodInputu = "";
    while ($row = mysqli_fetch_assoc($result)) {
        $nalezenych++;
        $textRadku = "";
        $sloupecku = count($row) - 1;
        foreach ($row as $bunka) {
            $sloupecku--;
            if ($sloupecku) {
                $textRadku = $textRadku . $bunka . " ";
                if (array_search($bunka, $row) != "jmeno") {
                    $textRadku = $textRadku . " - ";
                }
            }
        }
        $textRadkuFinal = substr($textRadku, 0, strlen($textRadku) - 7);
        $id = $row["id"];
        $kodInputu .= "<option value = '$id'>$textRadkuFinal</option>";
    }
    if ($nalezenych) {
        if ($nalezenych == 1) {
            echo "<h4>Hledání odpovídá 1 záznam:</h4>";
        } else if ($nalezenych < 5) {
            echo "<h4>Hledání odpovídají $nalezenych záznamy:</h4>";
        } else {
            echo "<h4>Hledání odpovídá $nalezenych záznamů:</h4>";
        }
        echo "<form action = '$odkud' method = 'post'>";
        echo '<select name = "id" id="">';
        echo $kodInputu;
        echo "</select></br>";
        echo "<button style='margin-top: 10px' class='tlacitko3' "
            . "name ='$tlacitkoName'>$tlacitkoText</button>";
        echo "</form>";
    } else {
        echo "<h3>Výsledky hledání:</h3>";
        echo '<button name ="' . str_replace("Vyber", "", $tlacitkoName) . '" style="background-color: darkblue">Pro vaše zadání nebylo nic nalezeno.</button>';
    }
}

function DokonciEditaci($tlacitkoName)
{
    global $jmenoTabulky;
    global $nactenyFormular;
    global $hlavicka;
    $rozbijTlacitko = (explode(",", $tlacitkoName));
    $id = end($rozbijTlacitko);
    if (EditaceOK()) {
        $text = "";
        foreach ($nactenyFormular as $name => $value) {
            if ($name != "rok" && $name != "telefon") {
                $value = '"' . $value . '"';
            }
            $text .= $name . " = " . $value . ", ";
        }
        $textFinal = substr($text, 0, strlen($text) - 2);
        ProvedSQL("UPDATE $jmenoTabulky SET $textFinal WHERE id=$id");
        echo "<h3>Aktualizován záznam:</h3>";
        VypisDetailDleId($hlavicka, $id);
    }
}

function EditujZaznam($hlavickaZpracovani, $tlacitkoName, $id)
{
    global $nactenyFormular;
    global $jmenoTabulky;
    global $hlavicka;
    $result = ProvedSQL("SELECT * FROM $jmenoTabulky WHERE id=" . $id);
    $nactenyFormular = mysqli_fetch_assoc($result);
    echo "<h4>Bude změněn záznam:</h4>";
    //print_r($nactenyFormular);
    VypisDetailDleId($hlavicka, $id);
    FormularHledani($hlavickaZpracovani, "Uložit zadané údaje", $tlacitkoName . "editKonec," . $id);
}

function TestUplnosti()
{
    global $nactenyFormular;
    $prazdnych = 0;
    foreach ($nactenyFormular as $name => $value) {
        if (!$value)
            $prazdnych++;
    }
    return ($prazdnych);
}

function TestDuplicity()
{
    global $jmenoTabulky;
    global $nactenyFormular;
    global $connection;
    $text = "";
    foreach ($nactenyFormular as $name => $value) {
        $subtext = $value;
        if ($name != "rok" && $name != "telefon") {
            $subtext = '"' . $value . '"';
        }
        if ($subtext == "") {
            $subtext = "0";
        }
        $text .= $name . " = " . $subtext . " AND ";
    }
    $sql = "SELECT COUNT(*) FROM $jmenoTabulky WHERE " . substr($text, 0, strlen($text) - 4);
    $result = mysqli_query($connection, $sql);

    $row = mysqli_fetch_assoc($result);
    $duplicitnich = $row["COUNT(*)"];
    return $duplicitnich;
}

function PolozkaDleID($id)
{
    global $jmenoTabulky;
    global $hlavicka;
    global $connection;
    $sql = "SELECT * FROM $jmenoTabulky WHERE id=" . $id;
    $result = mysqli_query($connection, $sql);
    return mysqli_fetch_assoc($result);
}

function EditaceOK()
{
    return true;
}

function VypisZpravu($zprava)
{
    global $tlacitkoName;
    echo "<h2> </h2>";
    echo '<button name ="' . str_replace("Vyber", "", $tlacitkoName) . '" style="background-color: darkblue">' . $zprava . '</button>';
}

function Potvrdit($textOtazky, $tlacitkoName, $id)
{
    echo '<button style="background-color: brown">' . $textOtazky . '</button>';
    echo '<form method="post">';
    echo '</br><input type="submit" name = "potvrzeno' . $tlacitkoName . ',' . $id . '" value="Potvrdit">';
    echo "</form>";
}

function ProvedSQL($sql)
{
    global $connection;
    Connect();
    return mysqli_query($connection, $sql);
}

function ExtrahovatId($tlacitkoName)
{
    if (isset($_POST["id"])) {
        $id = $_POST["id"];
    } else {
        $rozbijTlacitko = explode(",", $tlacitkoName);
        $id = end($rozbijTlacitko);
    }
    return $id;
}

function MoznoSmazat($jmenoTabulky, $id)
{
    if ($jmenoTabulky == "knihy" && PolozkaDleID($id)["pujceno"]) {
        VypisZpravu("POZOR, kniha je půjčena!");
    }
    if ($jmenoTabulky == "ctenari" && PolozkaDleID($id)["pujceno_knih"] > 0) {
        VypisZpravu("POZOR, čtenář má půjčené knihy!");
    }
}

function DoplneniDetailu($jmenoTabulky, $id)
{
    if ($jmenoTabulky == "knihy" && PolozkaDleID($id)["pujceno"]) {
        $idCtenare = PolozkaDleID($id)["ctenar"];
        echo "<h3>Zapůjčeno čtenáři:</h3>";
        global $hlavicka;
        NactiDatabazi("ctenari");
        VypisDetailDleId($hlavicka, $idCtenare);
    }
    if ($jmenoTabulky == "ctenari" && PolozkaDleID($id)["pujceno_knih"] > 0) {
        echo "<h4>Čtenář má půjčeny knihy:</h4>";
        $sql = "SELECT * FROM knihy WHERE ctenar=$id";
        $pujceneKnihy = ProvedSQL($sql);
        global $hlavicka;
        NactiDatabazi("knihy");
        VypisTabulku($pujceneKnihy, $hlavicka);
    }
}

function VytvorDatabazi()
{
    global $connection;
    $zalozitDB = mysqli_query(mysqli_connect("localhost", "root", ""), "CREATE DATABASE knihovna");
    ProvedSQL("CREATE TABLE ctenari (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        jmeno VARCHAR(20),
        prijmeni VARCHAR(20),
        email VARCHAR(30),
        adresa VARCHAR(50),
        telefon INT(9),
        pujceno_knih INT(5)
        )");

    ProvedSQL("INSERT INTO ctenari
    (jmeno,prijmeni,email,adresa,telefon,pujceno_knih)
    VALUES 
    ('Jan',  'Novák',  'jan.novak@mymail.cz', 'Družstevní 151, Dobruška, 51801',796236665, 0),
    ('Tomáš', 'Marný', 'tomas.marny@mymail.cz', 'Platnéřská 44, Solnice, 51701',763585531,0),
    ('Josef', 'Nový', 'josef.novy@mymail.cz', 'Rozdělovací 24, Opočno, 51795',731693494,0),
    ('Alfons', 'Svoboda', 'alfons.svoboda@mymail.cz', 'Riegrova 3, Hradec Králové, 50002',755698424,0),
    ('Ludmila', 'Dvořáková', 'ludmila.dvorakova@mymail.cz', 'Palackého 31, Nové Město nad Metují, 51697',729411265,0),
    ('Petr', 'Černý', 'petr.cerny@mymail.cz', 'Kroupova 10, Náchod, 51754',753302976,0),
    ('Vladimír', 'Pokorný', 'vladimir.pokorny@mymail.cz', 'Máchova 197, Broumov, 51485',785234137,0),
    ('Ondřej', 'Bohatý', 'ondrej.bohaty@mymail.cz', 'Havlíčkova 4, Trutnov, 51201',743204382,0),
    ('Vítězslav', 'Churý', 'vita.chury@mymail.cz', 'Růžová 85, Týniště nad Orlicí, 51726',797550145,0),
    ('Pavel', 'Procházka', 'pavel.prochazka@mymail.cz', 'Krkonošská 174, Vrchlabí, 51002',728292094,0)");
    ProvedSQL("CREATE TABLE knihy (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        titul VARCHAR(30),
        jmeno VARCHAR(30),
        prijmeni VARCHAR(50),
        rok VARCHAR(50),
        pujceno DATETIME NULL,
        ctenar INT(10) NULL
        )");
    ProvedSQL("INSERT INTO knihy
    (titul,jmeno,prijmeni,rok,pujceno,ctenar)
    VALUES 
    ('Martin Eden','Jack','London',1960,null,null),	
    ('Poslední Mohykán','James Fenimore','Cooper',1965,null,null),			
    ('Pýcha a předsudek','Jane','Austin',2006,null,null),			
    ('Egypťan Sinuhet','Mika','Waltari',2013,null,null),			
    ('Gejša','Arthur','Golden',2005,null,null),			
    ('Ranhojič','Noah','Gordon',2001,null,null),			
    ('Petrolejový princ','Karl','May',1980,null,null),			
    ('Já, Claudius','Robert','Graves',1994,null,null),			
    ('Ivanhoe','Walter','Scott',1929,null,null),
    ('Psohlavci','Alois','Jirásek',1956,null,null)");
}