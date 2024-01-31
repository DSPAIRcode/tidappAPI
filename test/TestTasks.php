<?php

declare (strict_types=1);
require_once __DIR__ . '/../src/tasks.php';

/**
 * Funktion för att testa alla aktiviteter
 * @return string html-sträng med resultatet av alla tester
 */
function allaTaskTester(): string {
// Kom ihåg att lägga till alla testfunktioner
    $retur = "<h1>Testar alla uppgiftsfunktioner</h1>";
    $retur .= test_HamtaEnUppgift();
    $retur .= test_HamtaUppgifterSida();
    $retur .= test_RaderaUppgift();
    $retur .= test_SparaUppgift();
    $retur .= test_UppdateraUppgifter();
    return $retur;
}

/**
 * Tester för funktionen hämta uppgifter för ett angivet sidnummer
 * @return string html-sträng med alla resultat för testerna 
 */
function test_HamtaUppgifterSida(): string {
    $retur = "<h2>test_HamtaUppgifterSida</h2>";
    try {
    // misslyckad med att hämta sida -1
    $svar= hamtaSida("-1");
    if($svar->getStatus()===400)    {
        $retur .="<p class='ok'>Misslyckades med att hämta sida -1 som förväntas</p>";
    } else {
        $retur .="<p class='ok'>Misslyckades med att hämta sida -1<br>"
            . $svar->getStatus() . " returneras instället för förväntat 400</p>";
    }

    // misslyckad med att hämta sida 0
    $svar= hamtaSida("0");
    if($svar->getStatus()===400)    {
        $retur .="<p class='ok'>Misslyckades med att hämta sida 0 som förväntas</p>";
    } else {
        $retur .="<p class='ok'>Misslyckades med att hämta sida 0<br>"
            . $svar->getStatus() . " returneras instället för förväntat 400</p>";
    }

    // misslyckad med att hämta sida sju
    $svar= hamtaSida("sju");
    if($svar->getStatus()===400)    {
        $retur .="<p class='ok'>Misslyckades med att hämta sida <i>sju</i> som förväntas</p>";
    } else {
        $retur .="<p class='ok'>Misslyckades med att hämta sida <i>sju</i><br>"
            . $svar->getStatus() . " returneras instället för förväntat 400</p>";
    }

    // lyckad med att hämta sida 1
    $svar= hamtaSida("1",2);
    if($svar->getStatus()===200)    {
        $retur .="<p class='ok'>Misslyckades med att hämta sida 1 som förväntas</p>";
        $sista=$svar->getContent()->pages;
    } else {
        $retur .="<p class='ok'>Misslyckades med att hämta sida 1<br>"
            . $svar->getStatus() . " returneras instället för förväntat 400</p>";
    }

    // misslyckad med att hämta sida > antal sidor
    if(isset($sista)) {
        $sista++;
        $svar= hamtaSida("$sista",2);
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades med att hämta sida > antal sidor, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Misslyckadat test att hämta sida > antal sidor<br>"
            . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }
    }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen hämta uppgifter mellan angivna datum
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaAllaUppgifterDatum(): string {
    $retur = "<h2>test_HamtaAllaUppgifterDatum</h2>";
    try {

        // misslyckas med från=igår till=2024-01-01
        $svar= hamtaDatum("igår", "2024-01-01");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckas med att hämta poster mellan <i>igår</i> och 2024-01-01 som förväntat</p>";
        } else {
            $retur .="<p class='error'>misslyckas test med att hämta poster mellan <i>igår</i> och 2024-01-01<br>"
                . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }

        // misslyckas med från=2024-01-01 till=imorgon
        $svar= hamtaDatum("2024-01-01", "imorgon");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckas med att hämta poster mellan 2024-01-01 och <i>imorgon</i> som förväntat</p>";
        } else {
            $retur .="<p class='error'>misslyckas test med att hämta poster mellan 2024-01-01 och <i>imorgon</i><br>"
                . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }

        // misslyckas med från=2024-12-37 till=2024-01-01
        $svar= hamtaDatum("2024-23-37", "2024-01-01");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckas med att hämta poster mellan 2024-23-37 och 2024-01-01 som förväntat</p>";
        } else {
            $retur .="<p class='error'>misslyckas test med att hämta poster mellan 2024-23-37 och 2024-01-01<br>"
                . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }

        // misslyckas med från=2024-01-01 till=2024-12-37
        $svar= hamtaDatum("2024-01-01", "2024-12-37");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckas med att hämta poster mellan 2024-01-01 och 2024-12-37 som förväntat</p>";
        } else {
            $retur .="<p class='error'>misslyckas test med att hämta poster mellan 2024-01-01 och 2024-12-37<br>"
                . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }

        // misslyckas med från=2024-01-01 till=2023-01-01
        $svar= hamtaDatum("2024-01-01", "2023-01-01");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckas med att hämta poster mellan 2024-01-01 och 2023-01-01 som förväntat</p>";
        } else {
            $retur .="<p class='error'>misslyckas test med att hämta poster mellan 2024-01-01 och 2023-01-01<br>"
                . $svar->getStatus() . " returneras instället för förväntat 400</p>";
        }

        // lyckas med korrekta datum
        // leta upp en månad med poster
        $db= connectDb();
        $stmt=$db->query("SELECT YEAR(datum), MONTH(datum), COUNT(*) AS antal "
            . "FROM uppgifter "
            . "GROUP BY YEAR(datum),MONTH(datum) "
            . "ORDER BY antal DESC "
            . "LIMIT 0,1");
        $row=$stmt->fetch();
        $ar= $row[0];
        $manad=substr("0$row[1]",-2);
        $antal= $row[2];

        // hämta alla poster från denna månad

        $svar= hamtaDatum("$ar-$manad-01", date("Y-m-d", strtotime("Last day of $ar-$manad", )));
        if($svar->getStatus()===200 && count($svar->getContent()->tasks)===$antal)   {
            $retur .="<p class='ok'>Lyckades hämta $antal poster för månad $ar-$manad</p>";
        } else {
            $retur .="<p class='error'>Misslyckades med att hämta $antal poster för $ar-$manad<br>"
                . $svar->getStatus() . " returneras instället för förväntat 200<br>"
                . print_r($svar->getContent(), true) . "</p>";
        }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test av funktionen hämta enskild uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_HamtaEnUppgift(): string {
    $retur = "<h2>test_HamtaEnUppgift</h2>";

    try {

        // Misslyckas med att hämta id=0
        $svar= hamtaEnskildUppgift("0");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=0, som förväntat</p>";
        } else {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=0<br> "
            . $svar->getStatus() . " returneras instället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }

        // Misslyckas med att hämta id=sju
        $svar= hamtaEnskildUppgift("sju");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=sju, som förväntat</p>";
        } else {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=sju<br> "
            . $svar->getStatus() . " returneras instället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }

        // Misslyckas med att hämta id=3.14
        $svar= hamtaEnskildUppgift("3.14");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=3.14, som förväntat</p>";
        } else {
            $retur .="<p class='ok'>Misslyckades hämta uppgift med id=3.14<br> "
            . $svar->getStatus() . " returneras instället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }

        // koppla databas och transaction
        $db= connectDb();
        $db->beginTransaction();

        // förbered data
        $content= hamtaAllaAktiviteter()->getContent();
        $aktiviteter=$content["activities"];
        $aktivitetid=$aktiviteter[0]->id;
        $postdata=["date"=> date("Y-m-d"),
            "time"=>"01:00",
            "description"=>"testa",
            "activityId"=>"$aktivitetid"];

        // skapa post
        $svar= sparaNyUppgift($postdata);
        $taskId=$svar->getContent()->id;

        // hämta nyss skapad post
        $svar= hamtaEnskildUppgift("$taskId");
        if($svar->getStatus()===200)    {
            $retur .="<p class='ok'>lyckades med att hämta en uppgift</p>";
        } else {
            $retur .="<p class='error'>misslyckades med att hämta ny skapd uppgift<br> "
            . $svar->getStatus() . " returneras instället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }

        //misslyckas med att hämta id som inte finns
        $taskId++;
        $svar= hamtaEnskildUppgift("$taskId");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>misslyckades med att hämta en uppgift som inte finns</p>";
        } else {
            $retur .="<p class='error'>misslyckades med att hämta uppgift som inte finns<br> "
            . $svar->getStatus() . " returneras instället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . "</p>";
        }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollback();
        }
    }
    return $retur;
}

/**
 * Test för funktionen spara uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_SparaUppgift(): string {
    $retur = "<h2>test_SparaUppgift</h2>";

    try {
        $db= connectDb();
        // skapar en tarnsaktion så att man inte får skräp i databasen
        $db->beginTransaction();

        // misslyckas med att spara pga saknad datum
        $postdata=["time"=>"01:00",
            "date"=>"2023-12-31",
            "description"=>"Detta är en testpost"];

            $svar= sparaNyUppgift($postdata);
            if($svar->getStatus()===400)    {
                $retur .="<p class='ok'>misslyckas med att spara post utan aktivitetsID, som förväntat</p>";
            } else {
                $retur .="<p class='error'>misslyckas med att spara post utan aktivitetsID<br>"
                . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
                . print_r($svar->getContent(), true)    . "</p>";
            }

        // lyckas med att spara post utan beskrivning
        $content= hamtaAllaAktiviteter()->getContent();
        $aktiviteter=$content["activities"];
        $aktivitetid=$aktiviteter[0]->id;
        $postdata=["time"=>"01:00",
            "date"=>"2023-12-31",
            "activityId"=>"$aktivitetid"];

        // testa
        $svar= sparaNyUppgift($postdata);
        if($svar->getStatus()===200)    {
            $retur .="<p class='ok'>Lyckades spara uppgift utan beskrivning</p>";
        } else {
            $retur .="<p class='error'>missLyckades spara uppgift utan beskrivning<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true) . print_r($postdata,true)   . "</p>";
        }

        // lyckas spara post med alla uppgifter
        $postdata["description"]="detta är en testpost";
        $svar= sparaNyUppgift($postdata);
        if($svar->getStatus()===200)    {
            $retur .="<p class='ok'>Lyckades spara uppgift med alla uppgifter</p>";
        } else {
            $retur .="<p class='error'>missLyckades spara uppgift med alla uppgifter<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollback();
        }
    }

    return $retur;
}

/**
 * Test för funktionen uppdatera befintlig uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_UppdateraUppgifter(): string {
    $retur = "<h2>test_UppdateraUppgifter</h2>";
    try {
        // skapar en tarnsaktion och koplla databas så att man inte får skräp i databasen
        $db= connectDb();
        $db->beginTransaction();

        // hämta postdata
        $svar= hamtaSida("1");
        if($svar->getStatus()!=200) {
            throw new Exception("kunde inte hämta poster för test av uppdatera uppgift");
        }
        $aktiviteter=$svar->getContent()->tasks;

        // misslyckas med ogiltigt id=0
        $postData=get_object_vars($aktiviteter[0]);
        $svar= uppdateraUppgift("0", $postData);
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades med att hämta post med ID=0, som förväntat</p>";
        } else  {
            $retur .="<p class='error'>Misslyckat test med att hämta post med ID=0<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med ogiltigt id=sju
        $svar= uppdateraUppgift("sju", $postData);
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades med att hämta post med ID=sju, som förväntat</p>";
        } else  {
            $retur .="<p class='error'>Misslyckat test med att hämta post med ID=sju<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med ogiltigt id=3.14
        $svar= uppdateraUppgift("3.14", $postData);
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckades med att hämta post med ID=3.14, som förväntat</p>";
        } else  {
            $retur .="<p class='error'>Misslyckat test med att hämta post med ID=3.14<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // lyckas med id som finns
        $id=$postData["id"];
        $postData["activityId"]=(string) $postData["activityId"];
        $postData["description"] = $postData["description"] . "(Uppdaterad)";
        $svar= uppdateraUppgift ("$id", $postData);
        if($svar->getStatus()===200 && $svar->getContent()->result===true)    {
            $retur .="<p class='ok'>uppdatera uppgift med id som finns lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>uppdatera uppgift med id som finns misslyckades<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med samma data
        $svar= uppdateraUppgift ("$id", $postData);
        if($svar->getStatus()===200 && $svar->getContent()->result===false)    {
            $retur .="<p class='ok'>uppdatera uppgift med samma data misslyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>uppdatera uppgift med samma data misslyckades<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med felaktig indata
        $postData["time"]="09:70";
        $svar= uppdateraUppgift("$id", $postData);
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckas med att uppdatera post med felaktig indata, som förväntas</p>";
        } else {
            $retur .="<p class='error'>Misslyckas med att uppdatera post med felaktig indata<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // lyckas med saknad beskrivning
        $postData["time"]="01:30";
        unset($postData["description"]);
        $svar= uppdateraUppgift("$id", $postData);
        if($svar->getStatus()===200)    {
            $retur .="<p class='ok'>uppdatera post med saknad description lyckades</p>";
        } else {
            $retur .="<p class='error'>uppdatera post med saknad description misslyckades<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        if($db) {
            $db->rollBack();
        }
    }

    return $retur;
}

function test_KontrolleraIndata(): string {
    $retur = "<h2>test_KontrolleraIndata</h2>";
    try {
        
        // testa alla saknas
        $postData=[];
        $svar= kontrolleraindata($postData);
        if(count($svar)===3)    {
            $retur .="<p class='ok'>testa alla element saknas lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>testa alla element saknas misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 3<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test datum finns
        $postData["date"]=date("Y-m-d");
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test alla element saknas utom datum lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test alla element saknas utom datum lyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test tid finns
        $postData["time"]="08:00";
        $svar= kontrolleraindata($postData);
        if(count($svar)===1)    {
            $retur .="<p class='ok'>Test tid lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test tid misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 1<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test description saknas
        $postData["description"]="en snabb grej";
        $svar= kontrolleraindata($postData);
        if(count($svar)===1)    {
            $retur .="<p class='ok'>Test description saknas lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test description saknas lyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 1<br>"
            . print_r($svar, true)    . "</p>";
        }
        
        // Test alla element finns lyckades
        $postData["description"]="random sak";
        $svar= kontrolleraindata($postData);
        if(count($svar)===1)    {
            $retur .="<p class='ok'>Test alla element saknas lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test alla element saknas misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 1<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktigt datum 27.3.2023 lyckades
        $postData["time"]="27.03.2023";
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test felaktigt datum 27.03.2023 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktigt datum 27.03.2023 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktigt datum 2023-05-37 lyckades
        $postData["time"]="37.05.2023";
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test felaktigt datum 37.05.2023 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktigt datum 37.05.2023 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktigt datum 2024-01-30 lyckades
        $postData["time"]="30.01.2024";
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test felaktigt datum 30.01.2024 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktigt datum 30.01.2024 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktig tid 1:00 lyckades
        $postData["time"]="01:00";
        $svar= kontrolleraindata($postData);
        if(count($svar)===1)    {
            $retur .="<p class='ok'>Test felaktig tid 01:00 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktig tid 01:00 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 1<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktig tid 01:79 lyckades
        $postData["time"]="01:79";
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test felaktig tid 01:79 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktig tid 01:79 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktig tid 17:30 lyckades
        $postData["time"]="17:30";
        $svar= kontrolleraindata($postData);
        if(count($svar)===2)    {
            $retur .="<p class='ok'>Test felaktig tid 17:30 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktig tid 17:30 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 2<br>"
            . print_r($svar, true)    . "</p>";
        }

        // Test felaktigt aktivitetId 0 lyckades
        $postData["activityId"]="0";
        $svar= kontrolleraindata($postData);
        if(count($svar)===0)    {
            $retur .="<p class='ok'>Test felaktigt aktivitetId 0 lyckades, som förväntat</p>";
        } else {
            $retur .="<p class='error'>Test felaktigt aktivitetId 0 misslyckades<br>"
            . count($svar) . " felmeddelande istället för förväntat 1<br>"
            . print_r($svar, true)    . "</p>";
        }
    

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    }

    return $retur;
}

/**
 * Test för funktionen radera uppgift
 * @return string html-sträng med alla resultat för testerna
 */
function test_RaderaUppgift(): string {
    $retur = "<h2>test_RaderaUppgift</h2>";
    try {
        // skapa transaction
        $db= connectDb();
        $db->beginTransaction();

        // misslyckas med att radera post med id=sju
        $svar= raderaUppgift("sju");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckas med att radera post med id=sju, som förväntas</p>";
        } else {
            $retur .="<p class='error'>Misslyckas med att radera post med id=sju<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med att radera post med id=0.1
        $svar= raderaUppgift("0.1");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckas med att radera post med id=0.1, som förväntas</p>";
        } else {
            $retur .="<p class='error'>Misslyckas med att radera post med id=0.1<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med att radera post med id=0
        $svar= raderaUppgift("0");
        if($svar->getStatus()===400)    {
            $retur .="<p class='ok'>Misslyckas med att radera post med id=0, som förväntas</p>";
        } else {
            $retur .="<p class='error'>Misslyckas med att radera post med id=0<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 400<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        /* Lyckas med att radera post som finns */

        // hämta poster
        $poster= hamtaSida("1");
        if($poster->getStatus()!==200)  {
            throw new Exception("kunde inte hämta poster");
        }
        $uppgifter=$poster->getContent()->tasks;

        // ta fram id från första posten
        $testId=$uppgifter[0]->id;

        // lyckas radera id för första posten
        $svar= raderaUppgift("$testId");
        if($svar->getStatus()===200 && $svar->getContent()->result===true)    {
            $retur .="<p class='ok'>Lyckades radera post, som förväntas</p>";
        } else {
            $retur .="<p class='error'>misslyckades radera post<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

        // misslyckas med att radera samma id som tidigare
        $svar= raderaUppgift("$testId");
        if($svar->getStatus()===200 && $svar->getContent()->result===false)    {
            $retur .="<p class='ok'>missLyckades radera post som inte finns, som förväntas</p>";
        } else {
            $retur .="<p class='error'>misslyckades radera post som inte finns<br>"
            . $svar->getStatus(). " returnerades istället för förväntat 200<br>"
            . print_r($svar->getContent(), true)    . "</p>";
        }

    } catch (Exception $ex) {
        $retur .= "<p class='error'>Något gick fel, meddelandet säger:<br> {$ex->getMessage()}</p>";
    } finally {
        // avlsuta transaktion
        if($db) {
            $db->rollBack();
        }
    }

    return $retur;
}