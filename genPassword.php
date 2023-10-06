<!DOCTYPE html> <html lang="en"> <head> 
    <meta charset="UTF-8"> <title>Split 
    calculator | generatePasswword</title> 
    <link rel="stylesheet" 
    href="style/default.css"> <script 
    src="js/sha3.js"></script> <script 
    type="text/javascript">
        function changePasswords() { if 
            (document.getElementById("password").value.length 
            != 0) {
                var hash1 = 
                CryptoJS.SHA3(document.getElementById("password").value, 
                {
                    outputLength: 512
                });
                document.getElementById("password").value 
                = hash1;
            }
        }
    </script> </head> <body> <?php echo 
    $_POST["password"]; ?> <header>
        <h1><a href="/">Split 
        calculator</a></h1>
    </header> <br> <section> <div 
        class='container'>
            <?php echo "<form 
            onsubmit=\"changePasswords()\" 
            method=\"POST\" action=\"\">"; 
            echo "<label 
            for=\"lname\">Password:</label>"; 
            echo "<input type=\"password\" 
            id=\"password\" 
            name=\"password\" 
            placeholder=\"Password\">"; echo 
            "<br>"; echo "<input 
            type=\"submit\" name=\"submit\" 
            value=\"Register\">"; echo 
            "</form>"; ?>
        </div> </section> </body>
</html>
