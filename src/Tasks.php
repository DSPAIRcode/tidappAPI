<?php

declare (strict_types=1);
require_once __DIR__ . '/Activities.php';

/**
 * Hämtar en lista med alla uppgifter och tillhörande aktiviteter 
 * Beroende på indata returneras en sida eller ett datumintervall
 * @param Route $route indata med information om vad som ska hämtas
 * @return Response
 */
function tasklists(Route $route): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaSida($route->getParams()[0]);
        }
        if (count($route->getParams()) === 2 && $route->getMethod() === RequestMethod::GET) {
            return hamtaDatum($route->getParams()[0], $route->getParams()[1]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }

    return new Response("Okänt anrop", 400);
}

/**
 * Läs av rutt-information och anropa funktion baserat på angiven rutt
 * @param Route $route Rutt-information
 * @param array $postData Indata för behandling i angiven rutt
 * @return Response
 */
function tasks(Route $route, array $postData): Response {
    try {
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::GET) {
            return hamtaEnskildUppgift($route->getParams()[0]);
        }
        if (count($route->getParams()) === 0 && $route->getMethod() === RequestMethod::POST) {
            return sparaNyUppgift($postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::PUT) {
            return uppdateraUppgift( $route->getParams()[0], $postData);
        }
        if (count($route->getParams()) === 1 && $route->getMethod() === RequestMethod::DELETE) {
            return raderaUppgift($route->getParams()[0]);
        }
    } catch (Exception $exc) {
        return new Response($exc->getMessage(), 400);
    }
}

/**
 * Hämtar alla uppgifter för en angiven sida
 * @param string $sida
 * @return Response
 */
function hamtaSida(string $sida, int $posterPerSida=10) : Response {

    // kontrollera indata
    $sidnummer= filter_var($sida, FILTER_VALIDATE_INT);
    if($sidnummer===false || $sidnummer<1)    {
        $retur=new stdClass();
        $retur->error=["Bad request", "Felaktigt angivet sidnummer"];
        return new Response($retur, 400);
    }
    // Koppla databas
    $db= connectDb();

    // Hämta poster
    $stmt=$db->query("SELECT COUNT(*) FROM uppgifter");
    $antalPoster=$stmt->fetchColumn();
    if(!$antalPoster)    {
        $retur=new stdClass();
        $retur->error=["Inga poster kunde hittas"];
        return new Response($retur, 400);
    }

    $antalSidor=ceil($antalPoster/$posterPerSida);
    if($sidnummer>$antalSidor)  {
        $retur = new stdClass();
        $retur->error=["Bad request", "Felaktigt sidnummer", "det finns bara $antalSidor sidor"];
        return new Response($retur, 400);
    }

    $forstaPost=($sidnummer-1)*$posterPerSida;
    $stmt=$db->query("SELECT u.id, datum, tid, beskrivning, aktivitetid, namn "
        . "FROM uppgifter u INNER JOIN aktiviteter a ON aktivitetid=a.id "
        . "ORDER BY datum "
        . "LIMIT $forstaPost, $posterPerSida");
    $result=$stmt->fetchAll();

    $uppgifter=[];
    foreach ($result as $row)   {
        $rad=new stdClass();
        $rad->id=$row["id"];
        $rad->activityId=$row["aktivitetid"];
        $rad->date=$row["datum"];
        $tid=new DateTime($row["tid"]);
        $rad->time=$tid->format("H:i");
        $rad->activity=$row["namn"];
        $rad->description=$row["beskrivning"];
        $uppgifter[]=$rad;
    }

    // returnena svar
    $retur=new stdClass();
    $retur->pages=$antalSidor;
    $retur->tasks=$uppgifter;

    return new Response($retur);
}

/**
 * Hämtar alla poster mellan angivna datum
 * @param string $from
 * @param string $tom
 * @return Response
 */
function hamtaDatum(string $from, string $tom): Response {
    // kontrollera indata
    $fromDate= DateTimeImmutable::createFromFormat("Y-m-d", $from);
    $tomDate= DateTimeImmutable::createFromFormat("Y-m-d", $tom);
    $datumFel=[];

    if($fromDate===false)   {
        $datumFel[]="Ogiltig från-datum";
    }
    if($tomDate===false)   {
        $datumFel[]="Ogiltig till-datum";
    }
    if($fromDate && $fromDate->format("Y-m-d")!==$from)  {
        $datumFel[]="Ogiltig angivet från-datum";
    }
    if($tomDate && $tomDate->format("Y-m-d")!==$tom)  {
        $datumFel[]="Ogiltig angivet till-datum";
    }
    if($fromDate && $tomDate && $fromDate->format("Y-m-d")>$tomDate->format("Y-m-d"))  {
        $datumFel[]="från-datum får inte vara större än till-datum";
    }

    if(count($datumFel)>0) {
        $retur= new stdClass();
        $retur->error=$datumFel;
        array_unshift($retur->error, "Bad request");
        return new Response($retur, 400);
    }

    // koppla databas
    $db= connectDb();

    //exekvera SQL
    $stmt=$db->prepare("SELECT u.id, datum, tid, beskrivning, aktivitetid, namn "
                . "FROM uppgifter u INNER JOIN aktiviteter a ON aktivitetid=a.id "
                . "WHERE datum BETWEEN :from AND :to "
                . "ORDER BY datum ");
    $stmt->execute(["from"=>$fromDate->format("Y-m-d"), "to"=>$tomDate->format("Y-m-d")]);
    $result=$stmt->fetchAll();

    $uppgifter=[];
    foreach ($result as $row)   {
        $rad=new stdClass();
        $rad->id=$row["id"];
        $rad->activityId=$row["aktivitetid"];
        $rad->date=$row["datum"];
        $tid=new DateTime($row["tid"]);
        $rad->time=$tid->format("H:i");
        $rad->activity=$row["namn"];
        $rad->description=$row["beskrivning"];
        $uppgifter[]=$rad;
    }

    // returnera svar
    $retur=new stdClass();
    $retur->tasks=$uppgifter;
    return new Response($retur);

}

/**
 * Hämtar en enskild uppgiftspost
 * @param string $id Id för post som ska hämtas
 * @return Response
 */
function hamtaEnskildUppgift(string $id): Response {
    // kontrollrea indata
    $kontrolleratId= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kontrolleratId)    {
        $retur= new stdClass();
        $retur->error=["Bad Request", "felaktig angivet id"];
        return new Response($retur, 400);
    }

    if($kontrolleratId && $kontrolleratId<1)    {
        $retur= new stdClass();
        $retur->error=["Bad Request", "ogiltigt id"];
        return new Response($retur, 400);
    }

    // kopla databas
    $db= connectDb();

    // exekvera SQL
    $stmt=$db->prepare("SELECT u.id, tid, datum, beskrivning, aktivitetid, namn "
        . "FROM uppgifter u INNER JOIN aktiviteter a ON aktivitetId=a.id "
        . "WHERE u.id=:id");
    $stmt->execute(["id"=>$kontrolleratId]);
    
    // retur svar
    if($row=$stmt->fetch()) {
        $retur=new stdClass();
        $retur->id=$row["id"];
        $retur->date=$row["datum"];
        $retur->time=substr($row["tid"], 0,-3);
        $retur->activity=$row["namn"];
        $retur->activityId=$row["aktivitetid"];
        return new Response($retur);
    } else {
        $retur=new stdClass();
        $retur->error=["Hämta misslyckades", "Kunde inte hitta uppgift med angivet id"];
        return new Response($retur, 400);
    }
}

/**
 * Sparar en ny uppgiftspost
 * @param array $postData indata för uppgiften
 * @return Response
 */
function sparaNyUppgift(array $postData): Response {
    // kontrollera indata
    $felMeddelande=kontrolleraindata($postData);

    if(count($felMeddelande)>0) {
        $retur= new stdClass();
        $retur->error=$felMeddelande;
        array_unshift($retur->error, "Bad Request");
        return new Response($retur, 400);
    }

    // Koppla databas
    $db= connectDb();

    // Exekvera databasfråga
    $stmt=$db->prepare("INSERT INTO uppgifter (datum, tid, beskrivning, aktivitetid) "
        . "VALUES (:datum, :tid, :beskrivning, :aktivitetid)");
    $stmt->execute(["datum"=>$postData["date"], "tid"=>$postData["time"],
        "beskrivning"=> trim(filter_var($postData["description"]??"", FILTER_SANITIZE_SPECIAL_CHARS)),
        "aktivitetid"=>$postData["activityId"]]);

    // kontrollera svaret
    if($stmt->rowCount()===1)   {
        $retur=new stdClass();
        $retur->id=$db->lastInsertId();
        $retur->message=["skapa ny post lyckades", "1 post sparad"];
        return new Response($retur);
    } else {
        $retur=new stdClass();
        $retur->error=["Fel vid databasanrop", "Kunde inte skapa post"];
        return new Response($retur, 400);
    }
}

/**
 * Uppdaterar en angiven uppgiftspost med ny information 
 * @param string $id id för posten som ska uppdateras
 * @param array $postData ny data att sparas
 * @return Response
 */
function uppdateraUppgift(string $id, array $postData): Response {
    // kontrollera indata
    // kontrollera ID
    $kontrolleraId= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kontrolleraId) {
        $retur=new stdClass();
        $retur->error=["Bad request", "Felaktig ID"];
        return new Response($retur, 400);
    }

    if($kontrolleraId<1) {
        $retur=new stdClass();
        $retur->error=["Bad request", "Ogiltigt ID"];
        return new Response($retur, 400);
    }

    // kontrollera postdata
    $error=kontrolleraindata($postData);
    if(count($error)!==0)   {
        $retur=new stdClass();
        $retur->error=$error;
        return new Response($retur, 400);
    }

    // connect databas
    $db= connectDb();

    // exekvera databas
    $stmt=$db->prepare("UPDATE uppgifter SET "
        . "datum=:date, tid=:time, aktivitetid=:activityid, beskrivning=:description "
        . "WHERE id=:id");
    $stmt->execute(["date"=>$postData["date"], "time"=>$postData["time"], "activityid"=>$postData["activityId"],
        "description"=>$postData["description"]?? "", "id"=>$kontrolleraId]);

    // Returnera svar
    if($stmt->rowCount()===1)   {
        $retur=new stdClass();
        $retur->result=true;
        $retur->message=["Uppdatering lyckades", "1 post uppdaterad"];
    } else {
        $retur=new stdClass();
        $retur->result=false;
        $retur->message=["Uppdatering misslyckades", "ingen post uppdaterad"];
    }

    return new Response($retur);
}

/**
 * Raderar en uppgiftspost
 * @param string $id Id för posten som ska raderas
 * @return Response
 */
function raderaUppgift(string $id): Response {
    // kontrollera indata
    $kontrolleratId= filter_var($id, FILTER_VALIDATE_INT);
    if(!$kontrolleratId)    {
        $retur= new stdClass();
        $retur->error=["Bad Request", "felaktig angivet id"];
        return new Response($retur, 400);
    }

    if($kontrolleratId && $kontrolleratId<1)    {
        $retur= new stdClass();
        $retur->error=["Bad Request", "ogiltigt id"];
        return new Response($retur, 400);
    }

    // koppla databas
    $db= connectDb();

    // exekvera datasfråga
    $stmt=$db->prepare("DELETE FROM uppgifter WHERE id=:id");
    $stmt->execute(["id"=>$kontrolleratId]);

    // returnera svar
    if($stmt->rowCount()===1)   {
    $retur=new stdClass();
        $retur->result=true;
        $retur->message=["radering lyckades", "1 post raderad"];
    } else {
        $retur=new stdClass();
        $retur->result=false;
        $retur->message=["radering misslyckades", "ingen post raderad"];
    }

    return new Response($retur);
}
