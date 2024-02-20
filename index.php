<!DOCTYPE html>

<html>
    <head>
        <meta charset="UTF-8">
        <title>Knihovna</title>
        <link rel="stylesheet" type="text/css" href="styles.css" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
        <style>
            button {
                padding: 5px 20px;
                margin-bottom: 10px;
                color: #fff;
                border: 1px solid black;
                border-radius: 10px;
                cursor: default;
                text-decoration: none;
            }
            body {
                margin: 30px;
                background-color: rgba(255, 255, 255, 0.5);
                background-blend-mode: overlay;
                height: 100%;
                background-repeat: no-repeat;
                background-size: cover;
            }
        </style>
    </head>
    <body background="library.jpg" >

        <h1>Vítejte v knihovně !</h1>
        <p></p>
        <?php
        $volba = ""; //onsubmit="return confirm('Are you sure you want to submit?');"
        ?>
        <form method="post" action="knihy.php" > 
            <button class="tlacitko1" name ="vypis" >Výpis knih</button>
            <button class="tlacitko1" name ="detail" >Detail knihy</button>
            <button class="tlacitko1" name ="pridat" >Přidat knihu</button>
            <button class="tlacitko1" name ="editovat" >Editovat knihu</button>
            <button class="tlacitko1" name ="vymazat" >Vymazat knihu</button>
        </form>
        <form method="post" action="ctenari.php">   
            <button class="tlacitko2" name ="vypis">Výpis čtenářů</button>
            <button class="tlacitko2" name ="detail" >Detail čtenáře</button>
            <button class="tlacitko2" name ="pridat" >Přidat čtenáře</button>
            <button class="tlacitko2" name ="editovat" >Editovat čtenáře</button>
            <button class="tlacitko2" name ="vymazat" >Vymazat čtenáře</button>
        </form> 
        <form method="post" action="servis.php">   
            <button  class="tlacitko3" name ="pujcit">Půjčit knihu</button>
            <button  class="tlacitko3" name ="vratit">Vrátit knihu</button>
        </form> 

    </body>
</html>
