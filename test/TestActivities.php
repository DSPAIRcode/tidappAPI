<?php

declare (strict_types=1);
require_once '../src/activities.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaActivityTester(): string {
    // Kom ihåg att lägga till alla funktioner i filen!
    $retur = "";
    $retur .= test_HamtaAllaAktiviteter();
    $retur .= test_HamtaEnAktivitet();
    $retur .= test_SparaNyAktivitet();
    $retur .= test_UppdateraAktivitet();
    $retur .= test_RaderaAktivitet();

    return $retur;
}

/**
 * Tester för funktionen hämta alla aktiviteter
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaAllaAktiviteter(): string {
    $retur = "<h2>test_HamtaAllaAktiviteter</h2>";
    try {
        $svar=hamtaAllaAktiviteter();
        if($svar->getStatus()===200) {
            $retur .=  "<p class='ok'>Hämta alla aktiviteter lyckades" . count($svar->getContent() )  . " poster returnerades</p>";
        } else {
            $retur .= "<p class='error'>Hämta alla aktiviteter misslyckades<br>"
                . $svar->getStatus() . " returnerades</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Tester för funktionen hämta enskild aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaEnAktivitet(): string {
    $retur = "<h2>test_HamtaEnAktivitet</h2>";
    try {
        // misslyckas hämta post id=-1
        $svar=hamtaEnskildAktivitet("-1");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Hämta post med id=-1 misslyckas, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=-1 returnerade " .$svar->getStatus()
                . " istället för förväntat 400</p>";
        }

        // misslyckas hämta post id=0
        $svar=hamtaEnskildAktivitet("0");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Hämta post med id=0 misslyckas, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=0 returnerade " .$svar->getStatus()
                . " istället för förväntat 400</p>";
        }

        // misslyckas hämta post id=3.14
        $svar=hamtaEnskildAktivitet("3.14");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Hämta post med id=3.14 misslyckas, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Hämta post med id=3.14 returnerade " .$svar->getStatus()
                . " istället för förväntat 400</p>";
        }

        // koppla databas
        $db= connectDb();

        // skapa transaktion
        $db->beginTransaction();

        // Skapa en ny post för att vara säker på att posten finns
        $svar= sparaNyAktivitet("aktivitet" . time());
        if($svar->getStatus()===200)    {
            $nyttID=$svar->getContent()->id;
        } else {
            throw new Exception("kunde inte skapa ny post för kontroll");
        }

        // lyckas hämta skapad post
        $svar= hamtaEnskildAktivitet("$nyttID");
        if($svar->getStatus()===200)    {
            $retur .="<p class='ok'>Hämta aen aktivitet gick bra</p>";
        } else {
            $retur .="<p class='error'>hämta en aktivitet misslyckades, status " .$svar->getStatus()
                . " Returnenades istället för förväntat 200</p>";
        }
        
        // misslyckas med att hämta post med ID +1
        $nyttID++;
        $svar= hamtaEnskildAktivitet("$nyttID");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Hämta en aktivitet med id som saknas misslyckades, som förväntas</p>";
        } else {
            $retur .="<p class='error'>hämta en aktivitet med id misslyckades, status " .$svar->getStatus()
                . " Returnenades istället för förväntat 400</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        // återställa databsen
        $db->rollBack();
    }

    return $retur;
}

/**
 * Tester för funktionen spara aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_SparaNyAktivitet(): string {
    $retur = "<h2>test_SparaNyAktivitet</h2>";

    $nyAktivitet="Aktivitet" . time();

    try {

    // koppla databas
    $db= connectDb();

    // starta transaktion
    $db->beginTransaction();

    // Spara en tom aktivitet - misslyckas
    $svar= sparaNyAktivitet("");
    if($svar->getStatus()===400)    {
        $retur .="<p class='ok'>Spara tom aktivitet misslyckades, som förväntat</p>";
    } else {
        $retur .= "<p class='error'>Spara tom aktivitet misslyckades, status " . $svar->getStatus()
            . "returnerades istället som förväntat 400</p>";
    }

    // Spara ny aktivitet - lyckat
    $svar= sparaNyAktivitet($nyAktivitet);
    if($svar->getStatus()===200)    {
        $retur .="<p class='ok'>Spara aktivitet lyckades</p>";
    } else {
        $retur .="<p class='error'>Spara aktivitet misslyckades, status " . $svar->getStatus()
            . "returnerades istället som förväntat 400</p>";
    }

    // Spara ny aktivitet - misslyckat
    $svar= sparaNyAktivitet($nyAktivitet);
    if($svar->getStatus() === 400)    {
        $retur .="<p class='ok'>Spara duplicerad aktivitet misslyckades, som förväntat</p>";
    } else {
        $retur .="<p class='error'>Spara duplicerad aktivitet misslyckades, status " . $svar->getStatus()
            . "returnerades istället som förväntat 400</p>";
    }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        // återställa databas
        if ($db)    {
            $db->rollback();
        }

    return $retur;
}
}

/**
 * Tester för uppdatera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_UppdateraAktivitet(): string {
    $retur = "<h2>test_UppdateraAktivitet</h2>";

    try {
        // koppla databas
        $db= connectDb();

        // starta transaktion
        $db->beginTransaction();

        // misslyckas med att uppdatera id=1
        $svar= uppdateraAktivitet("-1", "Aktivitet");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Uppdatera aktivitet med id=-1 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet med id=-1 misslyckades, status " .$svar->getStatus()
                . " istället för förväntad 400</p>";
        }

        // misslyckas med att uppdatera id=0
        $svar= uppdateraAktivitet("0", "Aktivitet");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Uppdatera aktivitet med id=0 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet med id=0 misslyckades, status " .$svar->getStatus()
                . " istället för förväntad 400</p>";
        }

        // misslyckas med att uppdatera id=3.14
        $svar= uppdateraAktivitet("3.14", "Aktivitet");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Uppdatera aktivitet med id=3.14 misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet med id=3.14 misslyckades, status " .$svar->getStatus()
                . " istället för förväntad 400</p>";
        }

        // uppdatera med samma info misslyckas
        $aktivitet="Aktivitet" . time();
        $svar= sparaNyAktivitet("Aktivitet" . time());
        if($svar->getStatus()===200)    {
            $nyttID=$svar->getContent()->id;
        } else {
            throw new Exception("Spara aktivitet för uppdatering misslyckades");
        }

        $svar= uppdateraAktivitet("$nyttID", $aktivitet);
        if($svar->getStatus()===200 && $svar->getContent()->result===false) {
            $retur .="<p class='ok'>Uppdatera aktivitet med samma info misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet med samma info misslyckades<br>"
                ."Status:" . $svar->getStatus() ." returnerades med följande innehåll:<br> "
                    .print_r($svar->getContent(), true) ."</p>";
        }

        // lyckas med att uppdatera aktiviteter
        $svar=uppdateraAktivitet("$nyttID", "NY " . $aktivitet);
        if($svar->getStatus()===200 && $svar->getContent()->result===true)  {
            $retur .="<p class='ok'>Uppdatera aktivitet lyckades</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet med samma info misslyckades<br>"
            ."Status:" . $svar->getStatus() ." returnerades med följande innehåll:<br> "
            .print_r($svar->getContent(), true) ."</p>";
        }

        // misslycka med att uppdatera aktivitet som inte finns
        $nyttID++;
        $svar=uppdateraAktivitet("$nyttID", "what ever");
        if($svar->getStatus()===200 && $svar->getContent()->result===false)  {
            $retur .="<p class='ok'>Uppdatera aktivitet misslyckades, som förväntas</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet misslyckades<br>"
                ."Status:" . $svar->getStatus() ." returnerades med följande innehåll:<br> "
                .print_r($svar->getContent(), true) ."</p>";
        }

        // misslyckades med att uppdatera till en aktivitet som redan finns
        $aktivitet="Aktivitet" . time();
        $svar= sparaNyAktivitet("Aktivitet" . time());
        if($svar->getStatus() === 200)    {
            $nyttID=$svar->getContent()->id;
        } else {
            throw new Exception("Spara aktivitet för uppdatering misslyckades");
        }

        $svar=uppdateraAktivitet("$nyttID", "NY " . $aktivitet);
        if($svar->getStatus() === 400 )  {
            $retur .="<p class='ok'>Uppdatera aktivitet till en redan befintlig misslyckades</p>";
        } else {
            $retur .="<p class='error'>Uppdatera aktivitet till en redan befintlig misslyckades<br>"
            ."Status:" . $svar->getStatus() ." returnerades med följande innehåll:<br> "
            .print_r($svar->getContent(), true) ."</p>";
        }
    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        // återställ databasen
        if($db) {
            $db->rollBack();
        }

    }



    return $retur;
}

/**
 * Tester för funktionen radera aktivitet
 * @return string html-sträng med alla resultat för testerna 
 */
function test_RaderaAktivitet(): string {
    $retur = "<h2>test_RaderaAktivitet</h2>";
    try {
        // testa felaktig ID
        $svar= raderaAktivetet("-1");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Radera felaktig id (-1) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>Radera felaktig id (-1) misslyckades<br>"
                . $svar->getStatus() . " returnenas istället för förväntat 400.</p>";
        }

        $svar= raderaAktivetet("3.14");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Radera felaktig id (3.14) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>Radera felaktig id (3.14) misslyckades<br>"
                . $svar->getStatus() . " returnenas istället för förväntat 400.</p>";
        }

        $svar= raderaAktivetet("0");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Radera felaktig id (0) misslyckades som förväntat</p>";
        } else {
            $retur .="<p class='error'>Radera felaktig id (0) misslyckades<br>"
                . $svar->getStatus() . " returnenas istället för förväntat 400.</p>";
        }

        // testa radera befintlig
        $db= connectDb();
        $db->beginTransaction();

        $nyPost=sparaNyAktivitet("Ny aktivitet");
        if ($nyPost->getStatus()===200) {
            $nyttID=$nyPost->getContent()->id;
        } else {
            throw new Exception ("Kan inte skapa en ny aktivitet, tester avbryts");
        }
        $svar=raderaAktivetet("$nyttID");
        if($svar->getStatus()===200 && $svar->getContent()->result===true)  {
            $retur .="<p class='ok'>radera aktivitet fungerade</p>";
        } else {
            $retur .="<p class='error'>Radera aktivitet misslyckades.<br>"
                . $svar->getStatus() . " och" . var_export($svar->getContent()->result, true)
                . " returnenades istället för förväntat 200 och 'true'</p>";
        }
        $db->rollBack();

        $svar=raderaAktivetet("$nyttID");
        if($svar->getStatus()===200 && $svar->getContent()->result===false)  {
            $retur .="<p class='ok'>radera aktivitet som inte finns fungerade</p>";
        } else {
            $retur .="<p class='error'>Radera aktivitet som inte finns misslyckades.<br>"
                . $svar->getStatus() . " och" . var_export($svar->getContent()->result, true)
                . " returnenades istället för förväntat 200 och 'true'</p>";
        }

        // Testa radera som inte finns
        $svar= raderaAktivetet("$nyttID");

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}
