<html>
    <head>
        <meta charset="UTF-8">
        <title>Čtenáři</title>
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
    <body background="reader.jpg">
        <h1>Databáze čtenářů</h1>
        <form method="post" action="index.php">
            <button class="tlacitko4"  >Zpět na hlavní stranu</button>
        </form>
        <form method="post" action="ctenari.php">   
            <button  class="tlacitko2" name ="vypis">Výpis čtenářů</button>
            <button class="tlacitko2" name ="detail" >Detail čtenáře</button>
            <button  class="tlacitko2" name ="pridat" >Přidat čtenáře</button>
            <button  class="tlacitko2" name ="editovat" >Editovat čtenáře</button>
            <button  class="tlacitko2" name ="vymazat" >Vymazat čtenáře</button>
        </form> 
        <?php
        include 'routines.php';

        NactiDatabazi("ctenari");
        if (isset($_POST)) {
            HlavniProcedura();
        }
        ?>
    </body>
</html>