<html>
    <head>
        <meta charset="UTF-8">
        <title>Servis</title>
        <link rel="stylesheet" type="text/css" href="styles.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <style>
            body {
                margin: 30px;
                background-color: rgba(255, 255, 255, 0.5);
                background-blend-mode: overlay;
                height: 100%;
                background-repeat: no-repeat;
                background-size: cover;
            }
            button {
                padding: 5px 20px;
                color: #fff;
                border: 1px solid black;
                border-radius: 10px;
                cursor: default;
                text-decoration: none;
            }
            table {
                width: 150%;
            }
            th {
                min-width: 1%;
            }
            table, td {
                border:1px solid black;
                padding: 5px;
                background-color: #D6EEEE;
            }
            th {
                border: 3px solid black;
                padding: 5px;
                background-color: #D6AEAE;

            }

        </style>
    </head>
    <body background="interior.jpg">
        <h1>Půjčování a vracení knih</h1>
        <form method="post" action="index.php">
            <button class="tlacitko4"  >Zpět na hlavní stranu</button>
        </form>
        <form method="post" action="servis.php">   
            <button  class="tlacitko3" name ="pujcit">Půjčit knihu</button>
            <button  class="tlacitko3" name ="vratit">Vrátit knihu</button>
        </form> 


        <?php
        include 'routines.php';
        $odeslano = $_POST;
        end($odeslano);
        $tlacitkoName = key($odeslano);
        global $datum;
        if ($_POST) {
            if (isset($_POST['datum']) && $_POST['datum']) {
                $datum = $_POST["datum"];
            } else {
                $datum = date('Y-m-d');
            }

            VypisNadpis("knihy", $tlacitkoName);
            NactiDatabazi("knihy");
            $nactenyFormular = ZpracujFormularVyberu($hlavickaZpracovani);
            if (str_contains($tlacitkoName, "pujcit")) {
                Pujcit($tlacitkoName);
            } else if (str_contains($tlacitkoName, "vratit")) {
                Vratit($tlacitkoName);
            }
        }

        function Vratit($tlacitkoName) {
            global $hlavickaZpracovani;
            global $hlavicka;
            if (str_contains($tlacitkoName, "potvrzeno")) {
                echo "<h3>Byla vrácena kniha:</h3>";
            }
            if (str_contains($tlacitkoName, "Vyber")) {
                $idKnihy = ExtrahovatId($tlacitkoName);
                VypisDetailDleId($hlavicka, $idKnihy);
                $knihaVratit = PolozkaDleID($idKnihy);
                if (!$knihaVratit["pujceno"]) {
                    VypisZpravu("Kniha není půjčena, není třeba vracet.");
                } else {
                    DokonciVraceni($tlacitkoName, $idKnihy);
                }
            } else {
                FormularHledani($hlavickaZpracovani, "Hledat knihu pro vrácení", $tlacitkoName);
                FormularVyberu("Potvrdit", "$tlacitkoName" . "Vyber", "servis.php");
            }
        }

        function DokonciVraceni($tlacitkoName, $idKnihy) {
            NactiDatabazi("knihy");
            $knihaKterouVratit = PolozkaDleID($idKnihy);
            $idCtenare = $knihaKterouVratit["ctenar"];
            if (str_contains($tlacitkoName, "potvrzeno")) {
                echo "<h3>Vrácena od čtenáře:</h3>";
            } else {
                echo "<h3>Zapůjčena čtenáři: </h3>";
            }
            global $hlavicka;
            NactiDatabazi("ctenari");
            VypisDetailDleId($hlavicka, $idCtenare);
            if (!str_contains($tlacitkoName, "potvrzeno")) {              
                Potvrdit("<h5>Potvrďte prosím vrácení knihy:</h5>", $tlacitkoName, $idKnihy);
            } else {
                ProvedSQL('UPDATE knihy SET pujceno=NULL, ctenar=NULL WHERE id=' . $idKnihy);
                ProvedSQL("UPDATE ctenari SET pujceno_knih=pujceno_knih-1 WHERE id=$idCtenare");
            }
        }

        function Pujcit($tlacitkoName) {
            global $hlavickaZpracovani;
            global $nactenyFormular;
            $id = ExtrahovatId($tlacitkoName);
            if (str_contains($tlacitkoName, "Konec")) {
                PujcKnihu($tlacitkoName, $id);
            } else
            if (str_contains($tlacitkoName, "Vyber")) {
                global $hlavicka;
                echo"<h3>Kniha vybraná k půjčení:</h3>";
                VypisDetailDleId($hlavicka, $id);
                $knihaKterouPujcit = PolozkaDleID($id);
                NactiDatabazi("ctenari");
                global $hlavickaZpracovani;
                global $hlavicka;
                if ($knihaKterouPujcit["pujceno"]) {
                    VypisZpravu("Tuto knihu nelze půjčit. Už je půjčena čtenáři:");
                    echo"<br><br>";
                    VypisDetailDleId($hlavicka, $knihaKterouPujcit["ctenar"]);
                } else {
                    //FORMULÁŘ PRO HLEDÁNÍ CTENÁŘŮ
                    echo"<h2>Pujčit čtenáři:</h2>";
                    $tlacitkoValue = "Hledej čtenáře";
                    $nactenyFormular = ZpracujFormularVyberu($hlavickaZpracovani);
                    FormularHledani($hlavickaZpracovani, $tlacitkoValue, "$tlacitkoName");
                    FormularVyberu("Potvrdit", "$tlacitkoName" . "Konec," . $id, "servis.php");
                }
            } else {
                NactiDatabazi("knihy");
                global $hlavicka;
                global $hlavickaZpracovani;
                $tlacitkoValue = "Hledat knihu";
                FormularHledani($hlavickaZpracovani, $tlacitkoValue, $tlacitkoName);
                FormularVyberu("Potvrdit", $tlacitkoName . "Vyber", "servis.php");
            }
        }

        function PujcKnihu($tlacitkoName, $id) {
            $rozbijTlacitko = explode(",", $tlacitkoName);
            global $datum;
            if (str_contains($tlacitkoName, "Datum")) {
                //DOKONČENÍ SERVISU - PŮJČENÍ              
                echo "<h3>Provedeno zapůjčení knihy:</h3>";
                global $hlavicka;
                global $jmenoTabulky;
                $idCtenare = end($rozbijTlacitko);
                $idKnihy = $rozbijTlacitko[count($rozbijTlacitko) - 2];
                NactiDatabazi("knihy");
                ProvedSQL('UPDATE ' . $jmenoTabulky . ' SET pujceno="' . $datum . '", ctenar=' . $idCtenare . ' WHERE id=' . $idKnihy);
                VypisDetailDleId($hlavicka, $idKnihy);
                ProvedSQL("UPDATE ctenari SET pujceno_knih=pujceno_knih+1 WHERE id=$idCtenare");
                NactiDatabazi("ctenari");
                echo "<h3>Zapůjčeno čtenáři:</h3>";
                VypisDetailDleId($hlavicka, $idCtenare);
            } else {
                $idKnihy = end($rozbijTlacitko);
                $idCtenare = ExtrahovatId($tlacitkoName);
                echo "<h3>Bude půjčena kniha:</h3>";
                global $hlavicka;
                global $jmenoTabulky;
                NactiDatabazi("knihy");
                VypisDetailDleId($hlavicka, $idKnihy);
                echo "<h3>Kniha bude půjčena čtenáři:</h3>";
                NactiDatabazi("ctenari");
                VypisDetailDleId($hlavicka, $id);
                echo"<h4>Potvrďte, případně změňte datum půjčení:</h4>";
                echo '<form method="post" action="servis.php">';
                echo '<input type="date" name="datum" value="' . $datum . '"/>';
                echo '<input type="submit" name = "Datum' . $tlacitkoName . "," . $id . '" value="Potvrdit datum">';
                echo "</form>";
            }
        }
        ?>
    </body>
</html>